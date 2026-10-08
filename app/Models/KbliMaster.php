<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Master judul KBLI 2020 (5 digit). Diisi lewat halaman upload Cek KBLI
 * (file master opsional, kolom: kode & judul).
 *
 * Kalau sebuah kode belum ada di master, label otomatis jatuh ke nama
 * Kategori (A–U) berdasarkan 2 digit awal kode, supaya tidak pernah kosong.
 */
class KbliMaster extends Model
{
    protected $table = 'kbli_masters';
    protected $primaryKey = 'kode';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['kode', 'judul'];

    /** Cache per-request: kode => judul */
    protected static ?array $cache = null;

    public const KATEGORI = [
        'A' => 'Pertanian, Kehutanan dan Perikanan',
        'B' => 'Pertambangan dan Penggalian',
        'C' => 'Industri Pengolahan',
        'D' => 'Pengadaan Listrik, Gas, Uap/Air Panas dan Udara Dingin',
        'E' => 'Treatment Air, Treatment Air Limbah, Treatment dan Pemulihan Material Sampah, dan Aktivitas Remediasi',
        'F' => 'Konstruksi',
        'G' => 'Perdagangan Besar dan Eceran; Reparasi dan Perawatan Mobil dan Sepeda Motor',
        'H' => 'Pengangkutan dan Pergudangan',
        'I' => 'Penyediaan Akomodasi dan Penyediaan Makan Minum',
        'J' => 'Informasi dan Komunikasi',
        'K' => 'Aktivitas Keuangan dan Asuransi',
        'L' => 'Real Estat',
        'M' => 'Aktivitas Profesional, Ilmiah dan Teknis',
        'N' => 'Aktivitas Penyewaan dan Sewa Guna Usaha Tanpa Hak Opsi, Ketenagakerjaan, Agen Perjalanan dan Penunjang Usaha Lainnya',
        'O' => 'Administrasi Pemerintahan, Pertahanan dan Jaminan Sosial Wajib',
        'P' => 'Pendidikan',
        'Q' => 'Aktivitas Kesehatan Manusia dan Aktivitas Sosial',
        'R' => 'Kesenian, Hiburan dan Rekreasi',
        'S' => 'Aktivitas Jasa Lainnya',
        'T' => 'Aktivitas Rumah Tangga sebagai Pemberi Kerja; Aktivitas yang Menghasilkan Barang dan Jasa oleh Rumah Tangga yang Digunakan untuk Memenuhi Kebutuhan Sendiri',
        'U' => 'Aktivitas Badan Internasional dan Badan Ekstra Internasional Lainnya',
    ];

    /** Rentang golongan pokok (2 digit) tiap kategori. */
    protected const RENTANG = [
        'A' => [1, 3], 'B' => [5, 9], 'C' => [10, 33], 'D' => [35, 35], 'E' => [36, 39],
        'F' => [41, 43], 'G' => [45, 47], 'H' => [49, 53], 'I' => [55, 56], 'J' => [58, 63],
        'K' => [64, 66], 'L' => [68, 68], 'M' => [69, 75], 'N' => [77, 82], 'O' => [84, 84],
        'P' => [85, 85], 'Q' => [86, 88], 'R' => [90, 93], 'S' => [94, 96], 'T' => [97, 98],
        'U' => [99, 99],
    ];

    public static function kategoriDari(?string $kode): ?string
    {
        if (!$kode || strlen($kode) < 2 || !ctype_digit(substr($kode, 0, 2))) return null;
        $gol = (int) substr($kode, 0, 2);
        foreach (self::RENTANG as $huruf => [$min, $max]) {
            if ($gol >= $min && $gol <= $max) return $huruf;
        }

        return null;
    }

    public static function labels(): array
    {
        return static::$cache ??= static::query()->pluck('judul', 'kode')->all();
    }

    public static function flushCache(): void
    {
        static::$cache = null;
    }

    /** Judul KBLI untuk sebuah kode (fallback ke nama kategori). */
    public static function label(?string $kode): string
    {
        if (!$kode) return '-';
        $labels = static::labels();
        if (isset($labels[$kode])) return $labels[$kode];

        $kat = static::kategoriDari($kode);

        return $kat ? 'Kategori ' . $kat . ' – ' . self::KATEGORI[$kat] : 'Kode tidak dikenal';
    }

    /** Apakah judul diambil dari master (bukan fallback kategori). */
    public static function adaDiMaster(?string $kode): bool
    {
        return $kode && isset(static::labels()[$kode]);
    }
}
