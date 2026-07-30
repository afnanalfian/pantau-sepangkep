<div wire:key="tab-produktivitas">

@include('livewire.dashboard._filter-bar', ['showKecamatan' => false, 'exportMethod' => 'exportProduktivitas'])

<!-- ============================================ -->
<!-- EMPTY STATE -->
<!-- ============================================ -->
@if(empty($prod['tanggalList']))
    <div class="bg-white rounded-xl sm:rounded-2xl border border-dashed border-slate-300 p-12 sm:p-16 text-center">
        <div class="w-12 sm:w-16 h-12 sm:h-16 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 sm:w-8 h-6 sm:h-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
        </div>
        <p class="text-sm text-slate-400 font-medium">Butuh minimal 2 hari data untuk menghitung produktivitas harian.</p>
        <p class="text-xs text-slate-400 mt-1">Unggah data untuk 2 hari atau lebih (misalnya 27 &amp; 28 Juli).</p>
    </div>
@else

<!-- ============================================ -->
<!-- TABLE - Mobile Card View -->
<!-- ============================================ -->
<div class="sm:hidden space-y-3">
    @forelse($prod['data'] as $r)
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm cursor-pointer hover:border-orange-300 transition" 
             wire:click="openPplChart('{{ $r['email'] }}')">
            <div class="font-semibold text-orange-600 text-sm">{{ $r['nama'] }}</div>
            <div class="grid grid-cols-3 gap-2 mt-3 pt-3 border-t border-slate-100">
                @foreach($prod['tanggalList'] as $t)
                    <div class="text-center">
                        <p class="text-[10px] text-slate-400">{{ \Carbon\Carbon::parse($t)->translatedFormat('d M') }}</p>
                        @if(is_null($r['harian'][$t]))
                            <span class="text-slate-300">-</span>
                        @else
                            <span class="font-semibold {{ $r['harian'][$t] > 0 ? 'text-emerald-600' : ($r['harian'][$t] < 0 ? 'text-red-500' : 'text-slate-400') }}">
                                {{ $r['harian'][$t] > 0 ? '+' : '' }}{{ $r['harian'][$t] }}
                            </span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @empty
        <div class="text-center py-8 bg-white rounded-xl border border-dashed border-slate-300">
            <p class="text-sm text-slate-400">Tidak ada data.</p>
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
                    <th class="px-4 py-3 text-left sticky left-0 bg-slate-50">Nama PPL</th>
                    @foreach($prod['tanggalList'] as $t)
                        <th class="px-4 py-3 text-center whitespace-nowrap">{{ \Carbon\Carbon::parse($t)->translatedFormat('d M') }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($prod['data'] as $r)
                    <tr class="hover:bg-slate-50 transition cursor-pointer" wire:click="openPplChart('{{ $r['email'] }}')">
                        <td class="px-4 py-3 font-semibold text-orange-600 sticky left-0 bg-white hover:underline">{{ $r['nama'] }}</td>
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

<p class="text-[10px] sm:text-xs text-slate-400 mt-3">Klik nama petugas untuk melihat grafik progres kumulatif hari ke hari.</p>
@endif

</div>