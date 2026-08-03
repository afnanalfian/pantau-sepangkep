@php
    $metrikSemua = $prod['metrikSemua'] ?? ['progres' => 'Progres', 'draft' => 'Draft', 'muatan' => 'Muatan'];
    $metrik = $prod['metrik'] ?? $metrikSemua;              // hanya yang dicentang
    $jmlKolom = max(1, count($prod['tanggalList']) * max(count($metrik), 1) + 1);

    // Warna selisih: progres & muatan naik = bagus (hijau), draft naik = perlu perhatian (amber)
    $warnaSelisih = function ($metrikKey, $nilai) {
        if ($nilai === null) return 'text-slate-300';
        if ($nilai == 0) return 'text-slate-400';
        if ($metrikKey === 'draft') {
            return $nilai > 0 ? 'text-amber-600' : 'text-emerald-600';
        }
        return $nilai > 0 ? 'text-emerald-600' : 'text-red-500';
    };

    // warna kotak centang saat aktif
    $warnaCentang = [
        'progres' => 'bg-emerald-600 border-emerald-600',
        'draft' => 'bg-amber-500 border-amber-500',
        'muatan' => 'bg-indigo-600 border-indigo-600',
    ];
@endphp

<div wire:key="tab-produktivitas">

@include('livewire.dashboard._filter-bar', [
    'exportMethod' => 'exportProduktivitas',
    'showPerPage' => false,
    'searchPlaceholder' => 'Cari nama PPL...',
])

<!-- ============================================ -->
<!-- PEMILIH KOLOM -->
<!-- ============================================ -->
<div class="bg-white rounded-xl sm:rounded-2xl border border-slate-200 p-3 sm:p-4 mb-4 shadow-sm">
    <div class="flex flex-col sm:flex-row sm:items-center gap-3">
        <span class="text-xs font-semibold text-slate-500 uppercase tracking-wide whitespace-nowrap">
            Kolom ditampilkan
        </span>
        <div class="flex flex-wrap gap-2">
            @foreach($metrikSemua as $key => $label)
                @php $aktif = in_array($key, $metrikAktif); @endphp
                <label class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg border cursor-pointer select-none transition
                    {{ $aktif ? 'border-slate-300 bg-slate-50 text-slate-700' : 'border-slate-200 bg-white text-slate-400 hover:border-slate-300' }}">
                    <input type="checkbox"
                           value="{{ $key }}"
                           wire:model.live="metrikAktif"
                           class="sr-only">
                    <span class="w-4 h-4 rounded border-2 flex items-center justify-center transition
                        {{ $aktif ? ($warnaCentang[$key] ?? 'bg-orange-600 border-orange-600') : 'border-slate-300 bg-white' }}">
                        @if($aktif)
                            <svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                        @endif
                    </span>
                    <span class="text-sm font-medium">{{ $label }}</span>
                </label>
            @endforeach
        </div>
        <span class="text-[11px] text-slate-400 sm:ml-auto">
            {{ count($metrik) }} dari {{ count($metrikSemua) }} kolom aktif
        </span>
    </div>
</div>

<!-- ============================================ -->
<!-- EMPTY STATE -->
<!-- ============================================ -->
@if(empty($metrik))
    <div class="bg-white rounded-xl sm:rounded-2xl border border-dashed border-amber-300 bg-amber-50/40 p-10 text-center">
        <p class="text-sm text-amber-700 font-medium">Belum ada kolom yang dipilih.</p>
        <p class="text-xs text-amber-600 mt-1">Centang minimal satu kolom (Progres, Draft, atau Muatan) di atas.</p>
    </div>
@elseif(empty($prod['tanggalList']))
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
    @if(isset($metrik['progres']))<span><b class="text-slate-700">Progres</b>: assignment selesai</span>@endif
    @if(isset($metrik['draft']))<span><b class="text-slate-700">Draft</b>: dokumen berstatus draft</span>@endif
    @if(isset($metrik['muatan']))<span><b class="text-slate-700">Muatan</b>: keluarga + usaha + UKDK</span>@endif
    <span class="text-slate-400">(angka kecil di bawah = posisi kumulatif hari itu)</span>
</div>

<!-- ============================================ -->
<!-- MOBILE: CARD VIEW -->
<!-- ============================================ -->
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
    Klik nama petugas untuk melihat grafik kumulatif hari ke hari (mengikuti kolom yang dicentang).
</p>
@endif

</div>
