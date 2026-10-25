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
    $chart = fn ($cfg) => 'if (window.Chart) { $el._c?.destroy(); $el._c = new Chart($el, ' . \Illuminate\Support\Js::from($cfg) . ') }';
@endphp

<div wire:key="ntb3-dashboard-{{ $dash['total'] }}-{{ $dash['selesai'] }}">
    <!-- PODIUM (paling atas) -->
    @include('livewire.ntb-flag3._podium', ['podium' => $dash['podium']])

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

        @foreach([
            ['Progress Penyelesaian per PIC', 'byPic', 'PIC', 'bg-amber-500', 'filterPic'],
            ['Progress Penyelesaian per Kecamatan', 'byKecamatan', 'Kecamatan', 'bg-sky-500', 'filterKecamatan'],
        ] as [$judul, $key, $kolom, $accent, $filter])
            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm mb-6">
                <h3 class="font-semibold text-slate-700 text-sm mb-1 flex items-center gap-2">
                    <span class="w-1 h-5 {{ $accent }} rounded-full"></span> {{ $judul }}
                </h3>
                <p class="text-xs text-slate-400 mb-3">Klik baris untuk membuka datanya.</p>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 text-xs uppercase text-slate-500 font-semibold">
                            <tr>
                                <th class="px-3 py-2 text-left">{{ $kolom }}</th>
                                <th class="px-3 py-2 text-right">Total</th>
                                <th class="px-3 py-2 text-right">Selesai</th>
                                <th class="px-3 py-2 text-right">Belum</th>
                                <th class="px-3 py-2 text-left w-1/3">Progress</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($dash[$key] as $r)
                                @php $bisaKlik = !str_starts_with($r['nama'], '('); @endphp
                                <tr @if($bisaKlik) class="hover:bg-slate-50 cursor-pointer" wire:click="lihat('{{ $filter }}', @js($r['nama']))" @endif>
                                    <td class="px-3 py-2 text-slate-700 font-medium">{{ $r['nama'] }}</td>
                                    <td class="px-3 py-2 text-right text-slate-600">{{ number_format($r['total']) }}</td>
                                    <td class="px-3 py-2 text-right text-emerald-600 font-medium">{{ number_format($r['selesai']) }}</td>
                                    <td class="px-3 py-2 text-right text-red-600">{{ number_format($r['total'] - $r['selesai']) }}</td>
                                    <td class="px-3 py-2">
                                        <div class="flex items-center gap-2">
                                            <div class="flex-1 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                                <div class="h-full {{ $r['persen'] >= 100 ? 'bg-emerald-500' : 'bg-amber-500' }} rounded-full" style="width: {{ $r['persen'] }}%"></div>
                                            </div>
                                            <span class="text-xs text-slate-500 w-12 text-right">{{ $r['persen'] }}%</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    @endif
</div>
