<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MatchingAsnBatch extends Model
{
    protected $table = 'matching_asn_batches';

    protected $fillable = ['tanggal', 'nama_file', 'uploaded_by'];

    protected $casts = ['tanggal' => 'date'];

    public function mikros(): HasMany
    {
        return $this->hasMany(MatchingAsnMikro::class, 'matching_asn_batch_id');
    }

    public function persenSelesai(): float
    {
        $total = $this->mikros_count ?? $this->mikros()->count();
        if ($total == 0) return 0;

        $selesai = $this->selesai_count ?? $this->mikros()->where('tindak_lanjut', 'sudah')->count();

        return round($selesai / $total * 100, 1);
    }
}
