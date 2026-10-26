<?php

namespace App\Livewire\Sosek;

use App\Models\SosekBatch;
use App\Models\SosekMikro;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class SosekList extends Component
{
    public function render()
    {
        $batches = SosekBatch::withCount([
                'mikros',
                'mikros as selesai_count' => fn ($q) => $q->where('tindak_lanjut', 'sudah'),
            ])
            ->orderByDesc('tanggal')
            ->get()
            ->each(fn ($b) => $b->persen = $b->persenSelesai());

        return view('livewire.sosek.sosek-list', [
            'batches' => $batches,
            'jenisOptions' => SosekMikro::jenisOptions(),
            'canUpload' => in_array(session('role'), config('sosek.upload_roles', ['admin'])),
        ]);
    }
}
