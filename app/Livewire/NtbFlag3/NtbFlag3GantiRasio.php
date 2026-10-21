<?php

namespace App\Livewire\NtbFlag3;

use App\Models\NtbFlag3Batch;
use App\Services\NtbFlag3RasioService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Khusus ADMIN: ganti massal rasio_ntb berdasarkan assignment_id.
 * Langkah 1 "Periksa" -> ringkasan & contoh perubahan (database belum berubah).
 * Langkah 2 "Terapkan" -> update database.
 */
#[Layout('components.layouts.app')]
class NtbFlag3GantiRasio extends Component
{
    use WithFileUploads;

    public $file;
    public string $batchId = '';        // '' = semua batch

    public ?string $cacheKey = null;    // data hasil parse disimpan di cache, bukan di state Livewire
    public ?array $ringkasan = null;    // hasil periksa (untuk ditampilkan)
    public ?string $namaFile = null;
    public string $successMessage = '';

    public function mount()
    {
        $this->pastikanAdmin();

        // dibuka dari halaman detail batch -> target langsung batch itu
        $b = request()->query('batch');
        if ($b && NtbFlag3Batch::whereKey($b)->exists()) {
            $this->batchId = (string) $b;
        }
    }

    protected function pastikanAdmin(): void
    {
        abort_unless(session('role') === 'admin', 403, 'Fitur ganti rasio NTB hanya untuk admin.');
    }

    public function updatedFile()
    {
        $this->bersihkanPratinjau();
    }

    public function updatedBatchId()
    {
        // target berubah -> hitung ulang pratinjau dari data yang sama
        if ($this->cacheKey && ($data = Cache::get($this->cacheKey))) {
            $this->ringkasan = $this->susunRingkasan($data, app(NtbFlag3RasioService::class));
        }
    }

    public function periksa(NtbFlag3RasioService $service)
    {
        $this->pastikanAdmin();
        $this->successMessage = '';

        $this->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:20480',
            'batchId' => 'nullable|exists:ntb_flag3_batches,id',
        ], [
            'file.required' => 'Pilih file Excel berisi kolom assignment_id dan rasio_ntb.',
        ]);

        try {
            $data = $service->parse($this->file);
        } catch (\Throwable $e) {
            report($e);
            $this->addError('file', $e->getMessage());
            return;
        }

        if (empty($data['map'])) {
            $this->addError('file', 'Tidak ada baris valid (assignment_id & rasio_ntb terisi) di file ini.');
            return;
        }

        $this->bersihkanPratinjau();
        $this->cacheKey = 'ntb3-rasio-' . Str::uuid();
        Cache::put($this->cacheKey, $data, now()->addMinutes(30));

        $this->namaFile = $this->file->getClientOriginalName();
        $this->ringkasan = $this->susunRingkasan($data, $service);
    }

    public function terapkan(NtbFlag3RasioService $service)
    {
        $this->pastikanAdmin();

        $data = $this->cacheKey ? Cache::get($this->cacheKey) : null;
        if (!$data) {
            $this->addError('file', 'Pratinjau sudah kedaluwarsa. Silakan klik "Periksa" lagi.');
            $this->ringkasan = null;
            return;
        }

        $n = $service->apply($data['map'], $this->batchId !== '' ? (int) $this->batchId : null);

        logger()->info('NTB Flag 3: rasio_ntb diganti massal', [
            'oleh' => session('role_label'),
            'file' => $this->namaFile,
            'batch_id' => $this->batchId ?: 'semua',
            'assignment' => count($data['map']),
            'baris_diupdate' => $n,
        ]);

        $target = $this->batchId !== '' ? 'batch ' . $this->labelBatch((int) $this->batchId) : 'semua batch';
        $this->bersihkanPratinjau();
        $this->reset('file');
        $this->successMessage = 'Rasio NTB berhasil diganti: ' . number_format($n) . ' baris diperbarui di ' . $target . '.';
    }

    public function batal()
    {
        $this->bersihkanPratinjau();
        $this->reset('file');
    }

    protected function bersihkanPratinjau(): void
    {
        if ($this->cacheKey) Cache::forget($this->cacheKey);
        $this->cacheKey = null;
        $this->ringkasan = null;
        $this->namaFile = null;
        $this->resetErrorBag();
    }

    protected function susunRingkasan(array $data, NtbFlag3RasioService $service): array
    {
        return [
            'baris_file' => $data['baris_file'],
            'duplikat' => $data['duplikat'],
            'invalid' => count($data['invalid']),
            'contoh_invalid' => array_slice($data['invalid'], 0, 10),
        ] + $service->preview($data['map'], $this->batchId !== '' ? (int) $this->batchId : null);
    }

    protected function labelBatch(int $id): string
    {
        $b = NtbFlag3Batch::find($id);

        return $b ? $b->tanggal->translatedFormat('d F Y') : '#' . $id;
    }

    public function render()
    {
        return view('livewire.ntb-flag3.ntb-flag3-ganti-rasio', [
            'batches' => NtbFlag3Batch::orderByDesc('tanggal')->get(['id', 'tanggal']),
        ]);
    }
}
