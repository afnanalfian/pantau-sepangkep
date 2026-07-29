<?php

namespace App\Livewire\Qna;

use App\Models\Qna;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.public')]
class QnaPublic extends Component
{
    use WithPagination;

    public string $nama = '';
    public string $pertanyaan = '';
    public bool $anonim = false;
    public bool $terkirim = false;

    public function kirim()
    {
        $this->validate([
            'pertanyaan' => 'required|string|min:5|max:1000',
            'nama' => 'nullable|string|max:100',
        ]);

        Qna::create([
            'nama' => $this->anonim ? null : ($this->nama ?: null),
            'pertanyaan' => $this->pertanyaan,
            'status' => 'menunggu',
        ]);

        $this->reset(['nama', 'pertanyaan', 'anonim']);
        $this->terkirim = true;
    }

    public function render()
    {
        return view('livewire.qna.qna-public', [
            'daftar' => Qna::where('status', 'dijawab')->latest('dijawab_at')->paginate(10),
        ]);
    }
}
