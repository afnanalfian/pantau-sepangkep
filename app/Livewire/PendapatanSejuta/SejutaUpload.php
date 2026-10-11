<?php

namespace App\Livewire\PendapatanSejuta;

use App\Services\SejutaUploadService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
class SejutaUpload extends Component
{
    use WithFileUploads;

    public string $tanggal = '';
    public $file;
    public string $successMessage = '';

    public function mount()
    {
        abort_unless(
            in_array(session('role'), config('modul-pendapatan.sejuta.upload_roles', ['admin'])),
            403,
            'Anda tidak memiliki akses untuk mengunggah data Pendapatan 1 Juta ke Bawah.'
        );
        $this->tanggal = now()->format('Y-m-d');
    }

    public function simpanUpload(SejutaUploadService $service)
    {
        $this->validate([
            'tanggal' => 'required|date',
            'file' => 'required|file|mimes:xlsx,xls,csv|max:20480',
        ], [
            'file.required' => 'Silakan pilih file Pendapatan 1 Juta ke Bawah.',
        ]);

        try {
            $batch = $service->importBatch($this->file, $this->tanggal, session('role_label'));
        } catch (\Throwable $e) {
            report($e);
            $this->addError('file', $e->getMessage());
            return;
        }

        $this->reset('file');
        $this->successMessage = 'Pendapatan 1 Juta ke Bawah tanggal ' . $batch->tanggal->translatedFormat('d F Y')
            . ' berhasil diunggah: ' . number_format($service->ringkasan['baris']) . ' baris.';
    }

    public function render()
    {
        return view('livewire.pendapatan-sejuta.sejuta-upload');
    }
}
