<?php

namespace App\Livewire\Sosek;

use App\Models\SosekMikro;
use App\Services\SosekUploadService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
class SosekUpload extends Component
{
    use WithFileUploads;

    public string $tanggal = '';
    public $file;                       // SATU workbook berisi semua sheet anomali
    public string $successMessage = '';
    public array $dilewati = [];

    public function mount()
    {
        abort_unless(
            in_array(session('role'), config('sosek.upload_roles', ['admin'])),
            403,
            'Anda tidak memiliki akses untuk mengunggah data Anomali Sosek.'
        );
        $this->tanggal = now()->format('Y-m-d');
    }

    public function simpanUpload(SosekUploadService $service)
    {
        $this->validate([
            'tanggal' => 'required|date',
            'file' => 'required|file|mimes:xlsx,xls|max:20480',
        ], [
            'file.required' => 'Pilih file Excel Data Mikro Anomali Sosek.',
        ]);

        $this->successMessage = '';
        $this->dilewati = [];

        try {
            $batch = $service->importWorkbook($this->file, $this->tanggal, session('role_label'));
        } catch (\Throwable $e) {
            report($e);
            $this->addError('file', $e->getMessage());
            return;
        }

        $opts = SosekMikro::semuaJenis();
        $bagian = [];
        foreach ($service->ringkasan['per_jenis'] ?? [] as $j => $n) {
            $bagian[] = $opts[$j]['no'] . '. ' . $opts[$j]['singkat'] . ' ' . number_format($n);
        }
        $pic = $service->ringkasan['pic'] ?? ['ada_pic' => 0, 'total' => 0, 'tanpa_pic' => 0];

        $this->reset('file');
        $this->dilewati = $service->ringkasan['dilewati'] ?? [];
        $this->successMessage = 'Anomali Sosek tanggal ' . $batch->tanggal->translatedFormat('d F Y') . ' berhasil diunggah: '
            . implode(', ', $bagian) . '. PIC terisi ' . number_format($pic['ada_pic']) . ' dari ' . number_format($pic['total']) . ' baris'
            . ($pic['tanpa_pic'] ? '; ' . number_format($pic['tanpa_pic']) . ' baris belum punya PIC (ID SubSLS tidak ada di data harian).' : '.');
    }

    public function render()
    {
        return view('livewire.sosek.sosek-upload', [
            'jenisOptions' => SosekMikro::jenisOptions(),
            'nonaktif' => array_diff_key(SosekMikro::semuaJenis(), SosekMikro::jenisOptions()),
        ]);
    }
}
