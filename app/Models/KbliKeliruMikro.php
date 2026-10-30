<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KbliKeliruMikro extends Model
{
    protected $table = 'kbli_keliru_mikros';

    protected $guarded = ['id'];

    protected $casts = ['tindak_lanjut_at' => 'datetime'];

    public static function statusOptions(): array
    {
        return [
            'sudah_sesuai' => 'Sudah sesuai',
            'diperbaiki' => 'Keliru dan telah diperbaiki',
        ];
    }

    /** Rincian 13b (b1–b4) */
    public const RINCIAN_13B = [
        'produk_sendiri' => 'Produk sendiri',
        'layanan_mamin' => 'Layanan mamin',
        'keg_penjualan' => 'Penjualan',
        'keg_jasa' => 'Jasa',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(KbliKeliruBatch::class, 'kbli_keliru_batch_id');
    }

    // =================================================================
    // ACCESSOR TAMPILAN
    // =================================================================

    public function getNamaDisplayAttribute(): string
    {
        return $this->keg_utama ?: '(Tanpa kegiatan utama)';
    }

    /** [['Produk sendiri', 'Tidak', false], ...] — ketiga: true bila "Ya" */
    public function getRincian13bAttribute(): array
    {
        $kode = config('kbli-keliru.kode_ya_tidak', ['1' => 'Ya', '2' => 'Tidak']);
        $out = [];
        foreach (self::RINCIAN_13B as $f => $label) {
            $v = $this->{$f};
            if ($v === null || $v === '') continue;
            $out[] = [$label, $kode[$v] ?? $v, ($kode[$v] ?? null) === 'Ya'];
        }

        return $out;
    }

    public function getKbliLabelAttribute(): ?string
    {
        if (!$this->kbli_akhir || !class_exists(KbliMaster::class)) return null;
        try {
            return KbliMaster::label($this->kbli_akhir);
        } catch (\Throwable) {
            return null; // tabel master KBLI belum ada
        }
    }

    public function getStatusLabelAttribute(): string
    {
        return static::statusOptions()[$this->status_penyelesaian] ?? 'Sudah';
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status_penyelesaian) {
            'sudah_sesuai' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
            'diperbaiki' => 'bg-blue-50 text-blue-700 border border-blue-200',
            default => 'bg-slate-100 text-slate-600 border border-slate-200',
        };
    }

    // =================================================================
    // LINK FASIH MODE EDIT (sama seperti modul lain)
    // =================================================================

    public function getFasihLinkAttribute(): ?string
    {
        if (!$this->assignment_id) return $this->link;

        if ($tpl = config('kbli-keliru.fasih_edit_url')) {
            return strtr($tpl, ['{assignment_id}' => $this->assignment_id]);
        }

        try {
            $link = $this->asAnomali()->fasih_link;
        } catch (\Throwable) {
            $link = null;
        }

        return $link ?: $this->link;
    }

    public function getFasihLinkShortAttribute(): string
    {
        if (!config('kbli-keliru.fasih_edit_url')) {
            try {
                $short = $this->asAnomali()->fasih_link_short;
                if ($short) return $short;
            } catch (\Throwable) {
                // pakai label default
            }
        }

        return 'Edit di FASIH';
    }

    protected function asAnomali(): AnomaliMikro
    {
        return (new AnomaliMikro())->forceFill([
            'assignment_id' => $this->assignment_id,
            'link_fasih' => $this->link,
        ]);
    }
}
