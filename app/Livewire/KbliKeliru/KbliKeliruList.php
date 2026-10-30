<?php

namespace App\Livewire\KbliKeliru;

use App\Models\KbliKeliruBatch;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class KbliKeliruList extends Component
{
    public function render()
    {
        $batches = KbliKeliruBatch::withCount([
                'mikros',
                'mikros as selesai_count' => fn ($q) => $q->where('tindak_lanjut', 'sudah'),
            ])
            ->orderByDesc('tanggal')
            ->get()
            ->each(fn ($b) => $b->persen = $b->persenSelesai());

        return view('livewire.kbli-keliru.kbli-keliru-list', [
            'batches' => $batches,
            'canUpload' => in_array(session('role'), config('kbli-keliru.upload_roles', ['admin'])),
        ]);
    }
}
