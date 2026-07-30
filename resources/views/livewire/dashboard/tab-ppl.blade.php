<div wire:key="tab-ppl">

@include('livewire.dashboard._filter-bar', ['exportMethod' => 'exportKinerjaPpl'])

<!-- ============================================ -->
<!-- MOBILE: CARD VIEW -->
<!-- ============================================ -->
<div class="sm:hidden space-y-3">
    @forelse($ppl as $r)
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <div class="font-semibold text-slate-800 text-sm">{{ $r['nama'] }}</div>
                    <div class="text-[10px] text-slate-400">{{ $r['email'] }}</div>
                </div>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full
                    {{ $r['progres'] >= 80 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : ($r['progres'] >= 40 ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-red-50 text-red-700 border border-red-200') }}">
                    {{ $r['progres'] }}%
                </span>
            </div>
            <div class="grid grid-cols-3 gap-2 mt-3 pt-3 border-t border-slate-100 text-xs">
                <div>
                    <p class="text-slate-400">Tidak Ditemukan</p>
                    <p class="font-semibold text-slate-700">{{ $r['tidak_ditemukan'] }}%</p>
                </div>
                <div>
                    <p class="text-slate-400">Muatan</p>
                    <p class="font-semibold text-slate-700">{{ number_format($r['muatan']) }}</p>
                </div>
                <div>
                    <p class="text-slate-400">PML</p>
                    <p class="font-semibold text-slate-700 text-[10px]">{{ $r['pml'] }}</p>
                </div>
            </div>
            <div class="text-[10px] text-slate-400 mt-2">{{ implode(', ', $r['kecamatan']) }}</div>
        </div>
    @empty
        <div class="text-center py-8 bg-white rounded-xl border border-dashed border-slate-300">
            <p class="text-sm text-slate-400">Tidak ada data PPL yang cocok.</p>
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
                    <th class="px-4 py-3 text-left cursor-pointer hover:text-slate-700 transition" wire:click="sortBy('nama')">Nama PPL</th>
                    <th class="px-4 py-3 text-center cursor-pointer hover:text-slate-700 transition" wire:click="sortBy('progres')">Progres (%)</th>
                    <th class="px-4 py-3 text-center cursor-pointer hover:text-slate-700 transition" wire:click="sortBy('tidak_ditemukan')">Tidak Ditemukan (%)</th>
                    <th class="px-4 py-3 text-center cursor-pointer hover:text-slate-700 transition" wire:click="sortBy('muatan')">Muatan</th>
                    <th class="px-4 py-3 text-left">PML</th>
                    <th class="px-4 py-3 text-left">Kecamatan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($ppl as $r)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-4 py-3">
                            <div class="font-semibold text-slate-700">{{ $r['nama'] }}</div>
                            <div class="text-[10px] text-slate-400">{{ $r['email'] }}</div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-1 rounded-full text-xs font-bold
                                {{ $r['progres'] >= 80 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : ($r['progres'] >= 40 ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-red-50 text-red-700 border border-red-200') }}">
                                {{ $r['progres'] }}%
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center text-slate-500">{{ $r['tidak_ditemukan'] }}%</td>
                        <td class="px-4 py-3 text-center font-semibold text-slate-700">{{ number_format($r['muatan']) }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $r['pml'] }}</td>
                        <td class="px-4 py-3 text-xs text-slate-500">{{ implode(', ', $r['kecamatan']) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-10 text-center text-slate-400">Tidak ada data PPL yang cocok.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-4 py-3 border-t border-slate-100 bg-slate-50">
        {{ $ppl->links() }}
    </div>
</div>

</div>