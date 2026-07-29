<div wire:key="anomali-dash">

<div class="grid sm:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-2xl p-5 border border-slate-200">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Total Kasus Anomali</p>
        <p class="font-display font-bold text-3xl text-[#0B2A4A] mt-2">{{ number_format($d['total']) }}</p>
    </div>
    <div class="bg-white rounded-2xl p-5 border border-slate-200">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Sudah Tindak Lanjut</p>
        <p class="font-display font-bold text-3xl text-emerald-600 mt-2">{{ number_format($d['selesai']) }}</p>
    </div>
    <div class="bg-white rounded-2xl p-5 border border-slate-200">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Persentase Penyelesaian</p>
        <p class="font-display font-bold text-3xl text-[#0F7B8A] mt-2">{{ $d['total'] > 0 ? round($d['selesai'] / $d['total'] * 100, 1) : 0 }}%</p>
    </div>
</div>

<div class="grid lg:grid-cols-2 gap-5 mb-5">
    <div class="bg-white rounded-2xl p-6 border border-slate-200">
        <h3 class="font-display font-bold text-[#0B2A4A] mb-4">Kasus berdasarkan Jenis</h3>
        <canvas id="chartJenis" height="140"></canvas>
    </div>
    <div class="bg-white rounded-2xl p-6 border border-slate-200">
        <h3 class="font-display font-bold text-[#0B2A4A] mb-4">Status Tindak Lanjut</h3>
        <canvas id="chartStatus" height="140"></canvas>
    </div>
</div>

<div class="bg-white rounded-2xl p-6 border border-slate-200 mb-5">
    <h3 class="font-display font-bold text-[#0B2A4A] mb-4">Kasus berdasarkan Jenis Anomali (Top 10)</h3>
    <canvas id="chartAnomali" height="90"></canvas>
</div>

<div class="bg-white rounded-2xl p-6 border border-slate-200">
    <h3 class="font-display font-bold text-[#0B2A4A] mb-4">Monitoring Penyelesaian per Kecamatan</h3>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-xs uppercase text-slate-500 font-semibold">
                <tr>
                    <th class="px-4 py-3 text-left">Kecamatan</th>
                    <th class="px-4 py-3 text-center">Total Kasus</th>
                    <th class="px-4 py-3 text-center">Selesai</th>
                    <th class="px-4 py-3 text-center">Persentase</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($d['byKecamatan'] as $r)
                    <tr class="hover:bg-slate-50">
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
            datasets: [{ data: Object.values(jenis), backgroundColor: ['#0F7B8A', '#E2A63B'] }]
        },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });

    if (window._chartStatus) window._chartStatus.destroy();
    window._chartStatus = new Chart(document.getElementById('chartStatus'), {
        type: 'doughnut',
        data: {
            labels: Object.keys(status).map(k => k === 'sudah' ? 'Sudah Tindak Lanjut' : 'Belum Tindak Lanjut'),
            datasets: [{ data: Object.values(status), backgroundColor: ['#D64545', '#1E8E5A'] }]
        },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });

    if (window._chartAnomali) window._chartAnomali.destroy();
    window._chartAnomali = new Chart(document.getElementById('chartAnomali'), {
        type: 'bar',
        data: {
            labels: Object.keys(anomali),
            datasets: [{ label: 'Jumlah Kasus', data: Object.values(anomali), backgroundColor: '#6366F1', borderRadius: 6 }]
        },
        options: { responsive: true, indexAxis: 'y', plugins: { legend: { display: false } } }
    });
})();
</script>

</div>
