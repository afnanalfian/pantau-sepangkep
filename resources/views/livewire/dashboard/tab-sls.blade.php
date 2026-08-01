<div wire:key="tab-sls">

@include('livewire.dashboard._filter-bar', [
    'exportMethod' => 'exportDetailSls',
    'searchPlaceholder' => 'Cari SLS / kode region / PPL / PML...',
])

<!-- ============================================ -->
<!-- MOBILE: CARD VIEW -->
<!-- ============================================ -->
<div class="sm:hidden space-y-3">
    @forelse($sls as $r)
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <div class="font-semibold text-slate-800 text-sm">{{ $r['nama_sls'] }}</div>
                    <div class="text-[10px] font-mono text-slate-400">{{ $r['region_code'] }}</div>
                </div>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full
                    {{ $r['progres'] >= 80 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : ($r['progres'] >= 40 ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-red-50 text-red-700 border border-red-200') }}">
                    {{ $r['progres'] }}%
                </span>
            </div>
            <div class="grid grid-cols-2 gap-2 mt-3 pt-3 border-t border-slate-100 text-xs">
                <div>
                    <p class="text-slate-400">Kecamatan</p>
                    <p class="font-semibold text-slate-700">{{ $r['kecamatan'] }}</p>
                </div>
                <div>
                    <p class="text-slate-400">Desa/Kel</p>
                    <p class="font-semibold text-slate-700">{{ $r['desa'] }}</p>
                </div>
                <div>
                    <p class="text-slate-400">PPL</p>
                    <p class="font-semibold text-slate-700">{{ $r['ppl'] }}</p>
                </div>
                <div>
                    <p class="text-slate-400">PML</p>
                    <p class="font-semibold text-slate-700">{{ $r['pml'] }}</p>
                </div>
                <div>
                    <p class="text-slate-400">Total / Draft</p>
                    <p class="font-semibold text-slate-700">{{ $r['total'] }} / <span class="text-amber-600">{{ $r['draft'] }}</span></p>
                </div>
                <div>
                    <p class="text-slate-400">Muatan</p>
                    <p class="font-semibold text-slate-700">{{ number_format($r['muatan']) }}</p>
                </div>
            </div>
        </div>
    @empty
        <div class="text-center py-8 bg-white rounded-xl border border-dashed border-slate-300">
            <p class="text-sm text-slate-400">Tidak ada data SLS yang cocok.</p>
        </div>
    @endforelse

    <div class="pt-2">{{ $sls->links() }}</div>
</div>

<!-- ============================================ -->
<!-- DESKTOP: TABLE VIEW -->
<!-- ============================================ -->
<div class="hidden sm:block bg-white rounded-xl sm:rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-xs uppercase text-slate-500 font-semibold border-b border-slate-200">
                <tr>
                    <th class="px-4 py-3 text-left">Kode Region</th>
                    <th class="px-4 py-3 text-left">Nama SLS</th>
                    <th class="px-4 py-3 text-left">Kecamatan</th>
                    <th class="px-4 py-3 text-left">Desa/Kel</th>
                    <th class="px-4 py-3 text-left">PPL</th>
                    <th class="px-4 py-3 text-left">PML</th>
                    <th class="px-4 py-3 text-center">Total</th>
                    <th class="px-4 py-3 text-center cursor-pointer hover:text-slate-700 transition" wire:click="sortBy('draft')">Draft</th>
                    <th class="px-4 py-3 text-center cursor-pointer hover:text-slate-700 transition" wire:click="sortBy('muatan')">Muatan</th>
                    <th class="px-4 py-3 text-center cursor-pointer hover:text-slate-700 transition" wire:click="sortBy('progres')">Progres (%)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($sls as $r)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-4 py-3 font-mono text-xs text-slate-500">{{ $r['region_code'] }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ $r['nama_sls'] }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $r['kecamatan'] }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $r['desa'] }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $r['ppl'] }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $r['pml'] }}</td>
                        <td class="px-4 py-3 text-center font-semibold text-slate-700">{{ $r['total'] }}</td>
                        <td class="px-4 py-3 text-center font-semibold {{ $r['draft'] > 0 ? 'text-amber-600' : 'text-slate-400' }}">{{ $r['draft'] }}</td>
                        <td class="px-4 py-3 text-center text-slate-600">{{ number_format($r['muatan']) }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-1 rounded-full text-xs font-bold
                                {{ $r['progres'] >= 80 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : ($r['progres'] >= 40 ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-red-50 text-red-700 border border-red-200') }}">
                                {{ $r['progres'] }}%
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="px-4 py-10 text-center text-slate-400">Tidak ada data yang cocok.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-4 py-3 border-t border-slate-100 bg-slate-50">
        {{ $sls->links() }}
    </div>
</div>

</div>
