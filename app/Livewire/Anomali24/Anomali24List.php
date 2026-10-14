<?php

namespace App\Livewire\Anomali24;

use App\Models\Anomali24Batch;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Anomali24List extends Component
{
    public function render()
    {
        $batches = Anomali24Batch::withCount([
                'mikros',
                'mikros as selesai_count' => fn ($q) => $q->where('tindak_lanjut', 'sudah'),
            ])
            ->orderByDesc('tanggal')
            ->get()
            ->map(function ($b) {
                $b->persen = $b->persenSelesai();
                return $b;
            });

        return view('livewire.anomali24.anomali24-list', [
            'batches' => $batches,
            'canUpload' => in_array(session('role'), config('anomali24.upload_roles', ['admin'])),
        ]);
    }
}
