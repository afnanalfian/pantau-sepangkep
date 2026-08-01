@php
    $gabunganKey = md5(json_encode([$d['keluargaTotal'], $d['usahaTotal'], $d['ukdkTotal']]));
@endphp

<div wire:key="tab-gabungan">

@include('livewire.dashboard._filter-bar', [
    'showSearch' => false,
    'showPerPage' => false,
])

<div class="grid sm:grid-cols-2 gap-4 sm:gap-5 mb-5 sm:mb-6">
    <div class="bg-white rounded-xl sm:rounded-2xl p-5 sm:p-7 border border-slate-200 shadow-sm">
        <p class="text-[10px] sm:text-xs font-semibold text-slate-400 uppercase tracking-wide">Persentase Tidak Ditemukan (Gabungan)</p>
        <p class="font-display font-bold text-3xl sm:text-4xl text-red-600 mt-3">{{ $d['persenTidakDitemukan'] }}%</p>
        <p class="text-xs text-slate-400 mt-2">Dari total {{ number_format($d['totalTidakDitemukan']) }} unit keluarga, usaha, dan usaha dalam keluarga selain berstatus Ditemukan &amp; Baru.</p>
    </div>
    <div class="bg-white rounded-xl sm:rounded-2xl p-5 sm:p-7 border border-slate-200 shadow-sm">
        <p class="text-[10px] sm:text-xs font-semibold text-slate-400 uppercase tracking-wide">Total Muatan</p>
        <p class="font-display font-bold text-3xl sm:text-4xl text-[#0F7B8A] mt-3">{{ number_format($d['muatan']) }}</p>
        <p class="text-xs text-slate-400 mt-2">Jumlah keluarga, usaha, dan usaha dalam keluarga berstatus Ditemukan &amp; Baru.</p>
    </div>
</div>

<div class="bg-white rounded-xl sm:rounded-2xl p-4 sm:p-6 border border-slate-200 shadow-sm" wire:key="chart-gabungan-{{ $gabunganKey }}">
    <h3 class="font-display font-bold text-[#0B2A4A] text-sm sm:text-base mb-4">Komposisi Unit Terdata</h3>
    <div class="relative w-full" style="height: 260px;">
        <canvas id="chartGabungan"></canvas>
    </div>
</div>

<script>
(function () {
    if (typeof Chart === 'undefined') return;
    const canvas = document.getElementById('chartGabungan');
    if (window._chartGabungan) { window._chartGabungan.destroy(); window._chartGabungan = null; }
    if (!canvas) return;

    window._chartGabungan = new Chart(canvas, {
        type: 'bar',
        data: {
            labels: ['Keluarga', 'Usaha', 'Usaha dalam Keluarga'],
            datasets: [{
                label: 'Total unit terproses',
                data: [{{ $d['keluargaTotal'] }}, {{ $d['usahaTotal'] }}, {{ $d['ukdkTotal'] }}],
                backgroundColor: ['#0F7B8A', '#E2A63B', '#6366F1'],
                borderRadius: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 10 } } },
                y: { grid: { color: 'rgba(0,0,0,0.05)' }, ticks: { font: { size: 10 } } }
            }
        }
    });
})();
</script>

</div>
