{{--
    Podium 3 PIC teratas. Variabel: $podium (list, sudah terurut dari controller)
    Urutan tampil: juara 2 (kiri) – juara 1 (tengah) – juara 3 (kanan)
--}}
@php
    $gaya = [
        1 => ['medali' => '🥇', 'tinggi' => 'h-36 sm:h-44', 'balok' => 'from-amber-300 via-yellow-400 to-amber-500', 'cincin' => 'ring-amber-400', 'teks' => 'text-amber-900', 'label' => 'Juara 1'],
        2 => ['medali' => '🥈', 'tinggi' => 'h-24 sm:h-32', 'balok' => 'from-slate-200 via-slate-300 to-slate-400', 'cincin' => 'ring-slate-300', 'teks' => 'text-slate-800', 'label' => 'Juara 2'],
        3 => ['medali' => '🥉', 'tinggi' => 'h-16 sm:h-24', 'balok' => 'from-orange-300 via-orange-400 to-amber-700', 'cincin' => 'ring-orange-300', 'teks' => 'text-orange-950', 'label' => 'Juara 3'],
    ];
    $urutanTampil = [2, 1, 3];
    $inisial = fn ($nama) => collect(preg_split('/[\s,.]+/', $nama, -1, PREG_SPLIT_NO_EMPTY))
        ->reject(fn ($w) => in_array(mb_strtoupper($w), ['S', 'SE', 'SST', 'SH', 'ST', 'MM', 'M', 'SI', 'SP', 'SPD', 'SKM', 'AMD'], true))
        ->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('') ?: '?';
@endphp

<div class="relative overflow-hidden rounded-2xl border border-amber-200 bg-gradient-to-b from-amber-50 via-white to-white p-4 sm:p-6 mb-6 shadow-sm"
     x-data="{ naik: false }" x-init="setTimeout(() => naik = true, 80)">

    {{-- hiasan --}}
    <div class="pointer-events-none absolute -top-10 -right-10 w-40 h-40 rounded-full bg-amber-200/40 blur-2xl"></div>
    <div class="pointer-events-none absolute -bottom-16 -left-10 w-48 h-48 rounded-full bg-orange-200/30 blur-2xl"></div>

    <div class="relative flex flex-col sm:flex-row sm:items-end sm:justify-between gap-1 mb-4">
        <div>
            <h3 class="font-bold text-slate-800 text-base sm:text-lg flex items-center gap-2">🏆 Podium PIC Tercepat</h3>
            <p class="text-xs text-slate-500">Peringkat: progres tertinggi, lalu yang paling cepat mencapainya sejak batch diunggah.</p>
        </div>
        @if(count($podium))
            <p class="text-[11px] text-amber-700 font-medium">Ayo kejar podiumnya! 💪</p>
        @endif
    </div>

    @if(count($podium) === 0)
        <div class="relative text-center py-8">
            <div class="text-4xl mb-2">🏁</div>
            <p class="text-sm font-semibold text-slate-700">Podium masih kosong</p>
            <p class="text-xs text-slate-500 mt-0.5">Jadilah PIC pertama yang menyelesaikan anomali dan naik ke podium!</p>
        </div>
    @else
        {{-- PODIUM --}}
        <div class="relative grid grid-cols-3 gap-2 sm:gap-4 items-end max-w-2xl mx-auto">
            @foreach($urutanTampil as $peringkat)
                @php $p = $podium[$peringkat - 1] ?? null; $g = $gaya[$peringkat]; @endphp
                <div class="flex flex-col items-center min-w-0">
                    @if($p)
                        {{-- orang --}}
                        <div class="relative mb-2 transition-all duration-700 ease-out"
                             :class="naik ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'"
                             style="transition-delay: {{ [1 => 450, 2 => 250, 3 => 100][$peringkat] }}ms">
                            @if($peringkat === 1)
                                <div class="absolute -top-5 left-1/2 -translate-x-1/2 text-xl sm:text-2xl animate-bounce">👑</div>
                            @endif
                            <div class="w-12 h-12 sm:w-16 sm:h-16 rounded-full bg-white ring-4 {{ $g['cincin'] }} shadow-md flex items-center justify-center font-bold text-slate-700 text-sm sm:text-lg">
                                {{ $inisial($p['nama']) }}
                            </div>
                            <div class="absolute -bottom-1 -right-1 text-lg sm:text-xl">{{ $g['medali'] }}</div>
                        </div>
                        <p class="text-[11px] sm:text-sm font-bold text-slate-800 text-center leading-tight line-clamp-2 px-1" title="{{ $p['nama'] }}">{{ $p['nama'] }}</p>
                        <p class="text-[10px] sm:text-xs text-slate-500 text-center">{{ number_format($p['selesai']) }}/{{ number_format($p['total']) }} selesai</p>
                        @if($p['durasi'])
                            <p class="mt-1 text-[9px] sm:text-[11px] font-semibold px-1.5 sm:px-2 py-0.5 rounded-full text-center {{ $p['tuntas'] ? 'bg-emerald-100 text-emerald-800' : 'bg-sky-100 text-sky-800' }}">
                                ⚡ {{ $p['tuntas'] ? 'Tuntas' : 'Aktif' }} {{ $p['durasi'] }}
                            </p>
                        @endif
                    @else
                        <div class="w-12 h-12 sm:w-16 sm:h-16 mb-2 rounded-full border-2 border-dashed border-slate-300 flex items-center justify-center text-slate-300 text-lg">?</div>
                        <p class="text-[11px] sm:text-xs text-slate-400 text-center">Kursi kosong</p>
                    @endif

                    {{-- balok podium --}}
                    <div class="w-full mt-2 overflow-hidden rounded-t-xl">
                        <div class="w-full {{ $g['tinggi'] }} bg-gradient-to-b {{ $g['balok'] }} shadow-inner flex flex-col items-center justify-start pt-2 sm:pt-3 origin-bottom transition-transform duration-700 ease-out {{ $p ? '' : 'opacity-40' }}"
                             :class="naik ? 'scale-y-100' : 'scale-y-0'"
                             style="transition-delay: {{ [1 => 300, 2 => 150, 3 => 0][$peringkat] }}ms">
                            <span class="text-2xl sm:text-4xl font-black {{ $g['teks'] }} drop-shadow-sm">{{ $peringkat }}</span>
                            @if($p)
                                <span class="text-sm sm:text-xl font-extrabold {{ $g['teks'] }}">{{ rtrim(rtrim(number_format($p['persen'], 1, ',', '.'), '0'), ',') }}%</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="relative max-w-2xl mx-auto h-2 rounded-b-lg bg-gradient-to-r from-slate-300 via-slate-400 to-slate-300"></div>

        {{-- PENANTANG (peringkat 4 & 5) --}}
        @if(count($podium) > 3)
            <div class="relative mt-4 flex flex-wrap justify-center gap-2">
                @foreach(array_slice($podium, 3) as $i => $p)
                    <div class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-white border border-slate-200 shadow-sm text-xs">
                        <span class="font-bold text-slate-400">#{{ $i + 4 }}</span>
                        <span class="font-semibold text-slate-700">{{ $p['nama'] }}</span>
                        <span class="text-slate-500">{{ rtrim(rtrim(number_format($p['persen'], 1, ',', '.'), '0'), ',') }}%</span>
                        @if($p['durasi'])<span class="text-slate-400">· {{ $p['durasi'] }}</span>@endif
                    </div>
                @endforeach
            </div>
        @endif
    @endif
</div>
