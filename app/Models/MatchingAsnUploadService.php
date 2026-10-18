<?php

namespace App\Services;

use App\Models\MatchingAsnBatch;
use App\Models\MatchingAsnMikro;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class MatchingAsnUploadService
{
    /** @var array<string, int> */
    public array $ringkasan = [];

    /**
     * Kolom penting saja. Header kembar (assignment_id, link_assignment muncul 2x)
     * -> yang dipakai kemunculan pertama. link_assignment & is_active tidak disimpan
     * (link dibangun ulang dari assignment_id).
     */
    protected const SPEC = [
        'assignment_id' => ['assignment_id', 'Assignment ID'],
        'code_identity' => ['code_identity', 'kode_identitas', 'kode identitas'],
        'kdkab' => ['kode_kab', 'kdkab'],
        'kdkec' => ['kode_kec', 'kdkec'],
        'kddesa' => ['kode_desa', 'kddesa'],
        'nmkab' => ['kabupaten_kota', 'nama_kab_kota', 'kabupaten'],
        'nmkec' => ['kecamatan', 'nama_kecamatan'],
        'nmdesa' => ['desa_kelurahan', 'nama_desa_kel', 'desa'],
        'nomor_kk' => ['nomor_kk', 'no_kk', 'kk'],
        'nik' => ['nik'],
        'sumber_nik' => ['sumber_nik'],
        'jumlah_kandidat_kk' => ['jumlah_kandidat_kk'],
        'nama_prelist' => ['nama_prelist', 'nama'],
        'alamat_prelist' => ['alamat_prelist', 'alamat'],
        'jenis_prelist' => ['jenis_prelist'],
        'assignment_status' => ['assignment_status_alias', 'assignment_status', 'status'],
        'petugas' => ['petugas'],
        'tanggal_modifikasi' => ['tanggal_modifikasi'],
        'instansi_kerja' => ['instansi_kerja', 'instansi'],
        'instansi_lokasi' => ['instansi_lokasi'],
        'duplikat' => ['DUPLIKAT', 'status_duplikat'],
    ];

    /** Nilai yang dianggap "tidak ada" (hasil VLOOKUP gagal, dsb.) */
    protected const KOSONG = ['#N/A', 'N/A', '#NA', 'NA', '#REF!', '#VALUE!', '-', '0', 'NULL'];

    public function importBatch(UploadedFile $file, string $tanggal, ?string $uploadedBy = null): MatchingAsnBatch
    {
        $rows = $this->readRows($file);
        [$headerIdx, $colMap] = $this->mapColumns($rows, $file->getClientOriginalName());

        if ($colMap['duplikat'] === null) {
            throw new \Exception('Kolom "DUPLIKAT" tidak ditemukan pada file ' . $file->getClientOriginalName() . '.');
        }

        $records = [];
        $no = 0;
        $now = now();
        $stat = ['baris' => 0, 'duplikat' => 0, 'instansi_na' => 0, 'tanpa_nik' => 0, 'tanpa_kk' => 0];

        for ($i = $headerIdx + 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            if ($this->isRowEmpty($row)) continue;

            $get = fn (string $f) => $this->cell($row, $colMap[$f] ?? null);

            $assignmentId = $get('assignment_id');
            if ($assignmentId === null) continue;

            $nik = $this->identitas($get('nik'));
            $kk = $this->identitas($get('nomor_kk'));
            $instansi = $this->teks($get('instansi_kerja'));
            $dupLabel = ($d = $get('duplikat')) !== null ? mb_strtoupper($d) : null;
            $isDup = $dupLabel === 'HAPUS';

            $stat['baris']++;
            if ($isDup) $stat['duplikat']++;
            if ($instansi === null) $stat['instansi_na']++;
            if ($nik === null) $stat['tanpa_nik']++;
            if ($kk === null) $stat['tanpa_kk']++;

            $records[] = [
                'no' => ++$no,
                'assignment_id' => $assignmentId,
                'code_identity' => $get('code_identity'),
                'kdkab' => $this->pad($get('kdkab'), 4),
                'kdkec' => $this->pad($get('kdkec'), 3),
                'kddesa' => $this->pad($get('kddesa'), 3),
                'nmkab' => $get('nmkab'),
                'nmkec' => $get('nmkec'),
                'nmdesa' => $get('nmdesa'),
                'nomor_kk' => $kk,
                'nik' => $nik,
                'sumber_nik' => $get('sumber_nik'),
                'jumlah_kandidat_kk' => is_numeric($j = $get('jumlah_kandidat_kk')) ? (int) $j : null,
                'nama_prelist' => $get('nama_prelist'),
                'alamat_prelist' => $get('alamat_prelist'),
                'jenis_prelist' => $get('jenis_prelist'),
                'assignment_status' => $get('assignment_status'),
                'petugas' => $get('petugas'),
                'tanggal_modifikasi' => $this->tanggal($get('tanggal_modifikasi')),
                'instansi_kerja' => $instansi,
                'instansi_lokasi' => $this->teks($get('instansi_lokasi')),
                'is_duplikat' => $isDup,
                'duplikat_label' => $dupLabel,
                'tindak_lanjut' => 'belum',
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (empty($records)) {
            throw new \Exception('Tidak ada baris data yang valid (kolom assignment_id kosong semua) pada file ' . $file->getClientOriginalName() . '.');
        }

        $batch = DB::transaction(function () use ($records, $tanggal, $uploadedBy, $file) {
            MatchingAsnBatch::where('tanggal', $tanggal)->first()?->delete();

            $batch = MatchingAsnBatch::create([
                'tanggal' => $tanggal,
                'nama_file' => $file->getClientOriginalName(),
                'uploaded_by' => $uploadedBy,
            ]);

            foreach (array_chunk($records, 500) as $chunk) {
                MatchingAsnMikro::insert(array_map(fn ($r) => $r + ['matching_asn_batch_id' => $batch->id], $chunk));
            }

            return $batch;
        });

        $this->ringkasan = $stat;

        return $batch->fresh();
    }

    // =====================================================================
    // NORMALISASI
    // =====================================================================

    /** NIK / No KK: hanya digit; "#N/A", "0", "-" dan sejenisnya -> null */
    protected function identitas(?string $v): ?string
    {
        if ($v === null || in_array(mb_strtoupper($v), self::KOSONG, true)) return null;
        $d = preg_replace('/\D/', '', $v);

        return ($d === '' || (int) ltrim($d, '0') === 0 || strlen($d) < 8) ? null : $d;
    }

    /** Teks bebas: "#N/A" -> null, spasi berlebih dirapikan */
    protected function teks(?string $v): ?string
    {
        if ($v === null) return null;
        $v = trim(preg_replace('/\s+/u', ' ', $v));

        return ($v === '' || in_array(mb_strtoupper($v), self::KOSONG, true)) ? null : $v;
    }

    protected function pad(?string $v, int $len): ?string
    {
        if ($v === null) return null;
        $d = preg_replace('/\D/', '', $v);

        return $d === '' ? null : str_pad($d, $len, '0', STR_PAD_LEFT);
    }

    /** Tanggal teks apa adanya; tanggal Excel (angka serial) -> "Y-m-d H:i:s" */
    protected function tanggal(?string $v): ?string
    {
        if ($v === null) return null;
        if (is_numeric($v) && (float) $v > 20000 && (float) $v < 80000) {
            try {
                return ExcelDate::excelToDateTimeObject((float) $v)->format('Y-m-d H:i:s');
            } catch (\Throwable) {
                // biarkan apa adanya
            }
        }

        return mb_substr($v, 0, 30);
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

        // nilai mentah supaya NIK 16 digit tidak berubah jadi 7,31E+15
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

        // header kembar -> simpan kemunculan pertama saja
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
            $val = floor($val) == $val && abs($val) < 1e18
                ? number_format($val, 0, '', '')
                : rtrim(rtrim(sprintf('%.10F', $val), '0'), '.');
        }
        $val = trim(preg_replace('/\s+/u', ' ', (string) $val));

        return $val === '' ? null : $val;
    }
}
