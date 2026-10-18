@php
    $cfgStatus = [
        'type' => 'doughnut',
        'data' => [
            'labels' => ['Sudah Diselesaikan', 'Belum Diselesaikan'],
            'datasets' => [['data' => [$dash['selesai'], $dash['belum']], 'backgroundColor' => ['#10B981', '#EF4444'], 'borderWidth' => 2, 'borderColor' => '#ffffff']],
        ],
        'options' => [
            'responsive' => true, 'maintainAspectRatio' => false, 'cutout' => '65%',
            'plugins' => ['legend' => ['position' => 'bottom', 'labels' => ['usePointStyle' => true, 'padding' => 15]]],
        ],
    ];
    $nd = $dash['nonDuplikat'];
    $dp = $dash['duplikat'];
    $bar = fn ($p, $cls = 'bg-emerald-500') => '<div class="mt-2 h-1.5 w-full bg-slate-100 rounded-full overflow-hidden"><div class="h-full ' . $cls . ' rounded-full" style="width: ' . $p . '%"></div></div>';
@endphp

<div wire:key="asn-dashboard-{{ $dash['total'] }}-{{ $dash['selesai'] }}">
    <!-- STATISTIK -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 mb-4">
        @foreach([
            ['Total ASN', number_format($dash['total']), 'text-slate-800'],
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

    <!-- DUPLIKAT vs TIDAK -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 mb-4">
        <button type="button" wire:click="lihat('filterDuplikat', 'ya')"
                class="text-left bg-white rounded-xl border-2 border-red-200 p-4 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold px-2 py-0.5 rounded bg-red-50 text-red-700 border border-red-200">DUPLIKAT · harus dihapus</span>
                <span class="text-xs text-orange-600 font-medium">Lihat data →</span>
            </div>
            <div class="flex items-end justify-between mt-2">
                <p class="text-sm text-slate-600">{{ number_format($dp['selesai']) }} / {{ number_format($dp['total']) }} sudah dihapus dari FASIH-SM</p>
                <p class="text-xl font-bold text-red-700">{{ $dp['persen'] }}%</p>
            </div>
            {!! $bar($dp['persen'], 'bg-red-500') !!}
        </button>

        <button type="button" wire:click="lihat('filterDuplikat', 'tidak')"
                class="text-left bg-white rounded-xl border-2 border-emerald-200 p-4 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-200">TIDAK DUPLIKAT</span>
                <span class="text-xs text-orange-600 font-medium">Lihat data →</span>
            </div>
            <div class="flex items-end justify-between mt-2">
                <p class="text-sm text-slate-600">{{ number_format($nd['selesai']) }} / {{ number_format($nd['total']) }} selesai</p>
                <p class="text-xl font-bold text-emerald-700">{{ $nd['persen'] }}%</p>
            </div>
            {!! $bar($nd['persen']) !!}
            <div class="flex gap-3 mt-2 text-xs">
                <span class="text-emerald-700">✅ Telah Didata: <b>{{ number_format($nd['didata']) }}</b></span>
                <span class="text-slate-700">🗑️ Telah Dihapus: <b>{{ number_format($nd['dihapus']) }}</b></span>
            </div>
        </button>
    </div>

    <!-- KELENGKAPAN IDENTITAS -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 mb-6">
        @foreach($dash['identitas'] as $k => $it)
            <button type="button" wire:click="lihat('filterIdentitas', '{{ $k }}')"
                    class="text-left p-3 rounded-xl border bg-white hover:shadow-md transition {{ $k === 'lengkap' ? 'border-slate-200' : 'border-amber-200' }}">
                <p class="text-[11px] font-medium text-slate-500">{{ $it['label'] }}</p>
                <p class="text-lg font-bold {{ $k === 'lengkap' ? 'text-slate-800' : 'text-amber-700' }}">{{ number_format($it['count']) }}</p>
            </button>
        @endforeach
    </div>

    @if($dash['total'] === 0)
        <div class="text-center py-10 bg-white rounded-xl border border-dashed border-slate-300 text-sm text-slate-400">Batch ini belum berisi data.</div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
                <h3 class="font-semibold text-slate-700 text-sm mb-3 flex items-center gap-2">
                    <span class="w-1 h-5 bg-emerald-500 rounded-full"></span> Status Penyelesaian
                </h3>
                <div class="relative" style="height: 240px;" wire:ignore>
                    <canvas x-data x-init="if (window.Chart) { $el._c?.destroy(); $el._c = new Chart($el, {{ \Illuminate\Support\Js::from($cfgStatus) }}) }"></canvas>
                </div>
            </div>

            <!-- PER KECAMATAN -->
            <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
                <h3 class="font-semibold text-slate-700 text-sm mb-3 flex items-center gap-2">
                    <span class="w-1 h-5 bg-sky-500 rounded-full"></span> Progress per Kecamatan
                </h3>
                <div class="overflow-x-auto max-h-[300px] overflow-y-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 text-xs uppercase text-slate-500 font-semibold sticky top-0">
                            <tr>
                                <th class="px-3 py-2 text-left">Kecamatan</th>
                                <th class="px-3 py-2 text-right">Total</th>
                                <th class="px-3 py-2 text-right">Selesai</th>
                                <th class="px-3 py-2 text-left w-1/3">Progress</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($dash['byKecamatan'] as $k)
                                <tr class="hover:bg-slate-50 cursor-pointer" wire:click="lihat('filterKecamatan', @js($k['nama']))">
                                    <td class="px-3 py-2 text-slate-700">{{ $k['nama'] }}</td>
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
        </div>

        <!-- PER INSTANSI -->
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm mb-6">
            <h3 class="font-semibold text-slate-700 text-sm mb-1 flex items-center gap-2">
                <span class="w-1 h-5 bg-purple-500 rounded-full"></span> Progress per Instansi
            </h3>
            <p class="text-xs text-slate-400 mb-3">Klik baris untuk membuka data instansi tersebut.</p>
            <div class="overflow-x-auto max-h-[420px] overflow-y-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-xs uppercase text-slate-500 font-semibold sticky top-0">
                        <tr>
                            <th class="px-3 py-2 text-left">Instansi</th>
                            <th class="px-3 py-2 text-right">Total</th>
                            <th class="px-3 py-2 text-right">Selesai</th>
                            <th class="px-3 py-2 text-left w-1/4">Progress</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($dash['byInstansi'] as $k)
                            @php $na = $k['nilai'] === \App\Models\MatchingAsnMikro::FILTER_INSTANSI_NA; @endphp
                            <tr class="hover:bg-slate-50 cursor-pointer {{ $na ? 'bg-amber-50/60' : '' }}" wire:click="lihat('filterInstansi', @js($k['nilai']))">
                                <td class="px-3 py-2 {{ $na ? 'text-amber-800 font-semibold' : 'text-slate-700' }}">{{ $k['nama'] }}</td>
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
