<?php

namespace App\Livewire\KbliKeliru;

use App\Services\KbliKeliruUploadService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
class KbliKeliruUpload extends Component
{
    use WithFileUploads;

    public string $tanggal = '';
    public $file;
    public string $successMessage = '';

    public function mount()
    {
        abort_unless(
            in_array(session('role'), config('kbli-keliru.upload_roles', ['admin'])),
            403,
            'Anda tidak memiliki akses untuk mengunggah data KBLI Keliru.'
        );
        $this->tanggal = now()->format('Y-m-d');
    }

    public function simpanUpload(KbliKeliruUploadService $service)
    {
        $this->validate([
            'tanggal' => 'required|date',
            'file' => 'required|file|mimes:xlsx,xls,csv|max:20480',
        ], [
            'file.required' => 'Silakan pilih file KBLI Keliru.',
        ]);

        try {
            $batch = $service->importBatch($this->file, $this->tanggal, session('role_label'));
        } catch (\Throwable $e) {
            report($e);
            $this->addError('file', $e->getMessage());
            return;
        }

        $r = $service->ringkasan;
        $this->reset('file');
        $this->successMessage = 'KBLI Keliru tanggal ' . $batch->tanggal->translatedFormat('d F Y') . ' berhasil diunggah: '
            . number_format($r['baris']) . ' baris, ' . number_format($r['pic']) . ' PIC.'
            . ($r['tanpa_pic'] ? ' (' . number_format($r['tanpa_pic']) . ' baris tanpa PIC.)' : '');
    }

    public function render()
    {
        return view('livewire.kbli-keliru.kbli-keliru-upload');
    }
}
