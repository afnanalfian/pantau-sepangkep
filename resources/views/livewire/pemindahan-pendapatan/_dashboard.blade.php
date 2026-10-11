@php
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
        'options' => [
            'responsive' => true, 'maintainAspectRatio' => false, 'cutout' => '65%',
            'plugins' => ['legend' => ['position' => 'bottom', 'labels' => ['usePointStyle' => true, 'padding' => 15]]],
        ],
    ];
@endphp

<div wire:key="pemindahan-dashboard-{{ $dash['total'] }}-{{ $dash['selesai'] }}">
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 mb-6">
        @foreach([
            ['Total Usaha', number_format($dash['total']), 'text-slate-800'],
            ['Selesai', number_format($dash['selesai']), 'text-emerald-600'],
            ['Belum', number_format($dash['belum']), 'text-red-600'],
            ['Progress', $dash['persen'] . '%', 'text-orange-600'],
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
                    <canvas x-data x-init="if (window.Chart) { $el._c?.destroy(); $el._c = new Chart($el, {{ \Illuminate\Support\Js::from($cfgStatus) }}) }"></canvas>
                </div>
            </div>

            <x-pendapatan-progress title="Per Jalur Pelaporan" :rows="$dash['byJalur']" accent="bg-purple-500" label="Jalur" />
        </div>

        <div class="mb-6">
            <x-pendapatan-progress title="Progress Penyelesaian per Kecamatan" :rows="$dash['byKecamatan']" accent="bg-sky-500" label="Kecamatan" />
        </div>

        <div class="mb-6">
            <x-pendapatan-progress title="10 Desa/Kelurahan dengan Kasus Terbanyak" :rows="$dash['topDesa']" accent="bg-orange-500" label="Desa · Kecamatan" :chart="false" />
        </div>
    @endif
</div>
