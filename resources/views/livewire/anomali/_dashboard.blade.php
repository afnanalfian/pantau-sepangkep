<div wire:key="anomali-dash">

<!-- ============================================ -->
<!-- STATS CARDS -->
<!-- ============================================ -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4 mb-5 sm:mb-6">
    <div class="bg-white rounded-xl sm:rounded-2xl p-4 sm:p-5 border border-slate-200 shadow-sm">
        <p class="text-[10px] sm:text-xs font-semibold text-slate-400 uppercase tracking-wide">Total Kasus Anomali</p>
        <p class="font-display font-bold text-2xl sm:text-3xl text-slate-900 mt-1 sm:mt-2">{{ number_format($d['total']) }}</p>
    </div>
    <div class="bg-white rounded-xl sm:rounded-2xl p-4 sm:p-5 border border-slate-200 shadow-sm">
        <p class="text-[10px] sm:text-xs font-semibold text-slate-400 uppercase tracking-wide">Sudah Tindak Lanjut</p>
        <p class="font-display font-bold text-2xl sm:text-3xl text-emerald-600 mt-1 sm:mt-2">{{ number_format($d['selesai']) }}</p>
    </div>
    <div class="bg-white rounded-xl sm:rounded-2xl p-4 sm:p-5 border border-slate-200 shadow-sm">
        <p class="text-[10px] sm:text-xs font-semibold text-slate-400 uppercase tracking-wide">Persentase Penyelesaian</p>
        <p class="font-display font-bold text-2xl sm:text-3xl text-orange-600 mt-1 sm:mt-2">{{ $d['total'] > 0 ? round($d['selesai'] / $d['total'] * 100, 1) : 0 }}%</p>
    </div>
</div>

<!-- ============================================ -->
<!-- CHARTS - Grid -->
<!-- ============================================ -->
<div class="grid lg:grid-cols-2 gap-4 sm:gap-5 mb-5">
    <div class="bg-white rounded-xl sm:rounded-2xl p-4 sm:p-6 border border-slate-200 shadow-sm">
        <h3 class="font-display font-semibold text-slate-800 text-sm sm:text-base mb-3 sm:mb-4">Kasus berdasarkan Jenis</h3>
        <canvas id="chartJenis" height="140"></canvas>
    </div>
    <div class="bg-white rounded-xl sm:rounded-2xl p-4 sm:p-6 border border-slate-200 shadow-sm">
        <h3 class="font-display font-semibold text-slate-800 text-sm sm:text-base mb-3 sm:mb-4">Status Tindak Lanjut</h3>
        <canvas id="chartStatus" height="140"></canvas>
    </div>
</div>

<!-- ============================================ -->
<!-- TOP ANOMALI CHART -->
<!-- ============================================ -->
<div class="bg-white rounded-xl sm:rounded-2xl p-4 sm:p-6 border border-slate-200 shadow-sm mb-5">
    <h3 class="font-display font-semibold text-slate-800 text-sm sm:text-base mb-3 sm:mb-4">Kasus berdasarkan Jenis Anomali (Top 10)</h3>
    <canvas id="chartAnomali" height="90"></canvas>
</div>

<!-- ============================================ -->
<!-- KECAMATAN TABLE -->
<!-- ============================================ -->
<div class="bg-white rounded-xl sm:rounded-2xl p-4 sm:p-6 border border-slate-200 shadow-sm">
    <h3 class="font-display font-semibold text-slate-800 text-sm sm:text-base mb-3 sm:mb-4">Monitoring Penyelesaian per Kecamatan</h3>
    
    <!-- Mobile: Card View -->
    <div class="sm:hidden space-y-3">
        @forelse($d['byKecamatan'] as $r)
            <div class="bg-slate-50 rounded-lg p-3">
                <div class="flex items-center justify-between">
                    <span class="font-semibold text-slate-700 text-sm">{{ $r['kecamatan'] }}</span>
                    <span class="text-xs font-bold px-2 py-0.5 rounded-full
                        {{ $r['persen'] >= 80 ? 'bg-emerald-50 text-emerald-700' : ($r['persen'] >= 40 ? 'bg-amber-50 text-amber-700' : 'bg-red-50 text-red-700') }}">
                        {{ $r['persen'] }}%
                    </span>
                </div>
                <div class="flex items-center gap-4 mt-1 text-xs text-slate-500">
                    <span>Total: {{ $r['total'] }}</span>
                    <span>Selesai: {{ $r['selesai'] }}</span>
                </div>
            </div>
        @empty
            <p class="text-center text-slate-400 text-sm py-4">Belum ada data.</p>
        @endforelse
    </div>

    <!-- Desktop: Table View -->
    <div class="hidden sm:block overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-xs uppercase text-slate-500 font-semibold border-b border-slate-200">
                <tr>
                    <th class="px-4 py-3 text-left">Kecamatan</th>
                    <th class="px-4 py-3 text-center">Total Kasus</th>
                    <th class="px-4 py-3 text-center">Selesai</th>
                    <th class="px-4 py-3 text-center">Persentase</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($d['byKecamatan'] as $r)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-4 py-3 font-semibold text-slate-700">{{ $r['kecamatan'] }}</td>
                        <td class="px-4 py-3 text-center text-slate-600">{{ $r['total'] }}</td>
                        <td class="px-4 py-3 text-center text-slate-600">{{ $r['selesai'] }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-1 rounded-full text-xs font-bold
                                {{ $r['persen'] >= 80 ? 'bg-emerald-50 text-emerald-700' : ($r['persen'] >= 40 ? 'bg-amber-50 text-amber-700' : 'bg-red-50 text-red-700') }}">
                                {{ $r['persen'] }}%
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-10 text-center text-slate-400">Belum ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- ============================================ -->
<!-- CHARTS SCRIPT -->
<!-- ============================================ -->
<script>
(function() {
    const jenis = @json($d['byJenis']);
    const status = @json($d['byStatus']);
    const anomali = @json($d['byAnomali']);

    if (window._chartJenis) window._chartJenis.destroy();
    window._chartJenis = new Chart(document.getElementById('chartJenis'), {
        type: 'doughnut',
        data: {
            labels: Object.keys(jenis).map(k => k === 'usaha' ? 'Usaha' : 'Keluarga'),
            datasets: [{ data: Object.values(jenis), backgroundColor: ['#F59E0B', '#1E293B'] }]
        },
        options: { responsive: true, plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, padding: 15 } } } }
    });

    if (window._chartStatus) window._chartStatus.destroy();
    window._chartStatus = new Chart(document.getElementById('chartStatus'), {
        type: 'doughnut',
        data: {
            labels: Object.keys(status).map(k => k === 'sudah' ? 'Sudah Tindak Lanjut' : 'Belum Tindak Lanjut'),
            datasets: [{ data: Object.values(status), backgroundColor: ['#10B981', '#EF4444'] }]
        },
        options: { responsive: true, plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, padding: 15 } } } }
    });

    if (window._chartAnomali) window._chartAnomali.destroy();
    window._chartAnomali = new Chart(document.getElementById('chartAnomali'), {
        type: 'bar',
        data: {
            labels: Object.keys(anomali),
            datasets: [{ label: 'Jumlah Kasus', data: Object.values(anomali), backgroundColor: '#F59E0B', borderRadius: 4 }]
        },
        options: { 
            responsive: true, 
            indexAxis: 'y', 
            plugins: { legend: { display: false } },
            scales: { x: { grid: { display: false } }, y: { grid: { display: false } } }
        }
    });
})();
</script>

</div>