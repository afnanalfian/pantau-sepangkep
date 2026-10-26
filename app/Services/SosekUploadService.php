<?php

namespace App\Services;

use App\Models\AnomaliMikro;
use App\Models\SlsDaily;
use App\Models\SosekBatch;
use App\Models\SosekMikro;
use App\Support\NamaPetugas;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class SosekUploadService
{
    /** @var array<string, mixed> */
    public array $ringkasan = [];

    /**
     * Semua kolom dari 7 template, dipetakan ke satu struktur.
     * Kolom yang tidak ada di suatu file -> null.
     */
    protected const SPEC = [
        'assignment_id' => ['assignment_id'],
        'link_fasih_sm' => ['link_fasih_sm', 'link_fasih', 'link'],
        'level_3' => ['level_3_full_code'],
        'level_4' => ['level_4_full_code'],
        'level_6' => ['level_6_full_code', 'id_subsls', 'idsubsls'],
        'nama_kk' => ['nama_kk'],
        'no_kk' => ['no_kk', 'nomor_kk'],
        'kode_prov_kk' => ['kode_prov_kk'],
        'nik' => ['nik_dtsen', 'nik'],
        'nama_art' => ['nama_dtsen'],
        'no_urut' => ['no_urut_kk', 'index1'],
        'umur' => ['umur_ak', 'umur'],
        'keberadaan_label' => ['keberadaan_dtsen_label'],
        'keberadaan_value' => ['keberadaan_dtsen_value'],
        'kode_profesi' => ['kode_profesi'],
        'profesi_label' => ['profesi_label'],
        'tgl_lahir' => ['tgl_lahir'],
        'bln_lahir_label' => ['bln_lahir_label'],
        'bln_lahir_value' => ['bln_lahir_value'],
        'thn_lahir' => ['thn_lahir'],
        'jml_meteran' => ['jml_meteran'],
        'daya_label' => ['daya_terpasang_label'],
        'daya_value' => ['daya_terpasang_value'],
        'jumlah_ac' => ['jumlah_ac'],
        'sumber_penerangan' => ['sumber_penerangan_label', 'sumber_penerangan_value', 'sumber_penerangan'],
        'jumlah_mobil' => ['jumlah_mobil_efektif', 'jumlah_mobil_new', 'jumlah_mobil'],
        'nilai_mobil' => ['nilai_mobil'],
        'jumlah_duplikasi' => ['jumlah_duplikasi_nasional', 'jumlah_duplikasi'],
        'daftar_remark' => ['daftar_remark'],
        'running_time' => ['running_time'],
    ];

    // =====================================================================
    // IMPORT
    // =====================================================================

    /**
     * Import SATU workbook berisi banyak sheet (satu sheet per jenis anomali).
     * Setiap sheet dikenali otomatis dari kolom khas & judulnya (urutan/nama sheet bebas).
     * Tanggal sama diunggah ulang -> jenis yang ada di workbook diganti, jenis lain tetap.
     */
    public function importWorkbook(UploadedFile $file, string $tanggal, ?string $uploadedBy = null): SosekBatch
    {
        $aktif = SosekMikro::jenisOptions();
        $parsed = [];          // jenis => records
        $sheetJenis = [];      // jenis => [nama sheet]
        $dilewati = [];        // nama sheet => alasan

        foreach ($this->readSheets($file) as $namaSheet => $rows) {
            [$headerIdx, $colMap, $slugs] = $this->mapColumns($rows, $namaSheet, false);
            if ($headerIdx === null) {
                $dilewati[$namaSheet] = 'header tidak dikenali';
                continue;
            }

            $judul = $this->judulSheet($rows, $headerIdx, $namaSheet);
            $jenis = $this->kenaliJenis($slugs, $judul);

            if ($jenis === null) {
                $dilewati[$namaSheet] = 'jenis anomali tidak dikenali';
                continue;
            }
            if (!isset($aktif[$jenis])) {
                $dilewati[$namaSheet] = 'jenis "' . SosekMikro::semuaJenis()[$jenis]['label'] . '" dinonaktifkan';
                continue;
            }

            $records = $this->parseRows($rows, $headerIdx, $colMap, $jenis, $namaSheet);
            $parsed[$jenis] = array_merge($parsed[$jenis] ?? [], $records);
            $sheetJenis[$jenis][] = $namaSheet;
        }

        if (empty($parsed)) {
            throw new \Exception('Tidak ada sheet anomali yang dikenali di file ' . $file->getClientOriginalName() . '.'
                . ($dilewati ? ' Sheet: ' . implode('; ', array_map(fn ($k, $v) => "$k ($v)", array_keys($dilewati), $dilewati)) : ''));
        }

        // nomor urut ulang per jenis (bila satu jenis tersebar di beberapa sheet)
        foreach ($parsed as $jenis => &$records) {
            foreach ($records as $i => &$r) $r['no'] = $i + 1;
            unset($r);
        }
        unset($records);

        $batch = DB::transaction(function () use ($parsed, $file, $tanggal, $uploadedBy, $sheetJenis) {
            $batch = SosekBatch::firstOrNew(['tanggal' => $tanggal]);
            $nama = $batch->nama_file ?? [];
            foreach ($sheetJenis as $jenis => $sheets) {
                $nama[$jenis] = $file->getClientOriginalName() . ' › ' . implode(', ', $sheets);
            }
            $batch->nama_file = $nama;
            $batch->uploaded_by = $uploadedBy;
            $batch->save();

            foreach ($parsed as $jenis => $records) {
                SosekMikro::where('sosek_batch_id', $batch->id)->where('jenis', $jenis)->delete();
                foreach (array_chunk($records, 500) as $chunk) {
                    SosekMikro::insert(array_map(fn ($r) => $r + ['sosek_batch_id' => $batch->id], $chunk));
                }
                $this->ringkasan['per_jenis'][$jenis] = count($records);
            }

            return $batch;
        });

        $this->ringkasan['dilewati'] = $dilewati;
        $this->ringkasan['pic'] = $this->lengkapiWilayahDanPic($batch, $tanggal);

        return $batch->fresh();
    }

    /**
     * Tentukan jenis anomali sebuah sheet.
     * Urutan penting: kolom yang paling khas diperiksa lebih dulu.
     *
     * @param  array<int,string>  $slugs  header sheet (sudah di-slug)
     */
    public function kenaliJenis(array $slugs, string $judul): ?string
    {
        $ada = fn (string ...$kolom) => (bool) array_intersect(array_map(fn ($k) => $this->slug($k), $kolom), $slugs);
        $j = mb_strtolower($judul);
        $judulMemuat = fn (string ...$kata) => !array_filter($kata, fn ($k) => !str_contains($j, $k));

        return match (true) {
            $ada('jumlah_duplikasi_nasional') => 'nik_duplikat',
            $ada('sumber_penerangan_value', 'sumber_penerangan_label', 'jumlah_mobil', 'jumlah_mobil_efektif') => 'penerangan_mobil',
            $ada('jumlah_ac') => 'ac_450',
            $ada('kode_profesi', 'profesi_label') => 'profesi',
            $ada('umur_ak') => 'umur',
            $ada('kode_prov_kk') => 'kk_prov',
            $ada('tgl_lahir', 'thn_lahir') => 'nik_kembar',
            $ada('nik_dtsen') => $judulMemuat('kembar') ? 'nik_kembar' : 'nik_prov',
            $ada('no_kk') => $judulMemuat('provinsi') ? 'kk_prov' : 'kk_kembar',
            // cadangan: hanya dari judul
            $judulMemuat('penerangan') => 'penerangan_mobil',
            $judulMemuat('450') => 'ac_450',
            $judulMemuat('profesi') => 'profesi',
            $judulMemuat('umur') => 'umur',
            $judulMemuat('duplikat') => 'nik_duplikat',
            default => null,
        };
    }

    /** Judul tabel = baris di atas header yang berisi satu teks (mis. "R.3 Keluarga dengan ..."). */
    protected function judulSheet(array $rows, int $headerIdx, string $namaSheet): string
    {
        for ($i = $headerIdx - 1; $i >= 0; $i--) {
            $isi = array_values(array_filter($rows[$i] ?? [], fn ($v) => $v !== null && trim((string) $v) !== ''));
            if (count($isi) >= 1) return (string) $isi[0];
        }

        return $namaSheet;
    }

    /** @return list<array<string, mixed>> */
    protected function parseRows(array $rows, int $headerIdx, array $colMap, string $jenis, string $namaSheet): array
    {
        $records = [];
        $now = now();

        for ($i = $headerIdx + 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            if ($this->isRowEmpty($row)) continue;

            $g = fn (string $f) => $this->cell($row, $colMap[$f] ?? null);

            $link = $g('link_fasih_sm');
            $assignment = $g('assignment_id') ?? $this->assignmentDariLink($link);
            if ($assignment === null && $link === null) continue;

            $region = $this->digits($g('level_6'));
            $region = strlen($region) >= 14 ? substr($region, 0, 16) : null;
            $kdkec = $this->digits($g('level_3')) ?: ($region ? substr($region, 0, 7) : null);
            $kddesa = $this->digits($g('level_4')) ?: ($region ? substr($region, 0, 10) : null);

            $rec = [
                'jenis' => $jenis,
                'sheet' => mb_substr($namaSheet, 0, 100),
                'no' => 0,
                'assignment_id' => $assignment,
                'link_fasih_sm' => $link,
                'kdkec' => $kdkec,
                'kddesa' => $kddesa,
                'region_code' => $region,
                'sumber_wilayah' => $region ? 'file' : null,
                'nama_kk' => $g('nama_kk'),
                'no_kk' => $this->digits($g('no_kk')) ?: null,
                'nik' => $this->digits($g('nik')) ?: null,
                'nama_art' => $g('nama_art'),
                'no_urut' => $g('no_urut'),
                'umur' => is_numeric($u = $g('umur')) ? (int) $u : null,
                'keberadaan' => $g('keberadaan_label') ?? $g('keberadaan_value'),
                'profesi' => $g('profesi_label') ?? $g('kode_profesi'),
                'tgl_lahir' => $this->tanggalLahir($g('tgl_lahir'), $g('bln_lahir_label') ?? $g('bln_lahir_value'), $g('thn_lahir')),
                'daya_terpasang' => $g('daya_label') ?? $g('daya_value'),
                'jml_meteran' => is_numeric($m = $g('jml_meteran')) ? (int) $m : null,
                'jumlah_ac' => is_numeric($a = $g('jumlah_ac')) ? (int) $a : null,
                'sumber_penerangan' => $g('sumber_penerangan'),
                'jumlah_mobil' => is_numeric($mb = $g('jumlah_mobil')) ? (int) $mb : null,
                'nilai_mobil' => is_numeric($nm = $g('nilai_mobil')) ? (float) $nm : null,
                'jumlah_duplikasi' => is_numeric($dp = $g('jumlah_duplikasi')) ? (int) $dp : null,
                'daftar_remark' => $g('daftar_remark'),
                'running_time' => $this->waktu($g('running_time')),
                'tindak_lanjut' => 'belum',
                'created_at' => $now,
                'updated_at' => $now,
            ];
            $rec['nilai_anomali'] = mb_substr($this->nilaiAnomali($jenis, $rec, $g('kode_prov_kk')), 0, 500);

            $records[] = $rec;
        }

        return $records;
    }

    /** Ringkasan isi anomali yang langsung bisa dibaca di tabel. */
    protected function nilaiAnomali(string $jenis, array $r, ?string $kodeProvKk): string
    {
        $kembar = fn (?string $s) => ($s && preg_match('/(\d)\1{5,}/', $s, $m)) ? $m[0] : null;

        return match ($jenis) {
            'umur' => 'Umur ' . ($r['umur'] ?? '?') . ' tahun',
            'profesi' => 'Profesi: ' . ($r['profesi'] ?? '-') . ($r['umur'] !== null ? ' · umur ' . $r['umur'] . ' th' : ''),
            'kk_prov' => 'No KK diawali "' . ($kodeProvKk ?: substr((string) $r['no_kk'], 0, 2)) . '" (seharusnya kode prov)',
            'kk_kembar' => 'No KK mengandung ' . ($kembar($r['no_kk']) ?? '6 digit kembar'),
            'nik_prov' => 'NIK diawali "' . substr((string) $r['nik'], 0, 2) . '" (seharusnya kode prov)',
            'nik_kembar' => 'NIK mengandung ' . ($kembar($r['nik']) ?? '6 digit kembar') . ($r['tgl_lahir'] ? ' · lahir ' . $r['tgl_lahir'] : ''),
            'ac_450' => 'Daya ' . ($r['daya_terpasang'] ? preg_replace('/^\d+\.\s*/', '', $r['daya_terpasang']) : '?')
                . ' · AC ' . ($r['jumlah_ac'] ?? '?') . ' unit'
                . ($r['jml_meteran'] !== null ? ' · ' . $r['jml_meteran'] . ' meteran' : ''),
            'penerangan_mobil' => 'Penerangan bukan listrik' . ($r['sumber_penerangan'] ? ' (kode ' . $r['sumber_penerangan'] . ')' : '')
                . ' · ' . ($r['jumlah_mobil'] ?? '?') . ' mobil'
                . ($r['nilai_mobil'] ? ' senilai Rp ' . number_format($r['nilai_mobil'], 0, ',', '.') : ''),
            'nik_duplikat' => 'NIK ' . ($r['nik'] ?? '?') . ' muncul ' . ($r['jumlah_duplikasi'] ?? '?') . 'x secara nasional',
            default => '',
        };
    }

    // =====================================================================
    // WILAYAH & PIC (PML ORGANIK)
    // =====================================================================

    /**
     * 1. baris tanpa level_6 -> cari region_code dari assignment_id yang sama
     *    di jenis lain dalam batch ini, lalu (opsional) dari tabel modul Anomali
     * 2. region_code -> PML organik, nama kec/desa/SLS dari sls_dailies
     *    (cocok 16 digit, cadangan 14 digit tanpa sub SLS)
     * 3. nama PML diseragamkan (NANDAR = nandar)
     *
     * @return array{total:int, ada_pic:int, tanpa_pic:int}
     */
    public function lengkapiWilayahDanPic(SosekBatch $batch, string $tanggal): array
    {
        $rows = SosekMikro::where('sosek_batch_id', $batch->id)
            ->get(['id', 'assignment_id', 'region_code', 'sumber_wilayah', 'kdkec', 'kddesa', 'nmkec', 'nmdesa', 'nama_sls', 'pml_raw', 'pic']);
        if ($rows->isEmpty()) return ['total' => 0, 'ada_pic' => 0, 'tanpa_pic' => 0];

        // --- 1a. assignment -> region dari baris yang punya level_6 di file
        $dariBatch = [];
        foreach ($rows as $r) {
            if ($r->assignment_id && $r->region_code && $r->sumber_wilayah === 'file') {
                $dariBatch[$r->assignment_id] ??= $r->region_code;
            }
        }

        // --- 1b. cadangan: tabel modul Anomali
        $perlu = $rows->filter(fn ($r) => !$r->region_code && $r->assignment_id && !isset($dariBatch[$r->assignment_id]))
            ->pluck('assignment_id')->unique()->values();
        $dariAnomali = [];
        if ($perlu->isNotEmpty() && config('sosek.cari_region_dari_anomali', true)) {
            try {
                foreach ($perlu->chunk(1000) as $chunk) {
                    AnomaliMikro::whereIn('assignment_id', $chunk->all())
                        ->whereNotNull('region_code')->where('region_code', '!=', '')
                        ->orderByDesc('id')
                        ->get(['assignment_id', 'region_code'])
                        ->each(function ($a) use (&$dariAnomali) {
                            $code = PetugasResolver::normalizeRegionCode($a->region_code);
                            if ($code && strlen($code) >= 14) $dariAnomali[$a->assignment_id] ??= $code;
                        });
                }
            } catch (\Throwable $e) {
                report($e); // tabel/kolom berbeda -> lewati saja
            }
        }

        // --- 2. peta sls_dailies
        $sls = $this->petaSls($tanggal);

        $hasil = [];
        foreach ($rows as $r) {
            $region = $r->region_code;
            $sumber = $r->sumber_wilayah;
            if (!$region && $r->assignment_id) {
                if (isset($dariBatch[$r->assignment_id])) {
                    [$region, $sumber] = [$dariBatch[$r->assignment_id], 'assignment_batch'];
                } elseif (isset($dariAnomali[$r->assignment_id])) {
                    [$region, $sumber] = [$dariAnomali[$r->assignment_id], 'anomali'];
                }
            }

            $info = $region ? ($sls['exact'][$region] ?? $sls['tanpaSub'][substr($region, 0, 14)] ?? null) : null;
            $kdkec = $r->kdkec ?: ($region ? substr($region, 0, 7) : null);
            $kddesa = $r->kddesa ?: ($region ? substr($region, 0, 10) : null);

            $hasil[$r->id] = [
                'region_code' => $region,
                'sumber_wilayah' => $sumber,
                'kdkec' => $kdkec,
                'kddesa' => $kddesa,
                'nmkec' => $info['nmkec'] ?? ($kdkec ? ($sls['kec'][$kdkec] ?? null) : null) ?? $r->nmkec,
                'nmdesa' => $info['nmdes'] ?? ($kddesa ? ($sls['desa'][$kddesa] ?? null) : null) ?? $r->nmdesa,
                'nama_sls' => $info['nama_sls'] ?? $r->nama_sls,
                'pml_raw' => $info['pml_organik'] ?? null,
            ];
        }

        // --- 3. seragamkan nama PML
        $kanon = NamaPetugas::kanonik(array_column($hasil, 'pml_raw'));
        foreach ($hasil as &$h) {
            $h['pic'] = $h['pml_raw'] !== null ? ($kanon[$h['pml_raw']] ?? null) : null;
        }
        unset($h);

        // --- simpan: kelompokkan baris yang nilainya sama -> 1 query per kelompok
        $kelompok = [];
        foreach ($hasil as $id => $h) {
            $kelompok[json_encode($h)][] = $id;
        }
        DB::transaction(function () use ($kelompok) {
            foreach ($kelompok as $json => $ids) {
                foreach (array_chunk($ids, 1000) as $chunk) {
                    SosekMikro::whereIn('id', $chunk)->update(json_decode($json, true));
                }
            }
        });

        $adaPic = count(array_filter($hasil, fn ($h) => $h['pic'] !== null));

        return ['total' => count($hasil), 'ada_pic' => $adaPic, 'tanpa_pic' => count($hasil) - $adaPic];
    }

    /** @return array{exact: array, tanpaSub: array, kec: array, desa: array} */
    protected function petaSls(string $tanggal): array
    {
        $peta = ['exact' => [], 'tanpaSub' => [], 'kec' => [], 'desa' => []];

        $uploadId = PetugasResolver::forDate($tanggal)->uploadId();
        if (!$uploadId) return $peta;

        $rows = SlsDaily::query()
            ->where('daily_upload_id', $uploadId)
            ->select(['region_code', 'pml_organik', 'nmkec', 'nmdes', 'nama_sls'])
            ->cursor();

        foreach ($rows as $r) {
            $code = PetugasResolver::normalizeRegionCode($r->region_code);
            if (!$code || strlen($code) < 10) continue;

            $d = [
                'pml_organik' => $this->kosongKeNull($r->pml_organik),
                'nmkec' => $this->kosongKeNull($r->nmkec),
                'nmdes' => $this->kosongKeNull($r->nmdes),
                'nama_sls' => $this->kosongKeNull($r->nama_sls),
            ];
            $peta['exact'][$code] = $d;
            if (strlen($code) >= 14) $peta['tanpaSub'][substr($code, 0, 14)] ??= $d;
            if ($d['nmkec']) $peta['kec'][substr($code, 0, 7)] ??= $d['nmkec'];
            if ($d['nmdes']) $peta['desa'][substr($code, 0, 10)] ??= $d['nmdes'];
        }

        return $peta;
    }

    // =====================================================================
    // HELPER
    // =====================================================================

    /** @return array<string, array> nama sheet => baris (nilai mentah) */
    protected function readSheets(UploadedFile $file): array
    {
        $reader = IOFactory::createReaderForFile($file->getRealPath());
        if (method_exists($reader, 'setReadDataOnly')) {
            $reader->setReadDataOnly(true);
        }

        $out = [];
        foreach ($reader->load($file->getRealPath())->getAllSheets() as $sheet) {
            // nilai mentah supaya NIK/KK/ID SubSLS 16 digit tidak jadi 7,31E+15
            $out[$sheet->getTitle()] = $sheet->toArray(null, true, false, false);
        }

        return $out;
    }

    /** @return array{0:?int, 1:array<string,int|null>, 2:array<int,string>} */
    protected function mapColumns(array $rows, string $fileName, bool $wajib = true): array
    {
        $known = [];
        foreach (self::SPEC as $aliases) {
            foreach ($aliases as $a) $known[] = $this->slug($a);
        }

        $headerIdx = 0;
        $best = 0;
        foreach (array_slice($rows, 0, 25, true) as $idx => $row) {
            $score = 0;
            foreach ($row as $v) {
                if (in_array($this->slug($v), $known, true)) $score++;
            }
            if ($score > $best) [$best, $headerIdx] = [$score, $idx];
        }

        $slugs = [];
        foreach ($rows[$headerIdx] ?? [] as $idx => $v) {
            $s = $this->slug($v);
            if ($s !== '' && !in_array($s, $slugs, true)) $slugs[$idx] = $s;
        }

        $colMap = [];
        foreach (self::SPEC as $field => $aliases) {
            $colMap[$field] = null;
            foreach ($aliases as $alias) {
                $found = array_search($this->slug($alias), $slugs, true);
                if ($found !== false) { $colMap[$field] = $found; break; }
            }
        }

        if ($colMap['assignment_id'] === null && $colMap['link_fasih_sm'] === null) {
            if (!$wajib) return [null, [], []];
            throw new \Exception(
                'Kolom "assignment_id" atau "link_fasih_sm" tidak ditemukan pada file ' . $fileName . '. ' .
                'Header yang terbaca: ' . implode(' | ', array_filter(array_map('strval', $rows[$headerIdx] ?? [])))
            );
        }

        return [$headerIdx, $colMap, array_values($slugs)];
    }

    protected function assignmentDariLink(?string $link): ?string
    {
        if ($link && preg_match('/([0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12})\/?\s*(?:[?#].*)?$/i', $link, $m)) {
            return strtolower($m[1]);
        }

        return null;
    }

    /** 11, "11 - NOVEMBER", 2019 -> "11 November 2019" */
    protected function tanggalLahir(?string $tgl, ?string $bln, ?string $thn): ?string
    {
        if ($tgl === null && $bln === null && $thn === null) return null;
        $bln = $bln !== null ? trim(preg_replace('/^\d+\s*[-.]\s*/', '', $bln)) : null;
        $bln = $bln !== null ? mb_convert_case(mb_strtolower($bln), MB_CASE_TITLE) : null;

        return trim(implode(' ', array_filter([$tgl, $bln, $thn], fn ($v) => $v !== null && $v !== '')));
    }

    /** Tanggal teks apa adanya; tanggal Excel (angka serial) -> "Y-m-d H:i:s" */
    protected function waktu(?string $v): ?string
    {
        if ($v === null) return null;
        if (is_numeric($v) && (float) $v > 20000 && (float) $v < 80000) {
            try {
                return ExcelDate::excelToDateTimeObject((float) $v)->format('Y-m-d H:i:s');
            } catch (\Throwable) {
                // biarkan
            }
        }

        return mb_substr(str_replace('T', ' ', $v), 0, 30);
    }

    protected function kosongKeNull($v): ?string
    {
        $v = $v === null ? '' : trim((string) $v);

        return ($v === '' || in_array(mb_strtoupper($v), ['-', '#N/A', 'N/A', 'NULL'], true)) ? null : $v;
    }

    protected function slug($v): string
    {
        $v = str_replace(["\xC2\xA0", "\xE2\x80\x8B", "\xEF\xBB\xBF"], ' ', (string) $v);

        return preg_replace('/[^a-z0-9]/', '', mb_strtolower($v)) ?? '';
    }

    protected function digits(?string $v): string
    {
        return preg_replace('/\D/', '', (string) $v) ?? '';
    }

    protected function isRowEmpty(array $row): bool
    {
        return empty(array_filter($row, fn ($v) => $v !== null && trim((string) $v) !== ''));
    }

    protected function cell(array $row, ?int $index): ?string
    {
        if ($index === null || !array_key_exists($index, $row) || $row[$index] === null) return null;

        $val = $row[$index];
        if (is_float($val)) {
            $val = floor($val) == $val && abs($val) < 1e18
                ? number_format($val, 0, '', '')
                : rtrim(rtrim(sprintf('%.10F', $val), '0'), '.');
        }
        $val = trim(preg_replace('/\s+/u', ' ', (string) $val));

        return ($val === '' || $val === '-') ? null : $val;
    }
}
