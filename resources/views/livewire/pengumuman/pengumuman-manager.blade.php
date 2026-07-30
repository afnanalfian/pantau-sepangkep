<div>

<!-- ============================================ -->
<!-- HEADER & CREATE BUTTON -->
<!-- ============================================ -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-5 sm:mb-6">
    <p class="text-xs sm:text-sm text-slate-500">Semua pegawai dapat membuat, mengubah, dan menghapus pengumuman.</p>
    <button wire:click="bukaForm" 
            class="w-full sm:w-auto px-4 sm:px-5 py-2.5 rounded-lg bg-orange-600 hover:bg-orange-700 text-white text-sm font-semibold transition active:scale-95 shadow-sm shadow-orange-600/20">
        <span class="inline-flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Buat Pengumuman
        </span>
    </button>
</div>

<!-- ============================================ -->
<!-- ANNOUNCEMENT LIST - Mobile First -->
<!-- ============================================ -->

<!-- Mobile: Card View -->
<div class="sm:hidden space-y-3">
    @forelse($daftar as $p)
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
            <div class="flex items-start justify-between gap-2">
                <h3 class="font-display font-semibold text-slate-800 text-sm flex-1">{{ $p->judul }}</h3>
                <span class="text-[10px] text-slate-400 whitespace-nowrap">{{ $p->created_at->translatedFormat('d M Y') }}</span>
            </div>
            <p class="text-xs text-slate-500 mt-1">Oleh: {{ $p->dibuat_oleh }}</p>
            <p class="text-xs text-slate-400 mt-0.5">{{ $p->created_at->translatedFormat('H:i') }}</p>
            <div class="flex flex-wrap items-center gap-2 mt-3 pt-3 border-t border-slate-100">
                <a href="{{ route('pengumuman.detail', $p) }}" target="_blank" 
                   class="text-xs font-medium text-orange-600 hover:text-orange-700 transition">
                    Lihat
                </a>
                <span class="text-slate-300">|</span>
                <button wire:click="edit({{ $p->id }})" 
                        class="text-xs font-medium text-amber-600 hover:text-amber-700 transition">
                    Edit
                </button>
                <span class="text-slate-300">|</span>
                <button wire:click="hapus({{ $p->id }})" wire:confirm="Hapus pengumuman ini?" 
                        class="text-xs font-medium text-red-500 hover:text-red-600 transition">
                    Hapus
                </button>
            </div>
        </div>
    @empty
        <div class="text-center py-10 bg-white rounded-xl border border-dashed border-slate-300">
            <p class="text-sm text-slate-400">Belum ada pengumuman.</p>
        </div>
    @endforelse
</div>

<!-- Desktop: Table View -->
<div class="hidden sm:block bg-white rounded-xl sm:rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-xs uppercase text-slate-500 font-semibold border-b border-slate-200">
            <tr>
                <th class="px-4 py-3 text-left">Judul</th>
                <th class="px-4 py-3 text-left">Dibuat Oleh</th>
                <th class="px-4 py-3 text-left">Tanggal</th>
                <th class="px-4 py-3 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($daftar as $p)
                <tr class="hover:bg-slate-50 transition">
                    <td class="px-4 py-3 font-semibold text-slate-700">{{ $p->judul }}</td>
                    <td class="px-4 py-3 text-slate-500">{{ $p->dibuat_oleh }}</td>
                    <td class="px-4 py-3 text-slate-500">{{ $p->created_at->translatedFormat('d M Y, H:i') }}</td>
                    <td class="px-4 py-3 text-right space-x-3">
                        <a href="{{ route('pengumuman.detail', $p) }}" target="_blank" 
                           class="text-xs font-semibold text-orange-600 hover:text-orange-700 transition">
                            Lihat
                        </a>
                        <button wire:click="edit({{ $p->id }})" 
                                class="text-xs font-semibold text-amber-600 hover:text-amber-700 transition">
                            Edit
                        </button>
                        <button wire:click="hapus({{ $p->id }})" wire:confirm="Hapus pengumuman ini?" 
                                class="text-xs font-semibold text-red-500 hover:text-red-600 transition">
                            Hapus
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-4 py-10 text-center text-slate-400">Belum ada pengumuman.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- ============================================ -->
<!-- PAGINATION -->
<!-- ============================================ -->
@if($daftar->hasPages())
    <div class="mt-4 sm:mt-6">
        {{ $daftar->links() }}
    </div>
@endif

<!-- ============================================ -->
<!-- MODAL FORM -->
<!-- ============================================ -->
@if($showForm)
<div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm" 
     wire:key="modal-{{ $editingId }}" 
     x-data="{ show: true }" 
     x-show="show"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 scale-95"
     x-transition:enter-end="opacity-100 scale-100"
     @click.self="show = false; $wire.set('showForm', false)">
    
    <div class="bg-white rounded-xl sm:rounded-2xl w-full max-w-2xl shadow-2xl max-h-[90vh] overflow-y-auto">
        
        <!-- Modal Header -->
        <div class="sticky top-0 z-10 bg-white px-5 sm:px-6 py-4 border-b border-slate-200 flex items-center justify-between">
            <h3 class="font-display font-bold text-lg text-slate-900">
                {{ $editingId ? 'Edit Pengumuman' : 'Buat Pengumuman' }}
            </h3>
            <button wire:click="$set('showForm', false)" 
                    class="text-slate-400 hover:text-slate-600 text-2xl leading-none transition p-1">
                ×
            </button>
        </div>

        <!-- Modal Body -->
        <form wire:submit="simpan" class="p-5 sm:p-6 space-y-4" 
              x-data="pengumumanEditor(@js($konten))" 
              x-init="init($refs.editorEl, (html) => $wire.set('konten', html))">
            
            <!-- Judul -->
            <div>
                <label class="block text-xs sm:text-sm font-semibold text-slate-700 mb-1.5">Judul <span class="text-red-500">*</span></label>
                <input type="text" 
                       wire:model="judul" 
                       placeholder="Masukkan judul pengumuman..." 
                       class="w-full px-3 sm:px-4 py-2.5 rounded-lg border border-slate-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition text-sm">
                @error('judul') <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p> @enderror
            </div>

            <!-- Ringkasan -->
            <div>
                <label class="block text-xs sm:text-sm font-semibold text-slate-700 mb-1.5">Ringkasan</label>
                <input type="text" 
                       wire:model="ringkasan" 
                       placeholder="Ringkasan singkat untuk daftar pengumuman..." 
                       class="w-full px-3 sm:px-4 py-2.5 rounded-lg border border-slate-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition text-sm">
            </div>

            <!-- Konten Editor -->
            <div>
                <label class="block text-xs sm:text-sm font-semibold text-slate-700 mb-1.5">Isi Pengumuman <span class="text-red-500">*</span></label>
                <div wire:ignore class="border border-slate-300 rounded-lg overflow-hidden focus-within:border-orange-500 focus-within:ring-2 focus-within:ring-orange-500/20 transition">
                    <div x-ref="editorEl" class="bg-white" style="min-height:180px;"></div>
                </div>
            </div>

            <!-- Lampiran -->
            <div>
                <label class="block text-xs sm:text-sm font-semibold text-slate-700 mb-1.5">Lampiran</label>
                
                <!-- Lampiran Tersimpan -->
                @if(count($lampiranTersimpan) > 0)
                    <div class="space-y-2 mb-3">
                        @foreach($lampiranTersimpan as $i => $l)
                            <div class="flex items-center justify-between px-3 py-2 bg-slate-50 rounded-lg text-sm border border-slate-200">
                                <span class="flex items-center gap-2 text-xs sm:text-sm">
                                    <span class="text-[9px] sm:text-[10px] font-bold uppercase px-1.5 py-0.5 rounded bg-slate-200 text-slate-600">{{ $l['type'] }}</span>
                                    <span class="text-slate-700 truncate max-w-[150px] sm:max-w-xs">{{ $l['name'] }}</span>
                                </span>
                                <button type="button" wire:click="hapusLampiran({{ $i }})" 
                                        class="text-red-400 hover:text-red-600 text-xs font-semibold transition">
                                    Hapus
                                </button>
                            </div>
                        @endforeach
                    </div>
                @endif

                <!-- Upload Files -->
                <div class="grid sm:grid-cols-2 gap-3 mb-3">
                    <div>
                        <input type="file" 
                               wire:model="lampiranFile" 
                               class="w-full text-xs border border-slate-300 rounded-lg px-3 py-2 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-orange-50 file:text-orange-600 hover:file:bg-orange-100">
                        <p class="text-[10px] text-slate-400 mt-1">File (PDF, dokumen, dll)</p>
                    </div>
                    <div>
                        <input type="file" 
                               wire:model="lampiranGambar" 
                               accept="image/*" 
                               class="w-full text-xs border border-slate-300 rounded-lg px-3 py-2 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-orange-50 file:text-orange-600 hover:file:bg-orange-100">
                        <p class="text-[10px] text-slate-400 mt-1">Gambar</p>
                    </div>
                </div>
                
                <button type="button" wire:click="unggahFile" 
                        class="text-xs font-semibold text-orange-600 hover:text-orange-700 transition mb-3">
                    + Tambahkan file/gambar
                </button>

                <!-- Tambah Link -->
                <div class="flex flex-col sm:flex-row gap-2">
                    <input type="text" 
                           wire:model="linkBaruNama" 
                           placeholder="Nama link" 
                           class="w-full sm:w-1/3 px-3 py-2 rounded-lg border border-slate-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition text-sm">
                    <input type="url" 
                           wire:model="linkBaru" 
                           placeholder="https://..." 
                           class="flex-1 px-3 py-2 rounded-lg border border-slate-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition text-sm">
                    <button type="button" wire:click="tambahLink" 
                            class="w-full sm:w-auto px-3 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold transition">
                        + Tambah Link
                    </button>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex flex-col sm:flex-row justify-end gap-2 pt-3 border-t border-slate-200">
                <button type="button" wire:click="$set('showForm', false)" 
                        class="w-full sm:w-auto px-4 py-2.5 rounded-lg text-slate-500 text-sm font-semibold hover:bg-slate-50 transition">
                    Batal
                </button>
                <button type="submit" 
                        class="w-full sm:w-auto px-5 py-2.5 rounded-lg bg-orange-600 hover:bg-orange-700 text-white text-sm font-semibold transition active:scale-95 shadow-sm shadow-orange-600/20">
                    Simpan Pengumuman
                </button>
            </div>
        </form>
    </div>
</div>
@endif

<!-- ============================================ -->
<!-- QUILL EDITOR SCRIPT -->
<!-- ============================================ -->
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