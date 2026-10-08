@php
    $hasilColors = ['sesuai_lapangan' => '#10B981', 'diperbaiki' => '#3B82F6'];

    // ---- Konfigurasi Chart.js (dibangun di PHP, murni data) ----
    $cfgStatus = [
        'type' => 'doughnut',
        'data' => [
            'labels' => ['Sudah Tindak Lanjut', 'Belum Tindak Lanjut'],
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

    $cfgHasil = [
        'type' => 'doughnut',
        'data' => [
            'labels' => array_column($dash['hasil'], 'label'),
            'datasets' => [[
                'data' => array_column($dash['hasil'], 'count'),
                'backgroundColor' => array_values(array_intersect_key($hasilColors, $dash['hasil'])),
                'borderWidth' => 2, 'borderColor' => '#ffffff',
            ]],
        ],
        'options' => $cfgStatus['options'],
    ];

    $cfgKbli = [
        'type' => 'bar',
        'data' => [
            'labels' => array_map(fn ($r) => $r['kode'] . ' – ' . \Illuminate\Support\Str::limit($r['label'], 45), $dash['topKbli']),
            'datasets' => [
                ['label' => 'Selesai', 'data' => array_column($dash['topKbli'], 'selesai'), 'backgroundColor' => '#10B981', 'borderRadius' => 4],
                ['label' => 'Total', 'data' => array_column($dash['topKbli'], 'total'), 'backgroundColor' => '#F59E0B', 'borderRadius' => 4],
            ],
        ],
        'options' => [
            'responsive' => true, 'maintainAspectRatio' => false, 'indexAxis' => 'y',
            'plugins' => ['legend' => ['position' => 'top', 'labels' => ['usePointStyle' => true]]],
            'scales' => ['x' => ['beginAtZero' => true, 'grid' => ['color' => 'rgba(0,0,0,0.05)']], 'y' => ['grid' => ['display' => false]]],
        ],
    ];

    $cfgKategori = [
        'type' => 'bar',
        'data' => [
            'labels' => array_column($dash['byKategori'], 'kode'),
            'datasets' => [[
                'label' => 'Jumlah usaha',
                'data' => array_column($dash['byKategori'], 'total'),
                'backgroundColor' => '#8B5CF6', 'borderRadius' => 4,
            ]],
        ],
        'options' => [
            'responsive' => true, 'maintainAspectRatio' => false,
            'plugins' => ['legend' => ['display' => false]],
            'scales' => ['y' => ['beginAtZero' => true, 'grid' => ['color' => 'rgba(0,0,0,0.05)']], 'x' => ['grid' => ['display' => false]]],
        ],
    ];

    $cfgKec = [
        'type' => 'bar',
        'data' => [
            'labels' => array_column($dash['byKecamatan'], 'kecamatan'),
            'datasets' => [
                ['label' => 'Selesai', 'data' => array_column($dash['byKecamatan'], 'selesai'), 'backgroundColor' => '#10B981', 'borderRadius' => 4],
                ['label' => 'Total', 'data' => array_column($dash['byKecamatan'], 'total'), 'backgroundColor' => '#F59E0B', 'borderRadius' => 4],
            ],
        ],
        'options' => $cfgKbli['options'],
    ];

    $chartInit = 'if (window.Chart) { $el._c?.destroy(); $el._c = new Chart($el, CFG) }';
@endphp

<div wire:key="kbli-dashboard-{{ $dash['total'] }}-{{ $dash['selesai'] }}">
    <!-- STATISTIK CARDS -->
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

    <!-- HASIL PEMERIKSAAN CARDS -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4 mb-6">
        <div class="flex items-center justify-between p-4 rounded-xl border-2 border-emerald-200 bg-emerald-50">
            <span class="text-sm font-medium text-slate-600">✅ {{ $dash['hasil']['sesuai_lapangan']['label'] }}</span>
            <span class="text-xl font-bold text-emerald-700">{{ number_format($dash['hasil']['sesuai_lapangan']['count']) }}</span>
        </div>
        <div class="flex items-center justify-between p-4 rounded-xl border-2 border-blue-200 bg-blue-50">
            <span class="text-sm font-medium text-slate-600">🛠️ {{ $dash['hasil']['diperbaiki']['label'] }}</span>
            <span class="text-xl font-bold text-blue-700">{{ number_format($dash['hasil']['diperbaiki']['count']) }}</span>
        </div>
        <div class="flex items-center justify-between p-4 rounded-xl border-2 border-slate-200 bg-white">
            <span class="text-sm font-medium text-slate-600">🏷️ Kode KBLI berbeda</span>
            <span class="text-xl font-bold text-slate-700">{{ number_format($dash['jumlahKodeKbli']) }}</span>
        </div>
    </div>

    @if($dash['total'] === 0)
        <div class="text-center py-10 bg-white rounded-xl border border-dashed border-slate-300 text-sm text-slate-400">Batch ini belum berisi data.</div>
    @else
    <!-- ROW 1: Doughnut -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
            <h3 class="font-semibold text-slate-700 text-sm mb-3 flex items-center gap-2">
                <span class="w-1 h-5 bg-emerald-500 rounded-full"></span> Status Tindak Lanjut
            </h3>
            <div class="relative" style="height: 250px;" wire:ignore>
                <canvas x-data x-init="{{ str_replace('CFG', \Illuminate\Support\Js::from($cfgStatus), $chartInit) }}"></canvas>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
            <h3 class="font-semibold text-slate-700 text-sm mb-3 flex items-center gap-2">
                <span class="w-1 h-5 bg-blue-500 rounded-full"></span> Hasil Pemeriksaan KBLI
            </h3>
            <div class="relative" style="height: 250px;" wire:ignore>
                @if($dash['selesai'] > 0)
                    <canvas x-data x-init="{{ str_replace('CFG', \Illuminate\Support\Js::from($cfgHasil), $chartInit) }}"></canvas>
                @else
                    <div class="h-full flex items-center justify-center text-sm text-slate-400">Belum ada yang ditindaklanjuti.</div>
                @endif
            </div>
        </div>
    </div>

    <!-- ROW 2: Top KBLI -->
    <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm mb-6">
        <h3 class="font-semibold text-slate-700 text-sm mb-3 flex items-center gap-2">
            <span class="w-1 h-5 bg-orange-500 rounded-full"></span> Top 10 KBLI Akhir
        </h3>
        <div class="relative" style="height: {{ max(220, count($dash['topKbli']) * 38 + 60) }}px;" wire:ignore>
            <canvas x-data x-init="{{ str_replace('CFG', \Illuminate\Support\Js::from($cfgKbli), $chartInit) }}"></canvas>
        </div>
    </div>

    <!-- ROW 3: Kategori -->
    <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm mb-6">
        <h3 class="font-semibold text-slate-700 text-sm mb-3 flex items-center gap-2">
            <span class="w-1 h-5 bg-purple-500 rounded-full"></span> Sebaran per Kategori KBLI
        </h3>
        <div class="relative" style="height: 260px;" wire:ignore>
            <canvas x-data x-init="{{ str_replace('CFG', \Illuminate\Support\Js::from($cfgKategori), $chartInit) }}"></canvas>
        </div>
        <div class="mt-3 flex flex-wrap gap-x-4 gap-y-1 text-[11px] text-slate-500">
            @foreach($dash['byKategori'] as $k)
                <span><b class="text-slate-700">{{ $k['kode'] }}</b> {{ \Illuminate\Support\Str::limit($k['label'], 40) }} ({{ number_format($k['total']) }})</span>
            @endforeach
        </div>
    </div>

    <!-- ROW 4: Kecamatan -->
    <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm mb-6">
        <h3 class="font-semibold text-slate-700 text-sm mb-3 flex items-center gap-2">
            <span class="w-1 h-5 bg-sky-500 rounded-full"></span> Progress Penyelesaian per Kecamatan
        </h3>
        <div class="relative" style="height: {{ max(200, count($dash['byKecamatan']) * 40 + 50) }}px;" wire:ignore>
            <canvas x-data x-init="{{ str_replace('CFG', \Illuminate\Support\Js::from($cfgKec), $chartInit) }}"></canvas>
        </div>

        <div class="overflow-x-auto mt-4">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-xs uppercase text-slate-500 font-semibold">
                    <tr>
                        <th class="px-3 py-2 text-left">Kecamatan</th>
                        <th class="px-3 py-2 text-right">Total</th>
                        <th class="px-3 py-2 text-right">Selesai</th>
                        <th class="px-3 py-2 text-left w-1/3">Progress</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($dash['byKecamatan'] as $k)
                        <tr>
                            <td class="px-3 py-2 text-slate-700">{{ $k['kecamatan'] }}</td>
                            <td class="px-3 py-2 text-right text-slate-600">{{ number_format($k['total']) }}</td>
                            <td class="px-3 py-2 text-right text-emerald-600 font-medium">{{ number_format($k['selesai']) }}</td>
                            <td class="px-3 py-2">
                                <div class="flex items-center gap-2">
                                    <div class="flex-1 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                        <div class="h-full bg-emerald-500 rounded-full" style="width: {{ $k['persen'] }}%"></div>
                                    </div>
                                    <span class="text-xs text-slate-500 w-12 text-right">{{ $k['persen'] }}%</span>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
