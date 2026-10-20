<?php

namespace App\Services;

use App\Models\NtbFlag3Batch;
use App\Models\NtbFlag3Mikro;
use App\Models\SlsDaily;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class NtbFlag3UploadService
{
    /** @var array<string, int> */
    public array $ringkasan = [];

    protected const SPEC = [
        'assignment_id' => ['assignment_id', 'Assignment ID'],
        'survey_period_id' => ['survey_period_id', 'survey_period', 'period_id'],
        'kdprov' => ['level_1_full_code', 'level_1_code', 'kode prov'],
        'nmprov' => ['level_1_name', 'nama provinsi'],
        'kdkab' => ['level_2_full_code', 'level_2_code', 'kode kab'],
        'nmkab' => ['level_2_name', 'nama kab'],
        'kdkec' => ['level_3_full_code', 'level_3_code', 'kode kec'],
        'kddesa' => ['level_4_full_code', 'level_4_code', 'kode desa'],
        'nama_usaha' => ['nama_usaha', 'nama usaha'],
        'total_pengeluaran' => ['metrik_total_pengeluaran', 'total_pengeluaran'],
        'pendapatan_barang_jasa' => ['metrik_pendapatan_barang_jasa', 'pendapatan_barang_jasa'],
        'pendapatan_lainnya' => ['metrik_pendapatan_lainnya', 'pendapatan_lainnya'],
        'nilai_tambah' => ['metrik_nilai_tambah', 'nilai_tambah'],
        'total_aset' => ['metrik_total_aset', 'total_aset'],
        'rasio_ntb' => ['rasio_ntb'],
        'pic' => ['pic', 'nama pic', 'penanggung jawab'],
    ];

    protected const KOLOM_ANGKA = ['total_pengeluaran', 'pendapatan_barang_jasa', 'pendapatan_lainnya', 'nilai_tambah', 'total_aset', 'rasio_ntb'];

    /**
     * Import satu file untuk satu tanggal. Jika tanggal sudah ada, batch lama
     * beserta data mikronya dihapus & digantikan (cascade).
     */
    public function importBatch(UploadedFile $file, string $tanggal, ?string $uploadedBy = null): NtbFlag3Batch
    {
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
            $nmdesa = $wilayah['desa'][$kddesa] ?? null;
            $nmkec = $wilayah['kec'][$kdkec] ?? ($kddesa ? ($wilayah['kec'][substr($kddesa, 0, 7)] ?? null) : null);
            if (!$nmkec || !$nmdesa) $tanpaWilayah++;

            $rec = [
                'no' => ++$no,
                'assignment_id' => $assignmentId,
                'survey_period_id' => $get('survey_period_id'),
                'kdprov' => $this->digits($get('kdprov')) ?: null,
                'nmprov' => $get('nmprov'),
                'kdkab' => $this->digits($get('kdkab')) ?: null,
                'nmkab' => $get('nmkab'),
                'kdkec' => $kdkec ?: null,
                'kddesa' => $kddesa ?: null,
                'nama_usaha' => $get('nama_usaha'),
                'pic' => $get('pic'),           // "S. HASAN, SE   " -> "S. HASAN, SE" (dirapikan di cell())
                'nmkec' => $nmkec,
                'nmdesa' => $nmdesa,
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
            throw new \Exception('Tidak ada baris data yang valid (kolom assignment_id kosong semua) pada file ' . $file->getClientOriginalName() . '.');
        }

        $batch = DB::transaction(function () use ($records, $tanggal, $uploadedBy, $file) {
            NtbFlag3Batch::where('tanggal', $tanggal)->first()?->delete();

            $batch = NtbFlag3Batch::create([
                'tanggal' => $tanggal,
                'nama_file' => $file->getClientOriginalName(),
                'uploaded_by' => $uploadedBy,
            ]);

            foreach (array_chunk($records, 500) as $chunk) {
                NtbFlag3Mikro::insert(array_map(fn ($r) => $r + ['ntb_flag3_batch_id' => $batch->id], $chunk));
            }

            return $batch;
        });

        $this->ringkasan = ['baris' => count($records), 'tanpa_wilayah' => $tanpaWilayah];

        return $batch->fresh();
    }

    // =====================================================================
    // NAMA WILAYAH (sama seperti Cek KBLI / Anomali 24)
    // =====================================================================

    /** @return array{kec: array<string,string>, desa: array<string,string>} */
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
        if (is_float($val)) {
            $val = floor($val) == $val && abs($val) < 1e18
                ? number_format($val, 0, '', '')
                : rtrim(rtrim(sprintf('%.10F', $val), '0'), '.');
        }
        $val = trim(preg_replace('/\s+/u', ' ', (string) $val));

        return ($val === '' || $val === '-') ? null : $val;
    }

    /**
     * "Rp1,014,000,000" (koma ribuan) / "16.800.000,00" (titik ribuan) /
     * "0.997569" / angka mentah Excel -> float
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
}
