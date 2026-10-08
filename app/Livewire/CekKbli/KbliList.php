<?php

namespace App\Livewire\CekKbli;

use App\Models\KbliBatch;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class KbliList extends Component
{
    public function render()
    {
        $batches = KbliBatch::withCount([
                'mikros',
                'mikros as selesai_count' => fn ($q) => $q->where('tindak_lanjut', 'sudah'),
            ])
            ->orderByDesc('tanggal')
            ->get()
            ->map(function ($b) {
                $b->persen = $b->persenSelesai();
                return $b;
            });

        return view('livewire.cek-kbli.kbli-list', [
            'batches' => $batches,
            'canUpload' => in_array(session('role'), config('cek-kbli.upload_roles', ['admin'])),
        ]);
    }
}
