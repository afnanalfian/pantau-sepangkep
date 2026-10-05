@php

    $total = $dash['total'];
    $selesai = $dash['selesai'];
    $belum = $total - $selesai;
    $progress = $total > 0 ? round($selesai / $total * 100, 1) : 0;

    $palette = ['#F59E0B', '#F97316', '#EF4444', '#EC4899', '#8B5CF6', '#3B82F6', '#10B981', '#14B8A6', '#64748B', '#84CC16'];
    $legendBottom = ['position' => 'bottom', 'labels' => ['padding' => 15, 'usePointStyle' => true, 'pointStyle' => 'circle', 'font' => ['size' => 12]]];
    $legendTop = ['position' => 'top', 'labels' => ['usePointStyle' => true, 'pointStyle' => 'circle', 'padding' => 15, 'font' => ['size' => 11]]];

    $stacked = function ($items, bool $horizontal) use ($legendTop) {
        $items = collect($items);
        return [
            'type' => 'bar',
            'data' => [
                'labels' => $items->map(fn ($k) => $k['label'] . ' (' . $k['persen'] . '%)')->values(),
                'datasets' => [
                    ['label' => 'Selesai', 'data' => $items->pluck('selesai')->values(), 'backgroundColor' => '#10B981', 'borderRadius' => 4],
                    ['label' => 'Belum', 'data' => $items->pluck('belum')->values(), 'backgroundColor' => '#F59E0B', 'borderRadius' => 4],
                ],
            ],
            'options' => [
                'responsive' => true, 'maintainAspectRatio' => false, 'indexAxis' => $horizontal ? 'y' : 'x',
                'plugins' => ['legend' => $legendTop],
                'scales' => [
                    'x' => ['stacked' => true, 'beginAtZero' => true, 'grid' => ['display' => !$horizontal ? false : true, 'color' => 'rgba(0,0,0,0.05)']],
                    'y' => ['stacked' => true, 'beginAtZero' => true, 'grid' => ['display' => false], 'ticks' => ['precision' => 0]],
                ],
            ],
        ];
    };

    $doughnut = function ($labels, $values, $colors) use ($legendBottom) {
        return [
            'type' => 'doughnut',
            'data' => [
                'labels' => array_values($labels),
                'datasets' => [[
                    'data' => array_values($values),
                    'backgroundColor' => array_values($colors),
                    'borderWidth' => 2, 'borderColor' => '#ffffff',
                ]],
            ],
            'options' => ['responsive' => true, 'maintainAspectRatio' => false, 'cutout' => '65%', 'plugins' => ['legend' => $legendBottom]],
        ];
    };

    $metode = collect($dash['byMetode']);
    $skala = collect($dash['bySkala']);
    $kbli = collect($dash['byKbli']);
    $flag = collect($dash['byFlag']);

    $charts = [
        'status' => $doughnut(['Sudah Ditangani', 'Belum Ditangani'], [$selesai, $belum], ['#10B981', '#EF4444']),
        'metode' => $doughnut(
            $metode->pluck('label')->all(),
            $metode->pluck('count')->all(),
            ['#10B981', '#3B82F6']
        ),
        'kategori' => $stacked($dash['byKategori'], false),
        'skala' => $doughnut(
            $skala->keys()->all(),
            $skala->values()->all(),
            $skala->keys()->values()->map(fn ($_, $i) => $palette[$i % count($palette)])->all()
        ),
        'flag' => [
            'type' => 'bar',
            'data' => [
                'labels' => $flag->keys()->map(fn ($c) => \App\Models\NtbNegatifMikro::FLAGS[$c])->values(),
                'datasets' => [[
                    'label' => 'Jumlah usaha ber-flag',
                    'data' => $flag->values(),
                    'backgroundColor' => ['#EF4444', '#F97316', '#8B5CF6'],
                    'borderRadius' => 4,
                ]],
            ],
            'options' => [
                'responsive' => true, 'maintainAspectRatio' => false,
                'plugins' => ['legend' => ['display' => false]],
                'scales' => ['y' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]], 'x' => ['grid' => ['display' => false]]],
            ],
        ],
        'kbli' => [
            'type' => 'bar',
            'data' => [
                'labels' => $kbli->keys()->map(fn ($k) => mb_strimwidth($k, 0, 45, '…'))->values(),
                'datasets' => [[
                    'label' => 'Jumlah Usaha',
                    'data' => $kbli->values(),
                    'backgroundColor' => $kbli->keys()->values()->map(fn ($_, $i) => $palette[$i % count($palette)]),
                    'borderRadius' => 4,
                ]],
            ],
            'options' => [
                'responsive' => true, 'maintainAspectRatio' => false, 'indexAxis' => 'y',
                'plugins' => ['legend' => ['display' => false]],
                'scales' => ['x' => ['beginAtZero' => true, 'ticks' => ['precision' => 0], 'grid' => ['display' => false]], 'y' => ['grid' => ['display' => false]]],
            ],
        ],
        'kecamatan' => $stacked($dash['byKecamatan'], true),
    ];
@endphp

<div wire:key="ntb-dashboard-{{ $batch->id }}">
    <!-- ============================================ -->
    <!-- STATISTIK CARDS -->
    <!-- ============================================ -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 mb-4">
        @foreach([
            ['Total NTB Negatif', number_format($total), 'text-slate-800', 'bg-orange-100 text-orange-600', 'M13 17h8m0 0V9m0 8l-8-8-4 4-6-6'],
            ['Selesai', number_format($selesai), 'text-emerald-600', 'bg-emerald-100 text-emerald-600', 'M5 13l4 4L19 7'],
            ['Belum', number_format($belum), 'text-red-600', 'bg-red-100 text-red-600', 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
            ['Progress', $progress . '%', 'text-orange-600', 'bg-orange-100 text-orange-600', 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6'],
        ] as [$label, $value, $valueCls, $iconCls, $path])
            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-slate-400 uppercase tracking-wide">{{ $label }}</p>
                        <p class="text-2xl font-bold {{ $valueCls }} mt-1">{{ $value }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-full {{ $iconCls }} flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $path }}"/>
                        </svg>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Progress bar + info tambahan -->
    <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm mb-6">
        <div class="flex flex-wrap items-center justify-between gap-2 text-xs text-slate-500 mb-2">
            <span>Progres penyelesaian: <b class="text-slate-700">{{ number_format($selesai) }}</b> dari <b class="text-slate-700">{{ number_format($total) }}</b></span>
            <span class="flex flex-wrap gap-3">
                <span>📉 Total NTB negatif: <b class="text-red-600">Rp {{ \App\Models\NtbNegatifMikro::rupiah($dash['totalNtbNegatif']) }}</b></span>
                <span>📝 {{ number_format($dash['denganCatatan']) }} memiliki catatan</span>
                <span>👤 {{ number_format($dash['terpetakan']) }}/{{ number_format($total) }} terpetakan ke petugas</span>
            </span>
        </div>
        <div class="h-2.5 rounded-full bg-slate-100 overflow-hidden">
            <div class="h-full bg-emerald-500 rounded-full transition-all" style="width: {{ $progress }}%"></div>
        </div>
    </div>

    @if($total === 0)
        <div class="text-center py-10 bg-white rounded-xl border border-dashed border-slate-300 text-sm text-slate-400">
            Batch ini belum memiliki data.
        </div>
    @else
        <!-- ============================================ -->
        <!-- METODE PENYELESAIAN (2 pilihan) -->
        <!-- ============================================ -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-6">
            @foreach($dash['byMetode'] as $key => $m)
                <div class="flex items-center justify-between p-4 rounded-xl border-2 {{ $key === 'diperbaiki' ? 'border-emerald-200 bg-emerald-50' : 'border-blue-200 bg-blue-50' }}">
                    <div class="flex items-center gap-2">
                        <span class="text-lg">{{ $key === 'diperbaiki' ? '🛠️' : '📍' }}</span>
                        <span class="text-sm font-medium text-slate-600">{{ $m['label'] }}</span>
                    </div>
                    <span class="text-xl font-bold {{ $key === 'diperbaiki' ? 'text-emerald-700' : 'text-blue-700' }}">{{ number_format($m['count']) }}</span>
                </div>
            @endforeach
        </div>

        <!-- ============================================ -->
        <!-- ROW 1 -->
        <!-- ============================================ -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
                <h3 class="font-semibold text-slate-700 text-sm mb-3 flex items-center gap-2">
                    <span class="w-1 h-5 bg-emerald-500 rounded-full"></span>
                    Status Tindak Lanjut
                </h3>
                @include('livewire.ntb-negatif._chart', ['key' => 'status', 'cfg' => $charts['status'], 'height' => 250])
            </div>
            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
                <h3 class="font-semibold text-slate-700 text-sm mb-3 flex items-center gap-2">
                    <span class="w-1 h-5 bg-blue-500 rounded-full"></span>
                    Metode Penyelesaian
                </h3>
                @if($selesai > 0)
                    @include('livewire.ntb-negatif._chart', ['key' => 'metode', 'cfg' => $charts['metode'], 'height' => 250])
                @else
                    <div class="h-[250px] flex items-center justify-center text-sm text-slate-400">Belum ada yang diselesaikan.</div>
                @endif
            </div>
            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
                <h3 class="font-semibold text-slate-700 text-sm mb-3 flex items-center gap-2">
                    <span class="w-1 h-5 bg-orange-500 rounded-full"></span>
                    Reklasifikasi Skala Usaha
                </h3>
                @include('livewire.ntb-negatif._chart', ['key' => 'skala', 'cfg' => $charts['skala'], 'height' => 250])
            </div>
        </div>

        <!-- ============================================ -->
        <!-- ROW 2: Kategori & Flag -->
        <!-- ============================================ -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
                <h3 class="font-semibold text-slate-700 text-sm mb-3 flex items-center gap-2">
                    <span class="w-1 h-5 bg-purple-500 rounded-full"></span>
                    Progress per Kategori
                </h3>
                @include('livewire.ntb-negatif._chart', ['key' => 'kategori', 'cfg' => $charts['kategori'], 'height' => 280])
            </div>
            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
                <h3 class="font-semibold text-slate-700 text-sm mb-3 flex items-center gap-2">
                    <span class="w-1 h-5 bg-red-500 rounded-full"></span>
                    Jumlah Usaha per Flag Rasio
                </h3>
                @include('livewire.ntb-negatif._chart', ['key' => 'flag', 'cfg' => $charts['flag'], 'height' => 280])
            </div>
        </div>

        <!-- ============================================ -->
        <!-- ROW 3: KBLI -->
        <!-- ============================================ -->
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm mb-6">
            <h3 class="font-semibold text-slate-700 text-sm mb-3 flex items-center gap-2">
                <span class="w-1 h-5 bg-pink-500 rounded-full"></span>
                Top 10 KBLI dengan NTB Negatif
            </h3>
            @include('livewire.ntb-negatif._chart', ['key' => 'kbli', 'cfg' => $charts['kbli'], 'height' => max(220, $kbli->count() * 32 + 40)])
        </div>

        <!-- ============================================ -->
        <!-- ROW 4: Kecamatan -->
        <!-- ============================================ -->
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm mb-6">
            <h3 class="font-semibold text-slate-700 text-sm mb-3 flex items-center gap-2">
                <span class="w-1 h-5 bg-sky-500 rounded-full"></span>
                Progress Penyelesaian per Kecamatan
            </h3>
            @include('livewire.ntb-negatif._chart', ['key' => 'kecamatan', 'cfg' => $charts['kecamatan'], 'height' => max(200, count($dash['byKecamatan']) * 40 + 50)])
        </div>

        <!-- ============================================ -->
        <!-- REKAP PER PPL -->
        <!-- ============================================ -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-4 pb-3">
                <h3 class="font-semibold text-slate-700 text-sm flex items-center gap-2">
                    <span class="w-1 h-5 bg-rose-500 rounded-full"></span>
                    PPL dengan NTB Negatif Belum Selesai Terbanyak
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-xs uppercase text-slate-500 font-semibold border-y border-slate-200">
                        <tr>
                            <th class="px-4 py-2.5 text-left">PPL</th>
                            <th class="px-4 py-2.5 text-left">PML</th>
                            <th class="px-4 py-2.5 text-right">Total</th>
                            <th class="px-4 py-2.5 text-right">Selesai</th>
                            <th class="px-4 py-2.5 text-right">Belum</th>
                            <th class="px-4 py-2.5 text-left w-40">Progres</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($dash['byPpl'] as $r)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-2.5 font-medium text-slate-700">{{ $r['ppl'] }}</td>
                                <td class="px-4 py-2.5 text-slate-500">{{ $r['pml'] ?: '-' }}</td>
                                <td class="px-4 py-2.5 text-right tabular-nums">{{ $r['total'] }}</td>
                                <td class="px-4 py-2.5 text-right tabular-nums text-emerald-600">{{ $r['selesai'] }}</td>
                                <td class="px-4 py-2.5 text-right tabular-nums text-red-600 font-semibold">{{ $r['belum'] }}</td>
                                <td class="px-4 py-2.5">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 h-1.5 rounded-full bg-slate-100 overflow-hidden">
                                            <div class="h-full bg-emerald-500" style="width: {{ $r['persen'] }}%"></div>
                                        </div>
                                        <span class="text-xs text-slate-500 tabular-nums w-10 text-right">{{ $r['persen'] }}%</span>
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
