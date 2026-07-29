<div>

<div class="max-w-7xl mx-auto px-5 lg:px-8 py-8">

    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-6">
        <div>
            <h1 class="font-display font-bold text-2xl text-[#0B2A4A]">Dashboard Progres Lapangan</h1>
            <p class="text-sm text-slate-500 mt-1">
                @if($latest)
                    Data per <span class="font-semibold text-slate-700">{{ $latest->tanggal->translatedFormat('d F Y') }}</span>
                    &middot; diunggah {{ $latest->created_at->diffForHumans() }}
                @else
                    Belum ada data yang diunggah.
                @endif
            </p>
        </div>
        <a href="{{ route('dashboard.upload') }}" class="text-xs font-semibold text-slate-400 hover:text-[#0F7B8A] transition">Panel unggah admin &rarr;</a>
    </div>

    @if(!$latest)
        <div class="bg-white rounded-2xl border border-dashed border-slate-300 p-16 text-center">
            <p class="text-slate-400 font-medium">Belum ada data harian yang diunggah oleh admin.</p>
        </div>
    @else

    {{-- TAB NAV --}}
    <div class="flex flex-wrap gap-1 bg-white rounded-xl p-1.5 border border-slate-200 mb-6 overflow-x-auto">
        @foreach([
            'utama' => 'Dashboard Utama',
            'ppl' => 'Kinerja Petugas (PPL)',
            'pml' => 'Kinerja Pengawas (PML)',
            'sls' => 'Detail SLS/Blok Sensus',
            'tidak-ditemukan' => 'Tidak Ditemukan',
            'gabungan' => 'Gabungan',
            'produktivitas' => 'Produktivitas Harian',
        ] as $key => $label)
            <button wire:click="setTab('{{ $key }}')"
                class="px-4 py-2 rounded-lg text-sm font-semibold whitespace-nowrap transition
                {{ $tab === $key ? 'bg-[#0B2A4A] text-white' : 'text-slate-500 hover:bg-slate-100' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

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
    @elseif($tab === 'gabungan')
        @include('livewire.dashboard.tab-gabungan', ['d' => $gabungan])
    @elseif($tab === 'produktivitas')
        @include('livewire.dashboard.tab-produktivitas')
    @endif

    @endif
</div>

{{-- MODAL: grafik produktivitas per PPL --}}
<div x-data="{ open: false, chart: null, nama: '' }"
     x-on:open-ppl-chart.window="
        open = true;
        nama = $event.detail.nama;
        $nextTick(() => {
            const ctx = $refs.pplCanvas.getContext('2d');
            if (chart) chart.destroy();
            chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: $event.detail.labels,
                    datasets: [{
                        label: 'Progres kumulatif',
                        data: $event.detail.values,
                        borderColor: '#0F7B8A',
                        backgroundColor: 'rgba(15,123,138,0.1)',
                        tension: 0.35,
                        fill: true,
                        spanGaps: true,
                    }]
                },
                options: { responsive: true, plugins: { legend: { display: false } } }
            });
        })
     "
     x-show="open" x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50"
     style="display:none;">
    <div @click.outside="open = false" class="bg-white rounded-2xl p-6 w-full max-w-2xl shadow-2xl">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-display font-bold text-lg text-[#0B2A4A]" x-text="nama"></h3>
            <button @click="open = false" class="text-slate-400 hover:text-slate-600">&times;</button>
        </div>
        <canvas x-ref="pplCanvas" height="100"></canvas>
    </div>
</div>

</div>
