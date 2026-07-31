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

        // Mapping untuk berbagai kemungkinan nama kolom
        $columnMapping = [
            'nama' => ['Nama KRT', 'Nama Kepala Keluarga', 'Nama KK', 'Nama Usaha', 'Nama Perusahaan', 'Nama'],
            'kdprov' => ['Kode Prov', 'Kode Provinsi', 'Provinsi Kode'],
            'nmprov' => ['Nama Provinsi', 'Provinsi', 'Nama Prov'],
            'kdkab' => ['Kode Kab/Kota', 'Kode Kabupaten', 'Kode Kota', 'Kab/Kota Kode'],
            'nmkab' => ['Nama Kab/Kota', 'Kabupaten', 'Kota', 'Nama Kabupaten'],
            'kdkec' => ['Kode Kec', 'Kode Kecamatan', 'Kecamatan Kode'],
            'nmkec' => ['Nama Kecamatan', 'Kecamatan'],
            'kddesa' => ['Kode Desa', 'Kode Kelurahan', 'Desa/Kel Kode'],
            'nmdesa' => ['Nama Desa/Kel', 'Nama Desa', 'Nama Kelurahan', 'Desa/Kel'],
            'kode_sls' => ['Kode SLS', 'SLS'],
            'sub_sls' => ['Sub SLS', 'SLS Sub'],
            'assignment_id' => ['Assignment ID', 'ID Assignment', 'Assignment'],
            'nama_anomali' => ['Nama Anomali', 'Anomali', 'Jenis Anomali'],
            'id_petugas' => ['ID Petugas', 'Petugas ID', 'NIP Petugas'],
            'email_petugas' => ['Email Petugas', 'Petugas Email', 'Email'],
            'link_fasih' => ['Link Fasih', 'Fasih Link', 'URL Fasih'],
            'no' => ['No', 'Nomor', 'Urutan'],
        ];

        // Fungsi untuk mencari kolom berdasarkan mapping
        $findColumn = function(string $field, array $headers) use ($columnMapping): ?int {
            $possibleNames = $columnMapping[$field] ?? [];
            foreach ($possibleNames as $possibleName) {
                $index = array_search($possibleName, $headers);
                if ($index !== false) {
                    return $index;
                }
                // Coba case-insensitive
                foreach ($headers as $idx => $header) {
                    if (strtolower(trim($header)) === strtolower(trim($possibleName))) {
                        return $idx;
                    }
                }
            }
            return null;
        };

        // Mapping field => column index
        $colMap = [];
        foreach (array_keys($columnMapping) as $field) {
            $colMap[$field] = $findColumn($field, $headers);
        }

        // Validasi: pastikan kolom penting ada
        $requiredFields = ['nama', 'assignment_id'];
        $missingRequired = [];
        foreach ($requiredFields as $field) {
            if ($colMap[$field] === null) {
                $possibleNames = implode(' atau ', $columnMapping[$field] ?? []);
                $missingRequired[] = "$field ($possibleNames)";
            }
        }

        if (!empty($missingRequired)) {
            throw new \Exception('Kolom wajib tidak ditemukan: ' . implode(', ', $missingRequired));
        }

        $insert = [];
        for ($i = $headerIdx + 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            if ($this->isLabelRow($row)) continue;
            if (empty(array_filter($row, fn ($v) => $v !== null && $v !== ''))) continue;

            // Ambil nilai dari kolom yang sudah dipetakan
            $getValue = function(string $field) use ($row, $colMap) {
                $index = $colMap[$field] ?? null;
                if ($index === null || !isset($row[$index])) {
                    return null;
                }
                return trim((string) $row[$index]);
            };

            $assignmentId = $getValue('assignment_id');
            if (empty($assignmentId)) continue;

            $nama = $getValue('nama');
            if (empty($nama)) continue;

            $petugas = $getValue('id_petugas');
            $emailPetugas = $getValue('email_petugas');

            $insert[] = [
                'anomali_batch_id' => $batch->id,
                'jenis' => $jenis,
                'no' => is_numeric($getValue('no')) ? (int) $getValue('no') : null,
                'nama' => $nama,
                'kdprov' => $getValue('kdprov') ?? '',
                'nmprov' => $getValue('nmprov') ?? '',
                'kdkab' => $getValue('kdkab') ?? '',
                'nmkab' => $getValue('nmkab') ?? '',
                'kdkec' => $getValue('kdkec') ?? '',
                'nmkec' => $getValue('nmkec') ?? '',
                'kddesa' => $getValue('kddesa') ?? '',
                'nmdesa' => $getValue('nmdesa') ?? '',
                'kode_sls' => $getValue('kode_sls') ?? '',
                'sub_sls' => $getValue('sub_sls') ?? '',
                'assignment_id' => $assignmentId,
                'nama_anomali' => $getValue('nama_anomali') ?? '',
                'tindak_lanjut' => 'belum',
                'id_petugas' => ($petugas === '-' || $petugas === '') ? null : $petugas,
                'email_petugas' => ($emailPetugas === '-' || $emailPetugas === '') ? null : strtolower($emailPetugas),
                'link_fasih' => $getValue('link_fasih') ?? '',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (empty($insert)) {
            throw new \Exception('Tidak ada data yang valid untuk diimport.');
        }

        foreach (array_chunk($insert, 300) as $chunk) {
            AnomaliMikro::insert($chunk);
        }
    }
}
