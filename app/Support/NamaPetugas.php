<?php

namespace App\Support;

/**
 * Menyeragamkan nama petugas (PML organik, PIC, dsb.) yang sebenarnya sama
 * tetapi ditulis berbeda: "NANDAR" = "nandar" = " Nandar " = "Nandar."
 * = "NANDAR, S.E" = "Nandar, SE".
 *
 * Kunci pembanding: huruf besar, hanya huruf & angka (spasi & tanda baca dibuang).
 * Nama tampilan: varian yang paling sering muncul, dalam HURUF BESAR & spasi rapi.
 *
 * Bisa dipakai di modul lain:
 *   $kanon = NamaPetugas::kanonik($daftarNamaMentah);   // [mentah => tampilan]
 *   NamaPetugas::kunci('Nandar.')                       // "NANDAR"
 */
class NamaPetugas
{
    public static function kunci(?string $nama): ?string
    {
        if ($nama === null) return null;
        $k = preg_replace('/[^A-Z0-9]/', '', mb_strtoupper(self::tanpaAksen($nama)));

        return $k === '' ? null : $k;
    }

    /** "  nandar ,s.e " -> "NANDAR, S.E" (tanda baca lain dibiarkan) */
    public static function rapikan(?string $nama): ?string
    {
        if ($nama === null) return null;
        $n = mb_strtoupper(trim(preg_replace('/\s+/u', ' ', $nama)));
        $n = preg_replace('/\s*,\s*/', ', ', $n);      // spasi di sekitar koma
        $n = trim($n, " ,.");

        return $n === '' ? null : $n;
    }

    /**
     * @param  iterable<?string>  $namaMentah
     * @return array<string, string>  nama mentah => nama tampilan seragam
     */
    public static function kanonik(iterable $namaMentah): array
    {
        $hitung = [];      // kunci => [tampilan => jumlah]
        $mentahKe = [];    // mentah => kunci

        foreach ($namaMentah as $m) {
            if ($m === null || ($k = self::kunci($m)) === null) continue;
            $tampil = self::rapikan($m);
            $hitung[$k][$tampil] = ($hitung[$k][$tampil] ?? 0) + 1;
            $mentahKe[$m] = $k;
        }

        $pilihan = [];
        foreach ($hitung as $k => $varian) {
            // paling sering; jika seri, yang paling panjang (biasanya lengkap dengan gelar)
            uksort($varian, fn ($a, $b) => [$varian[$b], mb_strlen($b)] <=> [$varian[$a], mb_strlen($a)]);
            $pilihan[$k] = array_key_first($varian);
        }

        $hasil = [];
        foreach ($mentahKe as $m => $k) {
            $hasil[$m] = $pilihan[$k];
        }

        return $hasil;
    }

    protected static function tanpaAksen(string $s): string
    {
        if (function_exists('iconv')) {
            $t = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s);
            if ($t !== false) return $t;
        }

        return $s;
    }
}
