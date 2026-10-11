<?php

namespace App\Models;

use App\Models\Concerns\HasFasihEditLink;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Data mikro modul "Pendapatan 1 Juta ke Bawah". */
class SejutaMikro extends Model
{
    use HasFasihEditLink;

    protected $table = 'sejuta_mikros';

    protected $guarded = ['id'];

    protected $casts = [
        'tindak_lanjut_at' => 'datetime',
        'total_pendapatan' => 'float',
        'pendapatan_utama' => 'float',
        'pendapatan_lain' => 'float',
        'total_pengeluaran' => 'float',
        'jml_tenaga_kerja' => 'integer',
    ];

    protected function fasihConfigKey(): string
    {
        return 'modul-pendapatan.sejuta';
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(SejutaBatch::class, 'sejuta_batch_id');
    }

    public function getNamaDisplayAttribute(): string
    {
        return $this->nama_usaha ?: '(Tanpa nama usaha)';
    }

    public function getSlsDisplayAttribute(): string
    {
        $kode = trim(($this->kode_sls ?? '') . ($this->sub_sls !== null ? '-' . $this->sub_sls : ''), '-');

        return trim($kode . ' ' . ($this->nama_sls ?? '')) ?: '-';
    }

    /** "1. Ditemukan" -> warna badge */
    public function getKeberadaanColorAttribute(): string
    {
        $k = mb_strtolower((string) $this->keberadaan);

        return match (true) {
            $k === '' => 'bg-slate-100 text-slate-500 border border-slate-200',
            str_contains($k, 'tidak') => 'bg-red-50 text-red-700 border border-red-200',
            str_contains($k, 'ditemukan') => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
            default => 'bg-amber-50 text-amber-700 border border-amber-200',
        };
    }

    /** Kelompok total pendapatan setahun (untuk dashboard). */
    public static function kelompokPendapatanSql(): string
    {
        return "CASE
            WHEN total_pendapatan IS NULL THEN '5. Kosong'
            WHEN total_pendapatan <= 0 THEN '1. Rp 0'
            WHEN total_pendapatan <= 500000 THEN '2. ≤ Rp 500 rb'
            WHEN total_pendapatan <= 1000000 THEN '3. Rp 500 rb – 1 jt'
            ELSE '4. > Rp 1 jt'
        END";
    }

    public static function rupiah(?float $v): string
    {
        return $v === null ? '-' : 'Rp ' . number_format($v, 0, ',', '.');
    }
}
