<div wire:key="tab-utama">

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
        <p class="text-[10px] sm:text-xs text-slate-400 mt-0.5">Selain Open & Draft</p>
    </div>
    <div class="bg-white rounded-xl sm:rounded-2xl p-4 sm:p-5 border border-slate-200 shadow-sm">
        <p class="text-[10px] sm:text-xs font-semibold text-slate-400 uppercase tracking-wide">Open & Draft</p>
        <p class="font-display font-bold text-2xl sm:text-3xl text-amber-600 mt-1">{{ number_format($d['jumlahOpenDraft']) }}</p>
        <p class="text-[10px] sm:text-xs text-slate-400 mt-0.5">Belum diproses</p>
    </div>
</div>

<!-- ============================================ -->
<!-- CHARTS -->
<!-- ============================================ -->
<div class="grid lg:grid-cols-3 gap-4 sm:gap-5">
    <div class="lg:col-span-2 bg-white rounded-xl sm:rounded-2xl p-4 sm:p-6 border border-slate-200 shadow-sm">
        <h3 class="font-display font-semibold text-slate-800 text-sm sm:text-base mb-3 sm:mb-4">Progres Pendataan menurut Kecamatan (%)</h3>
        <canvas id="chartKecamatan" height="110"></canvas>
    </div>
    <div class="bg-white rounded-xl sm:rounded-2xl p-4 sm:p-6 border border-slate-200 shadow-sm">
        <h3 class="font-display font-semibold text-slate-800 text-sm sm:text-base mb-3 sm:mb-4">Komposisi Progres Assignment</h3>
        <canvas id="chartKomposisi" height="180"></canvas>
    </div>
</div>

<!-- ============================================ -->
<!-- CHART SCRIPT -->
<!-- ============================================ -->
<script>
(function() {
    const kecData = @json($d['perKecamatan']);
    const komposisiData = @json($d['komposisi']);

    if (window._chartKec) window._chartKec.destroy();
    window._chartKec = new Chart(document.getElementById('chartKecamatan'), {
        type: 'bar',
        data: {
            labels: kecData.map(k => k.kecamatan),
            datasets: [{
                label: 'Progres (%)',
                data: kecData.map(k => k.progres),
                backgroundColor: '#F59E0B',
                borderRadius: 4,
            }]
        },
        options: {
            responsive: true,
            indexAxis: 'y',
            plugins: { legend: { display: false } },
            scales: { 
                x: { max: 100, grid: { display: false } },
                y: { grid: { display: false } }
            }
        }
    });

    if (window._chartKomp) window._chartKomp.destroy();
    window._chartKomp = new Chart(document.getElementById('chartKomposisi'), {
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
            plugins: { 
                legend: { 
                    position: 'bottom', 
                    labels: { 
                        boxWidth: 10, 
                        font: { size: 9 },
                        padding: 10
                    } 
                } 
            } 
        }
    });
})();
</script>

</div>