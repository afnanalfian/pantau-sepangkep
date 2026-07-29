<div>

<div class="flex items-center justify-between mb-5">
    <p class="text-sm text-slate-500">Semua pegawai dapat membuat, mengubah, dan menghapus pengumuman.</p>
    <button wire:click="bukaForm" class="px-4 py-2.5 rounded-lg bg-[#0B2A4A] hover:bg-[#0d3760] text-white text-sm font-semibold">+ Buat Pengumuman</button>
</div>

<div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-xs uppercase text-slate-500 font-semibold">
            <tr>
                <th class="px-4 py-3 text-left">Judul</th>
                <th class="px-4 py-3 text-left">Dibuat Oleh</th>
                <th class="px-4 py-3 text-left">Tanggal</th>
                <th class="px-4 py-3 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($daftar as $p)
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3 font-semibold text-slate-700">{{ $p->judul }}</td>
                    <td class="px-4 py-3 text-slate-500">{{ $p->dibuat_oleh }}</td>
                    <td class="px-4 py-3 text-slate-500">{{ $p->created_at->translatedFormat('d M Y, H:i') }}</td>
                    <td class="px-4 py-3 text-right space-x-3">
                        <a href="{{ route('pengumuman.detail', $p) }}" target="_blank" class="text-xs font-semibold text-[#0F7B8A] hover:underline">Lihat</a>
                        <button wire:click="edit({{ $p->id }})" class="text-xs font-semibold text-amber-600 hover:underline">Edit</button>
                        <button wire:click="hapus({{ $p->id }})" wire:confirm="Hapus pengumuman ini?" class="text-xs font-semibold text-red-500 hover:underline">Hapus</button>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="px-4 py-10 text-center text-slate-400">Belum ada pengumuman.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $daftar->links() }}</div>

{{-- MODAL FORM --}}
@if($showForm)
<div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50" wire:key="modal-{{ $editingId }}">
    <div class="bg-white rounded-2xl w-full max-w-2xl shadow-2xl max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between sticky top-0 bg-white z-10">
            <h3 class="font-display font-bold text-lg text-[#0B2A4A]">{{ $editingId ? 'Edit Pengumuman' : 'Buat Pengumuman' }}</h3>
            <button wire:click="$set('showForm', false)" class="text-slate-400 hover:text-slate-600 text-xl leading-none">&times;</button>
        </div>

        <form wire:submit="simpan" class="p-6 space-y-4" x-data="pengumumanEditor(@js($konten))" x-init="init($refs.editorEl, (html) => $wire.set('konten', html))">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Judul</label>
                <input type="text" wire:model="judul" class="w-full px-4 py-2.5 rounded-lg border border-slate-300 outline-none focus:border-[#0F7B8A]">
                @error('judul') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Ringkasan (untuk daftar pengumuman)</label>
                <input type="text" wire:model="ringkasan" class="w-full px-4 py-2.5 rounded-lg border border-slate-300 outline-none focus:border-[#0F7B8A]">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Isi Pengumuman</label>
                <div wire:ignore>
                    <div x-ref="editorEl" class="bg-white" style="min-height:180px;"></div>
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Lampiran</label>
                <div class="space-y-2 mb-3">
                    @foreach($lampiranTersimpan as $i => $l)
                        <div class="flex items-center justify-between px-3 py-2 bg-slate-50 rounded-lg text-sm">
                            <span class="flex items-center gap-2">
                                <span class="text-[10px] font-bold uppercase px-1.5 py-0.5 rounded bg-slate-200 text-slate-600">{{ $l['type'] }}</span>
                                {{ $l['name'] }}
                            </span>
                            <button type="button" wire:click="hapusLampiran({{ $i }})" class="text-red-400 hover:text-red-600 text-xs font-semibold">Hapus</button>
                        </div>
                    @endforeach
                </div>

                <div class="grid sm:grid-cols-2 gap-3 mb-2">
                    <div>
                        <input type="file" wire:model="lampiranFile" class="w-full text-xs border border-slate-300 rounded-lg px-3 py-2">
                        <p class="text-[10px] text-slate-400 mt-1">File (PDF, dokumen, dll)</p>
                    </div>
                    <div>
                        <input type="file" wire:model="lampiranGambar" accept="image/*" class="w-full text-xs border border-slate-300 rounded-lg px-3 py-2">
                        <p class="text-[10px] text-slate-400 mt-1">Gambar</p>
                    </div>
                </div>
                <button type="button" wire:click="unggahFile" class="text-xs font-semibold text-[#0F7B8A] hover:underline mb-3">+ Tambahkan file/gambar di atas</button>

                <div class="flex gap-2">
                    <input type="text" wire:model="linkBaruNama" placeholder="Nama link" class="w-1/3 px-3 py-2 rounded-lg border border-slate-300 text-sm">
                    <input type="url" wire:model="linkBaru" placeholder="https://..." class="flex-1 px-3 py-2 rounded-lg border border-slate-300 text-sm">
                    <button type="button" wire:click="tambahLink" class="px-3 py-2 rounded-lg bg-slate-100 text-slate-600 text-xs font-semibold">+ Link</button>
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" wire:click="$set('showForm', false)" class="px-4 py-2.5 rounded-lg text-slate-500 text-sm font-semibold">Batal</button>
                <button type="submit" class="px-5 py-2.5 rounded-lg bg-[#0B2A4A] hover:bg-[#0d3760] text-white text-sm font-semibold">Simpan Pengumuman</button>
            </div>
        </form>
    </div>
</div>
@endif

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('pengumumanEditor', (initialHtml) => ({
        quill: null,
        init(el, onChange) {
            this.quill = new Quill(el, {
                theme: 'snow',
                modules: {
                    toolbar: [
                        ['bold', 'italic', 'underline'],
                        [{ font: [] }, { size: [] }],
                        [{ list: 'ordered' }, { list: 'bullet' }],
                        ['link'],
                        ['clean'],
                    ]
                }
            });
            if (initialHtml) this.quill.root.innerHTML = initialHtml;

            // Debounce: jangan kirim ke server di setiap ketukan huruf,
            // cukup ~400ms setelah user berhenti mengetik.
            let timer = null;
            this.quill.on('text-change', () => {
                clearTimeout(timer);
                timer = setTimeout(() => onChange(this.quill.root.innerHTML), 400);
            });

            Livewire.on('konten-loaded', (e) => {
                this.quill.root.innerHTML = e.konten ?? '';
            });
        }
    }));
});
</script>

</div>
