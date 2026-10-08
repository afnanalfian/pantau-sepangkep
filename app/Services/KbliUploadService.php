<?php

namespace App\Services;

use App\Models\KbliBatch;
use App\Models\KbliMaster;
use App\Models\KbliMikro;
use App\Models\SlsDaily;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class KbliUploadService
{
    /** @var array<string, mixed> ringkasan import terakhir */
    public array $ringkasan = [];

    /**
     * Pemetaan field database => alias header excel.
     * Dicocokkan lewat "slug" (huruf+angka saja), jadi kebal spasi/underscore/kapital.
     */
    protected const SPEC = [
        'assignment_id' => ['assignment_id', 'Assignment ID', 'id_assignment'],
        'index1' => ['index1', 'index 1', 'index'],
        'kdprov' => ['level_1_full_code', 'level1', 'kode prov'],
        'kdkab' => ['level_2_full_code', 'level2', 'kode kab'],
        'kdkec' => ['level_3_full_code', 'level3', 'kode kec'],
        'kddesa' => ['level_4_full_code', 'level4', 'kode desa'],
        'kbli_akhir' => ['kbli_akhir', 'kbli akhir', 'kbli'],
        'nama_usaha' => ['nama_usaha', 'nama usaha', 'nama'],
        'keg_utama' => ['keg_utama', 'kegiatan utama', 'kegiatan'],
        'produk' => ['produk', 'produk utama'],
        'link_fasih_sm' => ['link_fasih_sm', 'link fasih', 'link'],
    ];

    // =====================================================================
    // IMPORT BATCH
    // =====================================================================

    /**
     * Import satu file Cek KBLI untuk satu tanggal. Jika tanggal sudah ada,
     * batch lama beserta data mikronya dihapus & digantikan (cascade).
     */
    public function importBatch(UploadedFile $file, string $tanggal, ?string $uploadedBy = null): KbliBatch
    {
        // Baca & susun baris di luar transaksi (bagian paling berat)
        $rows = $this->readRows($file);
        [$headerIdx, $colMap] = $this->mapColumns($rows, $file->getClientOriginalName());
        $wilayah = $this->wilayahMap($tanggal);

        $records = [];
        $no = 0;
        $tanpaWilayah = 0;
        $now = now();

        for ($i = $headerIdx + 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            if ($this->isRowEmpty($row)) continue;

            $get = fn (string $f) => $this->cell($row, $colMap[$f] ?? null);

            $assignmentId = $get('assignment_id');
            if ($assignmentId === null) continue;

            $kdkec = $this->digits($get('kdkec'));
            $kddesa = $this->digits($get('kddesa'));
            $kbli = $this->normalizeKbli($get('kbli_akhir'));

            $nmdesa = $wilayah['desa'][$kddesa] ?? null;
            $nmkec = $wilayah['kec'][$kdkec] ?? ($kddesa ? ($wilayah['kec'][substr($kddesa, 0, 7)] ?? null) : null);
            if (!$nmkec || !$nmdesa) $tanpaWilayah++;

            $records[] = [
                'no' => ++$no,
                'assignment_id' => $assignmentId,
                'index1' => $get('index1'),
                'kdprov' => $this->digits($get('kdprov')) ?: null,
                'kdkab' => $this->digits($get('kdkab')) ?: null,
                'kdkec' => $kdkec ?: null,
                'kddesa' => $kddesa ?: null,
                'kbli_akhir' => $kbli,
                'kbli_kategori' => KbliMaster::kategoriDari($kbli),
                'nama_usaha' => $get('nama_usaha'),
                'keg_utama' => $get('keg_utama'),
                'produk' => $get('produk'),
                'link_fasih_sm' => $get('link_fasih_sm'),
                'nmkec' => $nmkec,
                'nmdesa' => $nmdesa,
                'tindak_lanjut' => 'belum',
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (empty($records)) {
            throw new \Exception('Tidak ada baris data yang valid (kolom assignment_id kosong semua) pada file ' . $file->getClientOriginalName() . '.');
        }

        $batch = DB::transaction(function () use ($records, $tanggal, $uploadedBy, $file) {
            KbliBatch::where('tanggal', $tanggal)->first()?->delete();

            $batch = KbliBatch::create([
                'tanggal' => $tanggal,
                'nama_file' => $file->getClientOriginalName(),
                'uploaded_by' => $uploadedBy,
            ]);

            foreach (array_chunk($records, 500) as $chunk) {
                $chunk = array_map(fn ($r) => $r + ['kbli_batch_id' => $batch->id], $chunk);
                KbliMikro::insert($chunk);
            }

            return $batch;
        });

        $this->ringkasan = [
            'baris' => count($records),
            'tanpa_wilayah' => $tanpaWilayah,
        ];

        return $batch->fresh();
    }

    // =====================================================================
    // IMPORT MASTER KBLI (opsional)
    // =====================================================================

    /**
     * File master: minimal 2 kolom -> kode (5 digit) & judul.
     * Header dicari otomatis ("kode"/"kbli" dan "judul"/"nama"/"deskripsi").
     * Kalau tidak ada header, kolom A = kode, kolom B = judul.
     */
    public function importMaster(UploadedFile $file): int
    {
        $rows = $this->readRows($file);

        $kodeCol = 0;
        $judulCol = 1;
        $start = 0;

        foreach (array_slice($rows, 0, 10, true) as $idx => $row) {
            $k = $j = null;
            foreach ($row as $c => $v) {
                $s = $this->slug($v);
                if ($k === null && in_array($s, ['kode', 'kodekbli', 'kbli', 'kode5digit'], true)) $k = $c;
                if ($j === null && in_array($s, ['judul', 'judulkbli', 'nama', 'namakbli', 'deskripsi', 'uraian', 'keterangan'], true)) $j = $c;
            }
            if ($k !== null && $j !== null) {
                [$kodeCol, $judulCol, $start] = [$k, $j, $idx + 1];
                break;
            }
        }

        $data = [];
        $now = now();
        for ($i = $start; $i < count($rows); $i++) {
            $kode = $this->digits($this->cell($rows[$i], $kodeCol));
            $judul = $this->cell($rows[$i], $judulCol);
            // hanya kode 5 digit (level kelompok) yang dipakai
            if (strlen($kode) === 4) $kode = '0' . $kode; // kehilangan nol depan dari excel
            if (strlen($kode) !== 5 || !$judul) continue;

            $data[$kode] = ['kode' => $kode, 'judul' => mb_substr($judul, 0, 500), 'created_at' => $now, 'updated_at' => $now];
        }

        foreach (array_chunk(array_values($data), 500) as $chunk) {
            KbliMaster::upsert($chunk, ['kode'], ['judul', 'updated_at']);
        }
        KbliMaster::flushCache();

        return count($data);
    }

    // =====================================================================
    // NAMA WILAYAH
    // =====================================================================

    /**
     * Nama kecamatan & desa diambil dari sls_dailies (upload harian terakhir
     * sebelum/pada tanggal batch). region_code = kab(4)+kec(3)+desa(3)+...
     *
     * @return array{kec: array<string,string>, desa: array<string,string>}
     */
    protected function wilayahMap(string $tanggal): array
    {
        $map = ['kec' => [], 'desa' => []];

        $uploadId = PetugasResolver::forDate($tanggal)->uploadId();
        if (!$uploadId) return $map;

        $rows = SlsDaily::query()
            ->where('daily_upload_id', $uploadId)
            ->select(['region_code', 'nmkec', 'nmdes'])
            ->cursor();

        foreach ($rows as $r) {
            $code = PetugasResolver::normalizeRegionCode($r->region_code);
            if (!$code || strlen($code) < 10) continue;

            $kec = substr($code, 0, 7);
            $desa = substr($code, 0, 10);

            if ($r->nmkec && !isset($map['kec'][$kec])) $map['kec'][$kec] = trim($r->nmkec);
            if ($r->nmdes && !isset($map['desa'][$desa])) $map['desa'][$desa] = trim($r->nmdes);
        }

        return $map;
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
        $spreadsheet = $reader->load($file->getRealPath());

        // nilai mentah (tanpa format) supaya kode panjang tidak berubah jadi 7,31E+09
        return $spreadsheet->getActiveSheet()->toArray(null, true, false, false);
    }

    /**
     * @return array{0:int, 1:array<string,int|null>}
     */
    protected function mapColumns(array $rows, string $fileName): array
    {
        $known = [];
        foreach (self::SPEC as $aliases) {
            $known[] = $this->slug($aliases[0]);
        }

        // baris header = baris dengan paling banyak nama kolom yang dikenal
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
            if ($s !== '') $slugs[$idx] = $s;
        }

        $colMap = [];
        foreach (self::SPEC as $field => $aliases) {
            $colMap[$field] = null;
            // 1) cocok persis
            foreach ($aliases as $alias) {
                $found = array_search($this->slug($alias), $slugs, true);
                if ($found !== false) { $colMap[$field] = $found; break; }
            }
        }

        if ($colMap['assignment_id'] === null) {
            throw new \Exception(
                'Kolom "assignment_id" tidak ditemukan pada file ' . $fileName . '. ' .
                'Header yang terbaca: ' . implode(' | ', array_filter(array_map('strval', $rows[$headerIdx] ?? [])))
            );
        }

        return [$headerIdx, $colMap];
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
        if (is_float($val) && floor($val) == $val) {
            $val = number_format($val, 0, '', '');
        }
        $val = trim((string) $val);

        return ($val === '' || $val === '-') ? null : $val;
    }

    /** "1116" (nol depan hilang di excel) -> "01116" */
    protected function normalizeKbli(?string $v): ?string
    {
        $d = $this->digits($v);
        if ($d === '') return null;
        if (strlen($d) < 5) $d = str_pad($d, 5, '0', STR_PAD_LEFT);

        return substr($d, 0, 5);
    }
}
