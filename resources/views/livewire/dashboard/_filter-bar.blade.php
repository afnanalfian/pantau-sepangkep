@php
    // Semua opsi punya default supaya partial ini aman dipakai di tab manapun.
    $showSearch   = $showSearch   ?? true;
    $showPerPage  = $showPerPage  ?? true;
    $showWilayah  = $showWilayah  ?? true;
    $showPetugas  = $showPetugas  ?? true;
    $searchPlaceholder = $searchPlaceholder ?? 'Cari nama...';
    $kecamatanList = $kecamatanList ?? collect();
    $desaList = $desaList ?? collect();
    $organikList = $organikList ?? collect();
    $pmlList = $pmlList ?? collect();

    $adaFilter = $filterKecamatan || $filterDesa || $filterPml || $filterOrganik || $search;
@endphp

<div class="bg-white rounded-xl sm:rounded-2xl border border-slate-200 p-3 sm:p-4 mb-4 shadow-sm">
    <div class="flex flex-col gap-2">

        <!-- Row 1: Search & Export -->
        @if($showSearch || isset($exportMethod))
            <div class="flex flex-col sm:flex-row gap-2">
                @if($showSearch)
                    <div class="relative flex-1">
                        <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z"/>
                        </svg>
                        <input type="text"
                               wire:model.live.debounce.400ms="search"
                               placeholder="{{ $searchPlaceholder }}"
                               class="w-full pl-9 pr-3 py-2 rounded-lg border border-slate-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition text-sm">
                    </div>
                @endif
                @if(isset($exportMethod))
                    <button wire:click="{{ $exportMethod }}"
                            class="w-full sm:w-auto px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold transition active:scale-95 inline-flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H8a2 2 0 01-2-2V5a2 2 0 012-2h6l6 6v11a2 2 0 01-2 2z"/>
                        </svg>
                        Export Excel
                    </button>
                @endif
            </div>
        @endif

        <!-- Row 2: Filter wilayah & petugas -->
        <div class="grid grid-cols-2 sm:flex sm:flex-wrap gap-2">
            @if($showWilayah)
                <select wire:model.live="filterKecamatan"
                        class="sm:min-w-[170px] px-3 py-2 rounded-lg border border-slate-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition text-sm bg-white">
                    <option value="">Semua Kecamatan</option>
                    @foreach($kecamatanList as $k)
                        <option value="{{ $k }}">{{ $k }}</option>
                    @endforeach
                </select>

                <select wire:model.live="filterDesa"
                        @disabled($desaList->isEmpty())
                        class="sm:min-w-[170px] px-3 py-2 rounded-lg border border-slate-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition text-sm bg-white disabled:bg-slate-50 disabled:text-slate-400">
                    <option value="">Semua Desa/Kel</option>
                    @foreach($desaList as $d)
                        <option value="{{ $d }}">{{ $d }}</option>
                    @endforeach
                </select>
            @endif

            @if($showPetugas)
                <select wire:model.live="filterOrganik"
                        @disabled($organikList->isEmpty())
                        class="sm:min-w-[170px] px-3 py-2 rounded-lg border border-slate-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition text-sm bg-white disabled:bg-slate-50 disabled:text-slate-400">
                    <option value="">Semua PML Organik</option>
                    @foreach($organikList as $o)
                        <option value="{{ $o }}">{{ $o }}</option>
                    @endforeach
                </select>

                <select wire:model.live="filterPml"
                        @disabled($pmlList->isEmpty())
                        class="sm:min-w-[170px] px-3 py-2 rounded-lg border border-slate-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition text-sm bg-white disabled:bg-slate-50 disabled:text-slate-400">
                    <option value="">Semua PML</option>
                    @foreach($pmlList as $p)
                        <option value="{{ $p }}">{{ $p }}</option>
                    @endforeach
                </select>
            @endif

            @if($showPerPage)
                <select wire:model.live="perPage"
                        class="px-3 py-2 rounded-lg border border-slate-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition text-sm bg-white">
                    <option value="10">10 / hal</option>
                    <option value="20">20 / hal</option>
                    <option value="50">50 / hal</option>
                    <option value="100">100 / hal</option>
                </select>
            @endif

            @if($adaFilter)
                <button wire:click="resetFilter"
                        class="col-span-2 sm:col-span-1 px-3 py-2 rounded-lg border border-slate-300 text-slate-500 hover:bg-slate-50 hover:text-slate-700 text-sm transition inline-flex items-center justify-center gap-1">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Reset
                </button>
            @endif
        </div>

        <!-- Info filter aktif -->
        @if($filterKecamatan || $filterDesa || $filterPml || $filterOrganik)
            <div class="flex flex-wrap items-center gap-1.5 text-[11px]">
                <span class="text-slate-400">Filter aktif:</span>
                @foreach([
                    'Kecamatan' => $filterKecamatan,
                    'Desa/Kel' => $filterDesa,
                    'PML Organik' => $filterOrganik,
                    'PML' => $filterPml,
                ] as $labelFilter => $nilaiFilter)
                    @if($nilaiFilter)
                        <span class="px-2 py-0.5 rounded-full bg-orange-50 text-orange-700 border border-orange-200 font-medium">
                            {{ $labelFilter }}: {{ $nilaiFilter }}
                        </span>
                    @endif
                @endforeach
            </div>
        @endif
    </div>
    <p class="text-[10px] sm:text-xs text-slate-400 mt-3">
        <i>31 Juli terlewat tarikan datanya karena bermasalah pada saat scraping</i>
    </p>
</div>
