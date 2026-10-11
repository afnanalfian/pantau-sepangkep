<?php

namespace App\Livewire\PemindahanPendapatan;

use App\Services\PemindahanUploadService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
class PemindahanUpload extends Component
{
    use WithFileUploads;

    public string $tanggal = '';
    public $file;
    public string $successMessage = '';

    public function mount()
    {
        abort_unless(
            in_array(session('role'), config('modul-pendapatan.pemindahan.upload_roles', ['admin'])),
            403,
            'Anda tidak memiliki akses untuk mengunggah data Pemindahan Pendapatan.'
        );
        $this->tanggal = now()->format('Y-m-d');
    }

    public function simpanUpload(PemindahanUploadService $service)
    {
        $this->validate([
            'tanggal' => 'required|date',
            'file' => 'required|file|mimes:xlsx,xls,csv|max:20480',
        ], [
            'file.required' => 'Silakan pilih file Pemindahan Pendapatan.',
        ]);

        try {
            $batch = $service->importBatch($this->file, $this->tanggal, session('role_label'));
        } catch (\Throwable $e) {
            report($e);
            $this->addError('file', $e->getMessage());
            return;
        }

        $this->reset('file');
        $this->successMessage = 'Pemindahan Pendapatan tanggal ' . $batch->tanggal->translatedFormat('d F Y')
            . ' berhasil diunggah: ' . number_format($service->ringkasan['baris']) . ' baris.';
    }

    public function render()
    {
        return view('livewire.pemindahan-pendapatan.pemindahan-upload');
    }
}
