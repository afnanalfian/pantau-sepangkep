<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnomaliMikro extends Model
{
    protected $guarded = [];

    protected $casts = ['tindak_lanjut_at' => 'datetime'];

    public function batch()
    {
        return $this->belongsTo(AnomaliBatch::class, 'anomali_batch_id');
    }

    public function mitra()
    {
        return $this->belongsTo(Mitra::class, 'email_petugas', 'email');
    }
}
