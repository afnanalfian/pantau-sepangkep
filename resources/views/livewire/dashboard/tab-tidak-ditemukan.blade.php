<div wire:key="tab-td">

<div class="grid sm:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-2xl p-5 border border-slate-200">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Keluarga Tidak Ditemukan</p>
        <p class="font-display font-bold text-3xl text-red-600 mt-2">{{ number_format($d['totalKeluargaTd']) }}</p>
    </div>
    <div class="bg-white rounded-2xl p-5 border border-slate-200">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Usaha Tidak Ditemukan</p>
        <p class="font-display font-bold text-3xl text-red-600 mt-2">{{ number_format($d['totalUsahaTd']) }}</p>
    </div>
    <div class="bg-white rounded-2xl p-5 border border-slate-200">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Usaha dalam Keluarga Tidak Ditemukan</p>
        <p class="font-display font-bold text-3xl text-red-600 mt-2">{{ number_format($d['totalUkdkTd']) }}</p>
    </div>
</div>

<div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-xs uppercase text-slate-500 font-semibold">
                <tr>
                    <th class="px-4 py-3 text-left">Kecamatan</th>
                    <th class="px-4 py-3 text-center">Keluarga Tidak Ditemukan</th>
                    <th class="px-4 py-3 text-center">Usaha Tidak Ditemukan</th>
                    <th class="px-4 py-3 text-center">Usaha dalam Keluarga Tidak Ditemukan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($d['perKecamatan'] as $r)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 font-semibold text-slate-700">{{ $r['kecamatan'] }}</td>
                        <td class="px-4 py-3 text-center text-slate-600">{{ number_format($r['keluarga_td']) }}</td>
                        <td class="px-4 py-3 text-center text-slate-600">{{ number_format($r['usaha_td']) }}</td>
                        <td class="px-4 py-3 text-center text-slate-600">{{ number_format($r['ukdk_td']) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-10 text-center text-slate-400">Belum ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

</div>
