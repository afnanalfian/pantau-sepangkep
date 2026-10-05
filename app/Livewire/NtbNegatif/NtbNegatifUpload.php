<?php

namespace App\Livewire\NtbNegatif;

use App\Services\NtbNegatifUploadService;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
#[Title('Unggah NTB Negatif')]
class NtbNegatifUpload extends Component
{
    use WithFileUploads;

    public string $tanggal = '';
    public $fileNtb;

    public string $successMessage = '';

    public function mount()
    {
        abort_unless(in_array(session('role'), ['admin', 'anomali']), 403, 'Hanya Admin Anomali yang dapat mengunggah data ini.');
        $this->tanggal = now()->format('Y-m-d');
    }

    // Nama method sengaja bukan "upload" (bentrok dengan $wire.upload() Livewire v3)
    public function simpanUpload(NtbNegatifUploadService $service)
    {
        $this->validate([
            'tanggal' => 'required|date',
            'fileNtb' => 'required|file|mimes:xlsx,xls,csv,txt|max:51200',
        ], [
            'fileNtb.required' => 'File NTB Negatif wajib dipilih.',
            'fileNtb.mimes' => 'File harus berformat .xlsx, .xls, atau .csv.',
        ]);

        try {
            $batch = $service->importNtbNegatif($this->fileNtb, $this->tanggal, session('role_label'));
        } catch (\Throwable $e) {
            report($e);
            $this->addError('fileNtb', $e->getMessage());
            return;
        }

        $info = $service->ringkasan['ntb_negatif'] ?? [];
        $this->reset('fileNtb');
        $this->successMessage = 'NTB Negatif tanggal ' . Carbon::parse($batch->tanggal)->translatedFormat('d F Y')
            . ' berhasil diunggah (' . number_format($info['baris'] ?? 0) . ' baris).';
    }

    public function render()
    {
        return view('livewire.ntb-negatif.ntb-negatif-upload');
    }
}
