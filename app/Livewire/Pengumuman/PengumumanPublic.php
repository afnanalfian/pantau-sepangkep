<?php

namespace App\Livewire\Pengumuman;

use App\Models\Pengumuman;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.public')]
class PengumumanPublic extends Component
{
    use WithPagination;

    public function render()
    {
        return view('livewire.pengumuman.pengumuman-public', [
            'daftar' => Pengumuman::latest()->paginate(10),
        ]);
    }
}
