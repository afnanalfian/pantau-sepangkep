<?php

namespace App\Models;

use App\Services\PetugasResolver;
use Illuminate\Database\Eloquent\Model;

class AnomaliMikro extends Model
{
    protected $guarded = [];

    protected $casts = [
        'tindak_lanjut_at' => 'datetime',
    ];

    // Status penyelesaian
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

    // -----------------------------------------------------------------
    // WILAYAH
    // -----------------------------------------------------------------

    /**
     * region_code 16 digit (kunci untuk mencari petugas di tabel sls_dailies).
     * Kalau kolomnya kosong (data lama), dihitung on-the-fly dari kode wilayah.
     */
    public function getRegionKeyAttribute(): ?string
    {
        return PetugasResolver::normalizeRegionCode($this->region_code)
            ?: PetugasResolver::buildRegionCode(
                $this->kdkab, $this->kdkec, $this->kddesa, $this->kode_sls, $this->sub_sls
            );
    }

    /**
     * Nama yang aman untuk ditampilkan (beberapa baris FASIH memang tidak
     * memiliki nama KRT/usaha).
     */
    public function getNamaDisplayAttribute(): string
    {
        $nama = trim((string) $this->nama);

        return $nama !== '' && $nama !== '-' ? $nama : '(Nama tidak tersedia)';
    }

    public function getSlsLabelAttribute(): string
    {
        return trim(($this->kode_sls ?? '') . '/' . ($this->sub_sls ?? ''), '/');
    }

    // -----------------------------------------------------------------
    // STATUS
    // -----------------------------------------------------------------

    public function getStatusColorAttribute(): string
    {
        return match ($this->status_penyelesaian) {
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

    // -----------------------------------------------------------------
    // LINK FASIH
    // -----------------------------------------------------------------

    public function getFasihLinkAttribute(): ?string
    {
        if (!$this->assignment_id) {
            return null;
        }

        return "https://fasih-sm.bps.go.id/app/assignment/fd68e454-ba45-4b85-8205-f3bf777ded24/{$this->assignment_id}/edit";
    }

    public function getFasihLinkShortAttribute(): ?string
    {
        return $this->assignment_id ? 'Buka Link Fasih' : null;
    }
}
