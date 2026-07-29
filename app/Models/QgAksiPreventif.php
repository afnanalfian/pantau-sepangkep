<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QgAksiPreventif extends Model
{
    protected $fillable = [
        'qg_uk_id', 'urutan', 'deskripsi', 'pelaksana', 'template_path',
        'laporan_path', 'link_bukti_dukung', 'bukti_dukung_checklist',
    ];

    protected $casts = [
        'pelaksana' => 'array',
        'bukti_dukung_checklist' => 'boolean',
    ];

    public function uk()
    {
        return $this->belongsTo(QgUk::class, 'qg_uk_id');
    }

    public function isSelesai(): bool
    {
        return !empty($this->laporan_path) && $this->bukti_dukung_checklist;
    }
}
