<div wire:key="tab-pml">

@include('livewire.dashboard._filter-bar', ['exportMethod' => 'exportKinerjaPml'])

<div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-xs uppercase text-slate-500 font-semibold">
                <tr>
                    <th class="px-4 py-3 text-left cursor-pointer" wire:click="sortBy('nama')">Nama PML</th>
                    <th class="px-4 py-3 text-center">Jumlah PPL</th>
                    <th class="px-4 py-3 text-center cursor-pointer" wire:click="sortBy('progres')">Progres PML (%)</th>
                    <th class="px-4 py-3 text-center cursor-pointer" wire:click="sortBy('muatan')">Muatan</th>
                    <th class="px-4 py-3 text-left">Kecamatan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($pml as $r)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 font-semibold text-slate-700">{{ $r['nama'] }}</td>
                        <td class="px-4 py-3 text-center text-slate-500">{{ $r['jumlah_ppl'] }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-1 rounded-full text-xs font-bold
                                {{ $r['progres'] >= 80 ? 'bg-emerald-50 text-emerald-700' : ($r['progres'] >= 40 ? 'bg-amber-50 text-amber-700' : 'bg-red-50 text-red-700') }}">
                                {{ $r['progres'] }}%
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center font-semibold text-slate-700">{{ number_format($r['muatan']) }}</td>
                        <td class="px-4 py-3 text-xs text-slate-500">{{ implode(', ', $r['kecamatan']) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-10 text-center text-slate-400">Tidak ada data PML yang cocok.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-4 py-3 border-t border-slate-100">{{ $pml->links() }}</div>
</div>

</div>
