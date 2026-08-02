@php
    $metrik = $prod['metrik'] ?? ['progres' => 'Progres', 'draft' => 'Draft', 'muatan' => 'Muatan'];
    $jmlKolom = count($prod['tanggalList']) * count($metrik) + 1;

    // Warna selisih: progres & muatan naik = bagus (hijau), draft naik = perlu perhatian (amber)
    $warnaSelisih = function ($metrikKey, $nilai) {
        if ($nilai === null) return 'text-slate-300';
        if ($nilai == 0) return 'text-slate-400';
        if ($metrikKey === 'draft') {
            return $nilai > 0 ? 'text-amber-600' : 'text-emerald-600';
        }
        return $nilai > 0 ? 'text-emerald-600' : 'text-red-500';
    };
@endphp

<div wire:key="tab-produktivitas">

@include('livewire.dashboard._filter-bar', [
    'exportMethod' => 'exportProduktivitas',
    'showPerPage' => false,
    'searchPlaceholder' => 'Cari nama PPL...',
])

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

<!-- Legenda -->
<div class="flex flex-wrap items-center gap-x-4 gap-y-1 mb-3 text-[11px] text-slate-500">
    <span class="font-semibold text-slate-600">Angka = selisih terhadap hari sebelumnya.</span>
    <span><b class="text-slate-700">Progres</b>: assignment selesai</span>
    <span><b class="text-slate-700">Draft</b>: dokumen berstatus draft</span>
    <span><b class="text-slate-700">Muatan</b>: keluarga + usaha + UKDK</span>
    <span class="text-slate-400">(angka kecil di bawah = posisi kumulatif hari itu)</span>
</div>

<!-- ============================================ -->
<!-- MOBILE: CARD VIEW -->
<!-- ============================================ -->
<p><i>31 Juli terlewat tarikan datanya karena bermasalah pada saat scraping<i></p>
<div class="sm:hidden space-y-3">
    @forelse($prod['data'] as $r)
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
            <button wire:click="openPplChart('{{ $r['email'] }}')"
                    class="font-semibold text-orange-600 text-sm text-left w-full">
                {{ $r['nama'] }}
            </button>

            <div class="mt-3 pt-3 border-t border-slate-100 overflow-x-auto">
                <table class="w-full text-xs">
                    <thead>
                        <tr class="text-[10px] uppercase text-slate-400">
                            <th class="text-left font-semibold pb-1">Tanggal</th>
                            @foreach($metrik as $label)
                                <th class="text-center font-semibold pb-1">{{ $label }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($prod['tanggalList'] as $t)
                            <tr>
                                <td class="py-1.5 text-slate-500 whitespace-nowrap">{{ \Carbon\Carbon::parse($t)->translatedFormat('d M') }}</td>
                                @foreach($metrik as $key => $label)
                                    @php $nilai = $r['harian'][$t][$key] ?? null; @endphp
                                    <td class="py-1.5 text-center">
                                        <span class="font-semibold {{ $warnaSelisih($key, $nilai) }}">
                                            {{ $nilai === null ? '-' : ($nilai > 0 ? '+' . $nilai : $nilai) }}
                                        </span>
                                        <div class="text-[9px] text-slate-300 leading-none">
                                            {{ $r['harian'][$t]['abs_' . $key] ?? '-' }}
                                        </div>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @empty
        <div class="text-center py-8 bg-white rounded-xl border border-dashed border-slate-300">
            <p class="text-sm text-slate-400">Tidak ada data.</p>
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
                    <th rowspan="2" class="px-4 py-3 text-left sticky left-0 bg-slate-50 z-10 border-r border-slate-200">Nama PPL</th>
                    @foreach($prod['tanggalList'] as $t)
                        <th colspan="{{ count($metrik) }}"
                            class="px-3 py-2 text-center whitespace-nowrap border-l border-slate-200">
                            {{ \Carbon\Carbon::parse($t)->translatedFormat('d M') }}
                        </th>
                    @endforeach
                </tr>
                <tr>
                    @foreach($prod['tanggalList'] as $t)
                        @foreach($metrik as $key => $label)
                            <th class="px-2 py-2 text-center text-[10px] font-semibold whitespace-nowrap {{ $loop->first ? 'border-l border-slate-200' : '' }}">
                                {{ $label }}
                            </th>
                        @endforeach
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($prod['data'] as $r)
                    <tr class="hover:bg-slate-50 transition cursor-pointer" wire:click="openPplChart('{{ $r['email'] }}')">
                        <td class="px-4 py-3 font-semibold text-orange-600 sticky left-0 bg-white hover:underline border-r border-slate-100 whitespace-nowrap">
                            {{ $r['nama'] }}
                        </td>
                        @foreach($prod['tanggalList'] as $t)
                            @foreach($metrik as $key => $label)
                                @php $nilai = $r['harian'][$t][$key] ?? null; @endphp
                                <td class="px-2 py-3 text-center {{ $loop->first ? 'border-l border-slate-100' : '' }}">
                                    <span class="font-semibold {{ $warnaSelisih($key, $nilai) }}">
                                        {{ $nilai === null ? '-' : ($nilai > 0 ? '+' . $nilai : $nilai) }}
                                    </span>
                                    <div class="text-[10px] text-slate-300 leading-none mt-0.5">
                                        {{ $r['harian'][$t]['abs_' . $key] ?? '-' }}
                                    </div>
                                </td>
                            @endforeach
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $jmlKolom }}" class="px-4 py-10 text-center text-slate-400">Tidak ada data.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<p class="text-[10px] sm:text-xs text-slate-400 mt-3">
    Klik nama petugas untuk melihat grafik progres, draft, dan muatan kumulatif hari ke hari.
</p>
@endif

</div>
