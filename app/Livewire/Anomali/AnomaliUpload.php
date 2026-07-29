<?php

namespace App\Livewire\Anomali;

use App\Services\AnomaliUploadService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
class AnomaliUpload extends Component
{
    use WithFileUploads;

    public string $tanggal = '';

    public $radarUsaha;
    public $radarKeluarga;
    public $mikroUsaha;
    public $mikroKeluarga;

    public string $successMessage = '';

    public function mount()
    {
        abort_unless(in_array(session('role'), ['admin', 'anomali']), 403, 'Hanya Admin Anomali yang dapat mengunggah data ini.');
        $this->tanggal = now()->format('Y-m-d');
    }

    public function simpanUpload(AnomaliUploadService $service)
    {
        $this->validate([
            'tanggal' => 'required|date',
            'radarUsaha' => 'required|file|mimes:xlsx,xls',
            'radarKeluarga' => 'required|file|mimes:xlsx,xls',
            'mikroUsaha' => 'required|file|mimes:xlsx,xls',
            'mikroKeluarga' => 'required|file|mimes:xlsx,xls',
        ]);

        $batch = $service->importBatch([
            'radar_usaha' => $this->radarUsaha,
            'radar_keluarga' => $this->radarKeluarga,
            'mikro_usaha' => $this->mikroUsaha,
            'mikro_keluarga' => $this->mikroKeluarga,
        ], $this->tanggal, session('role_label'));

        $this->reset(['radarUsaha', 'radarKeluarga', 'mikroUsaha', 'mikroKeluarga']);
        $this->successMessage = 'Anomali tanggal ' . \Carbon\Carbon::parse($batch->tanggal)->translatedFormat('d F Y') . ' berhasil diunggah.';
    }

    public function render()
    {
        return view('livewire.anomali.anomali-upload');
    }
}
