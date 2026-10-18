<?php

namespace App\Livewire\MatchingAsn;

use App\Services\MatchingAsnUploadService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
class MatchingAsnUpload extends Component
{
    use WithFileUploads;

    public string $tanggal = '';
    public $file;
    public string $successMessage = '';

    public function mount()
    {
        abort_unless(
            in_array(session('role'), config('matching-asn.upload_roles', ['admin'])),
            403,
            'Anda tidak memiliki akses untuk mengunggah data Matching ASN.'
        );
        $this->tanggal = now()->format('Y-m-d');
    }

    public function simpanUpload(MatchingAsnUploadService $service)
    {
        $this->validate([
            'tanggal' => 'required|date',
            'file' => 'required|file|mimes:xlsx,xls,csv|max:20480',
        ], [
            'file.required' => 'Silakan pilih file Matching ASN.',
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
        $this->successMessage = 'Matching ASN tanggal ' . $batch->tanggal->translatedFormat('d F Y') . ' berhasil diunggah: '
            . number_format($r['baris']) . ' baris (' . number_format($r['duplikat']) . ' duplikat, '
            . number_format($r['instansi_na']) . ' instansi tidak ditemukan, '
            . number_format($r['tanpa_nik']) . ' tanpa NIK, ' . number_format($r['tanpa_kk']) . ' tanpa KK).';
    }

    public function render()
    {
        return view('livewire.matching-asn.matching-asn-upload');
    }
}
