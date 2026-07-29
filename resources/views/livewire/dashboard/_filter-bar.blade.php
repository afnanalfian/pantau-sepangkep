<div class="bg-white rounded-2xl border border-slate-200 p-4 mb-4 flex flex-col sm:flex-row gap-3 sm:items-center">
    <div class="relative flex-1">
        <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z"/></svg>
        <input type="text" wire:model.live.debounce.400ms="search" placeholder="Cari nama..."
               class="w-full pl-9 pr-3 py-2 rounded-lg border border-slate-200 text-sm focus:border-[#0F7B8A] focus:ring-2 focus:ring-[#0F7B8A]/20 outline-none">
    </div>

    @if(isset($showKecamatan) ? $showKecamatan : true)
    <select wire:model.live="filterKecamatan" class="px-3 py-2 rounded-lg border border-slate-200 text-sm outline-none focus:border-[#0F7B8A]">
        <option value="">Semua Kecamatan</option>
        @foreach($kecamatanList as $k)
            <option value="{{ $k }}">{{ $k }}</option>
        @endforeach
    </select>
    @endif

    <select wire:model.live="perPage" class="px-3 py-2 rounded-lg border border-slate-200 text-sm outline-none focus:border-[#0F7B8A]">
        <option value="10">10 / halaman</option>
        <option value="20">20 / halaman</option>
        <option value="50">50 / halaman</option>
        <option value="100">100 / halaman</option>
    </select>

    @if(isset($exportMethod))
    <button wire:click="{{ $exportMethod }}" class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold flex items-center gap-2 whitespace-nowrap">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H8a2 2 0 01-2-2V5a2 2 0 012-2h6l6 6v11a2 2 0 01-2 2z"/></svg>
        Export Excel
    </button>
    @endif
</div>
