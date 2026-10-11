@php
    $doughnutOpts = [
        'responsive' => true, 'maintainAspectRatio' => false, 'cutout' => '65%',
        'plugins' => ['legend' => ['position' => 'bottom', 'labels' => ['usePointStyle' => true, 'padding' => 15]]],
    ];

    $cfgStatus = [
        'type' => 'doughnut',
        'data' => [
            'labels' => ['Sudah Diselesaikan', 'Belum Diselesaikan'],
            'datasets' => [[
                'data' => [$dash['selesai'], $dash['belum']],
                'backgroundColor' => ['#10B981', '#EF4444'],
                'borderWidth' => 2, 'borderColor' => '#ffffff',
            ]],
        ],
        'options' => $doughnutOpts,
    ];

    $cfgKelompok = [
        'type' => 'doughnut',
        'data' => [
            'labels' => array_column($dash['byKelompok'], 'nama'),
            'datasets' => [[
                'data' => array_column($dash['byKelompok'], 'total'),
                'backgroundColor' => array_map(fn ($r) => [
                    'Rp 0' => '#EF4444',
                    '≤ Rp 500 rb' => '#F59E0B',
                    'Rp 500 rb – 1 jt' => '#FCD34D',
                    '> Rp 1 jt' => '#3B82F6',
                ][$r['nama']] ?? '#94A3B8', $dash['byKelompok']),
                'borderWidth' => 2, 'borderColor' => '#ffffff',
            ]],
        ],
        'options' => $doughnutOpts,
    ];

    $chartInit = 'if (window.Chart) { $el._c?.destroy(); $el._c = new Chart($el, CFG) }';
@endphp

<div wire:key="sejuta-dashboard-{{ $dash['total'] }}-{{ $dash['selesai'] }}-{{ $dash['denganCatatan'] }}">
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 sm:gap-4 mb-6">
        @foreach([
            ['Total Usaha', number_format($dash['total']), 'text-slate-800'],
            ['Selesai', number_format($dash['selesai']), 'text-emerald-600'],
            ['Belum', number_format($dash['belum']), 'text-red-600'],
            ['Progress', $dash['persen'] . '%', 'text-orange-600'],
            ['Ada Catatan', number_format($dash['denganCatatan']), 'text-blue-600'],
        ] as [$lbl, $val, $color])
            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
                <p class="text-xs font-medium text-slate-400 uppercase tracking-wide">{{ $lbl }}</p>
                <p class="text-2xl font-bold {{ $color }} mt-1">{{ $val }}</p>
            </div>
        @endforeach
    </div>

    @if($dash['total'] === 0)
        <div class="text-center py-10 bg-white rounded-xl border border-dashed border-slate-300 text-sm text-slate-400">Batch ini belum berisi data.</div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
                <h3 class="font-semibold text-slate-700 text-sm mb-3 flex items-center gap-2">
                    <span class="w-1 h-5 bg-emerald-500 rounded-full"></span> Status Penyelesaian
                </h3>
                <div class="relative" style="height: 250px;" wire:ignore>
                    <canvas x-data x-init="{{ str_replace('CFG', \Illuminate\Support\Js::from($cfgStatus), $chartInit) }}"></canvas>
                </div>
            </div>
            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
                <h3 class="font-semibold text-slate-700 text-sm mb-3 flex items-center gap-2">
                    <span class="w-1 h-5 bg-red-500 rounded-full"></span> Sebaran Total Pendapatan Setahun
                </h3>
                <div class="relative" style="height: 250px;" wire:ignore>
                    <canvas x-data x-init="{{ str_replace('CFG', \Illuminate\Support\Js::from($cfgKelompok), $chartInit) }}"></canvas>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
            <x-pendapatan-progress title="Per Keberadaan Usaha" :rows="$dash['byKeberadaan']" accent="bg-amber-500" label="Keberadaan" />
            <x-pendapatan-progress title="Per Jalur Pelaporan" :rows="$dash['byJalur']" accent="bg-purple-500" label="Jalur" />
        </div>

        <div class="mb-6">
            <x-pendapatan-progress title="Per Kelompok Total Pendapatan" :rows="$dash['byKelompok']" accent="bg-red-500" label="Total pendapatan setahun" :chart="false" />
        </div>

        <div class="mb-6">
            <x-pendapatan-progress title="Progress Penyelesaian per Kecamatan" :rows="$dash['byKecamatan']" accent="bg-sky-500" label="Kecamatan" />
        </div>
    @endif
</div>
