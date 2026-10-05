<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NtbNegatifBatch extends Model
{
    protected $table = 'ntb_negatif_batches';

    protected $fillable = ['tanggal', 'judul', 'nama_file', 'uploaded_by'];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function mikros(): HasMany
    {
        return $this->hasMany(NtbNegatifMikro::class, 'ntb_negatif_batch_id');
    }

    public function persenSelesai(): float
    {
        $total = $this->mikros()->count();
        if ($total === 0) return 0;

        return round($this->mikros()->where('tindak_lanjut', 'sudah')->count() / $total * 100, 1);
    }
}
