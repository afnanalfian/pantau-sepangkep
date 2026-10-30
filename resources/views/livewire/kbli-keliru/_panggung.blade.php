{{--
    PANGGUNG JUARA (gaya beda dari modul lain): panggung gelap dengan sorot lampu,
    3 juara di atas balok bercahaya, lalu "lintasan balap" untuk peringkat 4–10.
    Variabel: $peringkat (sudah terurut di komponen)
--}}
@php
    $juara = [
        1 => ['neon' => '#fbbf24', 'balok' => 'linear-gradient(180deg,#fde68a 0%,#f59e0b 55%,#b45309 100%)', 'tinggi' => 'h-36 sm:h-44', 'medali' => '🥇', 'delay' => 500],
        2 => ['neon' => '#cbd5e1', 'balok' => 'linear-gradient(180deg,#f1f5f9 0%,#94a3b8 60%,#475569 100%)', 'tinggi' => 'h-28 sm:h-32', 'medali' => '🥈', 'delay' => 300],
        3 => ['neon' => '#fb923c', 'balok' => 'linear-gradient(180deg,#fed7aa 0%,#ea580c 60%,#7c2d12 100%)', 'tinggi' => 'h-24 sm:h-24', 'medali' => '🥉', 'delay' => 150],
    ];
    $inisial = fn ($nama) => collect(preg_split('/[\s,.]+/', $nama, -1, PREG_SPLIT_NO_EMPTY))
        ->reject(fn ($w) => in_array(mb_strtoupper($w), ['S', 'SE', 'SH', 'ST', 'SST', 'MM', 'M', 'SI', 'MSI', 'SP', 'SPD', 'SKM', 'AMD', 'SKOM'], true))
        ->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('') ?: '?';
    $pct = fn ($v) => rtrim(rtrim(number_format($v, 1, ',', '.'), '0'), ',');
    $lintasan = array_slice($peringkat, 3);
@endphp

<div class="relative overflow-hidden rounded-2xl mb-6 text-white shadow-xl"
     style="background: radial-gradient(ellipse at 50% -20%, rgba(99,102,241,.35), transparent 60%), linear-gradient(180deg,#020617 0%,#0f172a 70%,#1e1b4b 100%);"
     x-data="{ nyala: false }" x-init="setTimeout(() => nyala = true, 100)">

    {{-- sorot lampu --}}
    <div class="pointer-events-none absolute inset-0 transition-opacity duration-1000" :class="nyala ? 'opacity-100' : 'opacity-0'">
        <div class="absolute -top-10 left-[18%] w-40 h-[140%] -rotate-12 origin-top" style="background: linear-gradient(180deg, rgba(255,255,255,.14), transparent 70%); filter: blur(6px);"></div>
        <div class="absolute -top-10 left-1/2 -translate-x-1/2 w-48 h-[140%] origin-top" style="background: linear-gradient(180deg, rgba(251,191,36,.22), transparent 70%); filter: blur(8px);"></div>
        <div class="absolute -top-10 right-[18%] w-40 h-[140%] rotate-12 origin-top" style="background: linear-gradient(180deg, rgba(255,255,255,.14), transparent 70%); filter: blur(6px);"></div>
    </div>
    {{-- bintang kecil --}}
    <div class="pointer-events-none absolute inset-0 opacity-60" style="background-image: radial-gradient(1px 1px at 10% 20%, #fff, transparent), radial-gradient(1px 1px at 80% 15%, #fff, transparent), radial-gradient(1.5px 1.5px at 65% 35%, #c7d2fe, transparent), radial-gradient(1px 1px at 30% 40%, #fff, transparent), radial-gradient(1px 1px at 92% 45%, #fde68a, transparent);"></div>

    <div class="relative p-4 sm:p-6">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-1 mb-5">
            <div>
                <p class="text-[11px] uppercase tracking-[0.25em] text-indigo-300 font-semibold">Panggung Juara</p>
                <h3 class="text-lg sm:text-2xl font-black tracking-tight">Koreksi KBLI Tercepat 🏆</h3>
            </div>
            <p class="text-[11px] sm:text-xs text-slate-400 sm:text-right max-w-xs">Progres tertinggi → paling cepat sejak batch diunggah → selesai terbanyak.</p>
        </div>

        @if(count($peringkat) === 0)
            <div class="text-center py-10">
                <div class="text-5xl mb-3">🎤</div>
                <p class="font-bold text-lg">Panggung masih kosong</p>
                <p class="text-sm text-slate-400">Selesaikan KBLI keliru pertamamu dan jadilah bintang di panggung ini!</p>
            </div>
        @else
            {{-- PODIUM 2 - 1 - 3 --}}
            <div class="grid grid-cols-3 gap-2 sm:gap-5 items-end max-w-3xl mx-auto">
                @foreach([2, 1, 3] as $rank)
                    @php $p = $peringkat[$rank - 1] ?? null; $g = $juara[$rank]; @endphp
                    <div class="flex flex-col items-center min-w-0">
                        @if($p)
                            <div class="relative mb-2 transition-all duration-700 ease-out"
                                 :class="nyala ? 'opacity-100 translate-y-0' : 'opacity-0 -translate-y-6'"
                                 style="transition-delay: {{ $g['delay'] + 200 }}ms">
                                @if($rank === 1)
                                    <div class="absolute -top-6 left-1/2 -translate-x-1/2 text-2xl sm:text-3xl animate-bounce">👑</div>
                                @endif
                                <div class="w-14 h-14 sm:w-20 sm:h-20 rounded-full flex items-center justify-center font-black text-base sm:text-2xl text-slate-900"
                                     style="background: radial-gradient(circle at 30% 30%, #fff, {{ $g['neon'] }}); box-shadow: 0 0 0 3px rgba(255,255,255,.15), 0 0 28px {{ $g['neon'] }};">
                                    {{ $inisial($p['nama']) }}
                                </div>
                                <span class="absolute -bottom-1 -right-1 text-xl sm:text-2xl">{{ $g['medali'] }}</span>
                            </div>
                            <p class="text-[11px] sm:text-sm font-bold text-center leading-tight line-clamp-2 px-1" title="{{ $p['nama'] }}">{{ $p['nama'] }}</p>
                            <p class="text-[10px] sm:text-xs text-slate-400">{{ number_format($p['selesai']) }}/{{ number_format($p['total']) }} selesai</p>
                            @if($p['durasi'])
                                <p class="mt-1 text-[9px] sm:text-[11px] font-semibold px-2 py-0.5 rounded-full border"
                                   style="border-color: {{ $g['neon'] }}66; color: {{ $g['neon'] }}; background: {{ $g['neon'] }}14;">
                                    ⚡ {{ $p['tuntas'] ? 'Tuntas' : 'Melaju' }} {{ $p['durasi'] }}
                                </p>
                            @endif
                        @else
                            <div class="w-14 h-14 sm:w-20 sm:h-20 mb-2 rounded-full border-2 border-dashed border-white/20 flex items-center justify-center text-white/30 text-xl">?</div>
                            <p class="text-[11px] text-slate-500">Menunggu penantang</p>
                        @endif

                        <div class="w-full mt-3 rounded-t-xl overflow-hidden">
                            <div class="w-full {{ $g['tinggi'] }} flex flex-col items-center justify-start pt-2 origin-bottom transition-transform duration-700 ease-out"
                                 :class="nyala ? 'scale-y-100' : 'scale-y-0'"
                                 style="background: {{ $g['balok'] }}; box-shadow: 0 -8px 30px {{ $g['neon'] }}55, inset 0 2px 0 rgba(255,255,255,.5); transition-delay: {{ $g['delay'] }}ms; {{ $p ? '' : 'opacity:.35' }}">
                                <span class="text-3xl sm:text-5xl font-black text-white/90 drop-shadow">{{ $rank }}</span>
                                @if($p)<span class="text-sm sm:text-xl font-extrabold text-slate-900/80">{{ $pct($p['persen']) }}%</span>@endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="max-w-3xl mx-auto h-2 rounded-b-lg" style="background: linear-gradient(90deg,#312e81,#6366f1,#312e81); box-shadow: 0 4px 24px rgba(99,102,241,.6);"></div>

            {{-- LINTASAN BALAP peringkat 4–10 --}}
            @if(count($lintasan))
                <div class="mt-6 max-w-3xl mx-auto">
                    <p class="text-[11px] uppercase tracking-[0.2em] text-slate-400 font-semibold mb-2">Lintasan Balap · siapa menyusul?</p>
                    <div class="space-y-1.5">
                        @foreach($lintasan as $i => $p)
                            <div class="flex items-center gap-2 sm:gap-3">
                                <span class="w-6 text-right text-xs font-black text-slate-500">#{{ $i + 4 }}</span>
                                <span class="w-24 sm:w-44 truncate text-[11px] sm:text-xs font-semibold text-slate-200" title="{{ $p['nama'] }}">{{ $p['nama'] }}</span>
                                <div class="relative flex-1 h-7 rounded-md overflow-hidden"
                                     style="background: repeating-linear-gradient(90deg, rgba(255,255,255,.06) 0 18px, transparent 18px 36px), #111827; border: 1px solid rgba(255,255,255,.08);">
                                    <div class="absolute inset-y-0 left-0 transition-all duration-1000 ease-out"
                                         :style="{ width: nyala ? '{{ $p['persen'] }}%' : '0%' }"
                                         style="background: linear-gradient(90deg, rgba(99,102,241,.15), rgba(99,102,241,.55)); transition-delay: {{ 600 + $i * 120 }}ms"></div>
                                    <span class="absolute top-1/2 text-base transition-all duration-1000 ease-out"
                                          :style="{ left: nyala ? 'calc({{ min($p['persen'], 100) }}% - 22px)' : '-22px' }"
                                          style="transition-delay: {{ 600 + $i * 120 }}ms; transform: translateY(-50%) scaleX(-1);">🏎️</span>
                                    <span class="absolute right-1 top-1/2 -translate-y-1/2 text-xs">🏁</span>
                                </div>
                                <span class="w-12 text-right text-xs font-bold text-indigo-300">{{ $pct($p['persen']) }}%</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endif
    </div>
</div>
