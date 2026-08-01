@php
    // Guard defensif: pastikan semua variable yang dipakai partial ini
    // selalu terdefinisi, apapun cara ia dipanggil (include biasa,
    // Livewire re-render, atau view cache lama).
    $showModal = $showModal ?? false;
    $selectedStatus = $selectedStatus ?? null;
    $modalAnomaliName = $modalAnomaliName ?? null;
    $statusOptions = $statusOptions ?? [];
    $kecamatanOptions = $kecamatanOptions ?? collect();
    $desaOptions = $desaOptions ?? collect();
    $petugasMap = $petugasMap ?? [];
@endphp
<div wire:key="anomali-mikro">

<!-- ============================================ -->
<!-- FILTERS -->
<!-- ============================================ -->
<div class="bg-white rounded-xl sm:rounded-2xl border border-slate-200 p-3 sm:p-4 mb-4 shadow-sm">
    <div class="flex flex-col gap-2">
        <!-- Row 1: Search & Export -->
        <div class="flex flex-col sm:flex-row gap-2">
            <div class="relative flex-1">
                <input type="text"
                       wire:model.live.debounce.400ms="search"
                       placeholder="Cari nama / assignment / SLS / nama PPL-PML..."
                       class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition text-sm">
            </div>
            <button wire:click="exportMikro"
                    class="w-full sm:w-auto px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold transition active:scale-95">
                Export Excel
            </button>
        </div>

        <!-- Row 2: Filters -->
        <div class="grid grid-cols-2 sm:flex sm:flex-wrap gap-2">
            <select wire:model.live="filterJenis"
                    class="px-3 py-2 rounded-lg border border-slate-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition text-sm">
                <option value="">Jenis</option>
                <option value="usaha">Usaha</option>
                <option value="keluarga">Keluarga</option>
            </select>
            <select wire:model.live="filterKecamatan"
                    class="px-3 py-2 rounded-lg border border-slate-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition text-sm">
                <option value="">Semua Kecamatan</option>
                @foreach($kecamatanOptions as $k)<option value="{{ $k }}">{{ $k }}</option>@endforeach
            </select>
            <select wire:model.live="filterDesa"
                    class="px-3 py-2 rounded-lg border border-slate-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition text-sm">
                <option value="">Semua Desa/Kel</option>
                @foreach($desaOptions as $k)<option value="{{ $k }}">{{ $k }}</option>@endforeach
            </select>
            <select wire:model.live="filterStatus"
                    class="px-3 py-2 rounded-lg border border-slate-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition text-sm">
                <option value="">Semua Status</option>
                <option value="belum">Belum Tindak Lanjut</option>
                <option value="sudah">Sudah Tindak Lanjut</option>
            </select>
            <select wire:model.live="perPage"
                    class="px-3 py-2 rounded-lg border border-slate-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition text-sm">
                <option value="10">10 / hal</option>
                <option value="20">20 / hal</option>
                <option value="50">50 / hal</option>
                <option value="100">100 / hal</option>
            </select>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- TABLE - Mobile Card View -->
<!-- ============================================ -->
<div class="sm:hidden space-y-3">
    @forelse($mikros as $m)
        @php $p = $petugasMap[$m->id] ?? null; @endphp
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
            <div class="flex items-start justify-between">
                <div class="flex-1 min-w-0">
                    <div class="font-semibold text-slate-800 text-sm {{ $m->nama ? '' : 'italic text-slate-400' }}">
                        {{ $m->nama_display }}
                    </div>
                    <div class="text-[10px] text-slate-400">{{ $m->nama_anomali }}</div>
                </div>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $m->tindak_lanjut === 'sudah' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-red-50 text-red-700 border border-red-200' }}">
                    {{ $m->tindak_lanjut === 'sudah' ? '✓ Selesai' : '⏳ Belum' }}
                </span>
            </div>
            <div class="grid grid-cols-2 gap-1 mt-2 text-xs text-slate-500">
                <span>{{ $m->nmkec }}</span>
                <span>{{ $m->nmdesa }}</span>
                <span class="font-mono">{{ $m->sls_label }}</span>
                <span class="font-mono text-[10px]">{{ $m->region_key ?? '-' }}</span>
            </div>
            <div class="mt-2 text-xs border-t border-slate-100 pt-2">
                @if($p)
                    <div class="text-slate-700"><span class="text-slate-400">PPL:</span> <span class="font-medium">{{ $p['nama_ppl'] ?: '-' }}</span></div>
                    <div class="text-slate-600"><span class="text-slate-400">PML:</span> {{ $p['nama_pml'] ?: '-' }}</div>
                    <div class="text-slate-600"><span class="text-slate-400">Organik:</span> {{ $p['pml_organik'] ?: '-' }}</div>
                @else
                    <span class="text-slate-300">Petugas tidak ditemukan untuk wilayah ini</span>
                @endif
            </div>
            <div class="flex items-center gap-2 mt-3 pt-2 border-t border-slate-100 flex-wrap">
                @if($m->fasih_link)
                    <a href="{{ $m->fasih_link }}" target="_blank"
                       class="text-xs font-medium text-orange-600 hover:text-orange-700 transition hover:underline">
                        🔗 {{ $m->fasih_link_short }}
                    </a>
                    <span class="text-slate-300">|</span>
                @endif
                @if($m->tindak_lanjut === 'sudah')
                    <span class="text-[10px] px-2 py-0.5 rounded-full {{ $m->status_color }} font-medium">
                        {{ $m->status_label }}
                    </span>
                    <button wire:click="batalkanTindakLanjut({{ $m->id }})"
                            class="text-xs font-medium text-amber-600 hover:text-amber-700 transition">
                        Batalkan
                    </button>
                @else
                    <button wire:click="bukaModalTindakLanjut({{ $m->id }})"
                            class="text-xs font-medium text-emerald-600 hover:text-emerald-700 transition">
                        Tandai Selesai
                    </button>
                @endif
            </div>
        </div>
    @empty
        <div class="text-center py-8 bg-white rounded-xl border border-dashed border-slate-300">
            <p class="text-sm text-slate-400">Tidak ada data yang cocok dengan filter.</p>
        </div>
    @endforelse

    <div class="pt-2">{{ $mikros->links() }}</div>
</div>

<!-- ============================================ -->
<!-- TABLE - Desktop View -->
<!-- ============================================ -->
<div class="hidden sm:block bg-white rounded-xl sm:rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-xs uppercase text-slate-500 font-semibold border-b border-slate-200">
                <tr>
                    <th class="px-3 py-3 text-left">No</th>
                    <th class="px-3 py-3 text-left">Nama KRT/Usaha</th>
                    <th class="px-3 py-3 text-left">Kecamatan</th>
                    <th class="px-3 py-3 text-left">Desa/Kel</th>
                    <th class="px-3 py-3 text-left">SLS</th>
                    <th class="px-3 py-3 text-left">PPL / PML</th>
                    <th class="px-3 py-3 text-left">Fasih</th>
                    <th class="px-3 py-3 text-center">Status</th>
                    <th class="px-3 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($mikros as $m)
                    @php $p = $petugasMap[$m->id] ?? null; @endphp
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-3 py-3 text-slate-500 text-center">{{ $m->no }}</td>
                        <td class="px-3 py-3">
                            <div class="font-semibold text-slate-700 {{ $m->nama ? '' : 'italic font-normal text-slate-400' }}">
                                {{ $m->nama_display }}
                            </div>
                            <div class="text-[10px] text-slate-400">{{ $m->nama_anomali }}</div>
                        </td>
                        <td class="px-3 py-3 text-slate-600">{{ $m->nmkec }}</td>
                        <td class="px-3 py-3 text-slate-600">{{ $m->nmdesa }}</td>
                        <td class="px-3 py-3 text-slate-500 font-mono text-xs">
                            {{ $m->sls_label }}
                            <div class="text-[10px] text-slate-300">{{ $m->region_key ?? '-' }}</div>
                        </td>
                        <td class="px-3 py-3 text-xs">
                            @if($p)
                                <div class="text-slate-700 font-medium">{{ $p['nama_ppl'] ?: '-' }}</div>
                                <div class="text-slate-400">{{ $p['nama_pml'] ?: '-' }} · {{ $p['pml_organik'] ?: '-' }}</div>
                            @else
                                <span class="text-slate-300">Tidak ditemukan</span>
                            @endif
                        </td>
                        <td class="px-3 py-3">
                            @if($m->fasih_link)
                                <a href="{{ $m->fasih_link }}" target="_blank"
                                   class="text-xs font-medium text-orange-600 hover:text-orange-700 transition hover:underline">
                                    {{ $m->fasih_link_short }}
                                </a>
                            @else
                                <span class="text-xs text-slate-300">-</span>
                            @endif
                        </td>
                        <td class="px-3 py-3 text-center">
                            @if($m->tindak_lanjut === 'sudah')
                                <span class="px-2 py-1 rounded-full text-xs font-bold {{ $m->status_color }}">
                                    {{ $m->status_label }}
                                </span>
                            @else
                                <span class="px-2 py-1 rounded-full text-xs font-bold bg-red-50 text-red-700 border border-red-200">
                                    Belum
                                </span>
                            @endif
                        </td>
                        <td class="px-3 py-3 text-right">
                            @if($m->tindak_lanjut === 'sudah')
                                <button wire:click="batalkanTindakLanjut({{ $m->id }})"
                                        class="text-xs font-medium text-amber-600 hover:text-amber-700 transition">
                                    Batalkan
                                </button>
                            @else
                                <button wire:click="bukaModalTindakLanjut({{ $m->id }})"
                                        class="text-xs font-medium text-emerald-600 hover:text-emerald-700 transition">
                                    Tandai Selesai
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-4 py-10 text-center text-slate-400">Tidak ada data yang cocok dengan filter.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-4 py-3 border-t border-slate-100 bg-slate-50">
        {{ $mikros->links() }}
    </div>
</div>

<!-- ============================================ -->
<!-- MODAL PILIH STATUS PENYELESAIAN -->
<!-- ============================================ -->
@if($showModal)
<div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 overflow-hidden">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-slate-200 bg-gradient-to-r from-orange-50 to-amber-50">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-slate-800">Pilih Status Penyelesaian</h3>
                <button wire:click="tutupModal" class="text-slate-400 hover:text-slate-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <p class="text-sm text-slate-500 mt-1">
                Menandai anomali: <span class="font-semibold text-slate-700">{{ $modalAnomaliName ?? '-' }}</span>
            </p>
        </div>

        <!-- Body -->
        <div class="px-6 py-4">
            <div class="space-y-3">
                @foreach($statusOptions as $value => $label)
                    @php
                        $colors = [
                            'revoked_pml' => 'border-blue-200 bg-blue-50 hover:bg-blue-100 text-blue-700',
                            'diselesaikan_admin' => 'border-emerald-200 bg-emerald-50 hover:bg-emerald-100 text-emerald-700',
                            'reject_admin' => 'border-red-200 bg-red-50 hover:bg-red-100 text-red-700',
                        ];
                        $badgeColors = [
                            'revoked_pml' => 'bg-blue-100 text-blue-700',
                            'diselesaikan_admin' => 'bg-emerald-100 text-emerald-700',
                            'reject_admin' => 'bg-red-100 text-red-700',
                        ];
                        $descriptions = [
                            'revoked_pml' => 'PML mencabut/membatalkan anomali',
                            'diselesaikan_admin' => 'Admin telah menyelesaikan anomali',
                            'reject_admin' => 'Admin menolak anomali',
                        ];
                    @endphp
                    <label class="flex items-start gap-3 p-3 rounded-lg border-2 transition cursor-pointer
                        {{ $selectedStatus === $value ? $colors[$value] . ' border-current' : 'border-slate-200 hover:border-slate-300' }}">
                        <input type="radio"
                               wire:model.live="selectedStatus"
                               value="{{ $value }}"
                               class="mt-1 w-4 h-4 accent-orange-600 cursor-pointer">
                        <div class="flex-1">
                            <span class="font-semibold text-sm block">{{ $label }}</span>
                            <span class="text-xs text-slate-500">{{ $descriptions[$value] ?? '' }}</span>
                        </div>
                        <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $badgeColors[$value] }}">
                            {{ $label }}
                        </span>
                    </label>
                @endforeach
            </div>

            @if($errors->has('selectedStatus'))
                <p class="mt-2 text-sm text-red-600">{{ $errors->first('selectedStatus') }}</p>
            @endif
        </div>

        <!-- Footer -->
        <div class="px-6 py-4 border-t border-slate-200 bg-slate-50 flex gap-2 justify-end">
            <button wire:click="tutupModal"
                    class="px-4 py-2 rounded-lg border border-slate-300 hover:bg-slate-100 text-slate-600 text-sm font-medium transition active:scale-95">
                Batal
            </button>
            <button wire:click="prosesTandaiSelesai"
                    wire:loading.attr="disabled"
                    class="px-5 py-2 rounded-lg bg-orange-600 hover:bg-orange-700 text-white text-sm font-semibold transition active:scale-95 flex items-center gap-2 disabled:opacity-50">
                <span wire:loading.remove>Konfirmasi</span>
                <span wire:loading>
                    <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                    </svg>
                </span>
            </button>
        </div>
    </div>
</div>
@endif

<!-- ============================================ -->
<!-- SESSION FLASH MESSAGES -->
<!-- ============================================ -->
@if(session()->has('success'))
    <div class="fixed top-4 right-4 z-50 max-w-sm bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg shadow-lg">
        {{ session('success') }}
    </div>
@endif
@if(session()->has('info'))
    <div class="fixed top-4 right-4 z-50 max-w-sm bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded-lg shadow-lg">
        {{ session('info') }}
    </div>
@endif
@if(session()->has('error'))
    <div class="fixed top-4 right-4 z-50 max-w-sm bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg shadow-lg">
        {{ session('error') }}
    </div>
@endif

</div>
