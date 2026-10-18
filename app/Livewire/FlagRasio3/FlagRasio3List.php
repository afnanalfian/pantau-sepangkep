<?php

namespace App\Livewire\FlagRasio3;

use App\Models\FlagRasio3Batch;
use App\Models\FlagRasio3Mikro;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class FlagRasio3List extends Component
{
    public function render()
    {
        $batches = FlagRasio3Batch::withCount([
                'mikros',
                'mikros as selesai_count' => fn ($q) => $q->where('tindak_lanjut', 'sudah'),
                'mikros as ub_count' => fn ($q) => $q->where('jenis', 'UB'),
                'mikros as um_count' => fn ($q) => $q->where('jenis', 'UM'),
            ])
            ->orderByDesc('tanggal')
            ->get()
            ->each(fn ($b) => $b->persen = $b->persenSelesai());

        return view('livewire.flag-rasio3.flag-rasio3-list', [
            'batches' => $batches,
            'jenisOptions' => FlagRasio3Mikro::jenisOptions(),
            'canUpload' => in_array(session('role'), config('flag-rasio3.upload_roles', ['admin'])),
        ]);
    }
}
