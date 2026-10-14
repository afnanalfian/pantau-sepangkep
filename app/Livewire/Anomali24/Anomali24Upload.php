<?php

namespace App\Livewire\Anomali24;

use App\Models\KbliMaster;
use App\Services\Anomali24UploadService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
class Anomali24Upload extends Component
{
    use WithFileUploads;

    public string $tanggal = '';
    public $fileKbli;
    public $fileMaster;

    public string $successMessage = '';
    public string $masterMessage = '';

    public function mount()
    {
        abort_unless(
            in_array(session('role'), config('anomali24.upload_roles', ['admin'])),
            403,
            'Anda tidak memiliki akses untuk mengunggah data Anomali 24.'
        );
        $this->tanggal = now()->format('Y-m-d');
    }

    public function simpanUpload(Anomali24UploadService $service)
    {
        $this->validate([
            'tanggal' => 'required|date',
            'fileKbli' => 'required|file|mimes:xlsx,xls,csv|max:51200',
        ], [
            'fileKbli.required' => 'Silakan pilih file Anomali 24.',
        ]);

        try {
            $batch = $service->importBatch($this->fileKbli, $this->tanggal, session('role_label'));
        } catch (\Throwable $e) {
            report($e);
            $this->addError('fileKbli', $e->getMessage());
            return;
        }

        $r = $service->ringkasan;
        $this->reset('fileKbli');
        $this->successMessage = 'Anomali 24 tanggal ' . $batch->tanggal->translatedFormat('d F Y')
            . ' berhasil diunggah: ' . number_format($r['baris']) . ' baris.'
            . ($r['tanpa_wilayah'] > 0 ? ' (' . number_format($r['tanpa_wilayah']) . ' baris nama kecamatan/desa-nya tidak ditemukan di data harian, ditampilkan sebagai kode.)' : '');
    }

    public function simpanMaster(Anomali24UploadService $service)
    {
        $this->validate([
            'fileMaster' => 'required|file|mimes:xlsx,xls,csv|max:20480',
        ], [
            'fileMaster.required' => 'Silakan pilih file master KBLI.',
        ]);

        try {
            $n = $service->importMaster($this->fileMaster);
        } catch (\Throwable $e) {
            report($e);
            $this->addError('fileMaster', $e->getMessage());
            return;
        }

        $this->reset('fileMaster');
        $this->masterMessage = number_format($n) . ' judul KBLI berhasil disimpan ke master.';
    }

    public function render()
    {
        return view('livewire.anomali24.anomali24-upload', [
            'jumlahMaster' => KbliMaster::count(),
        ]);
    }
}
