<?php

namespace App\Livewire\Pengumuman;

use App\Models\Pengumuman;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.public')]
class PengumumanDetail extends Component
{
    public Pengumuman $pengumuman;

    public function mount(Pengumuman $pengumuman)
    {
        $this->pengumuman = $pengumuman;
    }

    public function render()
    {
        return view('livewire.pengumuman.pengumuman-detail');
    }
}
