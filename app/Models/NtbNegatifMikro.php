<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NtbNegatifMikro extends Model
{
    protected $table = 'ntb_negatif_mikros';

    protected $guarded = ['id'];

    protected $casts = [
        'tindak_lanjut_at' => 'datetime',
        'metrik_biaya_produksi' => 'float',
        'metrik_biaya_pembelian' => 'float',
        'metrik_biaya_operasional' => 'float',
        'metrik_biaya_non_operasional' => 'float',
        'metrik_total_pengeluaran' => 'float',
        'metrik_pendapatan_barang_jasa' => 'float',
        'metrik_pendapatan_lainnya' => 'float',
        'metrik_nilai_tambah' => 'float',
        'metrik_total_aset' => 'float',
        'metrik_output' => 'float',
        'rasio_biaya_pembelian_omzet' => 'float',
        'rasio_ntb' => 'float',
        'produktivitas' => 'float',
        'flag_rasio_1_output_aset' => 'integer',
        'flag_rasio_2_upah_ntb' => 'integer',
        'flag_rasio_3_ntb_output' => 'integer',
    ];

    /** Cache objek AnomaliMikro "bayangan" untuk membangun link edit FASIH. */
    protected ?AnomaliMikro $anomaliProxy = null;

    /** Daftar flag: kolom => label singkat. */
    public const FLAGS = [
        'flag_rasio_1_output_aset' => 'Output/Aset',
        'flag_rasio_2_upah_ntb' => 'Upah/NTB',
        'flag_rasio_3_ntb_output' => 'NTB/Output',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(NtbNegatifBatch::class, 'ntb_negatif_batch_id');
    }

    // =================================================================
    // METODE PENYELESAIAN (khusus NTB Negatif: 2 pilihan)
    // =================================================================

    public static function statusOptions(): array
    {
        return [
            'diperbaiki' => 'Tidak Sesuai dan Telah Diperbaiki',
            'sesuai_lapangan' => 'Sesuai Kondisi Lapangan',
        ];
    }

    public static function statusDescriptions(): array
    {
        return [
            'diperbaiki' => 'Isian keliru, sudah dikoreksi di FASIH',
            'sesuai_lapangan' => 'NTB memang negatif sesuai kondisi usaha di lapangan',
        ];
    }

    public function getStatusLabelAttribute(): string
    {
        return static::statusOptions()[$this->status_penyelesaian] ?? ($this->status_penyelesaian ?: 'Selesai');
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status_penyelesaian) {
            'diperbaiki' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
            'sesuai_lapangan' => 'bg-blue-50 text-blue-700 border border-blue-200',
            default => 'bg-slate-50 text-slate-700 border border-slate-200',
        };
    }

    // =================================================================
    // TAMPILAN
    // =================================================================

    public function getNamaDisplayAttribute(): string
    {
        return $this->nama_usaha ?: '(Nama usaha tidak tercatat)';
    }

    public function getSlsLabelAttribute(): string
    {
        return trim(($this->kode_sls ?: '-') . ' / ' . ($this->sub_sls ?: '00'));
    }

    public function getRegionKeyAttribute(): ?string
    {
        return $this->region_code;
    }

    public function getKbliLabelAttribute(): string
    {
        return trim(($this->kode_kbli ?: '-') . ' · ' . ($this->des_kbli ?: '-'));
    }

    /** Resolver petugas memakai email sebagai cadangan; file NTB tidak punya kolom ini. */
    public function getEmailPetugasAttribute(): ?string
    {
        return null;
    }

    /** @return array<string, string> flag yang bernilai 1 (kolom => label) */
    public function getFlagAktifAttribute(): array
    {
        return array_filter(self::FLAGS, fn ($label, $col) => (int) $this->{$col} === 1, ARRAY_FILTER_USE_BOTH);
    }

    public static function rupiah(?float $v): string
    {
        if ($v === null) return '-';

        return ($v < 0 ? '-' : '') . number_format(abs($v), 0, ',', '.');
    }

    public static function angka(?float $v, int $dec = 4): string
    {
        if ($v === null) return '-';

        return rtrim(rtrim(number_format($v, $dec, ',', '.'), '0'), ',') ?: '0';
    }

    // =================================================================
    // LINK FASIH MODE EDIT
    // Logika SAMA PERSIS dengan anomali (accessor fasih_link milik
    // AnomaliMikro). NTB adalah data usaha -> jenis = 'usaha'.
    // Kolom link_fasih_sm dari excel ikut diberikan sebagai link_fasih,
    // tapi tombol tetap memakai hasil accessor anomali (mode edit).
    // =================================================================

    protected function anomaliProxy(): AnomaliMikro
    {
        return $this->anomaliProxy ??= (new AnomaliMikro())->forceFill(array_merge(
            $this->getAttributes(),
            [
                'jenis' => 'usaha',
                'nama' => $this->nama_usaha,
                'link_fasih' => $this->link_fasih_sm,
                'email_petugas' => null,
                'tindak_lanjut' => $this->tindak_lanjut ?? 'belum',
            ]
        ));
    }

    public function getFasihLinkAttribute(): ?string
    {
        return $this->anomaliProxy()->fasih_link ?: null;
    }

    public function getFasihLinkShortAttribute(): string
    {
        return $this->anomaliProxy()->fasih_link_short ?: 'Buka FASIH';
    }
}
