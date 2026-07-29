<?php

namespace App\Livewire\Dashboard;

use App\Models\DailyUpload;
use App\Services\DashboardUploadService;
use Livewire\Attributes\Validate;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.public')]
class UploadHarian extends Component
{
    use WithFileUploads;

    #[Validate('required|file|mimes:xlsx,xls|max:10240')]
    public $file;

    #[Validate('required|date')]
    public string $tanggal = '';

    public string $successMessage = '';

    public function mount()
    {
        abort_unless(in_array(session('role'), ['admin']), 403, 'Hanya admin yang dapat mengakses panel unggah.');
        $this->tanggal = now()->format('Y-m-d');
    }

    public function simpanUpload(DashboardUploadService $service)
    {
        $this->validate();

        $daily = $service->import($this->file, $this->tanggal, session('role_label'));

        $this->reset('file');
        $this->successMessage = 'Data tanggal ' . \Carbon\Carbon::parse($daily->tanggal)->translatedFormat('d F Y') . ' berhasil diunggah (' . $daily->slsDailies()->count() . ' baris SLS).';
    }

    public function deleteUpload(int $id)
    {
        abort_unless(session('role') === 'admin', 403);
        DailyUpload::findOrFail($id)->delete();
    }

    public function render()
    {
        return view('livewire.dashboard.upload-harian', [
            'riwayat' => DailyUpload::orderByDesc('tanggal')->get(),
        ]);
    }
}
