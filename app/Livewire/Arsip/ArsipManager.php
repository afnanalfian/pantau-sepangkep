<?php

namespace App\Livewire\Arsip;

use App\Models\Arsip;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class ArsipManager extends Component
{
    use WithFileUploads, WithPagination;

    public string $search = '';
    public string $filterKategori = '';

    public bool $showForm = false;
    public ?int $editingId = null;
    public string $judul = '';
    public string $kategori = '';
    public string $keterangan = '';
    public $file;

    protected function isAdmin(): bool
    {
        return session('role') === 'admin';
    }

    public function updatingSearch() { $this->resetPage(); }

    public function bukaForm()
    {
        abort_unless($this->isAdmin(), 403);
        $this->reset(['judul', 'kategori', 'keterangan', 'file', 'editingId']);
        $this->showForm = true;
    }

    public function edit(int $id)
    {
        abort_unless($this->isAdmin(), 403);
        $a = Arsip::findOrFail($id);
        $this->editingId = $a->id;
        $this->judul = $a->judul;
        $this->kategori = $a->kategori ?? '';
        $this->keterangan = $a->keterangan ?? '';
        $this->showForm = true;
    }

    public function simpan()
    {
        abort_unless($this->isAdmin(), 403);
        $this->validate([
            'judul' => 'required|string|max:200',
            'kategori' => 'nullable|string|max:100',
            'file' => $this->editingId ? 'nullable|file|max:20480' : 'required|file|max:20480',
        ]);

        $data = [
            'judul' => $this->judul,
            'kategori' => $this->kategori ?: null,
            'keterangan' => $this->keterangan ?: null,
            'diunggah_oleh' => session('role_label'),
        ];

        if ($this->file) {
            $data['file_path'] = Storage::url($this->file->store('arsip', 'public'));
            $data['file_asli'] = $this->file->getClientOriginalName();
        }

        if ($this->editingId) {
            Arsip::whereKey($this->editingId)->update($data);
        } else {
            Arsip::create($data);
        }

        $this->showForm = false;
        $this->reset(['judul', 'kategori', 'keterangan', 'file', 'editingId']);
    }

    public function hapus(int $id)
    {
        abort_unless($this->isAdmin(), 403);
        Arsip::findOrFail($id)->delete();
    }

    public function render()
    {
        $q = Arsip::query();
        if ($this->search) $q->where('judul', 'like', '%' . $this->search . '%');
        if ($this->filterKategori) $q->where('kategori', $this->filterKategori);

        return view('livewire.arsip.arsip-manager', [
            'daftar' => $q->latest()->paginate(10),
            'kategoriList' => Arsip::whereNotNull('kategori')->distinct()->pluck('kategori'),
            'isAdmin' => $this->isAdmin(),
        ]);
    }
}
