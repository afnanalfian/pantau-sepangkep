<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyUpload extends Model
{
    protected $fillable = ['tanggal', 'nama_file', 'uploaded_by'];

    protected $casts = ['tanggal' => 'date'];

    public function slsDailies()
    {
        return $this->hasMany(SlsDaily::class);
    }
}
