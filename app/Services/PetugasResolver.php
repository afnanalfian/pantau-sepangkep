<?php

namespace App\Services;

use App\Models\AnomaliMikro;
use App\Models\DailyUpload;
use App\Models\Mitra;
use App\Models\SlsDaily;

/**
 * Mencari petugas (PPL / PML / PML Organik) untuk sebuah anomali mikro
 * BERDASARKAN WILAYAH, bukan berdasarkan kolom "Email Petugas" di excel anomali.
 *
 * Kunci pencarian adalah `region_code` 16 digit di tabel `sls_dailies`:
 *
 *     region_code = KodeKab(4) + KodeKec(3) + KodeDesa(3) + KodeSLS(4) + SubSLS(2)
 *
 * Contoh dari file anomali:
 *     Kode Kab/Kota = 7309
 *     Kode Kec      = 7309060      -> 3 digit terakhir = 060
 *     Kode Desa     = 7309060001   -> 3 digit terakhir = 001
 *     Kode SLS      = 0008
 *     Sub SLS       = 00
 *     => region_code = 7309 060 001 0008 00 = "7309060001000800"
 *
 * Data petugas diambil dari upload harian TERBARU (daily_uploads terakhir),
 * sehingga kalau ada pergantian petugas, yang dipakai adalah yang paling baru.
 */
class PetugasResolver
{
    /** @var array<string, array<string, mixed>> region_code 16 digit => data petugas */
    protected array $exact = [];

    /** @var array<string, array<string, mixed>> 14 digit (tanpa sub SLS) => data petugas */
    protected array $tanpaSub = [];

    protected bool $loaded = false;

    protected ?int $dailyUploadId = null;

    /**
     * @param  int|null  $dailyUploadId  batasi ke satu upload harian tertentu.
     *                                   Null = pakai upload harian terbaru.
     */
    public function __construct(?int $dailyUploadId = null)
    {
        $this->dailyUploadId = $dailyUploadId;
    }

    /**
     * Pakai data petugas dari upload harian terakhir yang tanggalnya <= $tanggal
     * (mis. batch anomali tanggal 30 Juli -> pakai upload harian 30 Juli atau
     * sebelumnya). Kalau belum ada, dipakai upload harian paling awal.
     */
    public static function forDate($tanggal): self
    {
        $id = DailyUpload::whereDate('tanggal', '<=', $tanggal)
            ->orderByDesc('tanggal')
            ->value('id');

        $id ??= DailyUpload::orderBy('tanggal')->value('id');

        return new self($id);
    }

    // =====================================================================
    // PEMBENTUKAN & NORMALISASI KODE WILAYAH
    // =====================================================================

    protected static function digits(?string $val): string
    {
        return preg_replace('/\D/', '', (string) $val) ?? '';
    }

    /**
     * Ambil N digit terakhir, dipadding nol kalau kurang panjang.
     */
    protected static function tail(string $digits, int $len): string
    {
        if ($digits === '') return '';

        return strlen($digits) >= $len
            ? substr($digits, -$len)
            : str_pad($digits, $len, '0', STR_PAD_LEFT);
    }

    /**
     * Susun region_code 16 digit dari potongan kode wilayah file anomali.
     * Menerima kode kecamatan/desa dalam bentuk pendek (060 / 001) maupun
     * panjang (7309060 / 7309060001).
     */
    public static function buildRegionCode(
        ?string $kdkab,
        ?string $kdkec,
        ?string $kddesa,
        ?string $kodeSls,
        ?string $subSls,
        string $defaultKab = '7309'
    ): ?string {
        $kabDigits = self::digits($kdkab);
        // kalau kolom kab kosong, coba ambil dari 4 digit awal kode kecamatan
        if (strlen($kabDigits) < 4) {
            $kecDigits = self::digits($kdkec);
            $kabDigits = strlen($kecDigits) >= 7 ? substr($kecDigits, 0, 4) : $defaultKab;
        }
        $kab = substr($kabDigits, 0, 4);

        $kec = self::tail(self::digits($kdkec), 3);
        $desa = self::tail(self::digits($kddesa), 3);
        $sls = self::tail(self::digits($kodeSls), 4);
        $sub = self::digits($subSls);
        $sub = $sub === '' ? '00' : self::tail($sub, 2);

        if ($kec === '' || $desa === '' || $sls === '') {
            return null;
        }

        return $kab . $kec . $desa . $sls . $sub;
    }

    /**
     * Bersihkan region_code dari tabel sls_dailies (kadang ada apostrof/spasi
     * sisa export excel).
     */
    public static function normalizeRegionCode(?string $code): ?string
    {
        $digits = self::digits($code);
        if ($digits === '') return null;
        if (strlen($digits) > 16) {
            $digits = substr($digits, -16);
        }

        return $digits;
    }

    // =====================================================================
    // LOADING DATA PETUGAS
    // =====================================================================

    /** ID upload harian yang dipakai sebagai sumber data petugas. */
    public function uploadId(): ?int
    {
        return $this->dailyUploadId ??= DailyUpload::orderByDesc('tanggal')->value('id');
    }

    protected function load(): void
    {
        if ($this->loaded) return;
        $this->loaded = true;

        $uploadId = $this->uploadId();
        if (!$uploadId) return;

        $rows = SlsDaily::query()
            ->where('daily_upload_id', $uploadId)
            ->get(['region_code', 'username', 'email_pml', 'nama_ppl', 'nama_pml', 'pml_organik', 'nama_sls', 'nmkec', 'nmdes']);

        foreach ($rows as $r) {
            $code = self::normalizeRegionCode($r->region_code);
            if (!$code) continue;

            $petugas = [
                'region_code' => $code,
                'nama_ppl' => $r->nama_ppl,
                'nama_pml' => $r->nama_pml,
                'pml_organik' => $r->pml_organik,
                'username' => $r->username,
                'email_pml' => $r->email_pml,
                'nama_sls' => $r->nama_sls,
                'nmkec' => $r->nmkec,
                'nmdes' => $r->nmdes,
                'sumber' => 'region_code',
            ];

            $this->exact[$code] = $petugas;

            if (strlen($code) >= 14) {
                $short = substr($code, 0, 14);
                if (!isset($this->tanpaSub[$short])) {
                    $this->tanpaSub[$short] = array_merge($petugas, ['sumber' => 'region_code_tanpa_sub_sls']);
                }
            }
        }
    }

    // =====================================================================
    // RESOLVE
    // =====================================================================

    /**
     * Cari petugas untuk satu baris anomali.
     *
     * @return array<string, mixed>|null
     */
    public function resolve(AnomaliMikro $m): ?array
    {
        $this->load();

        $code = self::normalizeRegionCode($m->region_code)
            ?: self::buildRegionCode($m->kdkab, $m->kdkec, $m->kddesa, $m->kode_sls, $m->sub_sls);

        if ($code) {
            if (isset($this->exact[$code])) {
                return $this->exact[$code];
            }
            if (strlen($code) >= 14 && isset($this->tanpaSub[substr($code, 0, 14)])) {
                return $this->tanpaSub[substr($code, 0, 14)];
            }
        }

        // Cadangan terakhir: kolom email petugas dari file anomali (kalau ada).
        if ($m->email_petugas) {
            $mitra = Mitra::where('email', $m->email_petugas)->first();
            if ($mitra) {
                return [
                    'region_code' => $code,
                    'nama_ppl' => $mitra->nama_ppl,
                    'nama_pml' => $mitra->nama_pml,
                    'pml_organik' => $mitra->pml_organik,
                    'username' => $mitra->email,
                    'email_pml' => $mitra->email_pml,
                    'nama_sls' => null,
                    'nmkec' => null,
                    'nmdes' => null,
                    'sumber' => 'email_petugas',
                ];
            }
        }

        return null;
    }

    /**
     * Resolve banyak baris sekaligus (untuk tabel & export).
     *
     * @param  iterable<AnomaliMikro>  $mikros
     * @return array<int, array<string, mixed>|null>  id anomali mikro => petugas
     */
    public function resolveMany(iterable $mikros): array
    {
        $out = [];
        foreach ($mikros as $m) {
            $out[$m->id] = $this->resolve($m);
        }

        return $out;
    }

    /**
     * Statistik kecil untuk ditampilkan di UI (berapa yang berhasil dipetakan).
     *
     * @param  iterable<AnomaliMikro>  $mikros
     * @return array{total:int, ketemu:int, tidak_ketemu:int}
     */
    public function statistik(iterable $mikros): array
    {
        $total = 0;
        $ketemu = 0;
        foreach ($mikros as $m) {
            $total++;
            if ($this->resolve($m)) $ketemu++;
        }

        return ['total' => $total, 'ketemu' => $ketemu, 'tidak_ketemu' => $total - $ketemu];
    }
}
