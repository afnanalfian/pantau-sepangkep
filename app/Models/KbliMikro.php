<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KbliMikro extends Model
{
    protected $table = 'kbli_mikros';

    protected $guarded = ['id'];

    protected $casts = [
        'tindak_lanjut_at' => 'datetime',
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
        return $this->belongsTo(KbliBatch::class, 'kbli_batch_id');
    }

    // =================================================================
    // ACCESSOR TAMPILAN
    // =================================================================

    public function getNamaDisplayAttribute(): string
    {
        return $this->nama_usaha ?: '(Tanpa nama usaha)';
    }

    public function getKbliLabelAttribute(): string
    {
        return KbliMaster::label($this->kbli_akhir);
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

    public function getNmkecDisplayAttribute(): string
    {
        return $this->nmkec ?: ($this->kdkec ?: '-');
    }

    public function getNmdesaDisplayAttribute(): string
    {
        return $this->nmdesa ?: ($this->kddesa ?: '-');
    }

    // =================================================================
    // LINK FASIH MODE EDIT
    // =================================================================

    /**
     * Link FASIH mode edit (BUKAN link_fasih_sm mentah dari excel).
     *
     * Default-nya meminjam logika accessor `fasih_link` dari model AnomaliMikro,
     * sehingga format link-nya persis sama dengan modul Anomali. Bisa ditimpa
     * lewat config('cek-kbli.fasih_edit_url').
     */
    public function getFasihLinkAttribute(): ?string
    {
        if (!$this->assignment_id) return null;

        $template = config('cek-kbli.fasih_edit_url');
        if ($template) {
            return strtr($template, [
                '{assignment_id}' => $this->assignment_id,
                '{survey_id}' => $this->surveyId() ?? '',
            ]);
        }

        $anomali = $this->asAnomali();
        try {
            $link = $anomali->fasih_link;
        } catch (\Throwable) {
            $link = null;
        }

        return $link ?: $this->link_fasih_sm;
    }

    public function getFasihLinkShortAttribute(): string
    {
        if (!config('cek-kbli.fasih_edit_url')) {
            try {
                $short = $this->asAnomali()->fasih_link_short;
                if ($short) return $short;
            } catch (\Throwable) {
                // abaikan, pakai label default
            }
        }

        return 'Edit di FASIH';
    }

    /** ID survei/periode dari link_fasih_sm: /app/assignment/{survey}/{assignment} */
    public function surveyId(): ?string
    {
        if ($this->link_fasih_sm && preg_match('#/assignment/([^/]+)/[^/?\#]+#', $this->link_fasih_sm, $m)) {
            return $m[1];
        }

        return null;
    }

    /** Objek AnomaliMikro sementara (tidak disimpan) untuk meminjam accessor link-nya. */
    protected function asAnomali(): AnomaliMikro
    {
        return (new AnomaliMikro())->forceFill([
            'assignment_id' => $this->assignment_id,
            'link_fasih' => $this->link_fasih_sm,
        ]);
    }
}
