<div wire:key="tab-td">

@include('livewire.dashboard._filter-bar', [
    'showSearch' => false,
    'showPerPage' => false,
])

<!-- ============================================ -->
<!-- STATS CARDS -->
<!-- ============================================ -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4 mb-5 sm:mb-6">
    <div class="bg-white rounded-xl sm:rounded-2xl p-4 sm:p-5 border border-slate-200 shadow-sm">
        <p class="text-[10px] sm:text-xs font-semibold text-slate-400 uppercase tracking-wide">Keluarga Tidak Ditemukan</p>
        <p class="font-display font-bold text-2xl sm:text-3xl text-red-600 mt-1">{{ number_format($d['totalKeluargaTd']) }}</p>
    </div>
    <div class="bg-white rounded-xl sm:rounded-2xl p-4 sm:p-5 border border-slate-200 shadow-sm">
        <p class="text-[10px] sm:text-xs font-semibold text-slate-400 uppercase tracking-wide">Usaha Tidak Ditemukan</p>
        <p class="font-display font-bold text-2xl sm:text-3xl text-red-600 mt-1">{{ number_format($d['totalUsahaTd']) }}</p>
    </div>
    <div class="bg-white rounded-xl sm:rounded-2xl p-4 sm:p-5 border border-slate-200 shadow-sm">
        <p class="text-[10px] sm:text-xs font-semibold text-slate-400 uppercase tracking-wide">Usaha dalam Keluarga TD</p>
        <p class="font-display font-bold text-2xl sm:text-3xl text-red-600 mt-1">{{ number_format($d['totalUkdkTd']) }}</p>
    </div>
</div>

<!-- ============================================ -->
<!-- TABLE -->
<!-- ============================================ -->
<!-- Mobile: Card View -->
<div class="sm:hidden space-y-3">
    @forelse($d['perWilayah'] as $r)
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
            <div class="font-semibold text-slate-800 text-sm">{{ $r['wilayah'] }}</div>
            <div class="grid grid-cols-3 gap-2 mt-3 pt-3 border-t border-slate-100 text-xs">
                <div>
                    <p class="text-slate-400">Keluarga TD</p>
                    <p class="font-semibold text-red-600">{{ number_format($r['keluarga_td']) }}</p>
                </div>
                <div>
                    <p class="text-slate-400">Usaha TD</p>
                    <p class="font-semibold text-red-600">{{ number_format($r['usaha_td']) }}</p>
                </div>
                <div>
                    <p class="text-slate-400">UKDK TD</p>
                    <p class="font-semibold text-red-600">{{ number_format($r['ukdk_td']) }}</p>
                </div>
            </div>
        </div>
    @empty
        <div class="text-center py-8 bg-white rounded-xl border border-dashed border-slate-300">
            <p class="text-sm text-slate-400">Belum ada data.</p>
        </div>
    @endforelse
</div>

<!-- Desktop: Table View -->
<div class="hidden sm:block bg-white rounded-xl sm:rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-xs uppercase text-slate-500 font-semibold border-b border-slate-200">
                <tr>
                    <th class="px-4 py-3 text-left">{{ $d['labelWilayah'] }}</th>
                    <th class="px-4 py-3 text-center">Keluarga TD</th>
                    <th class="px-4 py-3 text-center">Usaha TD</th>
                    <th class="px-4 py-3 text-center">UKDK TD</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($d['perWilayah'] as $r)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-4 py-3 font-semibold text-slate-700">{{ $r['wilayah'] }}</td>
                        <td class="px-4 py-3 text-center text-red-600 font-semibold">{{ number_format($r['keluarga_td']) }}</td>
                        <td class="px-4 py-3 text-center text-red-600 font-semibold">{{ number_format($r['usaha_td']) }}</td>
                        <td class="px-4 py-3 text-center text-red-600 font-semibold">{{ number_format($r['ukdk_td']) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-10 text-center text-slate-400">Belum ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

</div>
