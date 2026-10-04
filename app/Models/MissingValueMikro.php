<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MissingValueMikro extends Model
{
    protected $table = 'missing_value_mikros';

    protected $guarded = ['id'];

    protected $casts = [
        'tindak_lanjut_at' => 'datetime',
        'skor_kemiripan' => 'float',
    ];

    /** Cache objek AnomaliMikro "bayangan" untuk membangun link edit FASIH. */
    protected ?AnomaliMikro $anomaliProxy = null;

    public function batch(): BelongsTo
    {
        return $this->belongsTo(MissingValueBatch::class, 'missing_value_batch_id');
    }

    // =================================================================
    // STATUS PENYELESAIAN (metode)
    // Sengaja memakai daftar yang SAMA dengan anomali supaya pilihan
    // metode di kedua modul selalu identik. Kalau nanti Missing Value
    // butuh metode berbeda, cukup ganti isi method ini.
    // =================================================================

    public static function statusOptions(): array
    {
        return AnomaliMikro::statusOptions();
    }

    public function getStatusLabelAttribute(): string
    {
        return static::statusOptions()[$this->status_penyelesaian] ?? ($this->status_penyelesaian ?: 'Selesai');
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status_penyelesaian) {
            'revoked_pml' => 'bg-blue-50 text-blue-700 border border-blue-200',
            'diselesaikan_admin' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
            'reject_admin' => 'bg-red-50 text-red-700 border border-red-200',
            default => 'bg-slate-50 text-slate-700 border border-slate-200',
        };
    }

    // =================================================================
    // TAMPILAN
    // =================================================================

    public function getNamaDisplayAttribute(): string
    {
        return $this->nama ?: '(Nama tidak tercatat)';
    }

    public function getSlsLabelAttribute(): string
    {
        return trim(($this->kode_sls ?: '-') . ' / ' . ($this->sub_sls ?: '00'));
    }

    public function getRegionKeyAttribute(): ?string
    {
        return $this->region_code;
    }

    /** "05-03-1990" dari kolom Tgl/Bln/Thn Lahir SE. */
    public function getTglLahirSeLabelAttribute(): string
    {
        $t = $this->tgl_lahir_se;
        $b = $this->bln_lahir_se;
        $y = $this->thn_lahir_se;
        if (!$t && !$b && !$y) return '-';

        $pad = fn ($v) => $v === null || $v === '' ? '??' : str_pad((string) $v, 2, '0', STR_PAD_LEFT);

        return $pad($t) . '-' . $pad($b) . '-' . ($y ?: '????');
    }

    public function getSkorLabelAttribute(): string
    {
        if ($this->skor_kemiripan === null) return '-';
        $s = (float) $this->skor_kemiripan;

        // skor 0–1 ditampilkan sebagai persen, skor 0–100 apa adanya
        return $s <= 1 ? round($s * 100, 1) . '%' : round($s, 1) . '%';
    }

    /** Skor dinormalisasi ke rentang 0–1 (null kalau kosong). */
    public function getSkorNormalAttribute(): ?float
    {
        if ($this->skor_kemiripan === null) return null;
        $s = (float) $this->skor_kemiripan;

        return $s > 1 ? $s / 100 : $s;
    }

    /** @return array<int, string> daftar variabel yang missing (dipecah koma/titik koma). */
    public function getVariabelListAttribute(): array
    {
        return static::splitVariabel($this->variabel_missing);
    }

    public static function splitVariabel(?string $raw): array
    {
        if (!$raw) return [];

        return collect(preg_split('/[,;|\n]+/', $raw) ?: [])
            ->map(fn ($v) => trim($v))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    // =================================================================
    // LINK FASIH MODE EDIT
    // -----------------------------------------------------------------
    // Link dibangun dengan logika yang SAMA PERSIS dengan modul anomali
    // (accessor fasih_link milik AnomaliMikro), BUKAN memakai kolom
    // "Link Fasih" dari excel. Caranya: buat objek AnomaliMikro sementara
    // (tidak disimpan ke DB) berisi data baris ini, lalu ambil link-nya.
    // Missing value adalah data anggota keluarga, jadi jenis = 'keluarga'.
    // =================================================================

    protected function anomaliProxy(): AnomaliMikro
    {
        return $this->anomaliProxy ??= (new AnomaliMikro())->forceFill(array_merge(
            $this->getAttributes(),
            [
                'jenis' => 'keluarga',
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
