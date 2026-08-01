@php
    // Batasi jumlah batang supaya grafik tetap terbaca kalau di-drill sampai SLS.
    $chartWilayah = collect($d['perWilayah'])->take(30)->values();
    // Tinggi kanvas dihitung dari jumlah kategori: 1 baris = 30px (min 240px).
    // Ini yang bikin semua kecamatan muncul di HP, tidak lagi terpotong.
    $chartHeight = max(240, $chartWilayah->count() * 30 + 30);
    // Key ikut berubah saat data berubah -> node dibuat ulang -> chart digambar ulang.
    $chartKey = md5($chartWilayah->toJson() . json_encode($d['komposisi']));
@endphp

<div wire:key="tab-utama">

@include('livewire.dashboard._filter-bar', [
    'showSearch' => false,
    'showPerPage' => false,
])

<!-- ============================================ -->
<!-- STATS CARDS -->
<!-- ============================================ -->
<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 mb-5 sm:mb-6">
    <div class="bg-white rounded-xl sm:rounded-2xl p-4 sm:p-5 border border-slate-200 shadow-sm">
        <p class="text-[10px] sm:text-xs font-semibold text-slate-400 uppercase tracking-wide">Total Progres</p>
        <p class="font-display font-bold text-2xl sm:text-3xl text-slate-900 mt-1">{{ $d['progresTotal'] }}%</p>
        <p class="text-[10px] sm:text-xs text-slate-400 mt-0.5">{{ number_format($d['selesai']) }} / {{ number_format($d['totalRegion']) }}</p>
    </div>
    <div class="bg-white rounded-xl sm:rounded-2xl p-4 sm:p-5 border border-slate-200 shadow-sm">
        <p class="text-[10px] sm:text-xs font-semibold text-slate-400 uppercase tracking-wide">PPL Lolos Termin 2</p>
        <p class="font-display font-bold text-2xl sm:text-3xl text-emerald-600 mt-1">{{ $d['lolosTermin2'] }}</p>
        <p class="text-[10px] sm:text-xs text-slate-400 mt-0.5">Progres 100%</p>
    </div>
    <div class="bg-white rounded-xl sm:rounded-2xl p-4 sm:p-5 border border-slate-200 shadow-sm">
        <p class="text-[10px] sm:text-xs font-semibold text-slate-400 uppercase tracking-wide">Realisasi PML</p>
        <p class="font-display font-bold text-2xl sm:text-3xl text-orange-600 mt-1">{{ $d['realisasiPml'] }}%</p>
        <p class="text-[10px] sm:text-xs text-slate-400 mt-0.5">Selain Open &amp; Draft</p>
    </div>
    <div class="bg-white rounded-xl sm:rounded-2xl p-4 sm:p-5 border border-slate-200 shadow-sm">
        <p class="text-[10px] sm:text-xs font-semibold text-slate-400 uppercase tracking-wide">Open &amp; Draft</p>
        <p class="font-display font-bold text-2xl sm:text-3xl text-amber-600 mt-1">{{ number_format($d['jumlahOpenDraft']) }}</p>
        <p class="text-[10px] sm:text-xs text-slate-400 mt-0.5">Belum diproses</p>
    </div>
</div>

<!-- ============================================ -->
<!-- CHARTS -->
<!-- ============================================ -->
<div class="grid lg:grid-cols-3 gap-4 sm:gap-5" wire:key="charts-{{ $chartKey }}">

    <!-- Progres per wilayah -->
    <div class="lg:col-span-2 bg-white rounded-xl sm:rounded-2xl p-4 sm:p-6 border border-slate-200 shadow-sm">
        <div class="flex items-baseline justify-between mb-3 sm:mb-4 gap-2">
            <h3 class="font-display font-semibold text-slate-800 text-sm sm:text-base">
                Progres Pendataan menurut {{ $d['labelWilayah'] }} (%)
            </h3>
            <span class="text-[10px] text-slate-400 whitespace-nowrap">{{ $chartWilayah->count() }} wilayah</span>
        </div>

        @if($chartWilayah->isEmpty())
            <p class="text-sm text-slate-400 py-8 text-center">Tidak ada data untuk filter ini.</p>
        @else
            <!-- Tinggi dinamis + scroll vertikal khusus mobile: semua wilayah pasti tampil -->
            <div class="w-full overflow-y-auto max-h-[70vh] sm:max-h-none -mx-1 px-1">
                <div style="height: {{ $chartHeight }}px; min-height: 240px;" class="relative w-full">
                    <canvas id="chartKecamatan"></canvas>
                </div>
            </div>
        @endif
    </div>

    <!-- Komposisi status -->
    <div class="bg-white rounded-xl sm:rounded-2xl p-4 sm:p-6 border border-slate-200 shadow-sm">
        <h3 class="font-display font-semibold text-slate-800 text-sm sm:text-base mb-3 sm:mb-4">Komposisi Progres Assignment</h3>
        <div class="relative w-full" style="height: 300px;">
            <canvas id="chartKomposisi"></canvas>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- CHART SCRIPT -->
<!-- ============================================ -->
<script>
(function () {
    if (typeof Chart === 'undefined') return;

    const wilayahData = @json($chartWilayah);
    const komposisiData = @json($d['komposisi']);
    const isMobile = window.matchMedia('(max-width: 640px)').matches;

    const warnaProgres = (v) => v >= 80 ? '#10B981' : (v >= 40 ? '#F59E0B' : '#EF4444');

    // Plugin kecil: tulis angka persen di ujung batang (biar kebaca di HP
    // walaupun sumbu X disembunyikan).
    const labelDiUjungBatang = {
        id: 'labelDiUjungBatang',
        afterDatasetsDraw(chart) {
            const { ctx } = chart;
            ctx.save();
            ctx.font = '600 10px Inter, sans-serif';
            ctx.fillStyle = '#475569';
            ctx.textBaseline = 'middle';
            chart.getDatasetMeta(0).data.forEach((bar, i) => {
                const val = chart.data.datasets[0].data[i];
                ctx.fillText(val + '%', bar.x + 6, bar.y);
            });
            ctx.restore();
        }
    };

    const canvasKec = document.getElementById('chartKecamatan');
    if (window._chartKec) { window._chartKec.destroy(); window._chartKec = null; }
    if (canvasKec && wilayahData.length) {
        window._chartKec = new Chart(canvasKec, {
            type: 'bar',
            data: {
                labels: wilayahData.map(k => k.wilayah),
                datasets: [{
                    label: 'Progres (%)',
                    data: wilayahData.map(k => k.progres),
                    backgroundColor: wilayahData.map(k => warnaProgres(k.progres)),
                    borderRadius: 4,
                    barPercentage: 0.75,
                    categoryPercentage: 0.85,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,   // <- kunci: ikut tinggi container, bukan rasio
                indexAxis: 'y',
                layout: { padding: { right: 40 } },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (c) => {
                                const d = wilayahData[c.dataIndex];
                                return ` ${d.progres}%  (${d.selesai.toLocaleString('id-ID')} / ${d.total.toLocaleString('id-ID')})`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        min: 0, max: 100,
                        grid: { display: false },
                        ticks: { display: !isMobile, callback: (v) => v + '%' },
                        border: { display: false },
                    },
                    y: {
                        grid: { display: false },
                        border: { display: false },
                        ticks: {
                            autoSkip: false,                 // <- semua label wajib tampil
                            font: { size: isMobile ? 9 : 11 },
                            padding: 2,
                            callback: function (value) {
                                const label = this.getLabelForValue(value) || '';
                                const maks = isMobile ? 14 : 26;
                                return label.length > maks ? label.slice(0, maks - 1) + '…' : label;
                            }
                        }
                    }
                }
            },
            plugins: [labelDiUjungBatang]
        });
    }

    const canvasKomp = document.getElementById('chartKomposisi');
    if (window._chartKomp) { window._chartKomp.destroy(); window._chartKomp = null; }
    if (canvasKomp && komposisiData.length) {
        window._chartKomp = new Chart(canvasKomp, {
            type: 'doughnut',
            data: {
                labels: komposisiData.map(k => k.label),
                datasets: [{
                    data: komposisiData.map(k => k.value),
                    backgroundColor: ['#F59E0B','#1E293B','#94A3B8','#10B981','#EF4444','#6366F1','#EC4899','#0F172A','#FCD34D','#34D399','#8B5CF6'],
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 10, font: { size: 9 }, padding: 8 }
                    }
                }
            }
        });
    }
})();
</script>

</div>
