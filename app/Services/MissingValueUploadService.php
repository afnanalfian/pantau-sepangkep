<?php

namespace App\Services;

use App\Models\MissingValueBatch;
use App\Models\MissingValueMikro;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Import excel Missing Value (1 file per tanggal).
 *
 * Meng-extend AnomaliUploadService supaya memakai helper yang sama persis
 * (deteksi baris header, pencarian kolom berbasis alias, normalisasi sel,
 * zero-padding kode SLS) — jadi perilakunya identik dengan modul anomali.
 */
class MissingValueUploadService extends AnomaliUploadService
{
    /**
     * Import satu file excel untuk satu tanggal.
     * Jika tanggal sudah ada, batch lama (beserta data mikronya) diganti.
     */
    public function importMissingValue(UploadedFile $file, string $tanggal, ?string $uploadedBy = null): MissingValueBatch
    {
        return DB::transaction(function () use ($file, $tanggal, $uploadedBy) {
            $existing = MissingValueBatch::whereDate('tanggal', $tanggal)->first();
            if ($existing) {
                $existing->delete(); // cascade menghapus data mikro lama
            }

            $batch = MissingValueBatch::create([
                'tanggal' => $tanggal,
                'uploaded_by' => $uploadedBy,
                'nama_file' => mb_substr($file->getClientOriginalName(), 0, 500),
            ]);

            $this->importMikroMissing($file, $batch);

            return $batch->fresh();
        });
    }

    protected function importMikroMissing(UploadedFile $file, MissingValueBatch $batch): void
    {
        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();

        // $rows  = nilai terformat (seperti tampilan excel) -> dipakai untuk teks
        // $raw   = nilai mentah -> dipakai untuk NIK & skor supaya NIK 16 digit
        //          tidak berubah jadi "7.30904E+15"
        $rows = $sheet->toArray(null, true, true, false);
        $raw = $sheet->toArray(null, true, false, false);

        $knownSlugs = [
            'no', 'namaanggotakeluarga', 'kodeprov', 'namaprovinsi', 'kodekabkota', 'namakabkota',
            'kodekec', 'namakecamatan', 'kodedesa', 'namadesakel', 'kodesls', 'subsls', 'assignmentid',
            'variabelmissing', 'idpetugas', 'emailpetugas', 'jeniskelaminse', 'tgllahirse', 'blnlahirse',
            'thnlahirse', 'nikhasilmatcheddtsen', 'namahasilmatcheddtsen', 'jeniskelaminhasilmatcheddtsen',
            'tanggallahirhasilmatcheddtsen', 'skorkemiripan', 'statusperbaikan', 'linkfasih',
        ];

        $headerIdx = $this->detectHeaderRow($rows, $knownSlugs);
        $headers = $this->extractHeaders($rows, $headerIdx);

        $notDtsen = ['dtsen', 'matched'];

        /** field => [alias (paling spesifik di depan), potongan slug terlarang] */
        $spec = [
            'no' => [['No', 'Nomor', 'Urutan'], ['nama', 'nik']],
            'nama' => [[
                'Nama Anggota Keluarga', 'Nama ART', 'Nama Anggota Rumah Tangga', 'Nama Anggota', 'Nama',
            ], ['dtsen', 'matched', 'prov', 'kab', 'kota', 'kec', 'desa', 'kel', 'sls', 'petugas', 'variabel', 'status']],
            'kdprov' => [['Kode Prov', 'Kode Provinsi', 'KDPROV'], []],
            'nmprov' => [['Nama Provinsi', 'NMPROV', 'Provinsi'], ['kode']],
            'kdkab' => [['Kode Kab/Kota', 'Kode Kabupaten', 'Kode Kab', 'KDKAB'], []],
            'nmkab' => [['Nama Kab/Kota', 'Nama Kabupaten', 'NMKAB'], ['kode']],
            'kdkec' => [['Kode Kec', 'Kode Kecamatan', 'KDKEC'], []],
            'nmkec' => [['Nama Kecamatan', 'NMKEC', 'Kecamatan'], ['kode']],
            'kddesa' => [['Kode Desa', 'Kode Desa/Kel', 'Kode Kelurahan', 'KDDESA'], []],
            'nmdesa' => [['Nama Desa/Kel', 'Nama Desa', 'Nama Kelurahan', 'NMDESA'], ['kode']],
            'kode_sls' => [['Kode SLS', 'KDSLS'], ['sub', 'nama']],
            'sub_sls' => [['Sub SLS', 'SUBSLS', 'Sub-SLS'], []],
            'assignment_id' => [['Assignment ID', 'ID Assignment', 'IdAssignment', 'Assignment'], []],
            'variabel_missing' => [['Variabel Missing', 'Variabel yang Missing', 'Missing Variabel', 'Variabel'], []],
            'id_petugas' => [['ID Petugas', 'Petugas ID', 'Id Petugas'], []],
            'email_petugas' => [['Email Petugas', 'Petugas Email', 'Email'], []],
            'jk_se' => [['Jenis Kelamin SE', 'JK SE'], $notDtsen],
            'tgl_lahir_se' => [['Tgl Lahir SE', 'Tanggal Lahir SE'], $notDtsen],
            'bln_lahir_se' => [['Bln Lahir SE', 'Bulan Lahir SE'], $notDtsen],
            'thn_lahir_se' => [['Thn Lahir SE', 'Tahun Lahir SE'], $notDtsen],
            'nik_dtsen' => [['NIK hasil matched DTSEN', 'NIK DTSEN', 'NIK matched'], []],
            'nama_dtsen' => [['Nama hasil matched DTSEN', 'Nama DTSEN', 'Nama matched'], []],
            'jk_dtsen' => [['Jenis Kelamin hasil matched DTSEN', 'Jenis Kelamin DTSEN', 'JK DTSEN'], []],
            'tgl_lahir_dtsen' => [['Tanggal lahir hasil matched DTSEN', 'Tgl Lahir DTSEN', 'Tanggal Lahir DTSEN'], []],
            'skor_kemiripan' => [['Skor Kemiripan', 'Skor', 'Similarity'], []],
            'status_perbaikan' => [['Status Perbaikan'], []],
            'link_fasih' => [['Link Fasih', 'Fasih Link', 'URL Fasih', 'Link'], []],
        ];

        $colMap = [];
        foreach ($spec as $field => [$aliases, $forbidden]) {
            $colMap[$field] = $this->findColumn($headers, $aliases, $forbidden);
        }

        // Fallback posisi: kolom nama selalu tepat setelah "No"
        if ($colMap['nama'] === null) {
            $kandidat = $colMap['no'] !== null ? $colMap['no'] + 1 : 1;
            $terpakai = array_filter($colMap, fn ($v) => $v !== null);
            if (!in_array($kandidat, $terpakai, true) && array_key_exists($kandidat, $headers)) {
                $colMap['nama'] = $kandidat;
                Log::warning('[MissingValueUpload] Kolom nama tidak dikenali, memakai fallback posisi.', [
                    'file' => $file->getClientOriginalName(),
                    'header_terbaca' => array_values($headers),
                ]);
            }
        }

        if ($colMap['assignment_id'] === null) {
            throw new \Exception(
                'Kolom "Assignment ID" tidak ditemukan pada file ' . $file->getClientOriginalName() . '. ' .
                'Header yang terbaca: ' . implode(' | ', array_filter(array_values($headers)))
            );
        }

        $insert = [];
        $tanpaNama = 0;
        $now = now();

        for ($i = $headerIdx + 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            $rawRow = $raw[$i] ?? [];
            if ($this->isLabelRow($row)) continue;
            if ($this->isRowEmpty($row)) continue;

            $get = fn (string $field) => $this->cell($row, $colMap[$field] ?? null);

            $assignmentId = $get('assignment_id');
            if ($assignmentId === null) continue;

            $nama = $get('nama');
            if ($nama === null) $tanpaNama++;

            $kdkab = $get('kdkab');
            $kdkec = $get('kdkec');
            $kddesa = $get('kddesa');
            $kodeSls = $this->padKode($get('kode_sls'), 4);
            $subSls = $this->padKode($get('sub_sls'), 2) ?? '00';
            $email = $get('email_petugas');

            $insert[] = [
                'missing_value_batch_id' => $batch->id,
                'no' => is_numeric($get('no')) ? (int) $get('no') : null,
                'nama' => $nama,
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
                'assignment_id' => $assignmentId,
                'variabel_missing' => $get('variabel_missing'),
                'id_petugas' => $get('id_petugas'),
                'email_petugas' => $email ? mb_strtolower($email) : null,
                'jk_se' => $get('jk_se'),
                'tgl_lahir_se' => $get('tgl_lahir_se'),
                'bln_lahir_se' => $get('bln_lahir_se'),
                'thn_lahir_se' => $get('thn_lahir_se'),
                'nik_dtsen' => $this->nikValue($rawRow, $colMap['nik_dtsen'], $get('nik_dtsen')),
                'nama_dtsen' => $get('nama_dtsen'),
                'jk_dtsen' => $get('jk_dtsen'),
                'tgl_lahir_dtsen' => $get('tgl_lahir_dtsen'),
                'skor_kemiripan' => $this->skorValue($rawRow, $colMap['skor_kemiripan'], $get('skor_kemiripan')),
                'status_perbaikan' => $get('status_perbaikan'),
                'link_fasih' => $get('link_fasih'),
                // kunci untuk mencari PPL/PML di sls_dailies (metode sama dengan anomali)
                'region_code' => PetugasResolver::buildRegionCode($kdkab, $kdkec, $kddesa, $kodeSls, $subSls),
                'tindak_lanjut' => 'belum',
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (empty($insert)) {
            throw new \Exception(
                'Tidak ada baris data yang valid pada file ' . $file->getClientOriginalName() . '. ' .
                'Header yang terbaca: ' . implode(' | ', array_filter(array_values($headers)))
            );
        }

        foreach (array_chunk($insert, 300) as $chunk) {
            MissingValueMikro::insert($chunk);
        }

        $this->ringkasan['missing_value'] = [
            'baris' => count($insert),
            'tanpa_nama' => $tanpaNama,
            'kolom_tidak_ditemukan' => array_keys(array_filter($colMap, fn ($v) => $v === null)),
        ];
    }

    /** NIK selalu string digit utuh (16 digit), aman dari notasi ilmiah excel. */
    protected function nikValue(array $rawRow, ?int $idx, ?string $formatted): ?string
    {
        $val = $idx !== null ? ($rawRow[$idx] ?? null) : null;

        if (is_int($val)) return (string) $val;
        if (is_float($val)) return sprintf('%.0f', $val);

        $str = trim((string) ($val ?? $formatted ?? ''), " '\t\n\r\0\x0B");
        if ($str === '' || $str === '-') return null;

        // "7.30904E+15" (kalau terlanjur tersimpan sebagai teks)
        if (preg_match('/^\d+(\.\d+)?E\+?\d+$/i', $str)) {
            return sprintf('%.0f', (float) $str);
        }

        return $str;
    }

    protected function skorValue(array $rawRow, ?int $idx, ?string $formatted): ?float
    {
        $val = $idx !== null ? ($rawRow[$idx] ?? null) : null;
        if (is_int($val) || is_float($val)) return (float) $val;

        $str = trim((string) ($val ?? $formatted ?? ''));
        if ($str === '' || $str === '-') return null;

        $str = rtrim($str, '%');
        if (is_numeric($str)) return (float) $str;
        $clean = str_replace(',', '.', $str);

        return is_numeric($clean) ? (float) $clean : null;
    }
}
