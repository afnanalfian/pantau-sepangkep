<?php

namespace App\Livewire\Qna;

use App\Models\Qna;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class QnaAdmin extends Component
{
    use WithPagination;

    public string $filterStatus = 'menunggu';
    public array $jawabanDraft = [];

    public function mount()
    {
        abort_unless(in_array(session('role'), ['admin', 'inda']), 403, 'Hanya Admin dan INDA yang dapat mengakses modul ini.');
    }

    public function simpanJawaban(int $id)
    {
        $teks = trim($this->jawabanDraft[$id] ?? '');
        if ($teks === '') return;

        Qna::whereKey($id)->update([
            'jawaban' => $teks,
            'status' => 'dijawab',
            'dijawab_oleh' => session('role_label'),
            'dijawab_at' => now(),
        ]);

        unset($this->jawabanDraft[$id]);
    }

    public function render()
    {
        $query = Qna::query();
        if ($this->filterStatus !== 'semua') {
            $query->where('status', $this->filterStatus);
        }

        return view('livewire.qna.qna-admin', [
            'daftar' => $query->latest()->paginate(10),
            'jumlahMenunggu' => Qna::where('status', 'menunggu')->count(),
        ]);
    }
}
