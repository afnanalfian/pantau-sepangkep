<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SosekMikro extends Model
{
    protected $table = 'sosek_mikros';

    protected $guarded = ['id'];

    protected $casts = [
        'tindak_lanjut_at' => 'datetime',
        'umur' => 'integer',
        'jml_meteran' => 'integer',
        'jumlah_ac' => 'integer',
    ];

    /**
     * 7 jenis anomali. 'wajib' = kolom yang harus ada di file agar file
     * tidak salah slot (mis. file Umur diunggah di slot AC).
     */
    public static function jenisOptions(): array
    {
        return [
            'umur' => ['no' => 1, 'label' => 'Umur di luar rentang 0 - 95', 'singkat' => 'Umur', 'wajib' => ['umur_ak'], 'warna' => 'bg-rose-50 text-rose-700 border-rose-200'],
            'profesi' => ['no' => 2, 'label' => 'Profesi pekerjaan utama', 'singkat' => 'Profesi', 'wajib' => ['profesi_label', 'kode_profesi'], 'warna' => 'bg-violet-50 text-violet-700 border-violet-200'],
            'kk_prov' => ['no' => 3, 'label' => '2 digit pertama Nomor KK tidak sesuai kode provinsi', 'singkat' => 'KK ≠ prov', 'wajib' => ['no_kk'], 'warna' => 'bg-sky-50 text-sky-700 border-sky-200'],
            'kk_kembar' => ['no' => 4, 'label' => 'Nomor KK mengandung 6 digit kembar berturut-turut', 'singkat' => 'KK kembar', 'wajib' => ['no_kk'], 'warna' => 'bg-cyan-50 text-cyan-700 border-cyan-200'],
            'nik_prov' => ['no' => 5, 'label' => '2 digit pertama NIK tidak sesuai kode provinsi', 'singkat' => 'NIK ≠ prov', 'wajib' => ['nik_dtsen'], 'warna' => 'bg-amber-50 text-amber-700 border-amber-200'],
            'nik_kembar' => ['no' => 6, 'label' => 'NIK mengandung 6 digit kembar berturut-turut', 'singkat' => 'NIK kembar', 'wajib' => ['nik_dtsen'], 'warna' => 'bg-orange-50 text-orange-700 border-orange-200'],
            'ac_450' => ['no' => 7, 'label' => 'Meteran 450 watt tetapi memiliki AC lebih dari 2 unit', 'singkat' => '450W + AC', 'wajib' => ['jumlah_ac'], 'warna' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
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
        return $this->belongsTo(SosekBatch::class, 'sosek_batch_id');
    }

    // =================================================================
    // ACCESSOR TAMPILAN
    // =================================================================

    public function getJenisInfoAttribute(): array
    {
        return static::jenisOptions()[$this->jenis] ?? ['no' => '?', 'label' => $this->jenis, 'singkat' => $this->jenis, 'warna' => 'bg-slate-100 text-slate-600 border-slate-200'];
    }

    /** Nama yang paling relevan: nama ART (DTSEN) bila ada, kalau tidak nama KK */
    public function getNamaDisplayAttribute(): string
    {
        return $this->nama_art ?: ($this->nama_kk ?: '(Tanpa nama)');
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

    // =================================================================
    // LINK FASIH MODE EDIT (sama seperti Cek KBLI / Anomali 24)
    // =================================================================

    public function surveyId(): ?string
    {
        if ($this->link_fasih_sm && preg_match('#/assignment/([^/]+)/[^/?\#]+#', $this->link_fasih_sm, $m)) {
            return $m[1];
        }

        return null;
    }

    public function getFasihLinkAttribute(): ?string
    {
        if (!$this->assignment_id) return $this->link_fasih_sm;

        if ($tpl = config('sosek.fasih_edit_url')) {
            return strtr($tpl, ['{assignment_id}' => $this->assignment_id, '{survey_id}' => $this->surveyId() ?? '']);
        }

        try {
            $link = $this->asAnomali()->fasih_link;
        } catch (\Throwable) {
            $link = null;
        }

        return $link ?: $this->link_fasih_sm;
    }

    public function getFasihLinkShortAttribute(): string
    {
        if (!config('sosek.fasih_edit_url')) {
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
