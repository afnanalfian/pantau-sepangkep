<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengumuman extends Model
{
    protected $table = 'pengumumen';

    protected $fillable = ['judul', 'ringkasan', 'konten', 'lampiran', 'dibuat_oleh'];

    protected $casts = ['lampiran' => 'array'];

    public function isBaru(): bool
    {
        return $this->created_at->greaterThanOrEqualTo(now()->subDays(3));
    }
}
