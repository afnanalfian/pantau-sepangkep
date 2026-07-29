<?php

namespace App\Services;

use App\Models\DailyUpload;
use App\Models\Mitra;
use App\Models\SlsDaily;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class DashboardUploadService
{
    /**
     * Mapping header kolom excel (persis 50 kolom template) -> field DB.
     */
    protected array $map = [
        'regionCode' => 'region_code',
        'username' => 'username',
        'Email PML' => 'email_pml',
        'NIK PPL' => 'nik_ppl',
        'NIK PML' => 'nik_pml',
        'Nama SLS' => 'nama_sls',
        'Nama PPL' => 'nama_ppl',
        'Nama PML' => 'nama_pml',
        'PML_Organik' => 'pml_organik',
        'KDDESA' => 'kddesa',
        'NMDES' => 'nmdes',
        'KDKEC' => 'kdkec',
        'NMKEC' => 'nmkec',
        'totalRegion' => 'total_region',
        'APPROVED BY Pengawas' => 'approved_pengawas',
        'OPEN' => 'open',
        'DRAFT' => 'draft',
        'SUBMITTED BY Pencacah' => 'submitted_pencacah',
        'REJECTED BY Pengawas' => 'rejected_pengawas',
        'EDITED BY Admin Kabupaten' => 'edited_admin_kab',
        'REVOKED BY Pengawas' => 'revoked_pengawas',
        'SUBMITTED RESPONDENT' => 'submitted_respondent',
        'REJECTED BY Admin Kabupaten' => 'rejected_admin_kab',
        'COMPLETED BY Admin Kabupaten' => 'completed_admin_kab',
        'EDITED BY Pengawas' => 'edited_pengawas',
        'capaian_ppl' => 'capaian_ppl',
        'capaian_pml' => 'capaian_pml',
        'Keluarga_Prelist Awal' => 'kk_prelist_awal',
        'Keluarga_Ditemukan' => 'kk_ditemukan',
        'Keluarga_Baru' => 'kk_baru',
        'Keluarga_Meninggal' => 'kk_meninggal',
        'Keluarga_Tidak Eligible' => 'kk_tidak_eligible',
        'Keluarga_Tidak Dapat Ditemui Sampai Akhir Pendataan' => 'kk_tidak_dapat_ditemui',
        'Keluarga_Tidak Ditemukan' => 'kk_tidak_ditemukan',
        'Muatan_Keluarga' => 'muatan_keluarga',
        'Usaha_Prelist Awal' => 'usaha_prelist_awal',
        'Usaha_ditemukan' => 'usaha_ditemukan',
        'Usaha_tutup' => 'usaha_tutup',
        'Usaha_ganda' => 'usaha_ganda',
        'Usaha_tidak_ditemukan' => 'usaha_tidak_ditemukan',
        'Usaha_baru' => 'usaha_baru',
        'Muatan_Usaha' => 'muatan_usaha',
        'Usaha_dalam_keluarga_ditemukan' => 'ukdk_ditemukan',
        'Usaha_dalam_keluarga_tutup' => 'ukdk_tutup',
        'Usaha_dalam_keluarga_ganda' => 'ukdk_ganda',
        'Usaha_dalam_keluarga_tidak_ditemukan' => 'ukdk_tidak_ditemukan',
        'Usaha_dalam_keluarga_baru' => 'ukdk_baru',
        'Muatan_Usaha_Keluarga' => 'muatan_usaha_keluarga',
        'Total_Prelist_Awal' => 'total_prelist_awal',
        'Muatan_Total' => 'muatan_total',
    ];

    protected array $numericFields = [
        'total_region', 'approved_pengawas', 'open', 'draft', 'submitted_pencacah',
        'rejected_pengawas', 'edited_admin_kab', 'revoked_pengawas', 'submitted_respondent',
        'rejected_admin_kab', 'completed_admin_kab', 'edited_pengawas', 'capaian_ppl', 'capaian_pml',
        'kk_prelist_awal', 'kk_ditemukan', 'kk_baru', 'kk_meninggal', 'kk_tidak_eligible',
        'kk_tidak_dapat_ditemui', 'kk_tidak_ditemukan', 'muatan_keluarga',
        'usaha_prelist_awal', 'usaha_ditemukan', 'usaha_tutup', 'usaha_ganda',
        'usaha_tidak_ditemukan', 'usaha_baru', 'muatan_usaha',
        'ukdk_ditemukan', 'ukdk_tutup', 'ukdk_ganda', 'ukdk_tidak_ditemukan', 'ukdk_baru',
        'muatan_usaha_keluarga', 'total_prelist_awal', 'muatan_total',
    ];

    /**
     * @param  UploadedFile  $file
     * @param  string  $tanggal  Y-m-d - tanggal progres yang direpresentasikan file ini
     */
    public function import(UploadedFile $file, string $tanggal, ?string $uploadedBy = null): DailyUpload
    {
        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, false);

        if (count($rows) < 2) {
            throw new \RuntimeException('File excel kosong atau tidak memiliki data.');
        }

        $headers = array_map(fn ($h) => trim((string) $h), $rows[0]);

        return DB::transaction(function () use ($rows, $headers, $tanggal, $uploadedBy, $file) {
            // Hapus data hari yg sama jika sudah pernah diupload (replace)
            $daily = DailyUpload::updateOrCreate(
                ['tanggal' => $tanggal],
                ['nama_file' => $file->getClientOriginalName(), 'uploaded_by' => $uploadedBy]
            );
            $daily->slsDailies()->delete();

            $mitraUpserts = [];
            $slsRows = [];

            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                if (empty(array_filter($row, fn ($v) => $v !== null && $v !== ''))) {
                    continue;
                }

                $assoc = [];
                foreach ($headers as $idx => $h) {
                    if ($h === '') continue;
                    $assoc[$h] = $row[$idx] ?? null;
                }

                $data = ['daily_upload_id' => $daily->id, 'tanggal' => $tanggal];
                foreach ($this->map as $excelCol => $dbField) {
                    $val = $assoc[$excelCol] ?? null;
                    if (in_array($dbField, $this->numericFields)) {
                        $val = is_numeric($val) ? (float) $val : 0;
                    } else {
                        $val = $val !== null ? trim((string) $val) : null;
                    }
                    $data[$dbField] = $val;
                }

                if (empty($data['region_code'])) continue;

                $slsRows[] = $data;

                if (!empty($data['username'])) {
                    $email = strtolower(trim($data['username']));
                    $mitraUpserts[$email] = [
                        'email' => $email,
                        'nama_ppl' => $data['nama_ppl'],
                        'nama_pml' => $data['nama_pml'],
                        'email_pml' => $data['email_pml'],
                        'pml_organik' => $data['pml_organik'],
                        'updated_at' => now(),
                        'created_at' => now(),
                    ];
                }
            }

            foreach (array_chunk($slsRows, 500) as $chunk) {
                SlsDaily::insert($chunk);
            }

            if (!empty($mitraUpserts)) {
                Mitra::upsert(
                    array_values($mitraUpserts),
                    ['email'],
                    ['nama_ppl', 'nama_pml', 'email_pml', 'pml_organik', 'updated_at']
                );
            }

            return $daily->fresh();
        });
    }
}
