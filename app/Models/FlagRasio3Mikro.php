<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FlagRasio3Mikro extends Model
{
    protected $table = 'flag_rasio3_mikros';

    protected $guarded = ['id'];

    /** Kolom rupiah (dipakai tampilan & export). */
    public const KOLOM_RUPIAH = [
        'upah_gaji' => 'Upah & Gaji',
        'biaya_produksi' => 'Biaya Produksi',
        'biaya_pembelian' => 'Biaya Pembelian',
        'biaya_operasional' => 'Biaya Operasional',
        'biaya_non_operasional' => 'Biaya Non Operasional',
        'total_pengeluaran' => 'Total Pengeluaran',
        'pendapatan_barang_jasa' => 'Pendapatan Barang & Jasa',
        'pendapatan_lainnya' => 'Pendapatan Lainnya',
        'nilai_tambah' => 'Nilai Tambah (NTB)',
        'total_aset' => 'Total Aset',
        'output' => 'Output',
    ];

    protected $casts = [
        'tindak_lanjut_at' => 'datetime',
        'tenaga_kerja' => 'integer',
        'upah_gaji' => 'float',
        'biaya_produksi' => 'float',
        'biaya_pembelian' => 'float',
        'biaya_operasional' => 'float',
        'biaya_non_operasional' => 'float',
        'total_pengeluaran' => 'float',
        'pendapatan_barang_jasa' => 'float',
        'pendapatan_lainnya' => 'float',
        'nilai_tambah' => 'float',
        'total_aset' => 'float',
        'output' => 'float',
        'rasio_biaya_pembelian' => 'float',
        'rasio_ntb' => 'float',
        'produktivitas' => 'float',
    ];

    public static function jenisOptions(): array
    {
        return [
            'UB' => [
                'kode' => 172,
                'label' => 'UB (Usaha Besar)',
                'judul' => 'Anomali 172 : UB (Usaha Besar) - Anomali Flag Rasio 3 NTB Output',
                'badge' => 'bg-indigo-50 text-indigo-700 border border-indigo-200',
            ],
            'UM' => [
                'kode' => 173,
                'label' => 'UM (Usaha Menengah)',
                'judul' => 'Anomali 173 : UM (Usaha Menengah) - Anomali Flag Rasio 3 NTB Output',
                'badge' => 'bg-teal-50 text-teal-700 border border-teal-200',
            ],
        ];
    }

    public static function statusOptions(): array
    {
        return [
            'sesuai_lapangan' => 'Sesuai kondisi lapangan',
            'diperbaiki' => 'Tidak sesuai dan telah diperbaiki',
        ];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(FlagRasio3Batch::class, 'flag_rasio3_batch_id');
    }

    // =================================================================
    // ACCESSOR TAMPILAN
    // =================================================================

    public function getNamaDisplayAttribute(): string
    {
        return $this->nama_usaha ?: '(Tanpa nama usaha)';
    }

    public function getJenisLabelAttribute(): string
    {
        return static::jenisOptions()[$this->jenis]['label'] ?? $this->jenis;
    }

    public function getJenisBadgeAttribute(): string
    {
        return static::jenisOptions()[$this->jenis]['badge'] ?? 'bg-slate-100 text-slate-600 border border-slate-200';
    }

    public function getKbliLabelAttribute(): string
    {
        if (!$this->kode_kbli) return '-';
        if (class_exists(KbliMaster::class)) {
            try {
                return KbliMaster::label($this->kode_kbli);
            } catch (\Throwable) {
                // tabel master belum ada
            }
        }

        return 'Kategori ' . ($this->kategori ?? '-');
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

    public static function rasio(?float $v, int $dec = 4): string
    {
        return $v === null ? '-' : number_format($v, $dec, ',', '.');
    }

    // =================================================================
    // LINK FASIH MODE EDIT
    // =================================================================

    public function getFasihLinkAttribute(): ?string
    {
        if (!$this->assignment_id) return null;

        if ($tpl = config('flag-rasio3.fasih_edit_url')) {
            return strtr($tpl, ['{assignment_id}' => $this->assignment_id]);
        }

        // pinjam logika link mode edit milik modul Anomali
        try {
            $link = $this->asAnomali()->fasih_link;
        } catch (\Throwable) {
            $link = null;
        }

        return $link ?: $this->link_fasih_sm;
    }

    public function getFasihLinkShortAttribute(): string
    {
        if (!config('flag-rasio3.fasih_edit_url')) {
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
