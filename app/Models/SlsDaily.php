<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SlsDaily extends Model
{
    protected $guarded = [];

    protected $casts = ['tanggal' => 'date'];

    public function dailyUpload()
    {
        return $this->belongsTo(DailyUpload::class);
    }

    // Selesai = total - open - draft (dipakai utk kinerja PPL, detail SLS, dashboard pusat, gabungan)
    public function getSelesaiAttribute(): int
    {
        return max(0, $this->total_region - $this->open - $this->draft);
    }

    // Realisasi PML = total - open - draft - submitted_pencacah
    public function getSelesaiPmlAttribute(): int
    {
        return max(0, $this->total_region - $this->open - $this->draft - $this->submitted_pencacah);
    }
}
