<?php

namespace App\Livewire\NtbFlag3;

use App\Services\NtbFlag3UploadService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
class NtbFlag3Upload extends Component
{
    use WithFileUploads;

    public string $tanggal = '';
    public $file;
    public string $successMessage = '';

    public function mount()
    {
        abort_unless(
            in_array(session('role'), config('ntb-flag3.upload_roles', ['admin'])),
            403,
            'Anda tidak memiliki akses untuk mengunggah data Anomali NTB Flag 3.'
        );
        $this->tanggal = now()->format('Y-m-d');
    }

    public function simpanUpload(NtbFlag3UploadService $service)
    {
        $this->validate([
            'tanggal' => 'required|date',
            'file' => 'required|file|mimes:xlsx,xls,csv|max:20480',
        ], [
            'file.required' => 'Silakan pilih file Anomali NTB Flag 3.',
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
        $this->successMessage = 'Anomali NTB Flag 3 tanggal ' . $batch->tanggal->translatedFormat('d F Y')
            . ' berhasil diunggah: ' . number_format($r['baris']) . ' baris.'
            . ($r['tanpa_wilayah'] > 0 ? ' (' . number_format($r['tanpa_wilayah']) . ' baris nama kecamatan/desa-nya tidak ditemukan di data harian, ditampilkan sebagai kode.)' : '');
    }

    public function render()
    {
        return view('livewire.ntb-flag3.ntb-flag3-upload');
    }
}
