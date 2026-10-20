<?php

namespace App\Livewire\NtbFlag3;

use App\Models\NtbFlag3Batch;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class NtbFlag3List extends Component
{
    public function render()
    {
        $batches = NtbFlag3Batch::withCount([
                'mikros',
                'mikros as selesai_count' => fn ($q) => $q->where('tindak_lanjut', 'sudah'),
            ])
            ->orderByDesc('tanggal')
            ->get()
            ->each(fn ($b) => $b->persen = $b->persenSelesai());

        return view('livewire.ntb-flag3.ntb-flag3-list', [
            'batches' => $batches,
            'canUpload' => in_array(session('role'), config('ntb-flag3.upload_roles', ['admin'])),
        ]);
    }
}
