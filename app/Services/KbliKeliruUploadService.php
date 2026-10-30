<?php

namespace App\Services;

use App\Models\KbliKeliruBatch;
use App\Models\KbliKeliruMikro;
use App\Support\NamaPetugas;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class KbliKeliruUploadService
{
    /** @var array<string, int> */
    public array $ringkasan = [];

    /** field database => alias header excel (semua kolom file disimpan) */
    protected const SPEC = [
        'kdkab' => ['level_2_full_code', 'level_2_code'],
        'assignment_id' => ['assignment_id'],
        'index1' => ['index1'],
        'produk_sendiri' => ['produk_sendiri_value', 'produk_sendiri'],
        'layanan_mamin' => ['layanan_mamin_value', 'layanan_mamin'],
        'keg_penjualan' => ['keg_penjualan_value', 'keg_penjualan'],
        'keg_jasa' => ['keg_jasa_value', 'keg_jasa'],
        'jenis_usaha' => ['jenis_usaha_value', 'jenis_usaha'],
        'keg_utama' => ['keg_utama', 'kegiatan_utama'],
        'kategori' => ['kategori'],
        'kbli_genai' => ['kbli_genai_value', 'kbli_genai'],
        'kbli_akhir' => ['kbli_akhir'],
        'label_sumber_kbli' => ['label_sumber_kbli'],
        'kategori_rule' => ['kategori_rule'],
        'kategori_rule_desc' => ['kategori_rule_desc'],
        'rule_13b' => ['rule_13b'],
        'rule_ju' => ['rule_ju'],
        'kat_val_kbli' => ['kat_val_kbli'],
        'dasar_rule' => ['dasar_rule'],
        'alasan_tidak_sesuai' => ['alasan_tidak_sesuai'],
        'kategori_rekomendasi' => ['kategori_rekomendasi'],
        'kategori_rekomendasi_desc' => ['kategori_rekomendasi_desc'],
        'keterangan_rekomendasi' => ['keterangan_rekomendasi'],
        'link' => ['LINK', 'link_fasih_sm', 'link_fasih'],
        'pic' => ['PIC', 'pml_organik'],
    ];

    /**
     * Import satu file untuk satu tanggal. Jika tanggal sudah ada, batch lama
     * beserta data mikronya dihapus & digantikan (cascade).
     */
    public function importBatch(UploadedFile $file, string $tanggal, ?string $uploadedBy = null): KbliKeliruBatch
    {
        $rows = $this->readRows($file);
        [$headerIdx, $colMap] = $this->mapColumns($rows, $file->getClientOriginalName());

        $records = [];
        $no = 0;
        $now = now();

        for ($i = $headerIdx + 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            if ($this->isRowEmpty($row)) continue;

            $g = fn (string $f) => $this->cell($row, $colMap[$f] ?? null);

            $link = $g('link');
            $assignment = $g('assignment_id') ?? $this->assignmentDariLink($link);
            if ($assignment === null) continue;

            $rec = ['no' => ++$no, 'assignment_id' => $assignment];
            foreach (array_keys(self::SPEC) as $f) {
                if (in_array($f, ['assignment_id', 'pic'], true)) continue;
                $rec[$f] = $g($f);
            }
            $rec['kategori'] = $rec['kategori'] !== null ? mb_strtoupper(mb_substr($rec['kategori'], 0, 2)) : null;
            $rec['kbli_akhir'] = $this->kbli($rec['kbli_akhir']);
            $rec['pic_raw'] = $g('pic');
            $rec['tindak_lanjut'] = 'belum';
            $rec['created_at'] = $now;
            $rec['updated_at'] = $now;

            // potong ke panjang kolom
            foreach (['kategori_rule_desc', 'dasar_rule', 'alasan_tidak_sesuai', 'kategori_rekomendasi_desc', 'pic_raw'] as $f) {
                if ($rec[$f] !== null) $rec[$f] = mb_substr($rec[$f], 0, 255);
            }

            $records[] = $rec;
        }

        if (empty($records)) {
            throw new \Exception('Tidak ada baris data yang valid (assignment_id kosong) pada file ' . $file->getClientOriginalName() . '.');
        }

        // seragamkan nama PIC: "Agussalim AB, SH, M.Si" = "AGUSSALIM AB,SH,M.SI"
        $kanon = NamaPetugas::kanonik(array_column($records, 'pic_raw'));
        foreach ($records as &$r) {
            $r['pic'] = $r['pic_raw'] !== null ? ($kanon[$r['pic_raw']] ?? null) : null;
        }
        unset($r);

        $batch = DB::transaction(function () use ($records, $tanggal, $uploadedBy, $file) {
            KbliKeliruBatch::where('tanggal', $tanggal)->first()?->delete();

            $batch = KbliKeliruBatch::create([
                'tanggal' => $tanggal,
                'nama_file' => $file->getClientOriginalName(),
                'uploaded_by' => $uploadedBy,
            ]);

            foreach (array_chunk($records, 500) as $chunk) {
                KbliKeliruMikro::insert(array_map(fn ($r) => $r + ['kbli_keliru_batch_id' => $batch->id], $chunk));
            }

            return $batch;
        });

        $this->ringkasan = [
            'baris' => count($records),
            'pic' => count(array_unique(array_filter(array_column($records, 'pic')))),
            'tanpa_pic' => count(array_filter($records, fn ($r) => $r['pic'] === null)),
        ];

        return $batch->fresh();
    }

    // =====================================================================
    // HELPER
    // =====================================================================

    /** "4734" (nol depan hilang) -> "04734"; "999999" dibiarkan */
    protected function kbli(?string $v): ?string
    {
        if ($v === null) return null;
        $d = preg_replace('/\D/', '', $v);
        if ($d === '') return $v;

        return strlen($d) === 4 ? '0' . $d : $d;
    }

    protected function assignmentDariLink(?string $link): ?string
    {
        if ($link && preg_match('/([0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12})\/?\s*(?:[?#].*)?$/i', $link, $m)) {
            return strtolower($m[1]);
        }

        return null;
    }

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

        if ($colMap['assignment_id'] === null && $colMap['link'] === null) {
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

        return ($val === '' || $val === '-') ? null : $val;
    }
}
