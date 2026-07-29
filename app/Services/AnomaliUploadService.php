<?php

namespace App\Services;

use App\Models\AnomaliBatch;
use App\Models\AnomaliMikro;
use App\Models\AnomaliRadar;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class AnomaliUploadService
{
    /**
     * Import keempat file sekaligus untuk satu tanggal (batch pekanan).
     * Jika tanggal sudah ada, batch lama (dan turunannya) dihapus & digantikan (via cascade).
     *
     * @param  array{radar_usaha: UploadedFile, radar_keluarga: UploadedFile, mikro_usaha: UploadedFile, mikro_keluarga: UploadedFile}  $files
     */
    public function importBatch(array $files, string $tanggal, ?string $uploadedBy = null): AnomaliBatch
    {
        return DB::transaction(function () use ($files, $tanggal, $uploadedBy) {
            $existing = AnomaliBatch::where('tanggal', $tanggal)->first();
            if ($existing) {
                $existing->delete(); // cascade menghapus radar & mikro lama
            }

            $batch = AnomaliBatch::create(['tanggal' => $tanggal, 'uploaded_by' => $uploadedBy]);

            if (!empty($files['radar_usaha'])) {
                $this->importRadar($files['radar_usaha'], $batch, 'usaha');
            }
            if (!empty($files['radar_keluarga'])) {
                $this->importRadar($files['radar_keluarga'], $batch, 'keluarga');
            }
            if (!empty($files['mikro_usaha'])) {
                $this->importMikro($files['mikro_usaha'], $batch, 'usaha');
            }
            if (!empty($files['mikro_keluarga'])) {
                $this->importMikro($files['mikro_keluarga'], $batch, 'keluarga');
            }

            return $batch->fresh();
        });
    }

    /**
     * Cari baris header di antara beberapa baris pertama file (biasanya baris ke-4).
     */
    protected function findHeaderRow(array $rows, string $firstColumnMarker): int
    {
        foreach ($rows as $i => $row) {
            $first = trim((string) ($row[0] ?? ''));
            if (strcasecmp($first, $firstColumnMarker) === 0) {
                return $i;
            }
        }
        return 3; // fallback: baris ke-4 (index 3) sesuai spesifikasi
    }

    protected function isLabelRow(array $row): bool
    {
        // baris berisi (1) (2) (3) dst, harus dilewati
        $first = trim((string) ($row[0] ?? ''));
        return (bool) preg_match('/^\(\d+\)$/', $first);
    }

    protected function toNumber($val): float
    {
        if ($val === null || $val === '') return 0;
        if (is_numeric($val)) return (float) $val;
        // handle koma desimal ala Indonesia: "45,84" -> 45.84
        $clean = str_replace(['.', ','], ['', '.'], (string) $val);
        return is_numeric($clean) ? (float) $clean : 0;
    }

    public function importRadar(UploadedFile $file, AnomaliBatch $batch, string $jenis): void
    {
        $spreadsheet = IOFactory::load($file->getRealPath());
        $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);

        $headerIdx = $this->findHeaderRow($rows, 'Kode');
        $headers = array_map(fn ($h) => trim((string) $h), $rows[$headerIdx]);

        $insert = [];
        for ($i = $headerIdx + 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            if ($this->isLabelRow($row)) continue;
            if (empty(array_filter($row, fn ($v) => $v !== null && $v !== ''))) continue;

            $assoc = [];
            foreach ($headers as $idx => $h) {
                if ($h === '') continue;
                $assoc[$h] = $row[$idx] ?? null;
            }

            $kode = trim((string) ($assoc['Kode'] ?? ''));
            $kecamatan = trim((string) ($assoc['Kecamatan'] ?? ''));
            if ($kode === '' && $kecamatan === '') continue;

            $data = [];
            foreach ($assoc as $col => $val) {
                if (preg_match('/^Anomali\s*(\d+)\s*-\s*(Belum|Sudah)\s*Tindak\s*Lanjut$/i', $col, $m)) {
                    $n = $m[1];
                    $status = strtolower($m[2]);
                    $data[$n][$status] = $this->toNumber($val);
                } elseif (preg_match('/^Persentase\s*Anomali\s*(\d+)\s*-\s*(Belum|Sudah)\s*Tindak\s*Lanjut$/i', $col, $m)) {
                    $n = $m[1];
                    $status = strtolower($m[2]);
                    $data[$n]['persen_' . $status] = $this->toNumber($val);
                }
            }

            $insert[] = [
                'anomali_batch_id' => $batch->id,
                'jenis' => $jenis,
                'kode' => $kode,
                'kecamatan' => $kecamatan,
                'total_assignment' => $this->toNumber($assoc['Total Assignment'] ?? 0),
                'data' => json_encode($data),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        foreach (array_chunk($insert, 300) as $chunk) {
            AnomaliRadar::insert($chunk);
        }
    }

    public function importMikro(UploadedFile $file, AnomaliBatch $batch, string $jenis): void
    {
        $spreadsheet = IOFactory::load($file->getRealPath());
        $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);

        $headerIdx = $this->findHeaderRow($rows, 'No');
        $headers = array_map(fn ($h) => trim((string) $h), $rows[$headerIdx]);

        $nameCol = $jenis === 'usaha' ? 'Nama Usaha' : 'Nama KRT';

        $insert = [];
        for ($i = $headerIdx + 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            if ($this->isLabelRow($row)) continue;
            if (empty(array_filter($row, fn ($v) => $v !== null && $v !== ''))) continue;

            $assoc = [];
            foreach ($headers as $idx => $h) {
                if ($h === '') continue;
                $assoc[$h] = $row[$idx] ?? null;
            }

            $assignmentId = trim((string) ($assoc['Assignment ID'] ?? ''));
            if ($assignmentId === '') continue;

            $petugas = trim((string) ($assoc['ID Petugas'] ?? ''));
            $emailPetugas = trim((string) ($assoc['Email Petugas'] ?? ''));

            $insert[] = [
                'anomali_batch_id' => $batch->id,
                'jenis' => $jenis,
                'no' => is_numeric($assoc['No'] ?? null) ? (int) $assoc['No'] : null,
                'nama' => trim((string) ($assoc[$nameCol] ?? '')),
                'kdprov' => trim((string) ($assoc['Kode Prov'] ?? '')),
                'nmprov' => trim((string) ($assoc['Nama Provinsi'] ?? '')),
                'kdkab' => trim((string) ($assoc['Kode Kab/Kota'] ?? '')),
                'nmkab' => trim((string) ($assoc['Nama Kab/Kota'] ?? '')),
                'kdkec' => trim((string) ($assoc['Kode Kec'] ?? '')),
                'nmkec' => trim((string) ($assoc['Nama Kecamatan'] ?? '')),
                'kddesa' => trim((string) ($assoc['Kode Desa'] ?? '')),
                'nmdesa' => trim((string) ($assoc['Nama Desa/Kel'] ?? '')),
                'kode_sls' => trim((string) ($assoc['Kode SLS'] ?? '')),
                'sub_sls' => trim((string) ($assoc['Sub SLS'] ?? '')),
                'assignment_id' => $assignmentId,
                'nama_anomali' => trim((string) ($assoc['Nama Anomali'] ?? '')),
                'tindak_lanjut' => 'belum',
                'id_petugas' => ($petugas === '-' || $petugas === '') ? null : $petugas,
                'email_petugas' => ($emailPetugas === '-' || $emailPetugas === '') ? null : strtolower($emailPetugas),
                'link_fasih' => trim((string) ($assoc['Link Fasih'] ?? '')),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        foreach (array_chunk($insert, 300) as $chunk) {
            AnomaliMikro::insert($chunk);
        }
    }
}
