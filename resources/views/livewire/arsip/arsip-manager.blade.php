<div>

<!-- ============================================ -->
<!-- HEADER & FILTERS -->
<!-- ============================================ -->
<div class="flex flex-col gap-3 mb-5 sm:mb-6">
    <!-- Row 1: Search & Add Button -->
    <div class="flex flex-col sm:flex-row gap-3">
        <div class="relative flex-1">
            <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" 
                   wire:model.live.debounce.400ms="search" 
                   placeholder="Cari judul berkas..." 
                   class="w-full pl-9 pr-4 py-2.5 rounded-lg border border-slate-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition text-sm">
        </div>
        @if($isAdmin)
            <button wire:click="bukaForm" 
                    class="w-full sm:w-auto px-4 sm:px-5 py-2.5 rounded-lg bg-orange-600 hover:bg-orange-700 text-white text-sm font-semibold transition active:scale-95 shadow-sm shadow-orange-600/20 inline-flex items-center justify-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Berkas
            </button>
        @endif
    </div>
    
    <!-- Row 2: Category Filter -->
    <div class="flex flex-wrap gap-2">
        <select wire:model.live="filterKategori" 
                class="px-3 py-2.5 rounded-lg border border-slate-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition text-sm bg-white flex-1 sm:flex-none">
            <option value="">Semua Kategori</option>
            @foreach($kategoriList as $k)
                <option value="{{ $k }}">{{ $k }}</option>
            @endforeach
        </select>
        @if($filterKategori)
            <button wire:click="$set('filterKategori', '')" 
                    class="px-3 py-2.5 rounded-lg text-sm text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">
                ✕ Hapus Filter
            </button>
        @endif
    </div>
</div>

<!-- ============================================ -->
<!-- MOBILE: CARD VIEW -->
<!-- ============================================ -->
<div class="sm:hidden space-y-3">
    @forelse($daftar as $a)
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
            <div class="flex items-start justify-between gap-2">
                <div class="flex-1 min-w-0">
                    <h3 class="font-semibold text-slate-800 text-sm">{{ $a->judul }}</h3>
                    @if($a->kategori)
                        <span class="inline-block text-[10px] font-medium px-2 py-0.5 rounded-full bg-orange-100 text-orange-700 mt-1">
                            {{ $a->kategori }}
                        </span>
                    @endif
                </div>
                <a href="{{ $a->file_path }}" target="_blank" 
                   class="px-3 py-1.5 rounded-lg bg-orange-600 text-white text-xs font-medium hover:bg-orange-700 transition flex-shrink-0">
                    Unduh
                </a>
            </div>
            @if($a->keterangan)
                <p class="text-xs text-slate-500 mt-2">{{ $a->keterangan }}</p>
            @endif
            <div class="flex items-center justify-between mt-3 pt-3 border-t border-slate-100">
                <span class="text-[10px] text-slate-400">{{ $a->created_at->translatedFormat('d M Y') }} · {{ $a->diunggah_oleh }}</span>
                @if($isAdmin)
                    <div class="flex items-center gap-2">
                        <button wire:click="edit({{ $a->id }})" 
                                class="text-xs font-medium text-amber-600 hover:text-amber-700 transition">
                            Edit
                        </button>
                        <span class="text-slate-300">|</span>
                        <button wire:click="hapus({{ $a->id }})" 
                                wire:confirm="Hapus berkas ini?" 
                                class="text-xs font-medium text-red-500 hover:text-red-600 transition">
                            Hapus
                        </button>
                    </div>
                @endif
            </div>
        </div>
    @empty
        <div class="text-center py-10 bg-white rounded-xl border border-dashed border-slate-300">
            <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
            </div>
            <p class="text-sm text-slate-400">Belum ada berkas.</p>
            <p class="text-xs text-slate-400 mt-1">Tambahkan berkas arsip di sini.</p>
        </div>
    @endforelse
</div>

<!-- ============================================ -->
<!-- DESKTOP: TABLE VIEW -->
<!-- ============================================ -->
<div class="hidden sm:block bg-white rounded-xl sm:rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-xs uppercase text-slate-500 font-semibold border-b border-slate-200">
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
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-4 py-3 font-semibold text-slate-700">{{ $a->judul }}</td>
                        <td class="px-4 py-3">
                            @if($a->kategori)
                                <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-orange-100 text-orange-700">
                                    {{ $a->kategori }}
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-slate-500 text-xs max-w-[200px] truncate">{{ $a->keterangan }}</td>
                        <td class="px-4 py-3 text-xs text-slate-400">
                            {{ $a->created_at->translatedFormat('d M Y') }}
                            <span class="block text-[10px]">{{ $a->diunggah_oleh }}</span>
                        </td>
                        <td class="px-4 py-3 text-right space-x-3 whitespace-nowrap">
                            <a href="{{ $a->file_path }}" target="_blank" 
                               class="text-xs font-semibold text-orange-600 hover:text-orange-700 transition">
                                Unduh
                            </a>
                            @if($isAdmin)
                                <span class="text-slate-300">|</span>
                                <button wire:click="edit({{ $a->id }})" 
                                        class="text-xs font-semibold text-amber-600 hover:text-amber-700 transition">
                                    Edit
                                </button>
                                <span class="text-slate-300">|</span>
                                <button wire:click="hapus({{ $a->id }})" 
                                        wire:confirm="Hapus berkas ini?" 
                                        class="text-xs font-semibold text-red-500 hover:text-red-600 transition">
                                    Hapus
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-10 text-center text-slate-400">Belum ada berkas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- ============================================ -->
<!-- PAGINATION -->
<!-- ============================================ -->
@if($daftar->hasPages())
    <div class="mt-4 sm:mt-5">
        {{ $daftar->links() }}
    </div>
@endif

<!-- ============================================ -->
<!-- MODAL FORM -->
<!-- ============================================ -->
@if($showForm)
<div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm" 
     x-data="{ show: true }" 
     x-show="show"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 scale-95"
     x-transition:enter-end="opacity-100 scale-100"
     @click.self="show = false; $wire.set('showForm', false)">
    
    <div class="bg-white rounded-xl sm:rounded-2xl w-full max-w-lg p-5 sm:p-6 shadow-2xl max-h-[90vh] overflow-y-auto">
        
        <!-- Modal Header -->
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-display font-bold text-lg text-slate-900">
                {{ $editingId ? 'Edit Berkas' : 'Tambah Berkas' }}
            </h3>
            <button wire:click="$set('showForm', false)" 
                    class="text-slate-400 hover:text-slate-600 text-2xl leading-none transition p-1">
                ×
            </button>
        </div>

        <!-- Form -->
        <form wire:submit="simpan" class="space-y-4">
            
            <!-- Judul -->
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1.5">Judul <span class="text-red-500">*</span></label>
                <input type="text" 
                       wire:model="judul" 
                       placeholder="Masukkan judul berkas..." 
                       class="w-full px-3 sm:px-4 py-2.5 rounded-lg border border-slate-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition text-sm">
                @error('judul') <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p> @enderror
            </div>

            <!-- Kategori -->
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1.5">Kategori</label>
                <input type="text" 
                       wire:model="kategori" 
                       placeholder="Surat, Undangan, Himbauan, dll" 
                       class="w-full px-3 sm:px-4 py-2.5 rounded-lg border border-slate-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition text-sm">
            </div>

            <!-- Keterangan -->
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1.5">Keterangan</label>
                <textarea wire:model="keterangan" 
                          rows="2" 
                          placeholder="Deskripsi singkat tentang berkas..." 
                          class="w-full px-3 sm:px-4 py-2.5 rounded-lg border border-slate-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition text-sm resize-y"></textarea>
            </div>

            <!-- File Upload -->
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1.5">
                    File Berkas 
                    @if(!$editingId) <span class="text-red-500">*</span> @endif
                </label>
                <input type="file" 
                       wire:model="file" 
                       class="w-full text-xs sm:text-sm border border-slate-300 rounded-lg px-3 py-2 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-orange-50 file:text-orange-600 hover:file:bg-orange-100">
                @if($editingId)
                    <p class="text-[10px] text-slate-400 mt-1">Kosongkan jika tidak ingin mengubah file</p>
                @endif
                @error('file') <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p> @enderror
            </div>

            <!-- Form Actions -->
            <div class="flex flex-col sm:flex-row justify-end gap-2 pt-3 border-t border-slate-200">
                <button type="button" wire:click="$set('showForm', false)" 
                        class="w-full sm:w-auto px-4 py-2 text-sm text-slate-500 font-semibold hover:bg-slate-50 rounded-lg transition">
                    Batal
                </button>
                <button type="submit" 
                        class="w-full sm:w-auto px-4 py-2 rounded-lg bg-orange-600 hover:bg-orange-700 text-white text-sm font-semibold transition active:scale-95 shadow-sm shadow-orange-600/20">
                    {{ $editingId ? 'Update' : 'Simpan' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endif

</div>