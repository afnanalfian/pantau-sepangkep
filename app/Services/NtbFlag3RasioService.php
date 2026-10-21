<?php

namespace App\Services;

use App\Models\NtbFlag3Mikro;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Ganti nilai rasio_ntb modul Anomali NTB Flag 3 secara massal
 * dari Excel berkolom: assignment_id | rasio_ntb.
 */
class NtbFlag3RasioService
{
    /**
     * Baca & validasi file (belum mengubah database).
     *
     * @return array{
     *   map: array<string, float>,          assignment_id => rasio baru
     *   baris_file: int, duplikat: int,
     *   invalid: list<array{baris:int, assignment_id:?string, nilai:?string}>,
     * }
     */
    public function parse(UploadedFile $file): array
    {
        $reader = IOFactory::createReaderForFile($file->getRealPath());
        if (method_exists($reader, 'setReadDataOnly')) {
            $reader->setReadDataOnly(true);
        }
        $rows = $reader->load($file->getRealPath())->getActiveSheet()->toArray(null, true, false, false);

        // cari baris header yang memuat assignment_id & rasio_ntb
        $headerIdx = $colId = $colRasio = null;
        foreach (array_slice($rows, 0, 25, true) as $idx => $row) {
            $id = $rs = null;
            foreach ($row as $c => $v) {
                $s = preg_replace('/[^a-z0-9]/', '', mb_strtolower((string) $v));
                if ($id === null && in_array($s, ['assignmentid', 'idassignment'], true)) $id = $c;
                if ($rs === null && in_array($s, ['rasiontb', 'rasio', 'ntb'], true)) $rs = $c;
            }
            if ($id !== null && $rs !== null) {
                [$headerIdx, $colId, $colRasio] = [$idx, $id, $rs];
                break;
            }
        }

        if ($headerIdx === null) {
            throw new \Exception('Header "assignment_id" dan "rasio_ntb" tidak ditemukan pada file ' . $file->getClientOriginalName() . '.');
        }

        $map = [];
        $invalid = [];
        $barisFile = 0;
        $duplikat = 0;

        for ($i = $headerIdx + 1; $i < count($rows); $i++) {
            $id = trim((string) ($rows[$i][$colId] ?? ''));
            $raw = $rows[$i][$colRasio] ?? null;
            if ($id === '' && ($raw === null || trim((string) $raw) === '')) continue;

            $barisFile++;
            $nilai = $this->rasio($raw);

            if ($id === '' || $nilai === null) {
                $invalid[] = ['baris' => $i + 1, 'assignment_id' => $id ?: null, 'nilai' => $raw === null ? null : (string) $raw];
                continue;
            }
            if (array_key_exists($id, $map)) $duplikat++;   // baris terakhir yang dipakai
            $map[$id] = $nilai;
        }

        return ['map' => $map, 'baris_file' => $barisFile, 'duplikat' => $duplikat, 'invalid' => $invalid];
    }

    /**
     * Ringkasan dampak sebelum diterapkan.
     *
     * @param  array<string, float>  $map
     */
    public function preview(array $map, ?int $batchId = null, int $contoh = 15): array
    {
        $ids = array_keys($map);
        $cocokIds = [];
        $barisDb = 0;
        $berubah = 0;
        $sampel = [];

        foreach (array_chunk($ids, 1000) as $chunk) {
            $rows = $this->scope($batchId)->whereIn('assignment_id', $chunk)
                ->get(['assignment_id', 'nama_usaha', 'rasio_ntb', 'ntb_flag3_batch_id']);

            foreach ($rows as $r) {
                $barisDb++;
                $cocokIds[$r->assignment_id] = true;
                $baru = $map[$r->assignment_id];
                $lama = $r->rasio_ntb === null ? null : (float) $r->rasio_ntb;
                if ($lama === null || abs($lama - $baru) > 1e-9) {
                    $berubah++;
                    if (count($sampel) < $contoh) {
                        $sampel[] = ['assignment_id' => $r->assignment_id, 'nama_usaha' => $r->nama_usaha, 'lama' => $lama, 'baru' => $baru];
                    }
                }
            }
        }

        $tidakDitemukan = array_values(array_diff($ids, array_keys($cocokIds)));

        return [
            'assignment_file' => count($ids),
            'assignment_cocok' => count($cocokIds),
            'baris_db' => $barisDb,          // 1 assignment bisa ada di beberapa batch
            'baris_berubah' => $berubah,
            'tidak_ditemukan' => count($tidakDitemukan),
            'contoh_tidak_ditemukan' => array_slice($tidakDitemukan, 0, 10),
            'contoh_perubahan' => $sampel,
        ];
    }

    /**
     * Terapkan ke database. Mengembalikan jumlah baris yang ter-update.
     *
     * @param  array<string, float>  $map
     */
    public function apply(array $map, ?int $batchId = null): int
    {
        $total = 0;
        $table = (new NtbFlag3Mikro())->getTable();

        DB::transaction(function () use ($map, $batchId, $table, &$total) {
            foreach (array_chunk($map, 500, true) as $chunk) {
                // UPDATE ... SET rasio_ntb = CASE assignment_id WHEN ? THEN ? ... END WHERE assignment_id IN (...)
                $case = '';
                $bindings = [];
                foreach ($chunk as $id => $nilai) {
                    $case .= ' WHEN ? THEN ?';
                    $bindings[] = $id;
                    $bindings[] = $nilai;
                }
                $in = implode(',', array_fill(0, count($chunk), '?'));
                $bindings = array_merge($bindings, [now()], array_keys($chunk));

                $sql = "UPDATE {$table} SET rasio_ntb = CASE assignment_id{$case} END, updated_at = ? WHERE assignment_id IN ({$in})";
                if ($batchId) {
                    $sql .= ' AND ntb_flag3_batch_id = ?';
                    $bindings[] = $batchId;
                }

                $total += DB::update($sql, $bindings);
            }
        });

        return $total;
    }

    protected function scope(?int $batchId)
    {
        $q = NtbFlag3Mikro::query();
        if ($batchId) $q->where('ntb_flag3_batch_id', $batchId);

        return $q;
    }

    /**
     * Parser khusus rasio: koma TUNGGAL selalu dianggap desimal
     * ("0,997916667" -> 0.997916667, "0,997" -> 0.997, bukan 997).
     * Angka mentah Excel (float) dipakai apa adanya.
     */
    public function rasio($v): ?float
    {
        if ($v === null) return null;
        if (is_int($v) || is_float($v)) return (float) $v;

        $v = preg_replace('/[^0-9,.\-]/', '', trim((string) $v));
        if ($v === '' || $v === '-' || $v === '.' || $v === ',') return null;

        $koma = substr_count($v, ',');
        $titik = substr_count($v, '.');

        if ($koma && $titik) {
            // yang terakhir muncul = pemisah desimal
            $v = strrpos($v, ',') > strrpos($v, '.')
                ? str_replace(',', '.', str_replace('.', '', $v))
                : str_replace(',', '', $v);
        } elseif ($koma === 1) {
            $v = str_replace(',', '.', $v);
        } elseif ($koma > 1) {
            $v = str_replace(',', '', $v);
        } elseif ($titik > 1) {
            $v = str_replace('.', '', $v);
        }

        return is_numeric($v) ? (float) $v : null;
    }
}
