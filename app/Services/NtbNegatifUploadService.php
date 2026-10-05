<?php

namespace App\Services;

use App\Models\NtbNegatifBatch;
use App\Models\NtbNegatifMikro;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Import excel NTB Negatif (1 file per tanggal).
 * Meng-extend AnomaliUploadService agar memakai helper deteksi header,
 * pencarian kolom, normalisasi sel, dan padding kode yang sama.
 */
class NtbNegatifUploadService extends AnomaliUploadService
{
    public function importNtbNegatif(UploadedFile $file, string $tanggal, ?string $uploadedBy = null): NtbNegatifBatch
    {
        return DB::transaction(function () use ($file, $tanggal, $uploadedBy) {
            $existing = NtbNegatifBatch::whereDate('tanggal', $tanggal)->first();
            if ($existing) {
                $existing->delete(); // cascade menghapus data mikro lama
            }

            $batch = NtbNegatifBatch::create([
                'tanggal' => $tanggal,
                'uploaded_by' => $uploadedBy,
                'nama_file' => mb_substr($file->getClientOriginalName(), 0, 500),
            ]);

            $this->importMikroNtb($file, $batch);

            return $batch->fresh();
        });
    }

    protected function importMikroNtb(UploadedFile $file, NtbNegatifBatch $batch): void
    {
        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();

        // $rows = nilai terformat (teks), $raw = nilai mentah (angka metrik/rasio)
        $rows = $sheet->toArray(null, true, true, false);
        $raw = $sheet->toArray(null, true, false, false);

        /** field DB => [alias header excel, slug terlarang] */
        $spec = [
            'kategori' => [['kategori'], []],
            'assignment_id' => [['assignment_id', 'Assignment ID'], []],
            'kdprov' => [['kode_prov', 'Kode Prov'], []],
            'nmprov' => [['nama_provinsi', 'Nama Provinsi'], ['kode']],
            'kdkab' => [['kode_kab', 'kode_kab_kota', 'Kode Kab/Kota'], []],
            'nmkab' => [['nama_kab_kota', 'nama_kab', 'Nama Kab/Kota'], ['kode']],
            'kdkec' => [['kode_kec', 'Kode Kecamatan'], []],
            'nmkec' => [['nama_kecamatan', 'Nama Kec'], ['kode']],
            'kddesa' => [['kode_desa', 'Kode Desa/Kel'], []],
            'nmdesa' => [['nama_desa_kel', 'nama_desa', 'Nama Desa/Kel'], ['kode']],
            'kode_sls' => [['kode_sls'], ['sub', 'nama']],
            'sub_sls' => [['sub_sls'], []],
            'kode_kbli' => [['kode_kbli', 'KBLI'], ['des']],
            'des_kbli' => [['des_kbli', 'Deskripsi KBLI'], []],
            'pjk' => [['PJK'], []],
            'nama_usaha' => [['nama_usaha', 'Nama Usaha', 'Nama Perusahaan'], []],
            'metrik_biaya_produksi' => [['metrik_biaya_produksi'], []],
            'metrik_biaya_pembelian' => [['metrik_biaya_pembelian'], []],
            'metrik_biaya_operasional' => [['metrik_biaya_operasional'], ['non']],
            'metrik_biaya_non_operasional' => [['metrik_biaya_non_operasional'], []],
            'metrik_total_pengeluaran' => [['metrik_total_pengeluaran'], []],
            'metrik_pendapatan_barang_jasa' => [['metrik_pendapatan_barang_jasa'], []],
            'metrik_pendapatan_lainnya' => [['metrik_pendapatan_lainnya'], []],
            'metrik_nilai_tambah' => [['metrik_nilai_tambah', 'nilai_tambah', 'NTB'], ['rasio', 'flag']],
            'metrik_total_aset' => [['metrik_total_aset'], []],
            'metrik_output' => [['metrik_output'], ['rasio', 'flag']],
            'survey_period_id' => [['survey_period_id'], []],
            'rasio_biaya_pembelian_omzet' => [['rasio_biaya_pembelian_barang_terhadap_omzet', 'rasio_biaya_pembelian'], []],
            'reklasifikasi_skala_usaha' => [['reklasifikasi_skala_usaha', 'skala_usaha'], []],
            'rasio_ntb' => [['rasio_ntb'], ['flag']],
            'produktivitas' => [['produktivitas'], []],
            'flag_rasio_1_output_aset' => [['flag_rasio_1_output_aset'], []],
            'flag_rasio_2_upah_ntb' => [['flag_rasio_2_upah_ntb'], []],
            'flag_rasio_3_ntb_output' => [['flag_rasio_3_ntb_output'], []],
            'link_fasih_sm' => [['link_fasih_sm', 'link_fasih', 'Link Fasih'], []],
        ];

        $knownSlugs = array_map(fn ($s) => $this->slugHeader($s[0][0]), $spec);

        $headerIdx = $this->detectHeaderRow($rows, array_values($knownSlugs), 0);
        $headers = $this->extractHeaders($rows, $headerIdx);

        $colMap = [];
        foreach ($spec as $field => [$aliases, $forbidden]) {
            $colMap[$field] = $this->findColumn($headers, $aliases, $forbidden);
        }

        if ($colMap['assignment_id'] === null) {
            throw new \Exception(
                'Kolom "assignment_id" tidak ditemukan pada file ' . $file->getClientOriginalName() . '. ' .
                'Header yang terbaca: ' . implode(' | ', array_filter(array_values($headers)))
            );
        }

        $metrik = [
            'metrik_biaya_produksi', 'metrik_biaya_pembelian', 'metrik_biaya_operasional',
            'metrik_biaya_non_operasional', 'metrik_total_pengeluaran', 'metrik_pendapatan_barang_jasa',
            'metrik_pendapatan_lainnya', 'metrik_nilai_tambah', 'metrik_total_aset', 'metrik_output',
            'rasio_biaya_pembelian_omzet', 'rasio_ntb', 'produktivitas',
        ];
        $flags = ['flag_rasio_1_output_aset', 'flag_rasio_2_upah_ntb', 'flag_rasio_3_ntb_output'];

        $insert = [];
        $now = now();

        for ($i = $headerIdx + 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            $rawRow = $raw[$i] ?? [];
            if ($this->isLabelRow($row)) continue;
            if ($this->isRowEmpty($row)) continue;

            $get = fn (string $field) => $this->cell($row, $colMap[$field] ?? null);

            $assignmentId = $get('assignment_id');
            if ($assignmentId === null) continue;

            $kdkab = $get('kdkab');
            $kdkec = $get('kdkec');
            $kddesa = $get('kddesa');
            $kodeSls = $this->padKode($get('kode_sls'), 4);
            $subSls = $this->padKode($get('sub_sls'), 2) ?? '00';
            $kbli = $get('kode_kbli');

            $data = [
                'ntb_negatif_batch_id' => $batch->id,
                'kategori' => $get('kategori'),
                'assignment_id' => $assignmentId,
                'kdprov' => $get('kdprov') ?? '',
                'nmprov' => $get('nmprov') ?? '',
                'kdkab' => $kdkab ?? '',
                'nmkab' => $get('nmkab') ?? '',
                'kdkec' => $kdkec ?? '',
                'nmkec' => $get('nmkec') ?? '',
                'kddesa' => $kddesa ?? '',
                'nmdesa' => $get('nmdesa') ?? '',
                'kode_sls' => $kodeSls ?? '',
                'sub_sls' => $subSls,
                // KBLI selalu 5 digit (01114), jaga nol di depan yang hilang di excel
                'kode_kbli' => $kbli !== null && ctype_digit($kbli) ? str_pad($kbli, 5, '0', STR_PAD_LEFT) : $kbli,
                'des_kbli' => $get('des_kbli'),
                'pjk' => $get('pjk'),
                'nama_usaha' => $get('nama_usaha'),
                'survey_period_id' => $get('survey_period_id'),
                'reklasifikasi_skala_usaha' => $get('reklasifikasi_skala_usaha'),
                'link_fasih_sm' => $get('link_fasih_sm'),
                // kunci untuk mencari PPL/PML di sls_dailies (sama dengan anomali)
                'region_code' => PetugasResolver::buildRegionCode($kdkab, $kdkec, $kddesa, $kodeSls, $subSls),
                'tindak_lanjut' => 'belum',
                'created_at' => $now,
                'updated_at' => $now,
            ];

            foreach ($metrik as $f) {
                $data[$f] = $this->numValue($rawRow, $colMap[$f], $get($f));
            }
            foreach ($flags as $f) {
                $v = $this->numValue($rawRow, $colMap[$f], $get($f));
                $data[$f] = $v === null ? null : (int) $v;
            }

            $insert[] = $data;
        }

        if (empty($insert)) {
            throw new \Exception(
                'Tidak ada baris data yang valid pada file ' . $file->getClientOriginalName() . '. ' .
                'Header yang terbaca: ' . implode(' | ', array_filter(array_values($headers)))
            );
        }

        foreach (array_chunk($insert, 300) as $chunk) {
            NtbNegatifMikro::insert($chunk);
        }

        $this->ringkasan['ntb_negatif'] = [
            'baris' => count($insert),
            'kolom_tidak_ditemukan' => array_keys(array_filter($colMap, fn ($v) => $v === null)),
        ];
    }

    /**
     * Angka dari sel. Utamakan nilai mentah; kalau berupa teks, dukung
     * format "1000000.00", "-1.500.000,00", "1,000,000.00".
     */
    protected function numValue(array $rawRow, ?int $idx, ?string $formatted): ?float
    {
        $val = $idx !== null ? ($rawRow[$idx] ?? null) : null;
        if (is_int($val) || is_float($val)) return (float) $val;
        if (is_bool($val)) return $val ? 1.0 : 0.0;

        $str = trim((string) ($val ?? $formatted ?? ''));
        $str = str_replace([' ', "\xC2\xA0", 'Rp', 'rp'], '', $str);
        if ($str === '' || $str === '-') return null;
        if (is_numeric($str)) return (float) $str;

        $lastDot = strrpos($str, '.');
        $lastComma = strrpos($str, ',');
        if ($lastComma !== false && ($lastDot === false || $lastComma > $lastDot)) {
            // gaya Indonesia: titik ribuan, koma desimal
            $str = str_replace(['.', ','], ['', '.'], $str);
        } else {
            // gaya Inggris: koma ribuan, titik desimal
            $str = str_replace(',', '', $str);
        }

        return is_numeric($str) ? (float) $str : null;
    }
}
