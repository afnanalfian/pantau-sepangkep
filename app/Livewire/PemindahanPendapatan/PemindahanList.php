<?php

namespace App\Livewire\PemindahanPendapatan;

use App\Models\PemindahanBatch;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class PemindahanList extends Component
{
    public function render()
    {
        $batches = PemindahanBatch::withCount([
                'mikros',
                'mikros as selesai_count' => fn ($q) => $q->where('tindak_lanjut', 'sudah'),
            ])
            ->orderByDesc('tanggal')
            ->get()
            ->each(fn ($b) => $b->persen = $b->persenSelesai());

        return view('livewire.pemindahan-pendapatan.pemindahan-list', [
            'batches' => $batches,
            'canUpload' => in_array(session('role'), config('modul-pendapatan.pemindahan.upload_roles', ['admin'])),
        ]);
    }
}
