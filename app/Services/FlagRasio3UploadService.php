<?php

namespace App\Services;

use App\Models\FlagRasio3Batch;
use App\Models\FlagRasio3Mikro;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class FlagRasio3UploadService
{
    /** @var array<string, int> jenis => jumlah baris */
    public array $ringkasan = [];

    /**
     * field database => alias header excel (dicocokkan lewat slug huruf+angka).
     * Kolom flag_rasio_1..3 & flag_kategori_P_dan_U sengaja tidak diimpor.
     */
    protected const SPEC = [
        'no' => ['No', 'Nomor'],
        'assignment_id' => ['assignment_id', 'Assignment ID'],
        'link_fasih_sm' => ['link_fasih_sm', 'link fasih', 'link'],
        'kdkab' => ['level_2_code', 'level_2_full_code', 'kode kab'],
        'kategori' => ['kategori', 'kategori kbli'],
        'kode_kbli' => ['kode_kbli', 'kbli', 'kbli_akhir'],
        'nama_usaha' => ['nama_usaha', 'nama usaha'],
        'tenaga_kerja' => ['metrik_tenaga_kerja', 'tenaga_kerja'],
        'upah_gaji' => ['metrik_upah_gaji', 'upah_gaji'],
        'biaya_produksi' => ['metrik_biaya_produksi', 'biaya_produksi'],
        'biaya_pembelian' => ['metrik_biaya_pembelian', 'biaya_pembelian'],
        'biaya_operasional' => ['metrik_biaya_operasional', 'biaya_operasional'],
        'biaya_non_operasional' => ['metrik_biaya_non_operasional', 'biaya_non_operasional'],
        'total_pengeluaran' => ['metrik_total_pengeluaran', 'total_pengeluaran'],
        'pendapatan_barang_jasa' => ['metrik_pendapatan_barang_jasa', 'pendapatan_barang_jasa'],
        'pendapatan_lainnya' => ['metrik_pendapatan_lainnya', 'pendapatan_lainnya'],
        'nilai_tambah' => ['metrik_nilai_tambah', 'nilai_tambah'],
        'total_aset' => ['metrik_total_aset', 'total_aset'],
        'output' => ['metrik_output', 'output'],
        'rasio_biaya_pembelian' => ['rasio_biaya_pembelian_barang_terhadap_omzet', 'rasio_biaya_pembelian'],
        'reklasifikasi_skala_usaha' => ['reklasifikasi_skala_usaha', 'skala_usaha'],
        'rasio_ntb' => ['rasio_ntb'],
        'produktivitas' => ['produktivitas'],
    ];

    protected const KOLOM_ANGKA = [
        'upah_gaji', 'biaya_produksi', 'biaya_pembelian', 'biaya_operasional', 'biaya_non_operasional',
        'total_pengeluaran', 'pendapatan_barang_jasa', 'pendapatan_lainnya', 'nilai_tambah', 'total_aset',
        'output', 'rasio_biaya_pembelian', 'rasio_ntb', 'produktivitas',
    ];

    /**
     * Import file UB dan/atau UM untuk satu tanggal.
     * Jika tanggal sudah ada, HANYA data jenis yang diunggah ulang yang diganti
     * (mis. unggah ulang UB saja -> data UM beserta tindak lanjutnya tetap).
     *
     * @param  array{UB?: UploadedFile|null, UM?: UploadedFile|null}  $files
     */
    public function importBatch(array $files, string $tanggal, ?string $uploadedBy = null): FlagRasio3Batch
    {
        $files = array_filter($files);
        if (empty($files)) {
            throw new \Exception('Pilih minimal satu file (UB atau UM).');
        }

        // baca semua file dulu di luar transaksi
        $parsed = [];
        foreach ($files as $jenis => $file) {
            $parsed[$jenis] = $this->parseFile($file, $jenis);
        }

        $batch = DB::transaction(function () use ($parsed, $files, $tanggal, $uploadedBy) {
            $batch = FlagRasio3Batch::firstOrNew(['tanggal' => $tanggal]);
            $batch->uploaded_by = $uploadedBy;
            foreach ($files as $jenis => $file) {
                $batch->{'nama_file_' . strtolower($jenis)} = $file->getClientOriginalName();
            }
            $batch->save();

            foreach ($parsed as $jenis => $records) {
                FlagRasio3Mikro::where('flag_rasio3_batch_id', $batch->id)->where('jenis', $jenis)->delete();

                foreach (array_chunk($records, 500) as $chunk) {
                    FlagRasio3Mikro::insert(array_map(fn ($r) => $r + ['flag_rasio3_batch_id' => $batch->id], $chunk));
                }
                $this->ringkasan[$jenis] = count($records);
            }

            return $batch;
        });

        return $batch->fresh();
    }

    /** @return list<array<string, mixed>> */
    protected function parseFile(UploadedFile $file, string $jenis): array
    {
        $rows = $this->readRows($file);
        [$headerIdx, $colMap] = $this->mapColumns($rows, $file->getClientOriginalName());

        $records = [];
        $urut = 0;
        $now = now();

        for ($i = $headerIdx + 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            if ($this->isRowEmpty($row)) continue;

            $get = fn (string $f) => $this->cell($row, $colMap[$f] ?? null);

            $assignmentId = $get('assignment_id');
            if ($assignmentId === null) continue;
            $urut++;

            $rec = [
                'jenis' => $jenis,
                'no' => ($n = $this->integer($get('no'))) !== null ? $n : $urut,
                'assignment_id' => $assignmentId,
                'link_fasih_sm' => $get('link_fasih_sm'),
                'kdkab' => $get('kdkab'),
                'kategori' => ($k = $get('kategori')) !== null ? mb_strtoupper(mb_substr($k, 0, 2)) : null,
                'kode_kbli' => $this->normalizeKbli($get('kode_kbli')),
                'nama_usaha' => $get('nama_usaha'),
                'tenaga_kerja' => $this->integer($get('tenaga_kerja')),
                'reklasifikasi_skala_usaha' => $get('reklasifikasi_skala_usaha'),
                'tindak_lanjut' => 'belum',
                'created_at' => $now,
                'updated_at' => $now,
            ];
            foreach (self::KOLOM_ANGKA as $f) {
                $rec[$f] = $this->number($get($f));
            }

            $records[] = $rec;
        }

        if (empty($records)) {
            throw new \Exception('Tidak ada baris data yang valid (kolom assignment_id kosong semua) pada file ' . $jenis . ': ' . $file->getClientOriginalName() . '.');
        }

        return $records;
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

        return $reader->load($file->getRealPath())->getActiveSheet()->toArray(null, true, false, false);
    }

    /** @return array{0:int, 1:array<string,int|null>} */
    protected function mapColumns(array $rows, string $fileName): array
    {
        $known = array_map(fn ($a) => $this->slug($a[0]), self::SPEC);

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
        if (is_float($val)) {
            // jangan pakai notasi ilmiah (7.5E+15) untuk angka besar
            $val = floor($val) == $val && abs($val) < 1e18
                ? number_format($val, 0, '', '')
                : rtrim(rtrim(sprintf('%.10F', $val), '0'), '.');
        }
        $val = trim(preg_replace('/\s+/u', ' ', (string) $val));

        return ($val === '' || $val === '-') ? null : $val;
    }

    /** "8995" (nol depan hilang) -> "08995" */
    protected function normalizeKbli(?string $v): ?string
    {
        $d = preg_replace('/\D/', '', (string) $v);
        if ($d === '') return null;

        return substr(str_pad($d, 5, '0', STR_PAD_LEFT), 0, 5);
    }

    /**
     * Angka dari berbagai format:
     *   "Rp1,014,000,000" / "1,014,000,000.50"  -> koma = ribuan (format FASIH)
     *   "16.800.000" / "16.800.000,00"          -> titik = ribuan (format Indonesia)
     *   "0.997569" / "16800000.00" / "1,5"      -> desimal biasa
     */
    protected function number(?string $v): ?float
    {
        if ($v === null) return null;

        $v = preg_replace('/[^0-9,.\-]/', '', $v);
        if ($v === '' || $v === '-' || $v === '.' || $v === ',') return null;

        if (preg_match('/^-?\d{1,3}(,\d{3})+(\.\d+)?$/', $v)) {
            $v = str_replace(',', '', $v);
        } elseif (preg_match('/^-?\d{1,3}(\.\d{3})+(,\d+)?$/', $v)) {
            $v = str_replace(',', '.', str_replace('.', '', $v));
        } elseif (substr_count($v, ',') === 1 && !str_contains($v, '.')) {
            $v = str_replace(',', '.', $v);
        }

        return is_numeric($v) ? (float) $v : null;
    }

    protected function integer(?string $v): ?int
    {
        $n = $this->number($v);

        return $n === null ? null : (int) round($n);
    }
}
