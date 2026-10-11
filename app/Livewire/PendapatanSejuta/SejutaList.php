<?php

namespace App\Livewire\PendapatanSejuta;

use App\Models\SejutaBatch;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class SejutaList extends Component
{
    public function render()
    {
        $batches = SejutaBatch::withCount([
                'mikros',
                'mikros as selesai_count' => fn ($q) => $q->where('tindak_lanjut', 'sudah'),
            ])
            ->orderByDesc('tanggal')
            ->get()
            ->each(fn ($b) => $b->persen = $b->persenSelesai());

        return view('livewire.pendapatan-sejuta.sejuta-list', [
            'batches' => $batches,
            'canUpload' => in_array(session('role'), config('modul-pendapatan.sejuta.upload_roles', ['admin'])),
        ]);
    }
}
