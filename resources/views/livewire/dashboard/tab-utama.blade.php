<div wire:key="tab-utama">

<div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-2xl p-5 border border-slate-200">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Total Progres Pendataan</p>
        <p class="font-display font-bold text-3xl text-[#0B2A4A] mt-2">{{ $d['progresTotal'] }}%</p>
        <p class="text-xs text-slate-400 mt-1">{{ number_format($d['selesai']) }} / {{ number_format($d['totalRegion']) }} assignment</p>
    </div>
    <div class="bg-white rounded-2xl p-5 border border-slate-200">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">PPL Lolos Termin 2</p>
        <p class="font-display font-bold text-3xl text-emerald-600 mt-2">{{ $d['lolosTermin2'] }}</p>
        <p class="text-xs text-slate-400 mt-1">Progres individu mencapai 100%</p>
    </div>
    <div class="bg-white rounded-2xl p-5 border border-slate-200">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Realisasi PML</p>
        <p class="font-display font-bold text-3xl text-[#0F7B8A] mt-2">{{ $d['realisasiPml'] }}%</p>
        <p class="text-xs text-slate-400 mt-1">Selain Open, Draft & Submitted Pencacah</p>
    </div>
    <div class="bg-white rounded-2xl p-5 border border-slate-200">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Jumlah Open &amp; Draft</p>
        <p class="font-display font-bold text-3xl text-amber-600 mt-2">{{ number_format($d['jumlahOpenDraft']) }}</p>
        <p class="text-xs text-slate-400 mt-1">Assignment belum diproses</p>
    </div>
</div>

<div class="grid lg:grid-cols-3 gap-5">
    <div class="lg:col-span-2 bg-white rounded-2xl p-6 border border-slate-200">
        <h3 class="font-display font-bold text-[#0B2A4A] mb-4">Progres Pendataan menurut Kecamatan (%)</h3>
        <canvas id="chartKecamatan" height="110"></canvas>
    </div>
    <div class="bg-white rounded-2xl p-6 border border-slate-200">
        <h3 class="font-display font-bold text-[#0B2A4A] mb-4">Komposisi Progres Assignment</h3>
        <canvas id="chartKomposisi" height="180"></canvas>
    </div>
</div>

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
                backgroundColor: '#0F7B8A',
                borderRadius: 6,
            }]
        },
        options: {
            responsive: true,
            indexAxis: 'y',
            plugins: { legend: { display: false } },
            scales: { x: { max: 100 } }
        }
    });

    if (window._chartKomp) window._chartKomp.destroy();
    window._chartKomp = new Chart(document.getElementById('chartKomposisi'), {
        type: 'doughnut',
        data: {
            labels: komposisiData.map(k => k.label),
            datasets: [{
                data: komposisiData.map(k => k.value),
                backgroundColor: ['#0F7B8A','#E2A63B','#94A3B8','#1E8E5A','#D64545','#6366F1','#EC4899','#0B2A4A','#F59E0B','#10B981','#8B5CF6'],
            }]
        },
        options: { responsive: true, plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 9 } } } } }
    });
})();
</script>

</div>
