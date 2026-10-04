<?php

namespace App\Livewire\MissingValue;

use App\Models\MissingValueBatch;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Missing Value')]
class MissingValueList extends Component
{
    public function render()
    {
        $batches = MissingValueBatch::withCount([
                'mikros',
                'mikros as selesai_count' => fn ($q) => $q->where('tindak_lanjut', 'sudah'),
            ])
            ->orderByDesc('tanggal')
            ->get()
            ->map(function ($b) {
                $b->persen = $b->mikros_count > 0 ? round($b->selesai_count / $b->mikros_count * 100, 1) : 0;
                return $b;
            });

        return view('livewire.missing-value.missing-value-list', [
            'batches' => $batches,
            'canUpload' => in_array(session('role'), ['admin', 'anomali']),
        ]);
    }
}
