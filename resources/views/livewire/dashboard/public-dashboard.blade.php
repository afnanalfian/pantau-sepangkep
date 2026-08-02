<div>

<!-- ============================================ -->
<!-- DASHBOARD HEADER -->
<!-- ============================================ -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">

    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 sm:gap-4 mb-5 sm:mb-6">
        <div>
            <h1 class="font-display font-bold text-xl sm:text-2xl text-slate-900">Dashboard Progres Lapangan</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                @if($latest)
                    Data per <span class="font-semibold text-slate-700">{{ $latest->tanggal->translatedFormat('d F Y') }}</span>
                    · diunggah {{ $latest->created_at->diffForHumans() }}
                @else
                    Belum ada data yang diunggah.
                @endif
            </p>
        </div>
        <a href="{{ route('dashboard.upload') }}"
           class="text-xs font-medium text-slate-400 hover:text-orange-600 transition inline-flex items-center gap-1 group">
            Panel unggah admin
            <span class="group-hover:translate-x-1 transition">→</span>
        </a>
    </div>

    <!-- ============================================ -->
    <!-- EMPTY STATE -->
    <!-- ============================================ -->
    @if(!$latest)
        <div class="bg-white rounded-xl sm:rounded-2xl border border-dashed border-slate-300 p-12 sm:p-16 text-center">
            <div class="w-12 sm:w-16 h-12 sm:h-16 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 sm:w-8 h-6 sm:h-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                </svg>
            </div>
            <p class="text-sm text-slate-400 font-medium">Belum ada data harian yang diunggah oleh admin.</p>
            <p class="text-xs text-slate-400 mt-1">Unggah data untuk mulai memantau progres lapangan.</p>
        </div>
    @else

    <!-- ============================================ -->
    <!-- TAB NAVIGATION -->
    <!-- ============================================ -->
    <div class="flex flex-wrap gap-1 bg-white rounded-xl p-1.5 border border-slate-200 mb-5 sm:mb-6 overflow-x-auto shadow-sm">
        @foreach([
            'utama' => 'Dashboard Utama',
            'ppl' => 'Kinerja PPL',
            'pml' => 'Kinerja PML',
            'sls' => 'Detail SLS',
            'tidak-ditemukan' => 'Tidak Ditemukan',
            // 'gabungan' => 'Gabungan',
            'produktivitas' => 'Produktivitas Harian',
        ] as $key => $label)
            <button wire:click="setTab('{{ $key }}')"
                    class="px-3 sm:px-4 py-1.5 sm:py-2 rounded-lg text-[10px] sm:text-sm font-medium whitespace-nowrap transition active:scale-95
                    {{ $tab === $key ? 'bg-orange-600 text-white shadow-sm shadow-orange-600/20' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-700' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    <!-- ============================================ -->
    <!-- TAB CONTENT -->
    <!-- ============================================ -->
    @if($tab === 'utama')
        @include('livewire.dashboard.tab-utama', ['d' => $utama])
    @elseif($tab === 'ppl')
        @include('livewire.dashboard.tab-ppl')
    @elseif($tab === 'pml')
        @include('livewire.dashboard.tab-pml')
    @elseif($tab === 'sls')
        @include('livewire.dashboard.tab-sls')
    @elseif($tab === 'tidak-ditemukan')
        @include('livewire.dashboard.tab-tidak-ditemukan', ['d' => $td])
    {{-- @elseif($tab === 'gabungan')
        @include('livewire.dashboard.tab-gabungan', ['d' => $gabungan]) --}}
    @elseif($tab === 'produktivitas')
        @include('livewire.dashboard.tab-produktivitas')
    @endif

    @endif
</div>

<!-- ============================================ -->
<!-- MODAL: GRAFIK PRODUKTIVITAS PPL              -->
<!-- 3 seri: Progres, Draft, Muatan (kumulatif)   -->
<!-- ============================================ -->
<div x-data="{ open: false, chart: null, nama: '' }"
     x-on:open-ppl-chart.window="
        open = true;
        nama = $event.detail.nama;
        const detail = $event.detail;
        $nextTick(() => {
            const warna = ['#F59E0B', '#6366F1', '#10B981'];
            const ctx = $refs.pplCanvas.getContext('2d');
            if (chart) chart.destroy();
            chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: detail.labels,
                    datasets: (detail.seri || []).map((s, i) => ({
                        label: s.label,
                        data: s.values,
                        borderColor: warna[i % warna.length],
                        backgroundColor: warna[i % warna.length] + '20',
                        tension: 0.35,
                        fill: false,
                        spanGaps: true,
                        pointRadius: 3,
                        borderWidth: 2,
                    }))
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } }
                    },
                    scales: {
                        x: { grid: { display: false }, ticks: { font: { size: 10 } } },
                        y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' }, ticks: { font: { size: 10 } } }
                    }
                }
            });
        })
     "
     x-show="open" x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm"
     style="display:none;">
    <div @click.outside="open = false"
         class="bg-white rounded-xl sm:rounded-2xl p-5 sm:p-6 w-full max-w-2xl shadow-2xl">
        <div class="flex items-center justify-between mb-1">
            <h3 class="font-display font-bold text-lg text-slate-900" x-text="nama"></h3>
            <button @click="open = false" class="text-slate-400 hover:text-slate-600 text-2xl leading-none transition p-1">
                ×
            </button>
        </div>
        <p class="text-xs text-slate-400 mb-4">Posisi kumulatif progres, draft, dan muatan per tanggal unggahan.</p>
        <div class="relative w-full" style="height: 300px;">
            <canvas x-ref="pplCanvas"></canvas>
        </div>
    </div>
</div>

</div>
