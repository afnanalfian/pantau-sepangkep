{{--
    Kartu progress per kelompok (kecamatan, jalur pelaporan, dll): bar chart + tabel.
    Dipakai modul Pemindahan Pendapatan & Pendapatan 1 Juta ke Bawah.

    <x-pendapatan-progress title="..." :rows="[['nama'=>..,'total'=>..,'selesai'=>..,'persen'=>..], ...]" accent="bg-sky-500" />
--}}
@props(['title', 'rows' => [], 'accent' => 'bg-sky-500', 'label' => 'Kelompok', 'chart' => true])

@php
    $cfg = [
        'type' => 'bar',
        'data' => [
            'labels' => array_column($rows, 'nama'),
            'datasets' => [
                ['label' => 'Selesai', 'data' => array_column($rows, 'selesai'), 'backgroundColor' => '#10B981', 'borderRadius' => 4],
                ['label' => 'Total', 'data' => array_column($rows, 'total'), 'backgroundColor' => '#F59E0B', 'borderRadius' => 4],
            ],
        ],
        'options' => [
            'responsive' => true, 'maintainAspectRatio' => false, 'indexAxis' => 'y',
            'plugins' => ['legend' => ['position' => 'top', 'labels' => ['usePointStyle' => true]]],
            'scales' => [
                'x' => ['beginAtZero' => true, 'grid' => ['color' => 'rgba(0,0,0,0.05)']],
                'y' => ['grid' => ['display' => false]],
            ],
        ],
    ];
@endphp

<div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
    <h3 class="font-semibold text-slate-700 text-sm mb-3 flex items-center gap-2">
        <span class="w-1 h-5 {{ $accent }} rounded-full"></span> {{ $title }}
    </h3>

    @if(empty($rows))
        <p class="text-sm text-slate-400 py-6 text-center">Belum ada data.</p>
    @else
        @if($chart)
            <div class="relative" style="height: {{ max(160, count($rows) * 38 + 50) }}px;" wire:ignore>
                <canvas x-data x-init="if (window.Chart) { $el._c?.destroy(); $el._c = new Chart($el, {{ \Illuminate\Support\Js::from($cfg) }}) }"></canvas>
            </div>
        @endif

        <div class="overflow-x-auto {{ $chart ? 'mt-4' : '' }}">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-xs uppercase text-slate-500 font-semibold">
                    <tr>
                        <th class="px-3 py-2 text-left">{{ $label }}</th>
                        <th class="px-3 py-2 text-right">Total</th>
                        <th class="px-3 py-2 text-right">Selesai</th>
                        <th class="px-3 py-2 text-left w-1/3">Progress</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($rows as $r)
                        <tr>
                            <td class="px-3 py-2 text-slate-700">{{ $r['nama'] }}</td>
                            <td class="px-3 py-2 text-right text-slate-600">{{ number_format($r['total']) }}</td>
                            <td class="px-3 py-2 text-right text-emerald-600 font-medium">{{ number_format($r['selesai']) }}</td>
                            <td class="px-3 py-2">
                                <div class="flex items-center gap-2">
                                    <div class="flex-1 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                        <div class="h-full bg-emerald-500 rounded-full" style="width: {{ $r['persen'] }}%"></div>
                                    </div>
                                    <span class="text-xs text-slate-500 w-12 text-right">{{ $r['persen'] }}%</span>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
