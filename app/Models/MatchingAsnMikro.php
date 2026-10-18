<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatchingAsnMikro extends Model
{
    protected $table = 'matching_asn_mikros';

    protected $guarded = ['id'];

    protected $casts = [
        'is_duplikat' => 'boolean',
        'jumlah_kandidat_kk' => 'integer',
        'tindak_lanjut_at' => 'datetime',
    ];

    public const INSTANSI_TIDAK_DITEMUKAN = 'Instansi Tidak Ditemukan';

    /** Nilai filter khusus untuk instansi #N/A */
    public const FILTER_INSTANSI_NA = '__na__';

    /** Pilihan penyelesaian untuk baris TIDAK duplikat */
    public static function statusOptions(): array
    {
        return [
            'didata' => 'Telah Didata',
            'dihapus' => 'Telah Dihapus',
        ];
    }

    /** Filter kelengkapan identitas */
    public static function identitasOptions(): array
    {
        return [
            'lengkap' => 'NIK & KK lengkap',
            'tanpa_nik' => 'Tanpa NIK',
            'tanpa_kk' => 'Tanpa Nomor KK',
            'tanpa_keduanya' => 'Tanpa NIK & KK',
        ];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(MatchingAsnBatch::class, 'matching_asn_batch_id');
    }

    // =================================================================
    // ACCESSOR TAMPILAN
    // =================================================================

    public function getNamaDisplayAttribute(): string
    {
        return $this->nama_prelist ?: '(Tanpa nama)';
    }

    public function getInstansiDisplayAttribute(): string
    {
        return $this->instansi_kerja ?: self::INSTANSI_TIDAK_DITEMUKAN;
    }

    public function getStatusLabelAttribute(): string
    {
        if ($this->tindak_lanjut !== 'sudah') return 'Belum';
        if ($this->is_duplikat) return 'Duplikat telah dihapus';

        return static::statusOptions()[$this->status_penyelesaian] ?? 'Sudah';
    }

    public function getStatusColorAttribute(): string
    {
        if ($this->tindak_lanjut !== 'sudah') return 'bg-red-50 text-red-700 border border-red-200';

        return match ($this->status_penyelesaian) {
            'didata' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
            'dihapus' => 'bg-slate-100 text-slate-700 border border-slate-300',
            default => 'bg-slate-100 text-slate-600 border border-slate-200',
        };
    }

    // =================================================================
    // LINK FASIH-SM (4 macam)
    // =================================================================

    protected static function searchUrl(?string $q): ?string
    {
        if ($q === null || $q === '') return null;

        return str_replace('{q}', rawurlencode($q), config('matching-asn.fasih.search_url'));
    }

    /** Detail assignment */
    public function getLinkDetailAttribute(): ?string
    {
        return $this->assignment_id
            ? str_replace('{assignment_id}', $this->assignment_id, config('matching-asn.fasih.detail_url'))
            : null;
    }

    /** Cari berdasarkan NIK */
    public function getLinkCariNikAttribute(): ?string
    {
        return static::searchUrl($this->nik);
    }

    /** Cari berdasarkan kode identitas */
    public function getLinkCariKodeAttribute(): ?string
    {
        return static::searchUrl($this->code_identity);
    }

    /** Cari berdasarkan assignment_id */
    public function getLinkCariAssignmentAttribute(): ?string
    {
        return static::searchUrl($this->assignment_id);
    }

    /** @return array<string, array{0:string, 1:?string}> label => url, untuk tombol di tabel */
    public function getFasihLinksAttribute(): array
    {
        return [
            'detail' => ['Detail', $this->link_detail],
            'nik' => ['Cari NIK', $this->link_cari_nik],
            'kode' => ['Cari Kode Identitas', $this->link_cari_kode],
            'assignment' => ['Cari Assignment', $this->link_cari_assignment],
        ];
    }
}
