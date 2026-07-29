<?php

namespace App\Livewire\Anomali;

use App\Models\AnomaliBatch;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class AnomaliList extends Component
{
    public function render()
    {
        $batches = AnomaliBatch::withCount('mikros')
            ->orderByDesc('tanggal')
            ->get()
            ->map(function ($b) {
                $b->persen = $b->persenSelesai();
                return $b;
            });

        return view('livewire.anomali.anomali-list', [
            'batches' => $batches,
            'canUpload' => in_array(session('role'), ['admin', 'anomali']),
        ]);
    }
}
