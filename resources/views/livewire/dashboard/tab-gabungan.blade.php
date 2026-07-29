<div wire:key="tab-gabungan">

<div class="grid sm:grid-cols-2 gap-5 mb-6">
    <div class="bg-white rounded-2xl p-7 border border-slate-200">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Persentase Tidak Ditemukan (Gabungan)</p>
        <p class="font-display font-bold text-4xl text-red-600 mt-3">{{ $d['persenTidakDitemukan'] }}%</p>
        <p class="text-xs text-slate-400 mt-2">Dari total {{ number_format($d['totalTidakDitemukan']) }} unit keluarga, usaha, dan usaha dalam keluarga selain berstatus Ditemukan &amp; Baru.</p>
    </div>
    <div class="bg-white rounded-2xl p-7 border border-slate-200">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Total Muatan</p>
        <p class="font-display font-bold text-4xl text-[#0F7B8A] mt-3">{{ number_format($d['muatan']) }}</p>
        <p class="text-xs text-slate-400 mt-2">Jumlah keluarga, usaha, dan usaha dalam keluarga berstatus Ditemukan &amp; Baru.</p>
    </div>
</div>

<div class="bg-white rounded-2xl p-6 border border-slate-200">
    <h3 class="font-display font-bold text-[#0B2A4A] mb-4">Komposisi Unit Terdata</h3>
    <canvas id="chartGabungan" height="90"></canvas>
</div>

<script>
(function() {
    if (window._chartGabungan) window._chartGabungan.destroy();
    window._chartGabungan = new Chart(document.getElementById('chartGabungan'), {
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
        options: { responsive: true, plugins: { legend: { display: false } } }
    });
})();
</script>

</div>
