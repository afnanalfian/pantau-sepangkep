@php
    $total = $dash['total'];
    $selesai = $dash['selesai'];
    $belum = $total - $selesai;
    $progress = $total > 0 ? round($selesai / $total * 100, 1) : 0;

    $palette = ['#F59E0B', '#F97316', '#EF4444', '#EC4899', '#8B5CF6', '#3B82F6', '#10B981', '#14B8A6', '#64748B', '#84CC16'];

    $legendBottom = ['position' => 'bottom', 'labels' => ['padding' => 15, 'usePointStyle' => true, 'pointStyle' => 'circle', 'font' => ['size' => 12]]];

    // 1. Status tindak lanjut
    $cfgStatus = [
        'type' => 'doughnut',
        'data' => [
            'labels' => ['Sudah Ditangani', 'Belum Ditangani'],
            'datasets' => [[
                'data' => [$dash['byStatus']['sudah'], $dash['byStatus']['belum']],
                'backgroundColor' => ['#10B981', '#EF4444'],
                'borderWidth' => 2, 'borderColor' => '#ffffff',
            ]],
        ],
        'options' => ['responsive' => true, 'maintainAspectRatio' => false, 'cutout' => '65%', 'plugins' => ['legend' => $legendBottom]],
    ];

    // 2. Status perbaikan (kolom dari excel)
    $sp = collect($dash['byStatusPerbaikan']);
    $cfgPerbaikan = [
        'type' => 'doughnut',
        'data' => [
            'labels' => $sp->keys()->values(),
            'datasets' => [[
                'data' => $sp->values(),
                'backgroundColor' => $sp->keys()->values()->map(fn ($_, $i) => $palette[$i % count($palette)]),
                'borderWidth' => 2, 'borderColor' => '#ffffff',
            ]],
        ],
        'options' => ['responsive' => true, 'maintainAspectRatio' => false, 'cutout' => '65%', 'plugins' => ['legend' => $legendBottom]],
    ];

    // 3. Top 10 variabel missing
    $bv = collect($dash['byVariabel']);
    $cfgVariabel = [
        'type' => 'bar',
        'data' => [
            'labels' => $bv->keys()->values(),
            'datasets' => [[
                'label' => 'Jumlah Kasus',
                'data' => $bv->values(),
                'backgroundColor' => $bv->keys()->values()->map(fn ($_, $i) => $palette[$i % count($palette)]),
                'borderRadius' => 4,
            ]],
        ],
        'options' => [
            'responsive' => true, 'maintainAspectRatio' => false, 'indexAxis' => 'y',
            'plugins' => ['legend' => ['display' => false]],
            'scales' => ['x' => ['beginAtZero' => true, 'grid' => ['display' => false]], 'y' => ['grid' => ['display' => false]]],
        ],
    ];

    // 4. Distribusi skor kemiripan DTSEN
    $sk = collect($dash['skorBuckets']);
    $cfgSkor = [
        'type' => 'bar',
        'data' => [
            'labels' => $sk->keys()->values(),
            'datasets' => [[
                'label' => 'Jumlah',
                'data' => $sk->values(),
                'backgroundColor' => ['#EF4444', '#F59E0B', '#3B82F6', '#10B981', '#94A3B8'],
                'borderRadius' => 4,
            ]],
        ],
        'options' => [
            'responsive' => true, 'maintainAspectRatio' => false,
            'plugins' => ['legend' => ['display' => false]],
            'scales' => ['y' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]], 'x' => ['grid' => ['display' => false]]],
        ],
    ];

    // 5. Progress per kecamatan (stacked: selesai + belum)
    $kc = collect($dash['byKecamatan']);
    $cfgKecamatan = [
        'type' => 'bar',
        'data' => [
            'labels' => $kc->map(fn ($k) => $k['kecamatan'] . ' (' . $k['persen'] . '%)')->values(),
            'datasets' => [
                ['label' => 'Selesai', 'data' => $kc->pluck('selesai')->values(), 'backgroundColor' => '#10B981', 'borderRadius' => 4],
                ['label' => 'Belum', 'data' => $kc->pluck('belum')->values(), 'backgroundColor' => '#F59E0B', 'borderRadius' => 4],
            ],
        ],
        'options' => [
            'responsive' => true, 'maintainAspectRatio' => false, 'indexAxis' => 'y',
            'plugins' => ['legend' => ['position' => 'top', 'labels' => ['usePointStyle' => true, 'pointStyle' => 'circle', 'padding' => 15, 'font' => ['size' => 11]]]],
            'scales' => [
                'x' => ['stacked' => true, 'beginAtZero' => true, 'grid' => ['color' => 'rgba(0,0,0,0.05)']],
                'y' => ['stacked' => true, 'grid' => ['display' => false]],
            ],
        ],
    ];

    $charts = [
        'status' => $cfgStatus,
        'perbaikan' => $cfgPerbaikan,
        'variabel' => $cfgVariabel,
        'skor' => $cfgSkor,
        'kecamatan' => $cfgKecamatan,
    ];
@endphp

<div wire:key="mv-dashboard-{{ $batch->id }}">
    <!-- ============================================ -->
    <!-- STATISTIK CARDS -->
    <!-- ============================================ -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 mb-4">
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-400 uppercase tracking-wide">Total Missing</p>
                    <p class="text-2xl font-bold text-slate-800 mt-1">{{ number_format($total) }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-orange-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18M10 3v18M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-400 uppercase tracking-wide">Selesai</p>
                    <p class="text-2xl font-bold text-emerald-600 mt-1">{{ number_format($selesai) }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-400 uppercase tracking-wide">Belum</p>
                    <p class="text-2xl font-bold text-red-600 mt-1">{{ number_format($belum) }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-400 uppercase tracking-wide">Progress</p>
                    <p class="text-2xl font-bold text-orange-600 mt-1">{{ $progress }}%</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-orange-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Progress bar + info tambahan -->
    <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm mb-6">
        <div class="flex flex-wrap items-center justify-between gap-2 text-xs text-slate-500 mb-2">
            <span>Progres penyelesaian: <b class="text-slate-700">{{ number_format($selesai) }}</b> dari <b class="text-slate-700">{{ number_format($total) }}</b></span>
            <span class="flex flex-wrap gap-3">
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
        <!-- CHART ROW 1 - Doughnut -->
        <!-- ============================================ -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
                <h3 class="font-semibold text-slate-700 text-sm mb-3 flex items-center gap-2">
                    <span class="w-1 h-5 bg-emerald-500 rounded-full"></span>
                    Status Tindak Lanjut
                </h3>
                @include('livewire.missing-value._chart', ['key' => 'status', 'cfg' => $charts['status'], 'height' => 250])
            </div>

            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
                <h3 class="font-semibold text-slate-700 text-sm mb-3 flex items-center gap-2">
                    <span class="w-1 h-5 bg-orange-500 rounded-full"></span>
                    Status Perbaikan (dari Excel)
                </h3>
                @include('livewire.missing-value._chart', ['key' => 'perbaikan', 'cfg' => $charts['perbaikan'], 'height' => 250])
            </div>
        </div>

        <!-- ============================================ -->
        <!-- CHART ROW 2 - Variabel & Skor -->
        <!-- ============================================ -->
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-4 mb-6">
            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm lg:col-span-3">
                <h3 class="font-semibold text-slate-700 text-sm mb-3 flex items-center gap-2">
                    <span class="w-1 h-5 bg-purple-500 rounded-full"></span>
                    Top 10 Variabel Missing
                </h3>
                @include('livewire.missing-value._chart', ['key' => 'variabel', 'cfg' => $charts['variabel'], 'height' => 300])
            </div>

            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm lg:col-span-2">
                <h3 class="font-semibold text-slate-700 text-sm mb-3 flex items-center gap-2">
                    <span class="w-1 h-5 bg-sky-500 rounded-full"></span>
                    Skor Kemiripan DTSEN
                </h3>
                @include('livewire.missing-value._chart', ['key' => 'skor', 'cfg' => $charts['skor'], 'height' => 300])
            </div>
        </div>

        <!-- ============================================ -->
        <!-- CHART ROW 3 - Kecamatan -->
        <!-- ============================================ -->
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm mb-6">
            <h3 class="font-semibold text-slate-700 text-sm mb-3 flex items-center gap-2">
                <span class="w-1 h-5 bg-blue-500 rounded-full"></span>
                Progress Penyelesaian per Kecamatan
            </h3>
            @include('livewire.missing-value._chart', ['key' => 'kecamatan', 'cfg' => $charts['kecamatan'], 'height' => max(200, count($dash['byKecamatan']) * 40 + 50)])
        </div>

        <!-- ============================================ -->
        <!-- STATUS PENYELESAIAN (METODE) -->
        <!-- ============================================ -->
        @if(count($dash['byStatusPenyelesaian']) > 0)
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm mb-6">
            <h3 class="font-semibold text-slate-700 text-sm mb-3 flex items-center gap-2">
                <span class="w-1 h-5 bg-indigo-500 rounded-full"></span>
                Metode Penyelesaian
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                @foreach($dash['byStatusPenyelesaian'] as $status => $data)
                    @php
                        $colors = [
                            'revoked_pml' => 'border-blue-200 bg-blue-50',
                            'diselesaikan_admin' => 'border-emerald-200 bg-emerald-50',
                            'reject_admin' => 'border-red-200 bg-red-50',
                        ];
                        $textColors = [
                            'revoked_pml' => 'text-blue-700',
                            'diselesaikan_admin' => 'text-emerald-700',
                            'reject_admin' => 'text-red-700',
                        ];
                        $icons = ['revoked_pml' => '🔄', 'diselesaikan_admin' => '✅', 'reject_admin' => '❌'];
                    @endphp
                    <div class="flex items-center justify-between p-4 rounded-lg border-2 {{ $colors[$status] ?? 'border-slate-200 bg-slate-50' }}">
                        <div>
                            <span class="text-lg">{{ $icons[$status] ?? '📊' }}</span>
                            <span class="text-sm font-medium text-slate-600 ml-2">{{ $data['label'] }}</span>
                        </div>
                        <span class="text-xl font-bold {{ $textColors[$status] ?? 'text-slate-800' }}">{{ $data['count'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- ============================================ -->
        <!-- REKAP PER PPL -->
        <!-- ============================================ -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-4 pb-3">
                <h3 class="font-semibold text-slate-700 text-sm flex items-center gap-2">
                    <span class="w-1 h-5 bg-rose-500 rounded-full"></span>
                    PPL dengan Missing Value Belum Selesai Terbanyak
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
