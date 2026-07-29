<div wire:key="tab-ppl">

@include('livewire.dashboard._filter-bar', ['exportMethod' => 'exportKinerjaPpl'])

<div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-xs uppercase text-slate-500 font-semibold">
                <tr>
                    <th class="px-4 py-3 text-left cursor-pointer" wire:click="sortBy('nama')">Nama PPL</th>
                    <th class="px-4 py-3 text-center cursor-pointer" wire:click="sortBy('progres')">Progres PPL (%)</th>
                    <th class="px-4 py-3 text-center cursor-pointer" wire:click="sortBy('tidak_ditemukan')">Rata-rata Tidak Ditemukan (%)</th>
                    <th class="px-4 py-3 text-center cursor-pointer" wire:click="sortBy('muatan')">Muatan</th>
                    <th class="px-4 py-3 text-left">PML</th>
                    <th class="px-4 py-3 text-left">Kecamatan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($ppl as $r)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3">
                            <div class="font-semibold text-slate-700">{{ $r['nama'] }}</div>
                            <div class="text-xs text-slate-400">{{ $r['email'] }}</div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-1 rounded-full text-xs font-bold
                                {{ $r['progres'] >= 80 ? 'bg-emerald-50 text-emerald-700' : ($r['progres'] >= 40 ? 'bg-amber-50 text-amber-700' : 'bg-red-50 text-red-700') }}">
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
    <div class="px-4 py-3 border-t border-slate-100">{{ $ppl->links() }}</div>
</div>

</div>
