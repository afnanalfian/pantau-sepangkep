@php
    $keliling = 2 * M_PI * 52;                       // cincin progres r=52
    $sisa = $keliling * (1 - $dash['persen'] / 100);
    $pct = fn ($v) => rtrim(rtrim(number_format($v, 1, ',', '.'), '0'), ',');
    $seg = fn ($n) => $dash['total'] > 0 ? round($n / $dash['total'] * 100, 2) : 0;
    $warnaKat = ['A' => '#16a34a', 'B' => '#78716c', 'C' => '#2563eb', 'F' => '#ea580c', 'G' => '#dc2626', 'H' => '#0891b2', 'I' => '#d97706', 'J' => '#7c3aed', 'K' => '#0d9488', 'L' => '#65a30d', 'M' => '#4f46e5', 'N' => '#0284c7', 'P' => '#c026d3', 'Q' => '#e11d48', 'R' => '#9333ea', 'S' => '#475569'];
    $kat = fn ($k) => $warnaKat[mb_substr((string) $k, 0, 1)] ?? '#64748b';
    $maksAlasan = max(1, ...array_column($dash['byAlasan'] ?: [['total' => 1]], 'total'));
    $maksDasar = max(1, ...array_column($dash['byDasar'] ?: [['total' => 1]], 'total'));
@endphp

<div wire:key="kk-dashboard-{{ $dash['total'] }}-{{ $dash['selesai'] }}" class="space-y-6">

    {{-- ============================== HERO ============================== --}}
    <div class="relative overflow-hidden rounded-2xl text-white shadow-lg"
         style="background: radial-gradient(circle at 85% 10%, rgba(244,114,182,.35), transparent 40%), radial-gradient(circle at 10% 90%, rgba(56,189,248,.30), transparent 45%), linear-gradient(135deg,#1e1b4b 0%,#312e81 45%,#4c1d95 100%);"
         x-data="{ siap: false }" x-init="setTimeout(() => siap = true, 60)">
        <div class="relative p-5 sm:p-7 grid grid-cols-1 md:grid-cols-[auto_1fr] gap-6 items-center">
            {{-- cincin progres --}}
            <div class="relative w-40 h-40 sm:w-44 sm:h-44 mx-auto">
                <svg viewBox="0 0 120 120" class="w-full h-full -rotate-90">
                    <circle cx="60" cy="60" r="52" fill="none" stroke="rgba(255,255,255,.12)" stroke-width="12"/>
                    <circle cx="60" cy="60" r="52" fill="none" stroke="url(#kkCincin)" stroke-width="12" stroke-linecap="round"
                            stroke-dasharray="{{ $keliling }}"
                            :style="{ strokeDashoffset: siap ? {{ $sisa }} : {{ $keliling }} }"
                            style="transition: stroke-dashoffset 1.4s cubic-bezier(.2,.8,.2,1);"/>
                    <defs>
                        <linearGradient id="kkCincin" x1="0" y1="0" x2="1" y2="1">
                            <stop offset="0%" stop-color="#34d399"/><stop offset="100%" stop-color="#38bdf8"/>
                        </linearGradient>
                    </defs>
                </svg>
                <div class="absolute inset-0 flex flex-col items-center justify-center">
                    <span class="text-4xl sm:text-5xl font-black leading-none">{{ $pct($dash['persen']) }}<span class="text-xl">%</span></span>
                    <span class="text-[11px] uppercase tracking-widest text-indigo-200 mt-1">terkoreksi</span>
                </div>
            </div>

            <div>
                <p class="text-[11px] uppercase tracking-[0.25em] text-pink-200 font-semibold">Misi Koreksi KBLI</p>
                <h3 class="text-xl sm:text-3xl font-black tracking-tight">
                    {{ number_format($dash['belum']) }} usaha lagi menunggu dibereskan
                </h3>
                <p class="text-sm text-indigo-200 mt-1">{{ number_format($dash['selesai']) }} dari {{ number_format($dash['total']) }} sudah ditindaklanjuti oleh {{ number_format($dash['pic']) }} PIC.</p>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 sm:gap-3 mt-4">
                    @foreach([
                        ['Total keliru', $dash['total'], '🧾', null],
                        ['Belum', $dash['belum'], '⏳', 'belum'],
                        ['Sudah sesuai', $dash['sudahSesuai'], '✅', null],
                        ['Diperbaiki', $dash['diperbaiki'], '🛠️', null],
                    ] as [$lbl, $val, $ikon, $klik])
                        <button type="button" @if($klik) wire:click="lihat('filterStatus', '{{ $klik }}')" @endif
                                class="text-left rounded-xl px-3 py-2.5 border border-white/15 bg-white/10 backdrop-blur-sm {{ $klik ? 'hover:bg-white/20 cursor-pointer' : 'cursor-default' }} transition">
                            <p class="text-[11px] text-indigo-100">{{ $ikon }} {{ $lbl }}</p>
                            <p class="text-xl sm:text-2xl font-black">{{ number_format($val) }}</p>
                        </button>
                    @endforeach
                </div>

                {{-- batang komposisi --}}
                <div class="mt-4">
                    <div class="flex h-2.5 w-full rounded-full overflow-hidden bg-white/10">
                        <div class="h-full bg-emerald-400 transition-all duration-1000" :style="{ width: siap ? '{{ $seg($dash['sudahSesuai']) }}%' : '0%' }"></div>
                        <div class="h-full bg-sky-400 transition-all duration-1000" :style="{ width: siap ? '{{ $seg($dash['diperbaiki']) }}%' : '0%' }"></div>
                    </div>
                    <div class="flex flex-wrap gap-x-4 gap-y-1 mt-1.5 text-[11px] text-indigo-100">
                        <span><span class="inline-block w-2 h-2 rounded-full bg-emerald-400 mr-1"></span>Sudah sesuai</span>
                        <span><span class="inline-block w-2 h-2 rounded-full bg-sky-400 mr-1"></span>Keliru &amp; telah diperbaiki</span>
                        <span><span class="inline-block w-2 h-2 rounded-full bg-white/30 mr-1"></span>Belum</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================== PANGGUNG ============================== --}}
    @include('livewire.kbli-keliru._panggung', ['peringkat' => $dash['peringkat']])

    @if($dash['total'] > 0)
    {{-- ============================== PERGESERAN KATEGORI ============================== --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-6 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-1 mb-4">
            <div>
                <h3 class="font-bold text-slate-800 text-base">🔀 Pergeseran Kategori yang Direkomendasikan</h3>
                <p class="text-xs text-slate-500">Kategori saat ini → kategori yang seharusnya. Klik kartu untuk membuka datanya.</p>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            @foreach($dash['pergeseran'] as $p)
                <button type="button" wire:click="lihat('filterKategori', @js($p['dari']), 'filterRekomendasi', @js($p['ke']))"
                        class="group text-left rounded-xl border border-slate-200 p-3 hover:border-indigo-300 hover:shadow-md transition">
                    <div class="flex items-center gap-2">
                        <span class="shrink-0 w-10 h-10 rounded-lg flex items-center justify-center text-lg font-black text-white shadow"
                              style="background: {{ $kat($p['dari']) }}">{{ $p['dari'] }}</span>
                        <svg class="w-6 h-6 shrink-0 text-slate-300 group-hover:text-indigo-500 group-hover:translate-x-0.5 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                        <div class="flex flex-wrap gap-1 min-w-0">
                            @foreach(array_slice(explode('/', $p['ke']), 0, 14) as $k)
                                <span class="px-1.5 py-0.5 rounded text-[11px] font-bold text-white" style="background: {{ $kat($k) }}">{{ trim($k) }}</span>
                            @endforeach
                        </div>
                        <span class="ml-auto shrink-0 text-right">
                            <span class="block text-lg font-black text-slate-800 leading-none">{{ number_format($p['total']) }}</span>
                            <span class="text-[10px] text-slate-400">usaha</span>
                        </span>
                    </div>
                    @if($p['deskripsi'])
                        <p class="text-[11px] text-slate-500 mt-2 line-clamp-2">{{ $p['deskripsi'] }}</p>
                    @endif
                    <div class="flex items-center gap-2 mt-2">
                        <div class="flex-1 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                            <div class="h-full rounded-full" style="width: {{ $p['persen'] }}%; background: linear-gradient(90deg,#34d399,#38bdf8)"></div>
                        </div>
                        <span class="text-[11px] font-semibold text-slate-500">{{ $pct($p['persen']) }}%</span>
                    </div>
                </button>
            @endforeach
        </div>
    </div>

    {{-- ============================== ALASAN & DASAR RULE ============================== --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        @foreach([
            ['🧩 Alasan Tidak Sesuai', 'byAlasan', $maksAlasan, 'filterAlasan', '#f43f5e'],
            ['📐 Dasar Rule', 'byDasar', $maksDasar, 'filterDasar', '#6366f1'],
        ] as [$judul, $key, $maks, $filter, $warna])
            <div class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-5 shadow-sm">
                <h3 class="font-bold text-slate-800 text-sm mb-3">{{ $judul }}</h3>
                <div class="space-y-3">
                    @foreach($dash[$key] as $r)
                        @php $bisa = !str_starts_with($r['nama'], '('); @endphp
                        <div @if($bisa) wire:click="lihat('{{ $filter }}', @js($r['nama']))" @endif class="{{ $bisa ? 'cursor-pointer group' : '' }}">
                            <div class="flex justify-between gap-2 text-xs">
                                <span class="font-medium text-slate-700 {{ $bisa ? 'group-hover:text-indigo-600' : '' }}">{{ $r['nama'] }}</span>
                                <span class="shrink-0 text-slate-500"><b class="text-slate-800">{{ number_format($r['total']) }}</b> · {{ $pct($r['persen']) }}% beres</span>
                            </div>
                            {{-- batang total (pudar) + batang selesai (tegas) --}}
                            <div class="relative mt-1 h-3 rounded-full bg-slate-100 overflow-hidden">
                                <div class="absolute inset-y-0 left-0 rounded-full opacity-25" style="width: {{ $r['total'] / $maks * 100 }}%; background: {{ $warna }}"></div>
                                <div class="absolute inset-y-0 left-0 rounded-full" style="width: {{ $r['selesai'] / $maks * 100 }}%; background: {{ $warna }}"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    {{-- ============================== KATEGORI SAAT INI + PIC ============================== --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-5 shadow-sm">
            <h3 class="font-bold text-slate-800 text-sm mb-3">🏷️ Kategori Saat Ini</h3>
            <div class="grid grid-cols-3 gap-2">
                @foreach($dash['byKategori'] as $k)
                    <button type="button" wire:click="lihat('filterKategori', @js($k['nama']))"
                            class="relative rounded-xl p-2.5 text-white text-left overflow-hidden hover:scale-[1.03] transition" style="background: {{ $kat($k['nama']) }}">
                        <span class="absolute -right-1 -bottom-3 text-5xl font-black opacity-20">{{ $k['nama'] }}</span>
                        <span class="block text-xl font-black">{{ $k['nama'] }}</span>
                        <span class="block text-[11px] opacity-90">{{ number_format($k['total']) }} usaha</span>
                        <span class="block text-[10px] opacity-80">{{ $pct($k['persen']) }}% beres</span>
                    </button>
                @endforeach
            </div>
        </div>

        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 p-4 sm:p-5 shadow-sm">
            <h3 class="font-bold text-slate-800 text-sm mb-1">👥 Progres per PIC</h3>
            <p class="text-xs text-slate-400 mb-3">Klik nama untuk membuka datanya.</p>
            <div class="max-h-[380px] overflow-y-auto pr-1 space-y-2">
                @foreach($dash['byPic'] as $r)
                    @php
                        $tanpa = $r['nama'] === '(Tanpa PIC)';
                        $nilai = $tanpa ? \App\Livewire\KbliKeliru\KbliKeliruDetail::TANPA_PIC : $r['nama'];
                    @endphp
                    <div wire:click="lihat('filterPic', @js($nilai))" class="group flex items-center gap-3 cursor-pointer rounded-lg px-2 py-1.5 hover:bg-slate-50">
                        <span class="w-28 sm:w-56 shrink-0 truncate text-xs font-semibold {{ $tanpa ? 'text-amber-600' : 'text-slate-700 group-hover:text-indigo-600' }}" title="{{ $r['nama'] }}">{{ $r['nama'] }}</span>
                        <div class="flex-1 h-2.5 rounded-full bg-slate-100 overflow-hidden">
                            <div class="h-full rounded-full" style="width: {{ $r['persen'] }}%; background: {{ $r['persen'] >= 100 ? 'linear-gradient(90deg,#10b981,#34d399)' : 'linear-gradient(90deg,#6366f1,#a855f7)' }}"></div>
                        </div>
                        <span class="w-20 sm:w-24 shrink-0 text-right text-[11px] text-slate-500"><b class="text-slate-800">{{ number_format($r['selesai']) }}</b>/{{ number_format($r['total']) }} · {{ $pct($r['persen']) }}%</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @else
        <div class="text-center py-10 bg-white rounded-xl border border-dashed border-slate-300 text-sm text-slate-400">Batch ini belum berisi data.</div>
    @endif
</div>
