<div wire:key="tab-produktivitas">

@include('livewire.dashboard._filter-bar', ['showKecamatan' => false, 'exportMethod' => 'exportProduktivitas'])

@if(empty($prod['tanggalList']))
    <div class="bg-white rounded-2xl border border-dashed border-slate-300 p-16 text-center">
        <p class="text-slate-400 font-medium">Butuh minimal 2 hari data (misalnya 27 &amp; 28 Juli) untuk menghitung produktivitas harian.</p>
    </div>
@else
<div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-xs uppercase text-slate-500 font-semibold">
                <tr>
                    <th class="px-4 py-3 text-left sticky left-0 bg-slate-50">Nama PPL</th>
                    @foreach($prod['tanggalList'] as $t)
                        <th class="px-4 py-3 text-center whitespace-nowrap">{{ \Carbon\Carbon::parse($t)->translatedFormat('d M') }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($prod['data'] as $r)
                    <tr class="hover:bg-slate-50 cursor-pointer"
                        wire:click="openPplChart('{{ $r['email'] }}')">
                        <td class="px-4 py-3 font-semibold text-[#0F7B8A] sticky left-0 bg-white hover:underline">{{ $r['nama'] }}</td>
                        @foreach($prod['tanggalList'] as $t)
                            <td class="px-4 py-3 text-center">
                                @if(is_null($r['harian'][$t]))
                                    <span class="text-slate-300">-</span>
                                @else
                                    <span class="font-semibold {{ $r['harian'][$t] > 0 ? 'text-emerald-600' : ($r['harian'][$t] < 0 ? 'text-red-500' : 'text-slate-400') }}">
                                        {{ $r['harian'][$t] > 0 ? '+' : '' }}{{ $r['harian'][$t] }}
                                    </span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr><td colspan="{{ count($prod['tanggalList']) + 1 }}" class="px-4 py-10 text-center text-slate-400">Tidak ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<p class="text-xs text-slate-400 mt-3">Klik nama petugas untuk melihat grafik progres kumulatif hari ke hari.</p>
@endif

</div>
