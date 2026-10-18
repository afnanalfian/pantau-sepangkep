@php
    $doughnut = fn (array $labels, array $data, array $colors) => [
        'type' => 'doughnut',
        'data' => ['labels' => $labels, 'datasets' => [['data' => $data, 'backgroundColor' => $colors, 'borderWidth' => 2, 'borderColor' => '#ffffff']]],
        'options' => [
            'responsive' => true, 'maintainAspectRatio' => false, 'cutout' => '65%',
            'plugins' => ['legend' => ['position' => 'bottom', 'labels' => ['usePointStyle' => true, 'padding' => 15]]],
        ],
    ];

    $cfgStatus = $doughnut(['Sudah Tindak Lanjut', 'Belum Tindak Lanjut'], [$dash['selesai'], $dash['belum']], ['#10B981', '#EF4444']);
    $cfgHasil = $doughnut(array_column($dash['hasil'], 'label'), array_column($dash['hasil'], 'count'), ['#10B981', '#3B82F6']);

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

    $chart = fn ($cfg) => 'if (window.Chart) { $el._c?.destroy(); $el._c = new Chart($el, ' . \Illuminate\Support\Js::from($cfg) . ') }';
    $kategoriNama = [
        'A' => 'Pertanian, Kehutanan & Perikanan', 'B' => 'Pertambangan & Penggalian', 'C' => 'Industri Pengolahan',
        'D' => 'Pengadaan Listrik & Gas', 'E' => 'Pengadaan Air & Pengelolaan Sampah', 'F' => 'Konstruksi',
        'G' => 'Perdagangan Besar & Eceran', 'H' => 'Transportasi & Pergudangan', 'I' => 'Akomodasi & Makan Minum',
        'J' => 'Informasi & Komunikasi', 'K' => 'Jasa Keuangan & Asuransi', 'L' => 'Real Estat',
        'M' => 'Jasa Profesional, Ilmiah & Teknis', 'N' => 'Jasa Persewaan & Penunjang Usaha', 'O' => 'Administrasi Pemerintahan',
        'P' => 'Jasa Pendidikan', 'Q' => 'Jasa Kesehatan & Sosial', 'R' => 'Kesenian, Hiburan & Rekreasi',
        'S' => 'Jasa Lainnya', 'T' => 'Jasa Rumah Tangga', 'U' => 'Badan Internasional',
    ];
@endphp

<div wire:key="fr3-dashboard-{{ $dash['total'] }}-{{ $dash['selesai'] }}">
    <!-- STATISTIK -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 mb-4">
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

    <!-- KARTU UB / UM (klik untuk lihat data) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 mb-4">
        @foreach($dash['byJenis'] as $kode => $j)
            <button type="button" wire:click="lihatJenis('{{ $kode }}')"
                    class="text-left bg-white rounded-xl border border-slate-200 p-4 shadow-sm hover:border-orange-300 hover:shadow-md transition">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-bold px-2 py-0.5 rounded {{ $j['badge'] }}">Anomali {{ $j['kode'] }} · {{ $kode }}</span>
                    <span class="text-xs text-orange-600 font-medium">Lihat data →</span>
                </div>
                <p class="text-sm font-semibold text-slate-700 mt-2">{{ $j['label'] }}</p>
                <div class="flex items-end justify-between mt-1">
                    <p class="text-xs text-slate-500">{{ number_format($j['selesai']) }} / {{ number_format($j['total']) }} selesai</p>
                    <p class="text-lg font-bold text-slate-800">{{ $j['persen'] }}%</p>
                </div>
                <div class="mt-2 h-1.5 w-full bg-slate-100 rounded-full overflow-hidden">
                    <div class="h-full bg-emerald-500 rounded-full" style="width: {{ $j['persen'] }}%"></div>
                </div>
            </button>
        @endforeach
    </div>

    <!-- HASIL PEMERIKSAAN -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 mb-6">
        <div class="flex items-center justify-between p-4 rounded-xl border-2 border-emerald-200 bg-emerald-50">
            <span class="text-sm font-medium text-slate-600">✅ {{ $dash['hasil']['sesuai_lapangan']['label'] }}</span>
            <span class="text-xl font-bold text-emerald-700">{{ number_format($dash['hasil']['sesuai_lapangan']['count']) }}</span>
        </div>
        <div class="flex items-center justify-between p-4 rounded-xl border-2 border-blue-200 bg-blue-50">
            <span class="text-sm font-medium text-slate-600">🛠️ {{ $dash['hasil']['diperbaiki']['label'] }}</span>
            <span class="text-xl font-bold text-blue-700">{{ number_format($dash['hasil']['diperbaiki']['count']) }}</span>
        </div>
    </div>

    @if($dash['total'] === 0)
        <div class="text-center py-10 bg-white rounded-xl border border-dashed border-slate-300 text-sm text-slate-400">Batch ini belum berisi data.</div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
                <h3 class="font-semibold text-slate-700 text-sm mb-3 flex items-center gap-2">
                    <span class="w-1 h-5 bg-emerald-500 rounded-full"></span> Status Tindak Lanjut
                </h3>
                <div class="relative" style="height: 250px;" wire:ignore>
                    <canvas x-data x-init="{{ $chart($cfgStatus) }}"></canvas>
                </div>
            </div>
            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
                <h3 class="font-semibold text-slate-700 text-sm mb-3 flex items-center gap-2">
                    <span class="w-1 h-5 bg-blue-500 rounded-full"></span> Hasil Pemeriksaan
                </h3>
                <div class="relative" style="height: 250px;" wire:ignore>
                    @if($dash['selesai'] > 0)
                        <canvas x-data x-init="{{ $chart($cfgHasil) }}"></canvas>
                    @else
                        <div class="h-full flex items-center justify-center text-sm text-slate-400">Belum ada yang ditindaklanjuti.</div>
                    @endif
                </div>
            </div>
        </div>

        <!-- TOP KBLI -->
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm mb-6">
            <h3 class="font-semibold text-slate-700 text-sm mb-3 flex items-center gap-2">
                <span class="w-1 h-5 bg-orange-500 rounded-full"></span> Top 10 KBLI
            </h3>
            <div class="relative" style="height: {{ max(200, count($dash['topKbli']) * 38 + 60) }}px;" wire:ignore>
                <canvas x-data x-init="{{ $chart($cfgKbli) }}"></canvas>
            </div>
        </div>

        <!-- PER KATEGORI -->
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm mb-6">
            <h3 class="font-semibold text-slate-700 text-sm mb-3 flex items-center gap-2">
                <span class="w-1 h-5 bg-purple-500 rounded-full"></span> Progress per Kategori
            </h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-xs uppercase text-slate-500 font-semibold">
                        <tr>
                            <th class="px-3 py-2 text-left">Kategori</th>
                            <th class="px-3 py-2 text-right">Total</th>
                            <th class="px-3 py-2 text-right">Selesai</th>
                            <th class="px-3 py-2 text-left w-1/3">Progress</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($dash['byKategori'] as $k)
                            <tr>
                                <td class="px-3 py-2 text-slate-700"><b>{{ $k['nama'] }}</b> <span class="text-slate-500 text-xs">{{ $kategoriNama[$k['nama']] ?? '' }}</span></td>
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
