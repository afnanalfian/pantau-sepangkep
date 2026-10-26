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
        'daftar_remark' => ['daftar_remark'],
        'running_time' => ['running_time'],
    ];

    // =====================================================================
    // IMPORT
    // =====================================================================

    /**
     * @param  array<string, UploadedFile|null>  $files  jenis => file (boleh sebagian)
     * Tanggal yang sama diunggah ulang -> hanya jenis yang diunggah yang diganti.
     */
    public function importBatch(array $files, string $tanggal, ?string $uploadedBy = null): SosekBatch
    {
        $files = array_filter($files);
        if (empty($files)) {
            throw new \Exception('Pilih minimal satu file anomali.');
        }

        $jenisOpts = SosekMikro::jenisOptions();
        $parsed = [];
        foreach ($files as $jenis => $file) {
            if (!isset($jenisOpts[$jenis])) continue;
            $parsed[$jenis] = $this->parseFile($file, $jenis);
        }

        $batch = DB::transaction(function () use ($parsed, $files, $tanggal, $uploadedBy) {
            $batch = SosekBatch::firstOrNew(['tanggal' => $tanggal]);
            $nama = $batch->nama_file ?? [];
            foreach ($files as $jenis => $file) {
                $nama[$jenis] = $file->getClientOriginalName();
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

        // isi wilayah & PIC untuk SELURUH batch (baris lama pun bisa ikut terisi
        // berkat assignment_id dari file yang baru diunggah)
        $this->ringkasan['pic'] = $this->lengkapiWilayahDanPic($batch, $tanggal);

        return $batch->fresh();
    }

    /** @return list<array<string, mixed>> */
    protected function parseFile(UploadedFile $file, string $jenis): array
    {
        $info = SosekMikro::jenisOptions()[$jenis];
        $rows = $this->readRows($file);
        [$headerIdx, $colMap, $slugs] = $this->mapColumns($rows, $file->getClientOriginalName());

        // cegah salah slot: file harus punya salah satu kolom khas jenis ini
        $punya = array_filter($info['wajib'], fn ($k) => in_array($this->slug($k), $slugs, true));
        if (!$punya) {
            throw new \Exception(sprintf(
                'File "%s" yang diunggah di slot %d (%s) tidak memiliki kolom %s. Pastikan file tidak tertukar slot.',
                $file->getClientOriginalName(), $info['no'], $info['label'], implode(' / ', $info['wajib'])
            ));
        }

        $records = [];
        $no = 0;
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
                'no' => ++$no,
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
                'daftar_remark' => $g('daftar_remark'),
                'running_time' => $this->waktu($g('running_time')),
                'tindak_lanjut' => 'belum',
                'created_at' => $now,
                'updated_at' => $now,
            ];
            $rec['nilai_anomali'] = mb_substr($this->nilaiAnomali($jenis, $rec, $g('kode_prov_kk')), 0, 500);

            $records[] = $rec;
        }

        if (empty($records)) {
            throw new \Exception('Tidak ada baris data yang valid pada file ' . $file->getClientOriginalName() . ' (slot ' . $info['no'] . ').');
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

    protected function readRows(UploadedFile $file): array
    {
        $reader = IOFactory::createReaderForFile($file->getRealPath());
        if (method_exists($reader, 'setReadDataOnly')) {
            $reader->setReadDataOnly(true);
        }

        // nilai mentah supaya NIK/KK 16 digit tidak jadi 7,31E+15
        return $reader->load($file->getRealPath())->getActiveSheet()->toArray(null, true, false, false);
    }

    /** @return array{0:int, 1:array<string,int|null>, 2:array<int,string>} */
    protected function mapColumns(array $rows, string $fileName): array
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

        return mb_substr($v, 0, 30);
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
