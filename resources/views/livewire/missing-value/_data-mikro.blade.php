@php
    // Guard defensif supaya partial ini tidak pernah "Undefined variable"
    $showModal = $showModal ?? false;
    $selectedStatus = $selectedStatus ?? null;
    $modalNama = $modalNama ?? null;
    $statusOptions = $statusOptions ?? [];
    $kecamatanOptions = $kecamatanOptions ?? collect();
    $desaOptions = $desaOptions ?? collect();
    $variabelOptions = $variabelOptions ?? collect();
    $statusPerbaikanOptions = $statusPerbaikanOptions ?? collect();
    $petugasMap = $petugasMap ?? [];

    $selectCls = 'px-3 py-2 rounded-lg border border-slate-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition text-sm min-w-0';
@endphp
<div wire:key="missing-value-mikro">

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
                       placeholder="Cari nama / NIK / assignment / SLS / variabel / catatan / nama PPL-PML..."
                       class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition text-sm">
            </div>
            <button wire:click="exportMikro"
                    wire:loading.attr="disabled" wire:target="exportMikro"
                    class="w-full sm:w-auto px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold transition active:scale-95 disabled:opacity-50 inline-flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                <span wire:loading.remove wire:target="exportMikro">Export Excel</span>
                <span wire:loading wire:target="exportMikro">Menyiapkan...</span>
            </button>
        </div>

        <!-- Row 2: Filters -->
        <div class="grid grid-cols-2 sm:flex sm:flex-wrap gap-2">
            <select wire:model.live="filterVariabel" class="{{ $selectCls }}">
                <option value="">Semua Variabel</option>
                @foreach($variabelOptions as $v)<option value="{{ $v }}">{{ $v }}</option>@endforeach
            </select>
            <select wire:model.live="filterKecamatan" class="{{ $selectCls }}">
                <option value="">Semua Kecamatan</option>
                @foreach($kecamatanOptions as $k)<option value="{{ $k }}">{{ $k }}</option>@endforeach
            </select>
            <select wire:model.live="filterDesa" class="{{ $selectCls }}">
                <option value="">Semua Desa/Kel</option>
                @foreach($desaOptions as $k)<option value="{{ $k }}">{{ $k }}</option>@endforeach
            </select>
            <select wire:model.live="filterStatus" class="{{ $selectCls }}">
                <option value="">Semua Status</option>
                <option value="belum">Belum Ditangani</option>
                <option value="sudah">Sudah Ditangani</option>
            </select>
            @if($statusPerbaikanOptions->isNotEmpty())
                <select wire:model.live="filterStatusPerbaikan" class="{{ $selectCls }}">
                    <option value="">Status Perbaikan (Excel)</option>
                    @foreach($statusPerbaikanOptions as $k)<option value="{{ $k }}">{{ $k }}</option>@endforeach
                </select>
            @endif
            <select wire:model.live="perPage" class="{{ $selectCls }}">
                <option value="10">10 / hal</option>
                <option value="20">20 / hal</option>
                <option value="50">50 / hal</option>
                <option value="100">100 / hal</option>
            </select>
        </div>
        <p class="text-[11px] text-slate-400">Export Excel mengikuti filter &amp; pencarian yang sedang aktif.</p>
    </div>
</div>

<!-- ============================================ -->
<!-- TABLE - Mobile Card View -->
<!-- ============================================ -->
<div class="sm:hidden space-y-3">
    @forelse($mikros as $m)
        @php $p = $petugasMap[$m->id] ?? null; @endphp
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm" wire:key="mv-card-{{ $m->id }}">
            <div class="flex items-start justify-between gap-2">
                <div class="flex-1 min-w-0">
                    <div class="font-semibold text-slate-800 text-sm {{ $m->nama ? '' : 'italic text-slate-400' }}">
                        {{ $m->nama_display }}
                    </div>
                    <div class="flex flex-wrap gap-1 mt-1">
                        @forelse($m->variabel_list as $v)
                            <span class="text-[10px] px-1.5 py-0.5 rounded bg-purple-50 text-purple-700 border border-purple-100">{{ $v }}</span>
                        @empty
                            <span class="text-[10px] text-slate-300">-</span>
                        @endforelse
                    </div>
                </div>
                <span class="shrink-0 text-[10px] font-bold px-2 py-0.5 rounded-full {{ $m->tindak_lanjut === 'sudah' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-red-50 text-red-700 border border-red-200' }}">
                    {{ $m->tindak_lanjut === 'sudah' ? '✓ Selesai' : '⏳ Belum' }}
                </span>
            </div>

            <div class="grid grid-cols-2 gap-1 mt-2 text-xs text-slate-500">
                <span>{{ $m->nmkec }}</span>
                <span>{{ $m->nmdesa }}</span>
                <span class="font-mono">{{ $m->sls_label }}</span>
                <span class="font-mono text-[10px]">{{ $m->region_key ?? '-' }}</span>
            </div>

            <div class="mt-2 text-xs border-t border-slate-100 pt-2 grid grid-cols-2 gap-x-3 gap-y-0.5">
                <div class="text-slate-400">SE</div>
                <div class="text-slate-400">DTSEN · skor {{ $m->skor_label }}</div>
                <div class="text-slate-600">{{ $m->jk_se ?? '-' }} · {{ $m->tgl_lahir_se_label }}</div>
                <div class="text-slate-600">
                    <div class="truncate">{{ $m->nama_dtsen ?? '-' }}</div>
                    <div class="font-mono text-[10px]">{{ $m->nik_dtsen ?? '-' }}</div>
                    <div>{{ $m->jk_dtsen ?? '-' }} · {{ $m->tgl_lahir_dtsen ?? '-' }}</div>
                </div>
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

            @if($m->tindak_lanjut === 'sudah' && $m->catatan)
                <div class="mt-2 text-xs bg-amber-50 border border-amber-100 rounded-lg px-2.5 py-1.5 text-amber-800 whitespace-pre-line">📝 {{ $m->catatan }}</div>
            @endif

            <div class="flex items-center gap-2 mt-3 pt-2 border-t border-slate-100 flex-wrap">
                @if($m->fasih_link)
                    <a href="{{ $m->fasih_link }}" target="_blank" rel="noopener"
                       class="text-xs font-medium text-orange-600 hover:text-orange-700 transition hover:underline">
                        ✏️ Edit di FASIH
                    </a>
                    <span class="text-slate-300">|</span>
                @endif
                @if($m->tindak_lanjut === 'sudah')
                    <span class="text-[10px] px-2 py-0.5 rounded-full {{ $m->status_color }} font-medium">{{ $m->status_label }}</span>
                    <button wire:click="batalkanTindakLanjut({{ $m->id }})"
                            wire:confirm="Batalkan tindak lanjut? Metode dan catatan akan dihapus."
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
                    <th class="px-3 py-3 text-left">Nama Anggota Keluarga</th>
                    <th class="px-3 py-3 text-left">Wilayah</th>
                    <th class="px-3 py-3 text-left">Variabel Missing</th>
                    <th class="px-3 py-3 text-left">Data SE</th>
                    <th class="px-3 py-3 text-left">Matched DTSEN</th>
                    <th class="px-3 py-3 text-left">PPL / PML</th>
                    <th class="px-3 py-3 text-left">Fasih</th>
                    <th class="px-3 py-3 text-left">Status &amp; Catatan</th>
                    <th class="px-3 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($mikros as $m)
                    @php $p = $petugasMap[$m->id] ?? null; @endphp
                    <tr class="hover:bg-slate-50 transition align-top" wire:key="mv-row-{{ $m->id }}">
                        <td class="px-3 py-3 text-slate-500 text-center">{{ $m->no }}</td>
                        <td class="px-3 py-3 min-w-[160px]">
                            <div class="font-semibold text-slate-700 {{ $m->nama ? '' : 'italic font-normal text-slate-400' }}">
                                {{ $m->nama_display }}
                            </div>
                            <div class="text-[10px] text-slate-400 font-mono">{{ $m->assignment_id }}</div>
                            @if($m->status_perbaikan)
                                <div class="text-[10px] text-slate-500 mt-0.5">Excel: {{ $m->status_perbaikan }}</div>
                            @endif
                        </td>
                        <td class="px-3 py-3 text-xs text-slate-600 min-w-[140px]">
                            <div>{{ $m->nmkec }}</div>
                            <div>{{ $m->nmdesa }}</div>
                            <div class="font-mono text-slate-400">{{ $m->sls_label }}</div>
                            <div class="font-mono text-[10px] text-slate-300">{{ $m->region_key ?? '-' }}</div>
                        </td>
                        <td class="px-3 py-3 max-w-[200px]">
                            <div class="flex flex-wrap gap-1">
                                @forelse($m->variabel_list as $v)
                                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-purple-50 text-purple-700 border border-purple-100">{{ $v }}</span>
                                @empty
                                    <span class="text-xs text-slate-300">-</span>
                                @endforelse
                            </div>
                        </td>
                        <td class="px-3 py-3 text-xs text-slate-600 whitespace-nowrap">
                            <div>JK: {{ $m->jk_se ?? '-' }}</div>
                            <div>Lahir: {{ $m->tgl_lahir_se_label }}</div>
                        </td>
                        <td class="px-3 py-3 text-xs text-slate-600 min-w-[160px]">
                            <div class="font-medium text-slate-700">{{ $m->nama_dtsen ?? '-' }}</div>
                            <div class="font-mono text-[10px] text-slate-500">{{ $m->nik_dtsen ?? '-' }}</div>
                            <div>{{ $m->jk_dtsen ?? '-' }} · {{ $m->tgl_lahir_dtsen ?? '-' }}</div>
                            @php $sn = $m->skor_normal; @endphp
                            <span class="inline-block mt-0.5 text-[10px] font-bold px-1.5 py-0.5 rounded
                                {{ $sn === null ? 'bg-slate-100 text-slate-500' : ($sn >= 0.9 ? 'bg-emerald-50 text-emerald-700' : ($sn >= 0.7 ? 'bg-blue-50 text-blue-700' : ($sn >= 0.5 ? 'bg-amber-50 text-amber-700' : 'bg-red-50 text-red-700'))) }}">
                                Skor {{ $m->skor_label }}
                            </span>
                        </td>
                        <td class="px-3 py-3 text-xs min-w-[140px]">
                            @if($p)
                                <div class="text-slate-700 font-medium">{{ $p['nama_ppl'] ?: '-' }}</div>
                                <div class="text-slate-400">{{ $p['nama_pml'] ?: '-' }} · {{ $p['pml_organik'] ?: '-' }}</div>
                            @else
                                <span class="text-slate-300">Tidak ditemukan</span>
                            @endif
                        </td>
                        <td class="px-3 py-3 whitespace-nowrap">
                            @if($m->fasih_link)
                                <a href="{{ $m->fasih_link }}" target="_blank" rel="noopener"
                                   title="Buka assignment di FASIH (mode edit)"
                                   class="text-xs font-medium text-orange-600 hover:text-orange-700 transition hover:underline">
                                    {{ $m->fasih_link_short }}
                                </a>
                            @else
                                <span class="text-xs text-slate-300">-</span>
                            @endif
                        </td>
                        <td class="px-3 py-3 min-w-[160px] max-w-[240px]">
                            @if($m->tindak_lanjut === 'sudah')
                                <span class="px-2 py-1 rounded-full text-xs font-bold {{ $m->status_color }}">{{ $m->status_label }}</span>
                                @if($m->catatan)
                                    <div class="mt-1.5 text-xs text-amber-800 bg-amber-50 border border-amber-100 rounded px-2 py-1 whitespace-pre-line break-words">📝 {{ $m->catatan }}</div>
                                @endif
                                <div class="text-[10px] text-slate-400 mt-1">
                                    {{ $m->diselesaikan_oleh ? $m->diselesaikan_oleh . ' · ' : '' }}{{ $m->tindak_lanjut_at?->format('d/m H:i') }}
                                </div>
                            @else
                                <span class="px-2 py-1 rounded-full text-xs font-bold bg-red-50 text-red-700 border border-red-200">Belum</span>
                            @endif
                        </td>
                        <td class="px-3 py-3 text-right whitespace-nowrap">
                            @if($m->tindak_lanjut === 'sudah')
                                <button wire:click="batalkanTindakLanjut({{ $m->id }})"
                                        wire:confirm="Batalkan tindak lanjut? Metode dan catatan akan dihapus."
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
                        <td colspan="10" class="px-4 py-10 text-center text-slate-400">Tidak ada data yang cocok dengan filter.</td>
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
<!-- MODAL TANDAI SELESAI: METODE + CATATAN -->
<!-- ============================================ -->
@if($showModal)
<div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 overflow-hidden max-h-[90vh] flex flex-col">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-slate-200 bg-gradient-to-r from-orange-50 to-amber-50">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-slate-800">Tandai Missing Value Selesai</h3>
                <button wire:click="tutupModal" class="text-slate-400 hover:text-slate-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <p class="text-sm text-slate-500 mt-1">
                Anggota keluarga: <span class="font-semibold text-slate-700">{{ $modalNama ?? '-' }}</span>
            </p>
        </div>

        <!-- Body -->
        <div class="px-6 py-4 overflow-y-auto">
            <p class="text-xs font-semibold text-slate-600 mb-2">Metode Penyelesaian <span class="text-red-500">*</span></p>
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
                            'revoked_pml' => 'Assignment di-revoke PML lalu diperbaiki PPL',
                            'diselesaikan_admin' => 'Admin telah memperbaiki isian yang missing',
                            'reject_admin' => 'Admin me-reject assignment untuk diperbaiki',
                        ];
                    @endphp
                    <label class="flex items-start gap-3 p-3 rounded-lg border-2 transition cursor-pointer
                        {{ $selectedStatus === $value ? ($colors[$value] ?? 'bg-slate-50') . ' border-current' : 'border-slate-200 hover:border-slate-300' }}">
                        <input type="radio"
                               wire:model.live="selectedStatus"
                               value="{{ $value }}"
                               class="mt-1 w-4 h-4 accent-orange-600 cursor-pointer">
                        <div class="flex-1">
                            <span class="font-semibold text-sm block">{{ $label }}</span>
                            <span class="text-xs text-slate-500">{{ $descriptions[$value] ?? '' }}</span>
                        </div>
                        <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $badgeColors[$value] ?? 'bg-slate-100 text-slate-700' }}">
                            {{ $label }}
                        </span>
                    </label>
                @endforeach
            </div>
            @error('selectedStatus') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror

            <!-- Catatan opsional -->
            <div class="mt-4" x-data="{ len: @js(mb_strlen($catatan ?? '')) }">
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                    Catatan <span class="font-normal text-slate-400">(opsional)</span>
                </label>
                <textarea wire:model="catatan"
                          x-on:input="len = $event.target.value.length"
                          rows="3" maxlength="1000"
                          placeholder="Mis. NIK sudah diisi sesuai KK, tanggal lahir dikonfirmasi ke responden..."
                          class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition text-sm resize-y"></textarea>
                <div class="flex justify-between mt-1">
                    @error('catatan') <p class="text-xs text-red-600">{{ $message }}</p> @else <span></span> @enderror
                    <span class="text-[10px] text-slate-400" x-text="len + '/1000'"></span>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="px-6 py-4 border-t border-slate-200 bg-slate-50 flex gap-2 justify-end">
            <button wire:click="tutupModal"
                    class="px-4 py-2 rounded-lg border border-slate-300 hover:bg-slate-100 text-slate-600 text-sm font-medium transition active:scale-95">
                Batal
            </button>
            <button wire:click="prosesTandaiSelesai"
                    wire:loading.attr="disabled" wire:target="prosesTandaiSelesai"
                    class="px-5 py-2 rounded-lg bg-orange-600 hover:bg-orange-700 text-white text-sm font-semibold transition active:scale-95 flex items-center gap-2 disabled:opacity-50">
                <span wire:loading.remove wire:target="prosesTandaiSelesai">Konfirmasi</span>
                <span wire:loading wire:target="prosesTandaiSelesai">
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
    <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3500)" x-show="show" x-transition
         class="fixed top-4 right-4 z-50 max-w-sm bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg shadow-lg">
        {{ session('success') }}
    </div>
@endif
@if(session()->has('info'))
    <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3500)" x-show="show" x-transition
         class="fixed top-4 right-4 z-50 max-w-sm bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded-lg shadow-lg">
        {{ session('info') }}
    </div>
@endif

</div>
