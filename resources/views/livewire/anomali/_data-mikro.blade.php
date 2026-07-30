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
                       placeholder="Cari nama/assignment/SLS..." 
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
        @php $mitra = $mitraMap->get($m->email_petugas); @endphp
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
            <div class="flex items-start justify-between">
                <div class="flex-1 min-w-0">
                    <div class="font-semibold text-slate-800 text-sm">{{ $m->nama }}</div>
                    <div class="text-[10px] text-slate-400">{{ $m->nama_anomali }}</div>
                </div>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $m->tindak_lanjut === 'sudah' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-red-50 text-red-700 border border-red-200' }}">
                    {{ $m->tindak_lanjut === 'sudah' ? '✓ Selesai' : '⏳ Belum' }}
                </span>
            </div>
            <div class="grid grid-cols-2 gap-1 mt-2 text-xs text-slate-500">
                <span>{{ $m->nmkec }}</span>
                <span>{{ $m->nmdesa }}</span>
                <span class="font-mono">{{ $m->kode_sls }}/{{ $m->sub_sls }}</span>
                <span class="font-mono text-[10px]">{{ $m->assignment_id }}</span>
            </div>
            @if($mitra)
                <div class="mt-2 text-xs text-slate-600 border-t border-slate-100 pt-2">
                    <span class="font-medium">{{ $mitra->nama_ppl }}</span>
                    <span class="text-slate-400"> · {{ $mitra->nama_pml }}</span>
                </div>
            @endif
            <div class="flex items-center gap-2 mt-3 pt-2 border-t border-slate-100">
                @if($m->link_fasih)
                    <a href="{{ $m->link_fasih }}" target="_blank" 
                       class="text-xs font-medium text-orange-600 hover:text-orange-700 transition">
                        Buka Fasih
                    </a>
                    <span class="text-slate-300">|</span>
                @endif
                @if($m->tindak_lanjut === 'sudah')
                    <button wire:click="batalkanTindakLanjut({{ $m->id }})" 
                            class="text-xs font-medium text-amber-600 hover:text-amber-700 transition">
                        Batalkan
                    </button>
                @else
                    <button wire:click="tandaiSelesai({{ $m->id }})" 
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
                    <th class="px-3 py-3 text-left">Assignment ID</th>
                    <th class="px-3 py-3 text-left">PPL / PML</th>
                    <th class="px-3 py-3 text-left">Link Fasih</th>
                    <th class="px-3 py-3 text-center">Status</th>
                    <th class="px-3 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($mikros as $m)
                    @php $mitra = $mitraMap->get($m->email_petugas); @endphp
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-3 py-3 text-slate-500 text-center">{{ $m->no }}</td>
                        <td class="px-3 py-3">
                            <div class="font-semibold text-slate-700">{{ $m->nama }}</div>
                            <div class="text-[10px] text-slate-400">{{ $m->nama_anomali }}</div>
                        </td>
                        <td class="px-3 py-3 text-slate-600">{{ $m->nmkec }}</td>
                        <td class="px-3 py-3 text-slate-600">{{ $m->nmdesa }}</td>
                        <td class="px-3 py-3 text-slate-500 font-mono text-xs">{{ $m->kode_sls }}/{{ $m->sub_sls }}</td>
                        <td class="px-3 py-3 text-slate-400 font-mono text-[10px]">{{ $m->assignment_id }}</td>
                        <td class="px-3 py-3 text-xs">
                            @if($mitra)
                                <div class="text-slate-700 font-medium">{{ $mitra->nama_ppl }}</div>
                                <div class="text-slate-400">{{ $mitra->nama_pml }} · {{ $mitra->pml_organik }}</div>
                            @else
                                <span class="text-slate-300">Tidak ada</span>
                            @endif
                        </td>
                        <td class="px-3 py-3">
                            @if($m->link_fasih)
                                <a href="{{ $m->link_fasih }}" target="_blank" 
                                   class="text-xs font-medium text-orange-600 hover:text-orange-700 transition">
                                    Buka Fasih
                                </a>
                            @endif
                        </td>
                        <td class="px-3 py-3 text-center">
                            <span class="px-2 py-1 rounded-full text-xs font-bold 
                                {{ $m->tindak_lanjut === 'sudah' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-red-50 text-red-700 border border-red-200' }}">
                                {{ $m->tindak_lanjut === 'sudah' ? 'Selesai' : 'Belum' }}
                            </span>
                        </td>
                        <td class="px-3 py-3 text-right">
                            @if($m->tindak_lanjut === 'sudah')
                                <button wire:click="batalkanTindakLanjut({{ $m->id }})" 
                                        class="text-xs font-medium text-amber-600 hover:text-amber-700 transition">
                                    Batalkan
                                </button>
                            @else
                                <button wire:click="tandaiSelesai({{ $m->id }})" 
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

</div>