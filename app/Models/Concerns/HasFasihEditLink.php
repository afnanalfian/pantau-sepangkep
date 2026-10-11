<?php

namespace App\Models\Concerns;

use App\Models\AnomaliMikro;

/**
 * Link FASIH mode edit untuk model yang hanya punya kolom assignment_id.
 *
 * Model pemakai wajib mendefinisikan:
 *   protected function fasihConfigKey(): string  // mis. 'modul-pendapatan.pemindahan'
 */
trait HasFasihEditLink
{
    abstract protected function fasihConfigKey(): string;

    protected function fasihTemplate(): ?string
    {
        return config($this->fasihConfigKey() . '.fasih_edit_url')
            ?: config('modul-pendapatan.fasih_edit_url');
    }

    public function getFasihLinkAttribute(): ?string
    {
        if (!$this->assignment_id) return null;

        if ($tpl = $this->fasihTemplate()) {
            return strtr($tpl, ['{assignment_id}' => $this->assignment_id]);
        }

        // Pinjam logika link mode edit milik modul Anomali
        try {
            return $this->asAnomali()->fasih_link ?: null;
        } catch (\Throwable) {
            return null;
        }
    }

    public function getFasihLinkShortAttribute(): string
    {
        if (!$this->fasihTemplate()) {
            try {
                $short = $this->asAnomali()->fasih_link_short;
                if ($short) return $short;
            } catch (\Throwable) {
                // pakai label default
            }
        }

        return 'Edit di FASIH';
    }

    /** Objek AnomaliMikro sementara (tidak disimpan) untuk meminjam accessor link-nya. */
    protected function asAnomali(): AnomaliMikro
    {
        return (new AnomaliMikro())->forceFill([
            'assignment_id' => $this->assignment_id,
            'link_fasih' => '',
        ]);
    }
}
