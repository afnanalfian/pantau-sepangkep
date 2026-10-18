<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FlagRasio3Batch extends Model
{
    protected $table = 'flag_rasio3_batches';

    protected $fillable = ['tanggal', 'nama_file_ub', 'nama_file_um', 'uploaded_by'];

    protected $casts = ['tanggal' => 'date'];

    public function mikros(): HasMany
    {
        return $this->hasMany(FlagRasio3Mikro::class, 'flag_rasio3_batch_id');
    }

    public function persenSelesai(): float
    {
        $total = $this->mikros_count ?? $this->mikros()->count();
        if ($total == 0) return 0;

        $selesai = $this->selesai_count ?? $this->mikros()->where('tindak_lanjut', 'sudah')->count();

        return round($selesai / $total * 100, 1);
    }
}
