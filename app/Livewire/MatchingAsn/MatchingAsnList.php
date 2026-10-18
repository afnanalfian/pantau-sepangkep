<?php

namespace App\Livewire\MatchingAsn;

use App\Models\MatchingAsnBatch;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class MatchingAsnList extends Component
{
    public function render()
    {
        $batches = MatchingAsnBatch::withCount([
                'mikros',
                'mikros as selesai_count' => fn ($q) => $q->where('tindak_lanjut', 'sudah'),
                'mikros as duplikat_count' => fn ($q) => $q->where('is_duplikat', true),
                'mikros as instansi_na_count' => fn ($q) => $q->whereNull('instansi_kerja'),
            ])
            ->orderByDesc('tanggal')
            ->get()
            ->each(fn ($b) => $b->persen = $b->persenSelesai());

        return view('livewire.matching-asn.matching-asn-list', [
            'batches' => $batches,
            'canUpload' => in_array(session('role'), config('matching-asn.upload_roles', ['admin'])),
        ]);
    }
}
