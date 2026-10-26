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

    /** satu properti per slot (lebih aman untuk upload Livewire daripada array) */
    public $file_umur;
    public $file_profesi;
    public $file_kk_prov;
    public $file_kk_kembar;
    public $file_nik_prov;
    public $file_nik_kembar;
    public $file_ac_450;

    public string $successMessage = '';

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
        $jenis = array_keys(SosekMikro::jenisOptions());
        $rules = ['tanggal' => 'required|date'];
        foreach ($jenis as $j) {
            $rules["file_$j"] = 'nullable|file|mimes:xlsx,xls,csv|max:20480';
        }
        $this->validate($rules);

        $terpilih = [];
        foreach ($jenis as $j) {
            if ($this->{"file_$j"}) $terpilih[$j] = $this->{"file_$j"};
        }
        if (!$terpilih) {
            $this->addError('files', 'Pilih minimal satu file anomali.');
            return;
        }

        try {
            $batch = $service->importBatch($terpilih, $this->tanggal, session('role_label'));
        } catch (\Throwable $e) {
            report($e);
            $this->addError('files', $e->getMessage());
            return;
        }

        $opts = SosekMikro::jenisOptions();
        $bagian = [];
        foreach ($service->ringkasan['per_jenis'] ?? [] as $j => $n) {
            $bagian[] = $opts[$j]['no'] . '. ' . $opts[$j]['singkat'] . ' ' . number_format($n);
        }
        $pic = $service->ringkasan['pic'] ?? ['ada_pic' => 0, 'total' => 0, 'tanpa_pic' => 0];

        $this->reset(array_map(fn ($j) => "file_$j", array_keys($opts)));
        $this->successMessage = 'Anomali Sosek tanggal ' . $batch->tanggal->translatedFormat('d F Y') . ' berhasil diunggah ('
            . implode(', ', $bagian) . '). PIC terisi ' . number_format($pic['ada_pic']) . ' dari ' . number_format($pic['total']) . ' baris'
            . ($pic['tanpa_pic'] ? '; ' . number_format($pic['tanpa_pic']) . ' baris belum punya PIC (wilayah tidak terlacak).' : '.');
    }

    public function render()
    {
        return view('livewire.sosek.sosek-upload', [
            'jenisOptions' => SosekMikro::jenisOptions(),
        ]);
    }
}
