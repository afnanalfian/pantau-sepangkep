<?php

namespace App\Livewire\FlagRasio3;

use App\Models\FlagRasio3Mikro;
use App\Services\FlagRasio3UploadService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
class FlagRasio3Upload extends Component
{
    use WithFileUploads;

    public string $tanggal = '';
    public $fileUb;
    public $fileUm;
    public string $successMessage = '';

    public function mount()
    {
        abort_unless(
            in_array(session('role'), config('flag-rasio3.upload_roles', ['admin'])),
            403,
            'Anda tidak memiliki akses untuk mengunggah data Anomali Flag Rasio 3.'
        );
        $this->tanggal = now()->format('Y-m-d');
    }

    public function simpanUpload(FlagRasio3UploadService $service)
    {
        $this->validate([
            'tanggal' => 'required|date',
            'fileUb' => 'nullable|required_without:fileUm|file|mimes:xlsx,xls,csv|max:20480',
            'fileUm' => 'nullable|required_without:fileUb|file|mimes:xlsx,xls,csv|max:20480',
        ], [
            'fileUb.required_without' => 'Pilih minimal satu file: UB atau UM.',
            'fileUm.required_without' => 'Pilih minimal satu file: UB atau UM.',
        ]);

        try {
            $batch = $service->importBatch(['UB' => $this->fileUb, 'UM' => $this->fileUm], $this->tanggal, session('role_label'));
        } catch (\Throwable $e) {
            report($e);
            $this->addError('fileUb', $e->getMessage());
            return;
        }

        $parts = [];
        foreach ($service->ringkasan as $jenis => $n) {
            $parts[] = $jenis . ' ' . number_format($n) . ' baris';
        }

        $this->reset(['fileUb', 'fileUm']);
        $this->successMessage = 'Anomali Flag Rasio 3 tanggal ' . $batch->tanggal->translatedFormat('d F Y')
            . ' berhasil diunggah: ' . implode(', ', $parts) . '.';
    }

    public function render()
    {
        return view('livewire.flag-rasio3.flag-rasio3-upload', [
            'jenisOptions' => FlagRasio3Mikro::jenisOptions(),
        ]);
    }
}
