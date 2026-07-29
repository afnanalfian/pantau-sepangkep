<div>

<div class="flex flex-wrap items-center gap-3 mb-5">
    <input type="text" wire:model.live.debounce.400ms="search" placeholder="Cari judul berkas..."
           class="flex-1 min-w-[200px] px-4 py-2.5 rounded-lg border border-slate-300 text-sm outline-none focus:border-[#0F7B8A]">
    <select wire:model.live="filterKategori" class="px-3 py-2.5 rounded-lg border border-slate-300 text-sm outline-none">
        <option value="">Semua Kategori</option>
        @foreach($kategoriList as $k)<option value="{{ $k }}">{{ $k }}</option>@endforeach
    </select>
    @if($isAdmin)
        <button wire:click="bukaForm" class="px-4 py-2.5 rounded-lg bg-[#0B2A4A] hover:bg-[#0d3760] text-white text-sm font-semibold whitespace-nowrap">+ Tambah Berkas</button>
    @endif
</div>

<div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-xs uppercase text-slate-500 font-semibold">
            <tr>
                <th class="px-4 py-3 text-left">Judul</th>
                <th class="px-4 py-3 text-left">Kategori</th>
                <th class="px-4 py-3 text-left">Keterangan</th>
                <th class="px-4 py-3 text-left">Diunggah</th>
                <th class="px-4 py-3 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($daftar as $a)
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3 font-semibold text-slate-700">{{ $a->judul }}</td>
                    <td class="px-4 py-3">
                        @if($a->kategori)<span class="text-xs font-semibold px-2 py-1 rounded-full bg-slate-100 text-slate-600">{{ $a->kategori }}</span>@endif
                    </td>
                    <td class="px-4 py-3 text-slate-500 text-xs">{{ $a->keterangan }}</td>
                    <td class="px-4 py-3 text-xs text-slate-400">{{ $a->created_at->translatedFormat('d M Y') }} &middot; {{ $a->diunggah_oleh }}</td>
                    <td class="px-4 py-3 text-right space-x-3">
                        <a href="{{ $a->file_path }}" target="_blank" class="text-xs font-semibold text-[#0F7B8A] hover:underline">Unduh</a>
                        @if($isAdmin)
                            <button wire:click="edit({{ $a->id }})" class="text-xs font-semibold text-amber-600 hover:underline">Edit</button>
                            <button wire:click="hapus({{ $a->id }})" wire:confirm="Hapus berkas ini?" class="text-xs font-semibold text-red-500 hover:underline">Hapus</button>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-4 py-10 text-center text-slate-400">Belum ada berkas.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $daftar->links() }}</div>

@if($showForm)
<div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50">
    <div class="bg-white rounded-2xl w-full max-w-lg p-6">
        <h3 class="font-display font-bold text-lg text-[#0B2A4A] mb-4">{{ $editingId ? 'Edit Berkas' : 'Tambah Berkas' }}</h3>
        <form wire:submit="simpan" class="space-y-3">
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Judul</label>
                <input type="text" wire:model="judul" class="w-full px-4 py-2.5 rounded-lg border border-slate-300 outline-none">
                @error('judul') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Kategori</label>
                <input type="text" wire:model="kategori" placeholder="Surat, Undangan, Himbauan, dll" class="w-full px-4 py-2.5 rounded-lg border border-slate-300 outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Keterangan</label>
                <textarea wire:model="keterangan" rows="2" class="w-full px-4 py-2.5 rounded-lg border border-slate-300 outline-none"></textarea>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">File Berkas</label>
                <input type="file" wire:model="file" class="w-full text-sm border border-slate-300 rounded-lg px-4 py-2">
                @error('file') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" wire:click="$set('showForm', false)" class="px-4 py-2 text-sm text-slate-500 font-semibold">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-[#0B2A4A] text-white text-sm font-semibold">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endif

</div>
