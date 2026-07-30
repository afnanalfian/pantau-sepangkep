<div>

<!-- ============================================ -->
<!-- STATS CARDS -->
<!-- ============================================ -->
<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 mb-5 sm:mb-6">
    <div class="bg-white rounded-xl sm:rounded-2xl p-4 sm:p-5 border border-slate-200 shadow-sm">
        <p class="text-[10px] sm:text-xs font-semibold text-slate-400 uppercase tracking-wide">Total Gate</p>
        <p class="font-display font-bold text-2xl sm:text-3xl text-slate-900 mt-1">{{ $stats['totalGate'] }}</p>
    </div>
    <div class="bg-white rounded-xl sm:rounded-2xl p-4 sm:p-5 border border-slate-200 shadow-sm">
        <p class="text-[10px] sm:text-xs font-semibold text-slate-400 uppercase tracking-wide">Total UK</p>
        <p class="font-display font-bold text-2xl sm:text-3xl text-orange-600 mt-1">{{ $stats['totalUk'] }}</p>
    </div>
    <div class="bg-white rounded-xl sm:rounded-2xl p-4 sm:p-5 border border-slate-200 shadow-sm">
        <p class="text-[10px] sm:text-xs font-semibold text-slate-400 uppercase tracking-wide">Aksi Selesai</p>
        <p class="font-display font-bold text-2xl sm:text-3xl text-emerald-600 mt-1">{{ $stats['selesai'] }} / {{ $stats['totalAksi'] }}</p>
    </div>
    <div class="bg-white rounded-xl sm:rounded-2xl p-4 sm:p-5 border border-slate-200 shadow-sm">
        <p class="text-[10px] sm:text-xs font-semibold text-slate-400 uppercase tracking-wide">Anomali Berjalan</p>
        @if($anomaliBerjalan)
            <p class="font-display font-bold text-base sm:text-lg text-amber-600 mt-1">{{ $anomaliBerjalan->tanggal->translatedFormat('d M Y') }}</p>
            <p class="text-[10px] sm:text-xs text-slate-400">{{ $anomaliBerjalan->persenSelesai() }}% selesai</p>
        @else
            <p class="text-xs text-slate-400 mt-1">Belum ada batch</p>
        @endif
    </div>
</div>

<!-- ============================================ -->
<!-- HEADER & ADD BUTTON -->
<!-- ============================================ -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4 sm:mb-5">
    <div>
        <h2 class="font-display font-semibold text-lg sm:text-xl text-slate-800">Daftar Quality Gates</h2>
        <p class="text-xs text-slate-400 mt-0.5">Kelola Gate, UK, dan Aksi Preventif</p>
    </div>
    @if($isQg)
        <button wire:click="bukaFormGate" 
                class="w-full sm:w-auto px-4 sm:px-5 py-2.5 rounded-lg bg-orange-600 hover:bg-orange-700 text-white text-sm font-semibold transition active:scale-95 shadow-sm shadow-orange-600/20 inline-flex items-center justify-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Gate
        </button>
    @endif
</div>

<!-- ============================================ -->
<!-- GATES LIST -->
<!-- ============================================ -->
<div class="space-y-3 sm:space-y-4">
    @forelse($gates as $gate)
        <div class="bg-white rounded-xl sm:rounded-2xl border border-slate-200 overflow-hidden shadow-sm hover:shadow-md transition">
            
            <!-- Gate Header -->
            <div class="flex flex-wrap items-center justify-between gap-2 px-4 sm:px-5 py-3 sm:py-4 cursor-pointer hover:bg-slate-50 transition" 
                 wire:click="toggleGate({{ $gate->id }})">
                <div class="flex items-center gap-2 sm:gap-3 min-w-0">
                    <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-lg bg-indigo-100 flex items-center justify-center text-indigo-600 flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 sm:w-5 h-4 sm:h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <span class="font-display font-semibold text-slate-800 text-sm sm:text-base">{{ $gate->nama }}</span>
                        <span class="text-xs text-slate-400 ml-2">{{ $gate->uks->count() }} UK</span>
                    </div>
                </div>
                <div class="flex items-center gap-2 sm:gap-3" wire:click.stop>
                    <span class="text-xs text-slate-400 hidden sm:inline">
                        {{ $expandedGate === $gate->id ? '▲' : '▼' }}
                    </span>
                    @if($isQg)
                        <button wire:click="bukaFormGate({{ $gate->id }})" 
                                class="text-xs font-medium text-amber-600 hover:text-amber-700 transition">
                            Edit
                        </button>
                        <button wire:click="hapusGate({{ $gate->id }})" 
                                wire:confirm="Hapus gate ini beserta seluruh UK dan aksi preventifnya?" 
                                class="text-xs font-medium text-red-500 hover:text-red-600 transition">
                            Hapus
                        </button>
                    @endif
                </div>
            </div>

            <!-- Gate Content (Expanded) -->
            @if($expandedGate === $gate->id)
                <div class="border-t border-slate-100 p-3 sm:p-5 bg-slate-50/50 space-y-3 sm:space-y-4">
                    
                    @if($isQg)
                        <button wire:click="bukaFormUk({{ $gate->id }})" 
                                class="text-xs font-medium text-orange-600 hover:text-orange-700 transition inline-flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Tambah UK
                        </button>
                    @endif

                    <!-- UK List -->
                    @foreach($gate->uks as $uk)
                        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
                            
                            <!-- UK Header -->
                            <div class="flex flex-wrap items-center justify-between gap-2 px-3 sm:px-4 py-2.5 sm:py-3 cursor-pointer hover:bg-slate-50 transition" 
                                 wire:click="toggleUk({{ $uk->id }})">
                                <div class="flex items-center gap-2 min-w-0">
                                    <span class="text-sm font-semibold text-slate-700">{{ $uk->nama }}</span>
                                    <span class="text-[10px] text-slate-400">{{ $uk->aksiPreventifs->count() }} aksi</span>
                                </div>
                                <div class="flex items-center gap-2 sm:gap-3" wire:click.stop>
                                    @if($isQg)
                                        <button wire:click="bukaFormUk({{ $gate->id }}, {{ $uk->id }})" 
                                                class="text-xs font-medium text-amber-600 hover:text-amber-700 transition">
                                            Edit
                                        </button>
                                        <button wire:click="hapusUk({{ $uk->id }})" 
                                                wire:confirm="Hapus UK ini?" 
                                                class="text-xs font-medium text-red-500 hover:text-red-600 transition">
                                            Hapus
                                        </button>
                                    @endif
                                </div>
                            </div>

                            <!-- UK Content (Expanded) -->
                            @if($expandedUk === $uk->id)
                                <div class="border-t border-slate-100 p-3 sm:p-4 space-y-3 bg-slate-50/30">
                                    
                                    @if($isQg)
                                        <button wire:click="bukaFormAksi({{ $uk->id }})" 
                                                class="text-xs font-medium text-orange-600 hover:text-orange-700 transition inline-flex items-center gap-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                            </svg>
                                            Tambah Aksi Preventif
                                        </button>
                                    @endif

                                    <!-- Aksi List -->
                                    @forelse($uk->aksiPreventifs as $aksi)
                                        <div class="border border-slate-200 rounded-lg p-3 sm:p-4 bg-white hover:border-orange-200 transition">
                                            
                                            <!-- Aksi Header -->
                                            <div class="flex flex-wrap items-start justify-between gap-2">
                                                <div class="flex-1 min-w-0">
                                                    <div class="flex items-center gap-2 flex-wrap">
                                                        <span class="text-[10px] font-bold text-slate-400">Aksi #{{ $aksi->urutan }}</span>
                                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $aksi->isSelesai() ? 'bg-emerald-100 text-emerald-700 border border-emerald-200' : 'bg-red-100 text-red-700 border border-red-200' }}">
                                                            {{ $aksi->isSelesai() ? '✓ Selesai' : '⏳ Belum' }}
                                                        </span>
                                                    </div>
                                                    <p class="text-sm text-slate-700 mt-1">{{ $aksi->deskripsi }}</p>
                                                    @if(!empty($aksi->pelaksana))
                                                        <p class="text-xs text-slate-400 mt-1">Pelaksana: {{ implode(', ', $aksi->pelaksana) }}</p>
                                                    @endif
                                                </div>
                                                @if($isQg)
                                                    <div class="flex items-center gap-2 flex-shrink-0">
                                                        <button wire:click="bukaFormAksi({{ $uk->id }}, {{ $aksi->id }})" 
                                                                class="text-xs font-medium text-amber-600 hover:text-amber-700 transition">
                                                            Edit
                                                        </button>
                                                        <button wire:click="hapusAksi({{ $aksi->id }})" 
                                                                wire:confirm="Hapus aksi preventif ini?" 
                                                                class="text-xs font-medium text-red-500 hover:text-red-600 transition">
                                                            Hapus
                                                        </button>
                                                    </div>
                                                @endif
                                            </div>

                                            <!-- Aksi Details Grid -->
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mt-3 pt-3 border-t border-slate-100 text-xs">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-slate-400 w-20 shrink-0">Template:</span>
                                                    @if($aksi->template_path)
                                                        <a href="{{ $aksi->template_path }}" target="_blank" 
                                                           class="font-medium text-orange-600 hover:text-orange-700 transition">
                                                            Unduh Template
                                                        </a>
                                                    @else
                                                        <span class="text-slate-300">-</span>
                                                    @endif
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <span class="text-slate-400 w-20 shrink-0">Bukti Dukung:</span>
                                                    @if($aksi->link_bukti_dukung)
                                                        <a href="{{ $aksi->link_bukti_dukung }}" target="_blank" 
                                                           class="font-medium text-orange-600 hover:text-orange-700 transition">
                                                            Buka Link
                                                        </a>
                                                    @else
                                                        <span class="text-slate-300">-</span>
                                                    @endif
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <span class="text-slate-400 w-20 shrink-0">Laporan:</span>
                                                    @if($aksi->laporan_path)
                                                        <a href="{{ $aksi->laporan_path }}" target="_blank" 
                                                           class="font-medium text-emerald-600 hover:text-emerald-700 transition">
                                                            Unduh Laporan
                                                        </a>
                                                    @else
                                                        <span class="text-slate-300">-</span>
                                                    @endif
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <span class="text-slate-400 w-20 shrink-0">Ceklis Bukti:</span>
                                                    <button wire:click="toggleChecklist({{ $aksi->id }})"
                                                            class="w-5 h-5 rounded border-2 flex items-center justify-center transition 
                                                                   {{ $aksi->bukti_dukung_checklist ? 'bg-emerald-500 border-emerald-500' : 'border-slate-300 hover:border-orange-400' }}">
                                                        @if($aksi->bukti_dukung_checklist)
                                                            <span class="text-white text-[10px]">✓</span>
                                                        @endif
                                                    </button>
                                                </div>
                                            </div>

                                            <!-- Upload Laporan -->
                                            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 mt-3 pt-3 border-t border-slate-100">
                                                <input type="file" 
                                                       wire:model="laporanUpload.{{ $aksi->id }}" 
                                                       class="flex-1 text-xs border border-slate-300 rounded-lg px-3 py-1.5 file:mr-2 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-orange-50 file:text-orange-600 hover:file:bg-orange-100">
                                                <button wire:click="unggahLaporan({{ $aksi->id }})" 
                                                        class="w-full sm:w-auto px-3 py-1.5 rounded-lg bg-orange-600 hover:bg-orange-700 text-white text-xs font-medium transition active:scale-95">
                                                    Unggah Laporan
                                                </button>
                                            </div>
                                        </div>
                                    @empty
                                        <p class="text-xs text-slate-400 text-center py-4">Belum ada aksi preventif untuk UK ini.</p>
                                    @endforelse
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @empty
        <div class="text-center py-10 sm:py-16 bg-white rounded-xl sm:rounded-2xl border border-dashed border-slate-300">
            <div class="w-12 sm:w-16 h-12 sm:h-16 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 sm:w-8 h-6 sm:h-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
            <p class="text-sm text-slate-400 font-medium">Belum ada Gate yang dibuat.</p>
            <p class="text-xs text-slate-400 mt-1">Mulai dengan membuat Gate untuk mengelola Quality Gates.</p>
        </div>
    @endforelse
</div>

<!-- ============================================ -->
<!-- MODAL: GATE -->
<!-- ============================================ -->
@if($showGateForm)
<div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm" 
     x-data="{ show: true }" 
     x-show="show"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 scale-95"
     x-transition:enter-end="opacity-100 scale-100"
     @click.self="show = false; $wire.set('showGateForm', false)">
    
    <div class="bg-white rounded-xl sm:rounded-2xl w-full max-w-md p-5 sm:p-6 shadow-2xl">
        <h3 class="font-display font-bold text-lg text-slate-900 mb-4">{{ $editingGateId ? 'Edit Gate' : 'Tambah Gate' }}</h3>
        <form wire:submit="simpanGate" class="space-y-4">
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1.5">Nama Gate <span class="text-red-500">*</span></label>
                <input type="text" 
                       wire:model="gateNama" 
                       placeholder="Contoh: Gate Kualitas Data" 
                       class="w-full px-3 sm:px-4 py-2.5 rounded-lg border border-slate-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition text-sm">
                @error('gateNama') <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p> @enderror
            </div>
            <div class="flex flex-col sm:flex-row justify-end gap-2 pt-2">
                <button type="button" wire:click="$set('showGateForm', false)" 
                        class="w-full sm:w-auto px-4 py-2 text-sm text-slate-500 font-semibold hover:bg-slate-50 rounded-lg transition">
                    Batal
                </button>
                <button type="submit" 
                        class="w-full sm:w-auto px-4 py-2 rounded-lg bg-orange-600 hover:bg-orange-700 text-white text-sm font-semibold transition active:scale-95">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>
@endif

<!-- ============================================ -->
<!-- MODAL: UK -->
<!-- ============================================ -->
@if($showUkForm)
<div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm" 
     x-data="{ show: true }" 
     x-show="show"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 scale-95"
     x-transition:enter-end="opacity-100 scale-100"
     @click.self="show = false; $wire.set('showUkForm', false)">
    
    <div class="bg-white rounded-xl sm:rounded-2xl w-full max-w-md p-5 sm:p-6 shadow-2xl">
        <h3 class="font-display font-bold text-lg text-slate-900 mb-4">{{ $editingUkId ? 'Edit UK' : 'Tambah UK' }}</h3>
        <form wire:submit="simpanUk" class="space-y-4">
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1.5">Nama UK <span class="text-red-500">*</span></label>
                <input type="text" 
                       wire:model="ukNama" 
                       placeholder="Contoh: Ketepatan Waktu" 
                       class="w-full px-3 sm:px-4 py-2.5 rounded-lg border border-slate-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition text-sm">
                @error('ukNama') <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p> @enderror
            </div>
            <div class="flex flex-col sm:flex-row justify-end gap-2 pt-2">
                <button type="button" wire:click="$set('showUkForm', false)" 
                        class="w-full sm:w-auto px-4 py-2 text-sm text-slate-500 font-semibold hover:bg-slate-50 rounded-lg transition">
                    Batal
                </button>
                <button type="submit" 
                        class="w-full sm:w-auto px-4 py-2 rounded-lg bg-orange-600 hover:bg-orange-700 text-white text-sm font-semibold transition active:scale-95">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>
@endif

<!-- ============================================ -->
<!-- MODAL: AKSI PREVENTIF -->
<!-- ============================================ -->
@if($showAksiForm)
<div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm" 
     x-data="{ show: true }" 
     x-show="show"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 scale-95"
     x-transition:enter-end="opacity-100 scale-100"
     @click.self="show = false; $wire.set('showAksiForm', false)">
    
    <div class="bg-white rounded-xl sm:rounded-2xl w-full max-w-lg p-5 sm:p-6 shadow-2xl max-h-[90vh] overflow-y-auto">
        <h3 class="font-display font-bold text-lg text-slate-900 mb-4">{{ $editingAksiId ? 'Edit Aksi Preventif' : 'Tambah Aksi Preventif' }}</h3>
        <form wire:submit="simpanAksi" class="space-y-4">
            
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1.5">Deskripsi <span class="text-red-500">*</span></label>
                <textarea wire:model="aksiDeskripsi" 
                          rows="3" 
                          placeholder="Deskripsi aksi preventif..." 
                          class="w-full px-3 sm:px-4 py-2.5 rounded-lg border border-slate-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition text-sm resize-y"></textarea>
                @error('aksiDeskripsi') <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p> @enderror
            </div>
            
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1.5">Pelaksana</label>
                <input type="text" 
                       wire:model="aksiPelaksanaText" 
                       placeholder="Nama 1, Nama 2, ..." 
                       class="w-full px-3 sm:px-4 py-2.5 rounded-lg border border-slate-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition text-sm">
                <p class="text-[10px] text-slate-400 mt-1">Pisahkan dengan koma untuk lebih dari satu orang</p>
            </div>
            
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1.5">Link Bukti Dukung</label>
                <input type="url" 
                       wire:model="aksiLinkBukti" 
                       placeholder="https://..." 
                       class="w-full px-3 sm:px-4 py-2.5 rounded-lg border border-slate-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition text-sm">
                @error('aksiLinkBukti') <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p> @enderror
            </div>
            
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1.5">Template Laporan</label>
                <input type="file" 
                       wire:model="aksiTemplateFile" 
                       class="w-full text-xs sm:text-sm border border-slate-300 rounded-lg px-3 py-2 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-orange-50 file:text-orange-600 hover:file:bg-orange-100">
            </div>
            
            <div class="flex flex-col sm:flex-row justify-end gap-2 pt-3 border-t border-slate-200">
                <button type="button" wire:click="$set('showAksiForm', false)" 
                        class="w-full sm:w-auto px-4 py-2 text-sm text-slate-500 font-semibold hover:bg-slate-50 rounded-lg transition">
                    Batal
                </button>
                <button type="submit" 
                        class="w-full sm:w-auto px-4 py-2 rounded-lg bg-orange-600 hover:bg-orange-700 text-white text-sm font-semibold transition active:scale-95">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>
@endif

</div>