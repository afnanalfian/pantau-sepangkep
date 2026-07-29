<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnomaliBatch extends Model
{
    protected $fillable = ['tanggal', 'uploaded_by'];

    protected $casts = ['tanggal' => 'date'];

    public function radars()
    {
        return $this->hasMany(AnomaliRadar::class);
    }

    public function mikros()
    {
        return $this->hasMany(AnomaliMikro::class);
    }

    public function persenSelesai(): float
    {
        $total = $this->mikros()->count();
        if ($total === 0) return 0;
        $selesai = $this->mikros()->where('tindak_lanjut', 'sudah')->count();
        return round(($selesai / $total) * 100, 1);
    }
}
