<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NtbFlag3Mikro extends Model
{
    protected $table = 'ntb_flag3_mikros';

    protected $guarded = ['id'];

    public const KOLOM_RUPIAH = [
        'total_pengeluaran' => 'Total Pengeluaran',
        'pendapatan_barang_jasa' => 'Pendapatan Barang & Jasa',
        'pendapatan_lainnya' => 'Pendapatan Lainnya',
        'nilai_tambah' => 'Nilai Tambah (NTB)',
        'total_aset' => 'Total Aset',
    ];

    protected $casts = [
        'tindak_lanjut_at' => 'datetime',
        'total_pengeluaran' => 'float',
        'pendapatan_barang_jasa' => 'float',
        'pendapatan_lainnya' => 'float',
        'nilai_tambah' => 'float',
        'total_aset' => 'float',
        'rasio_ntb' => 'float',
    ];

    public static function statusOptions(): array
    {
        return [
            'sesuai_lapangan' => 'Sesuai kondisi lapangan',
            'diperbaiki' => 'Tidak sesuai dan telah diperbaiki',
        ];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(NtbFlag3Batch::class, 'ntb_flag3_batch_id');
    }

    // =================================================================
    // ACCESSOR TAMPILAN
    // =================================================================

    public function getNamaDisplayAttribute(): string
    {
        return $this->nama_usaha ?: '(Tanpa nama usaha)';
    }

    public function getNmkecDisplayAttribute(): string
    {
        return $this->nmkec ?: ($this->kdkec ?: '-');
    }

    public function getNmdesaDisplayAttribute(): string
    {
        return $this->nmdesa ?: ($this->kddesa ?: '-');
    }

    public function getStatusLabelAttribute(): string
    {
        return static::statusOptions()[$this->status_penyelesaian] ?? 'Sudah';
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status_penyelesaian) {
            'sesuai_lapangan' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
            'diperbaiki' => 'bg-blue-50 text-blue-700 border border-blue-200',
            default => 'bg-slate-100 text-slate-600 border border-slate-200',
        };
    }

    public static function rupiah(?float $v): string
    {
        return $v === null ? '-' : 'Rp ' . number_format($v, 0, ',', '.');
    }

    public static function rasio(?float $v): string
    {
        return $v === null ? '-' : rtrim(rtrim(number_format($v, 4, ',', '.'), '0'), ',');
    }

    // =================================================================
    // LINK FASIH MODE EDIT
    // =================================================================

    /** Link FASIH "biasa" seperti kolom link_fasih_sm modul lain (dibangun dari survey_period_id). */
    public function getLinkFasihSmAttribute(): ?string
    {
        if (!$this->assignment_id) return null;

        return $this->survey_period_id
            ? "https://fasih-sm.bps.go.id/app/assignment/{$this->survey_period_id}/{$this->assignment_id}"
            : "https://fasih-sm.bps.go.id/app/assignment-detail/{$this->assignment_id}";
    }

    public function getFasihLinkAttribute(): ?string
    {
        if (!$this->assignment_id) return null;

        if ($tpl = config('ntb-flag3.fasih_edit_url')) {
            return strtr($tpl, [
                '{assignment_id}' => $this->assignment_id,
                '{survey_id}' => $this->survey_period_id ?? '',
            ]);
        }

        // pinjam logika link mode edit milik modul Anomali (sama seperti Cek KBLI / Anomali 24)
        try {
            $link = $this->asAnomali()->fasih_link;
        } catch (\Throwable) {
            $link = null;
        }

        return $link ?: $this->link_fasih_sm;
    }

    public function getFasihLinkShortAttribute(): string
    {
        if (!config('ntb-flag3.fasih_edit_url')) {
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
            'link_fasih' => $this->link_fasih_sm,
        ]);
    }
}
