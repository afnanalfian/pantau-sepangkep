<div wire:key="tab-sls">

@include('livewire.dashboard._filter-bar', ['exportMethod' => 'exportDetailSls'])

<div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-xs uppercase text-slate-500 font-semibold">
                <tr>
                    <th class="px-4 py-3 text-left">Kode Region</th>
                    <th class="px-4 py-3 text-left">Nama SLS</th>
                    <th class="px-4 py-3 text-left">Kecamatan</th>
                    <th class="px-4 py-3 text-left">Desa</th>
                    <th class="px-4 py-3 text-left">PPL</th>
                    <th class="px-4 py-3 text-left">PML</th>
                    <th class="px-4 py-3 text-center">Total</th>
                    <th class="px-4 py-3 text-center cursor-pointer" wire:click="sortBy('progres')">Progres (%)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($sls as $r)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 font-mono text-xs text-slate-500">{{ $r['region_code'] }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ $r['nama_sls'] }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $r['kecamatan'] }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $r['desa'] }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $r['ppl'] }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $r['pml'] }}</td>
                        <td class="px-4 py-3 text-center font-semibold text-slate-700">{{ $r['total'] }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-1 rounded-full text-xs font-bold
                                {{ $r['progres'] >= 80 ? 'bg-emerald-50 text-emerald-700' : ($r['progres'] >= 40 ? 'bg-amber-50 text-amber-700' : 'bg-red-50 text-red-700') }}">
                                {{ $r['progres'] }}%
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-4 py-10 text-center text-slate-400">Tidak ada data yang cocok.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-4 py-3 border-t border-slate-100">{{ $sls->links() }}</div>
</div>

</div>
