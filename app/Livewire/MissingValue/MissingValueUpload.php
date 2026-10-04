<?php

namespace App\Livewire\MissingValue;

use App\Services\MissingValueUploadService;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
#[Title('Unggah Missing Value')]
class MissingValueUpload extends Component
{
    use WithFileUploads;

    public string $tanggal = '';
    public $fileMissing;

    public string $successMessage = '';

    public function mount()
    {
        abort_unless(in_array(session('role'), ['admin', 'anomali']), 403, 'Hanya Admin Anomali yang dapat mengunggah data ini.');
        $this->tanggal = now()->format('Y-m-d');
    }

    // Catatan: nama method sengaja BUKAN "upload" agar tidak bentrok
    // dengan $wire.upload() bawaan Livewire v3.
    public function simpanUpload(MissingValueUploadService $service)
    {
        $this->validate([
            'tanggal' => 'required|date',
            'fileMissing' => 'required|file|mimes:xlsx,xls|max:51200',
        ], [
            'fileMissing.required' => 'File excel Missing Value wajib dipilih.',
            'fileMissing.mimes' => 'File harus berformat .xlsx atau .xls.',
        ]);

        try {
            $batch = $service->importMissingValue($this->fileMissing, $this->tanggal, session('role_label'));
        } catch (\Throwable $e) {
            report($e);
            $this->addError('fileMissing', $e->getMessage());
            return;
        }

        $info = $service->ringkasan['missing_value'] ?? [];
        $this->reset('fileMissing');
        $this->successMessage = 'Missing value tanggal ' . Carbon::parse($batch->tanggal)->translatedFormat('d F Y')
            . ' berhasil diunggah (' . number_format($info['baris'] ?? 0) . ' baris).';
    }

    public function render()
    {
        return view('livewire.missing-value.missing-value-upload');
    }
}
