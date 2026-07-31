<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnomaliMikro extends Model
{
    protected $guarded = [];

    protected $casts = [
        'tindak_lanjut_at' => 'datetime',
    ];

    // Tambahkan konstanta untuk status
    public const STATUS_REVOKED_PML = 'revoked_pml';
    public const STATUS_DISELESAIKAN_ADMIN = 'diselesaikan_admin';
    public const STATUS_REJECT_ADMIN = 'reject_admin';

    public static function statusOptions(): array
    {
        return [
            self::STATUS_REVOKED_PML => 'Revoked PML',
            self::STATUS_DISELESAIKAN_ADMIN => 'Diselesaikan Admin',
            self::STATUS_REJECT_ADMIN => 'Reject Admin',
        ];
    }

    public function batch()
    {
        return $this->belongsTo(AnomaliBatch::class, 'anomali_batch_id');
    }

    public function mitra()
    {
        return $this->belongsTo(Mitra::class, 'email_petugas', 'email');
    }

    // Helper untuk warna status
    public function getStatusColorAttribute(): string
    {
        return match($this->status_penyelesaian) {
            self::STATUS_REVOKED_PML => 'bg-blue-50 text-blue-700 border-blue-200',
            self::STATUS_DISELESAIKAN_ADMIN => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            self::STATUS_REJECT_ADMIN => 'bg-red-50 text-red-700 border-red-200',
            default => 'bg-gray-50 text-gray-700 border-gray-200',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return self::statusOptions()[$this->status_penyelesaian] ?? '-';
    }

    // Method untuk generate link fasih yang lengkap
    public function getFasihLinkAttribute(): ?string
    {
        if (!$this->assignment_id) {
            return null;
        }
        
        // Format: https://fasih-sm.bps.go.id/app/assignment/fd68e454-ba45-4b85-8205-f3bf777ded24/{assignment_id}/edit
        return "https://fasih-sm.bps.go.id/app/assignment/fd68e454-ba45-4b85-8205-f3bf777ded24/{$this->assignment_id}/edit";
    }

    // Method untuk mendapatkan link fasih yang sudah di-shorten (untuk display)
    public function getFasihLinkShortAttribute(): ?string
    {
        if (!$this->assignment_id) {
            return null;
        }
        
        // Menampilkan assignment_id saja sebagai link
        return $this->assignment_id;
    }
}