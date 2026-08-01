<?php

namespace App\Services;

use App\Models\AnomaliBatch;
use App\Models\AnomaliMikro;
use App\Models\AnomaliRadar;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;

class AnomaliUploadService
{
    /**
     * Ringkasan hasil import terakhir (dipakai untuk pesan di UI / debugging).
     *
     * @var array<string, mixed>
     */
    public array $ringkasan = [];

    /**
     * Import keempat file sekaligus untuk satu tanggal (batch pekanan).
     * Jika tanggal sudah ada, batch lama (dan turunannya) dihapus & digantikan (via cascade).
     *
     * @param  array{radar_usaha?: UploadedFile, radar_keluarga?: UploadedFile, mikro_usaha?: UploadedFile, mikro_keluarga?: UploadedFile}  $files
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

    // =====================================================================
    // HELPER NORMALISASI
    // =====================================================================

    /**
     * Normalisasi teks header: buang non-breaking space, rapatkan spasi ganda,
     * lowercase. "Nama  Kepala Keluarga " -> "nama kepala keluarga".
     */
    protected function normalizeHeader($h): string
    {
        $h = (string) $h;
        // NBSP (U+00A0), zero width space, dsb -> spasi biasa
        $h = str_replace(["\xC2\xA0", "\xE2\x80\x8B", "\xEF\xBB\xBF"], ' ', $h);
        $h = preg_replace('/\s+/u', ' ', $h) ?? $h;

        return trim(mb_strtolower($h));
    }

    /**
     * Bentuk "slug" header: hanya huruf & angka. Kebal terhadap perbedaan
     * spasi, garis miring, titik, strip, dsb.
     * "Nama Kab/Kota" -> "namakabkota", "Nama_KRT" -> "namakrt".
     */
    protected function slugHeader($h): string
    {
        return preg_replace('/[^a-z0-9]/', '', $this->normalizeHeader($h)) ?? '';
    }

    protected function isLabelRow(array $row): bool
    {
        // baris berisi (1) (2) (3) dst, harus dilewati
        $first = trim((string) ($row[0] ?? ''));

        return (bool) preg_match('/^\(\d+\)$/', $first);
    }

    protected function isRowEmpty(array $row): bool
    {
        return empty(array_filter($row, fn ($v) => $v !== null && trim((string) $v) !== ''));
    }

    protected function toNumber($val): float
    {
        if ($val === null || $val === '') return 0;
        if (is_numeric($val)) return (float) $val;
        // handle koma desimal ala Indonesia: "45,84" -> 45.84
        $clean = str_replace(['.', ','], ['', '.'], (string) $val);

        return is_numeric($clean) ? (float) $clean : 0;
    }

    /**
     * Nilai sel apa adanya (string), sekaligus membersihkan placeholder "-".
     */
    protected function cell(array $row, ?int $index): ?string
    {
        if ($index === null || !array_key_exists($index, $row)) return null;
        $val = $row[$index];
        if ($val === null) return null;

        // Excel kadang mengembalikan angka float utk kolom kode: 8.0 -> "8"
        if (is_float($val) && floor($val) == $val) {
            $val = (string) (int) $val;
        }

        $val = trim((string) $val);

        return ($val === '' || $val === '-') ? null : $val;
    }

    // =====================================================================
    // DETEKSI BARIS HEADER
    // =====================================================================

    /**
     * Cari baris header dengan cara memberi skor ke tiap baris di 25 baris
     * pertama: makin banyak sel yang cocok dengan daftar header yang kita
     * kenal, makin besar kemungkinan baris itu header.
     *
     * Jauh lebih tahan banting dibanding "cari baris yang sel pertamanya = No",
     * karena file FASIH kadang punya judul/kop di atas, kolom kosong di kiri,
     * atau penulisan header yang sedikit berbeda.
     *
     * @param  array<int, array<int, mixed>>  $rows
     * @param  array<int, string>  $knownSlugs
     */
    protected function detectHeaderRow(array $rows, array $knownSlugs, int $fallback = 3): int
    {
        $bestIdx = null;
        $bestScore = 0;
        $limit = min(count($rows), 25);

        for ($i = 0; $i < $limit; $i++) {
            $score = 0;
            foreach ($rows[$i] as $cell) {
                $slug = $this->slugHeader($cell);
                if ($slug === '') continue;
                if (in_array($slug, $knownSlugs, true)) {
                    $score++;
                }
            }
            if ($score > $bestScore) {
                $bestScore = $score;
                $bestIdx = $i;
            }
        }

        if ($bestIdx !== null && $bestScore >= 3) {
            return $bestIdx;
        }

        return min($fallback, max(0, count($rows) - 1));
    }

    /**
     * Ambil header pada baris tertentu. Jika ada sel header yang kosong
     * (kasus header ter-merge 2 baris), diisi dari baris di atasnya.
     *
     * @param  array<int, array<int, mixed>>  $rows
     * @return array<int, string>  index kolom => teks header asli
     */
    protected function extractHeaders(array $rows, int $headerIdx): array
    {
        $headers = [];
        $row = $rows[$headerIdx] ?? [];
        $above = $headerIdx > 0 ? ($rows[$headerIdx - 1] ?? []) : [];

        foreach ($row as $idx => $val) {
            $text = trim((string) ($val ?? ''));
            if ($text === '') {
                $textAbove = trim((string) ($above[$idx] ?? ''));
                // hanya dipakai kalau baris atas benar-benar terlihat seperti header
                if ($textAbove !== '' && !is_numeric($textAbove)) {
                    $text = $textAbove;
                }
            }
            $headers[$idx] = $text;
        }

        return $headers;
    }

    /**
     * Cari index kolom untuk sebuah field.
     *
     * Urutan pencarian:
     *   1. Cocok persis (berdasarkan slug) dengan salah satu alias.
     *   2. Cocok "mengandung" alias, tapi header tsb tidak boleh mengandung
     *      kata terlarang (mis. kolom "Nama Anomali" tidak boleh dianggap
     *      sebagai kolom nama KRT).
     *
     * @param  array<int, string>  $headers
     * @param  array<int, string>  $aliases  daftar nama kolom yang mungkin
     * @param  array<int, string>  $forbidden  potongan slug yang membatalkan match
     */
    protected function findColumn(array $headers, array $aliases, array $forbidden = []): ?int
    {
        $slugs = [];
        foreach ($headers as $idx => $h) {
            $slug = $this->slugHeader($h);
            if ($slug !== '') {
                $slugs[$idx] = $slug;
            }
        }

        // 1) exact match, mengikuti urutan prioritas alias
        foreach ($aliases as $alias) {
            $aliasSlug = $this->slugHeader($alias);
            if ($aliasSlug === '') continue;
            foreach ($slugs as $idx => $slug) {
                if ($slug === $aliasSlug) {
                    return $idx;
                }
            }
        }

        // 2) partial match
        foreach ($aliases as $alias) {
            $aliasSlug = $this->slugHeader($alias);
            if ($aliasSlug === '' || mb_strlen($aliasSlug) < 4) continue;
            foreach ($slugs as $idx => $slug) {
                if (!str_contains($slug, $aliasSlug)) continue;
                foreach ($forbidden as $bad) {
                    if (str_contains($slug, $bad)) {
                        continue 2;
                    }
                }

                return $idx;
            }
        }

        return null;
    }

    // =====================================================================
    // IMPORT RADAR
    // =====================================================================

    public function importRadar(UploadedFile $file, AnomaliBatch $batch, string $jenis): void
    {
        $spreadsheet = IOFactory::load($file->getRealPath());
        $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);

        $headerIdx = $this->detectHeaderRow($rows, ['kode', 'kecamatan', 'totalassignment']);
        $headers = array_map(fn ($h) => trim((string) $h), $this->extractHeaders($rows, $headerIdx));

        $insert = [];
        for ($i = $headerIdx + 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            if ($this->isLabelRow($row)) continue;
            if ($this->isRowEmpty($row)) continue;

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

    // =====================================================================
    // IMPORT MIKRO
    // =====================================================================

    public function importMikro(UploadedFile $file, AnomaliBatch $batch, string $jenis): void
    {
        $spreadsheet = IOFactory::load($file->getRealPath());
        $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);

        // Header yang "dikenali" untuk mendeteksi baris header.
        $knownSlugs = [
            'no', 'namakrt', 'namakepalakeluarga', 'namakk', 'namausaha', 'namaperusahaan',
            'kodeprov', 'namaprovinsi', 'kodekabkota', 'namakabkota', 'kodekec', 'namakecamatan',
            'kodedesa', 'namadesakel', 'kodesls', 'subsls', 'assignmentid', 'namaanomali',
            'tindaklanjut', 'idpetugas', 'emailpetugas', 'linkfasih',
        ];

        $headerIdx = $this->detectHeaderRow($rows, $knownSlugs);
        $headers = $this->extractHeaders($rows, $headerIdx);

        /**
         * Daftar alias tiap field. Alias paling spesifik ditaruh paling depan.
         * $forbidden mencegah salah tangkap (mis. "Nama Anomali" / "Nama Provinsi"
         * ikut terpilih sebagai kolom nama KRT).
         */
        $forbiddenNama = [
            'anomali', 'prov', 'kab', 'kota', 'kec', 'desa', 'kel', 'sls',
            'petugas', 'file', 'batch', 'wilayah', 'status',
        ];

        $spec = [
            'nama' => [[
                'Nama KRT', 'Nama Kepala Keluarga', 'Nama Kepala Rumah Tangga', 'Nama KK',
                'Nama Kepala RT', 'Nama Responden', 'Nama Usaha', 'Nama Perusahaan',
                'Nama Usaha/Perusahaan', 'Nama Pemilik', 'Nama Komersial', 'Nama',
            ], $forbiddenNama],
            'kdprov' => [['Kode Prov', 'Kode Provinsi', 'KDPROV', 'Prov Kode'], []],
            'nmprov' => [['Nama Provinsi', 'NMPROV', 'Provinsi'], ['kode']],
            'kdkab' => [['Kode Kab/Kota', 'Kode Kabupaten', 'Kode Kab', 'KDKAB'], []],
            'nmkab' => [['Nama Kab/Kota', 'Nama Kabupaten', 'NMKAB', 'Kabupaten', 'Kota'], ['kode']],
            'kdkec' => [['Kode Kec', 'Kode Kecamatan', 'KDKEC'], []],
            'nmkec' => [['Nama Kecamatan', 'NMKEC', 'Kecamatan'], ['kode']],
            'kddesa' => [['Kode Desa', 'Kode Desa/Kel', 'Kode Kelurahan', 'KDDESA'], []],
            'nmdesa' => [['Nama Desa/Kel', 'Nama Desa', 'Nama Kelurahan', 'NMDESA', 'Desa/Kel'], ['kode']],
            'kode_sls' => [['Kode SLS', 'KDSLS', 'SLS'], ['sub', 'nama']],
            'sub_sls' => [['Sub SLS', 'SUBSLS', 'Sub-SLS'], []],
            'assignment_id' => [['Assignment ID', 'ID Assignment', 'IdAssignment', 'Assignment'], []],
            'nama_anomali' => [['Nama Anomali', 'Jenis Anomali', 'Anomali'], ['penjelasan', 'jumlah']],
            'id_petugas' => [['ID Petugas', 'Petugas ID', 'NIP Petugas', 'Id Petugas'], []],
            'email_petugas' => [['Email Petugas', 'Petugas Email', 'Email'], []],
            'link_fasih' => [['Link Fasih', 'Fasih Link', 'URL Fasih', 'Link'], []],
            'no' => [['No', 'Nomor', 'Urutan'], ['nama', 'anomali']],
        ];

        $colMap = [];
        foreach ($spec as $field => [$aliases, $forbidden]) {
            $colMap[$field] = $this->findColumn($headers, $aliases, $forbidden);
        }

        // ---------------------------------------------------------------
        // Fallback posisi untuk kolom nama.
        // Template FASIH selalu menaruh kolom nama TEPAT setelah kolom "No".
        // Jadi kalau nama kolomnya tidak dikenali sama sekali (typo, karakter
        // aneh, header ter-merge), kita ambil kolom sebelah kanan "No".
        // ---------------------------------------------------------------
        if ($colMap['nama'] === null) {
            $kandidat = $colMap['no'] !== null ? $colMap['no'] + 1 : 1;
            $terpakai = array_filter($colMap, fn ($v) => $v !== null);
            if (!in_array($kandidat, $terpakai, true) && array_key_exists($kandidat, $headers)) {
                $colMap['nama'] = $kandidat;
                Log::warning('[AnomaliUpload] Kolom nama tidak dikenali, memakai fallback posisi.', [
                    'file' => $file->getClientOriginalName(),
                    'header_terbaca' => array_values($headers),
                    'kolom_dipakai' => $headers[$kandidat] ?? '(kosong)',
                ]);
            }
        }

        // Hanya assignment_id yang benar-benar wajib. Nama TIDAK lagi dijadikan
        // syarat wajib supaya barisnya tetap masuk walaupun namanya kosong.
        if ($colMap['assignment_id'] === null) {
            throw new \Exception(
                'Kolom "Assignment ID" tidak ditemukan pada file ' . $file->getClientOriginalName() . '. ' .
                'Header yang terbaca: ' . implode(' | ', array_filter(array_values($headers)))
            );
        }

        $insert = [];
        $tanpaNama = 0;

        for ($i = $headerIdx + 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            if ($this->isLabelRow($row)) continue;
            if ($this->isRowEmpty($row)) continue;

            $get = fn (string $field) => $this->cell($row, $colMap[$field] ?? null);

            $assignmentId = $get('assignment_id');
            if ($assignmentId === null) continue;

            $nama = $get('nama');
            if ($nama === null) {
                $tanpaNama++;
            }

            $kdkec = $get('kdkec');
            $kddesa = $get('kddesa');
            $kodeSls = $this->padKode($get('kode_sls'), 4);
            $subSls = $this->padKode($get('sub_sls'), 2) ?? '00';

            $emailPetugas = $get('email_petugas');

            $insert[] = [
                'anomali_batch_id' => $batch->id,
                'jenis' => $jenis,
                'no' => is_numeric($get('no')) ? (int) $get('no') : null,
                'nama' => $nama,
                'kdprov' => $get('kdprov') ?? '',
                'nmprov' => $get('nmprov') ?? '',
                'kdkab' => $get('kdkab') ?? '',
                'nmkab' => $get('nmkab') ?? '',
                'kdkec' => $kdkec ?? '',
                'nmkec' => $get('nmkec') ?? '',
                'kddesa' => $kddesa ?? '',
                'nmdesa' => $get('nmdesa') ?? '',
                'kode_sls' => $kodeSls ?? '',
                'sub_sls' => $subSls,
                // kunci untuk mencari petugas di tabel sls_dailies
                'region_code' => PetugasResolver::buildRegionCode(
                    $get('kdkab'), $kdkec, $kddesa, $kodeSls, $subSls
                ),
                'assignment_id' => $assignmentId,
                'nama_anomali' => $get('nama_anomali') ?? '',
                'tindak_lanjut' => 'belum',
                'id_petugas' => $get('id_petugas'),
                'email_petugas' => $emailPetugas ? mb_strtolower($emailPetugas) : null,
                'link_fasih' => $get('link_fasih') ?? '',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (empty($insert)) {
            throw new \Exception(
                'Tidak ada baris data yang valid pada file ' . $file->getClientOriginalName() . '. ' .
                'Header yang terbaca: ' . implode(' | ', array_filter(array_values($headers)))
            );
        }

        foreach (array_chunk($insert, 300) as $chunk) {
            AnomaliMikro::insert($chunk);
        }

        $this->ringkasan[$jenis] = [
            'baris' => count($insert),
            'tanpa_nama' => $tanpaNama,
            'kolom_nama' => $colMap['nama'] !== null ? ($headers[$colMap['nama']] ?? '?') : null,
        ];
    }

    /**
     * Zero-pad kode SLS / sub SLS. "8" -> "0008", "0" -> "00".
     */
    protected function padKode(?string $val, int $length): ?string
    {
        if ($val === null) return null;
        $digits = preg_replace('/\D/', '', $val) ?? '';
        if ($digits === '') return $val;
        if (strlen($digits) > $length) {
            return substr($digits, -$length);
        }

        return str_pad($digits, $length, '0', STR_PAD_LEFT);
    }
}
