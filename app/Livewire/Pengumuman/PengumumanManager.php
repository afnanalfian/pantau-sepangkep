<?php

namespace App\Livewire\Pengumuman;

use App\Models\Pengumuman;
use Livewire\Attributes\Validate;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class PengumumanManager extends Component
{
    use WithFileUploads, WithPagination;

    public bool $showForm = false;
    public ?int $editingId = null;

    #[Validate('required|string|max:200')]
    public string $judul = '';

    #[Validate('nullable|string|max:300')]
    public string $ringkasan = '';

    #[Validate('required|string')]
    public string $konten = '';

    public array $lampiranLink = [];
    public string $linkBaru = '';
    public string $linkBaruNama = '';

    public $lampiranFile = null;
    public $lampiranGambar = null;

    public array $lampiranTersimpan = [];

    public function mount()
    {
        // semua role pegawai bisa CRUD pengumuman
    }

    public function bukaForm()
    {
        $this->reset(['judul', 'ringkasan', 'konten', 'lampiranLink', 'lampiranTersimpan', 'editingId', 'linkBaru', 'linkBaruNama']);
        $this->showForm = true;
    }

    public function edit(int $id)
    {
        $p = Pengumuman::findOrFail($id);
        $this->editingId = $p->id;
        $this->judul = $p->judul;
        $this->ringkasan = $p->ringkasan ?? '';
        $this->konten = $p->konten;
        $this->lampiranTersimpan = $p->lampiran ?? [];
        $this->showForm = true;
        $this->dispatch('konten-loaded', konten: $this->konten);
    }

    public function tambahLink()
    {
        if (trim($this->linkBaru) === '') return;
        $this->lampiranTersimpan[] = [
            'type' => 'link',
            'url' => $this->linkBaru,
            'name' => $this->linkBaruNama ?: $this->linkBaru,
        ];
        $this->linkBaru = '';
        $this->linkBaruNama = '';
    }

    public function hapusLampiran(int $index)
    {
        unset($this->lampiranTersimpan[$index]);
        $this->lampiranTersimpan = array_values($this->lampiranTersimpan);
    }

    public function unggahFile()
    {
        if ($this->lampiranFile) {
            $path = $this->lampiranFile->store('lampiran', 'public');
            $this->lampiranTersimpan[] = [
                'type' => 'file',
                'url' => Storage::url($path),
                'name' => $this->lampiranFile->getClientOriginalName(),
            ];
            $this->lampiranFile = null;
        }
        if ($this->lampiranGambar) {
            $path = $this->lampiranGambar->store('lampiran', 'public');
            $this->lampiranTersimpan[] = [
                'type' => 'image',
                'url' => Storage::url($path),
                'name' => $this->lampiranGambar->getClientOriginalName(),
            ];
            $this->lampiranGambar = null;
        }
    }

    public function simpan()
    {
        $this->validate();

        $data = [
            'judul' => $this->judul,
            'ringkasan' => $this->ringkasan,
            'konten' => $this->konten,
            'lampiran' => $this->lampiranTersimpan,
            'dibuat_oleh' => session('role_label'),
        ];

        if ($this->editingId) {
            Pengumuman::whereKey($this->editingId)->update($data);
        } else {
            Pengumuman::create($data);
        }

        $this->showForm = false;
        $this->reset(['judul', 'ringkasan', 'konten', 'lampiranTersimpan', 'editingId']);
    }

    public function hapus(int $id)
    {
        Pengumuman::findOrFail($id)->delete();
    }

    public function render()
    {
        return view('livewire.pengumuman.pengumuman-manager', [
            'daftar' => Pengumuman::latest()->paginate(10),
        ]);
    }
}
