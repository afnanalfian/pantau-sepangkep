<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnomaliRadar extends Model
{
    protected $fillable = ['anomali_batch_id', 'jenis', 'kode', 'kecamatan', 'total_assignment', 'data'];

    protected $casts = ['data' => 'array'];

    public function batch()
    {
        return $this->belongsTo(AnomaliBatch::class, 'anomali_batch_id');
    }
}
