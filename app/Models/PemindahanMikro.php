<?php

namespace App\Models;

use App\Models\Concerns\HasFasihEditLink;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PemindahanMikro extends Model
{
    use HasFasihEditLink;

    protected $table = 'pemindahan_mikros';

    protected $guarded = ['id'];

    protected $casts = [
        'tindak_lanjut_at' => 'datetime',
        'pendapatan_utama' => 'float',
        'pendapatan_lain' => 'float',
        'total_pengeluaran' => 'float',
    ];

    protected function fasihConfigKey(): string
    {
        return 'modul-pendapatan.pemindahan';
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(PemindahanBatch::class, 'pemindahan_batch_id');
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

    public static function rupiah(?float $v): string
    {
        return $v === null ? '-' : 'Rp ' . number_format($v, 0, ',', '.');
    }
}
