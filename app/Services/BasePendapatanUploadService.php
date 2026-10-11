<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Importer bersama untuk modul Pemindahan Pendapatan & Pendapatan 1 Juta ke Bawah.
 * Kedua file punya kolom identitas & wilayah yang sama; kolom tambahan diatur
 * oleh subclass lewat extraSpec() & extraFields().
 */
abstract class BasePendapatanUploadService
{
    /** @var array<string, mixed> */
    public array $ringkasan = [];

    /** @return class-string<Model> */
    abstract protected function batchModel(): string;

    /** @return class-string<Model> */
    abstract protected function mikroModel(): string;

    /** Nama kolom foreign key batch di tabel mikro. */
    abstract protected function batchForeignKey(): string;

    /** Alias header tambahan: field => [alias...] */
    abstract protected function extraSpec(): array;

    /** Nilai kolom tambahan untuk satu baris. */
    abstract protected function extraFields(callable $get): array;

    protected function commonSpec(): array
    {
        return [
            'assignment_id' => ['assignment_id', 'Assignment ID', 'id_assignment'],
            'no_usaha' => ['no_usaha', 'No Usaha', 'nomor_usaha'],
            'nama_usaha' => ['nama_usaha', 'Nama Usaha'],
            'kdprov' => ['kode_prov', 'Kode Provinsi', 'kdprov'],
            'nmprov' => ['nama_provinsi', 'Nama Prov', 'nmprov'],
            'kdkab' => ['kode_kab', 'Kode Kab/Kota', 'kdkab'],
            'nmkab' => ['nama_kab_kota', 'Nama Kab', 'nmkab'],
            'kdkec' => ['kode_kec', 'Kode Kecamatan', 'kdkec'],
            'nmkec' => ['nama_kecamatan', 'Nama Kec', 'nmkec'],
            'kddesa' => ['kode_desa', 'Kode Desa/Kel', 'kddesa'],
            'nmdesa' => ['nama_desa_kel', 'Nama Desa', 'Nama Desa/Kel', 'nmdesa'],
            'kode_sls' => ['kode_sls', 'kdsls'],
            'nama_sls' => ['nama_sls', 'nmsls'],
            'sub_sls' => ['sub_sls', 'subsls'],
            'jalur_pelaporan' => ['jalur_pelaporan', 'Jalur Pelaporan', 'jalur'],
            'pendapatan_utama' => ['pendapatan_utama_setahun', 'pendapatan_utama'],
            'pendapatan_lain' => ['pendapatan_lain_setahun', 'pendapatan_lain'],
            'total_pengeluaran' => ['total_pengeluaran_setahun', 'total_pengeluaran'],
        ];
    }

    // =====================================================================
    // IMPORT
    // =====================================================================

    /**
     * Import satu file untuk satu tanggal. Jika tanggal sudah ada, batch lama
     * beserta data mikronya dihapus & digantikan (cascade).
     */
    public function importBatch(UploadedFile $file, string $tanggal, ?string $uploadedBy = null): Model
    {
        $spec = $this->commonSpec() + $this->extraSpec();

        $rows = $this->readRows($file);
        [$headerIdx, $colMap] = $this->mapColumns($rows, $spec, $file->getClientOriginalName());

        $records = [];
        $no = 0;
        $now = now();

        for ($i = $headerIdx + 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            if ($this->isRowEmpty($row)) continue;

            $get = fn (string $f) => $this->cell($row, $colMap[$f] ?? null);

            $assignmentId = $get('assignment_id');
            if ($assignmentId === null) continue;

            $records[] = [
                'no' => ++$no,
                'assignment_id' => $assignmentId,
                'no_usaha' => $get('no_usaha'),
                'nama_usaha' => $get('nama_usaha'),
                'kdprov' => $this->pad($get('kdprov'), 2),
                'nmprov' => $get('nmprov'),
                'kdkab' => $this->pad($get('kdkab'), 4),
                'nmkab' => $get('nmkab'),
                'kdkec' => $this->pad($get('kdkec'), 7),
                'nmkec' => $get('nmkec'),
                'kddesa' => $this->pad($get('kddesa'), 10),
                'nmdesa' => $get('nmdesa'),
                'kode_sls' => $this->pad($get('kode_sls'), 4),
                'nama_sls' => $get('nama_sls'),
                'sub_sls' => $this->pad($get('sub_sls'), 2),
                'jalur_pelaporan' => $get('jalur_pelaporan'),
                'pendapatan_utama' => $this->number($get('pendapatan_utama')),
                'pendapatan_lain' => $this->number($get('pendapatan_lain')),
                'total_pengeluaran' => $this->number($get('total_pengeluaran')),
                'tindak_lanjut' => 'belum',
                'created_at' => $now,
                'updated_at' => $now,
            ] + $this->extraFields($get);
        }

        if (empty($records)) {
            throw new \Exception('Tidak ada baris data yang valid (kolom assignment_id kosong semua) pada file ' . $file->getClientOriginalName() . '.');
        }

        $batchModel = $this->batchModel();
        $mikroModel = $this->mikroModel();
        $fk = $this->batchForeignKey();

        $batch = DB::transaction(function () use ($records, $tanggal, $uploadedBy, $file, $batchModel, $mikroModel, $fk) {
            $batchModel::where('tanggal', $tanggal)->first()?->delete();

            $batch = $batchModel::create([
                'tanggal' => $tanggal,
                'nama_file' => $file->getClientOriginalName(),
                'uploaded_by' => $uploadedBy,
            ]);

            foreach (array_chunk($records, 500) as $chunk) {
                $mikroModel::insert(array_map(fn ($r) => $r + [$fk => $batch->id], $chunk));
            }

            return $batch;
        });

        $this->ringkasan = ['baris' => count($records)];

        return $batch->fresh();
    }

    // =====================================================================
    // HELPER EXCEL
    // =====================================================================

    protected function readRows(UploadedFile $file): array
    {
        $reader = IOFactory::createReaderForFile($file->getRealPath());
        if (method_exists($reader, 'setReadDataOnly')) {
            $reader->setReadDataOnly(true);
        }

        // nilai mentah (tanpa format) supaya kode & angka tidak berubah bentuk
        return $reader->load($file->getRealPath())->getActiveSheet()->toArray(null, true, false, false);
    }

    /** @return array{0:int, 1:array<string,int|null>} */
    protected function mapColumns(array $rows, array $spec, string $fileName): array
    {
        $known = array_map(fn ($aliases) => $this->slug($aliases[0]), $spec);

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
            if ($s !== '' && !in_array($s, $slugs, true)) $slugs[$idx] = $s;
        }

        $colMap = [];
        foreach ($spec as $field => $aliases) {
            $colMap[$field] = null;
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

    protected function isRowEmpty(array $row): bool
    {
        return empty(array_filter($row, fn ($v) => $v !== null && trim((string) $v) !== ''));
    }

    protected function cell(array $row, ?int $index): ?string
    {
        if ($index === null || !array_key_exists($index, $row) || $row[$index] === null) return null;

        $val = $row[$index];
        if (is_float($val) && floor($val) == $val && abs($val) < 1e15) {
            $val = number_format($val, 0, '', '');
        }
        $val = trim((string) $val);

        return ($val === '' || $val === '-') ? null : $val;
    }

    /** Kode wilayah yang kehilangan nol depan di excel ("17" -> "0017"). */
    protected function pad(?string $v, int $len): ?string
    {
        if ($v === null) return null;
        $d = preg_replace('/\D/', '', $v);
        if ($d === '') return $v;

        return strlen($d) < $len ? str_pad($d, $len, '0', STR_PAD_LEFT) : $d;
    }

    /** "16800000.00" / "16.800.000,00" / "Rp 16,800,000" -> 16800000.0 */
    protected function number(?string $v): ?float
    {
        if ($v === null) return null;
        $v = trim($v);

        // "1.200" / "16.800.000" = pemisah ribuan ala Indonesia, bukan desimal
        if (preg_match('/^-?\d{1,3}(\.\d{3})+$/', $v)) {
            return (float) str_replace('.', '', $v);
        }
        if (is_numeric($v)) return (float) $v;

        $v = preg_replace('/[^0-9,.\-]/', '', $v);
        if ($v === '' || $v === '-') return null;

        $lastComma = strrpos($v, ',');
        $lastDot = strrpos($v, '.');
        if ($lastComma !== false && ($lastDot === false || $lastComma > $lastDot)) {
            // format Indonesia: titik ribuan, koma desimal
            $v = str_replace(',', '.', str_replace('.', '', $v));
        } else {
            $v = str_replace(',', '', $v);
        }

        return is_numeric($v) ? (float) $v : null;
    }

    protected function integer(?string $v): ?int
    {
        $n = $this->number($v);

        return $n === null ? null : (int) round($n);
    }
}
