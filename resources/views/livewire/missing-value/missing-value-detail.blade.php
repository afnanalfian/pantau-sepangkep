<div>
    <!-- Header dengan Navigasi -->
    <div class="bg-white rounded-xl sm:rounded-2xl border border-slate-200 p-4 sm:p-6 mb-4 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <a href="{{ route('pegawai.missing-value') }}" class="text-xs text-slate-400 hover:text-orange-600 transition">← Semua batch</a>
                <h2 class="text-xl sm:text-2xl font-bold text-slate-800 mt-1">
                    Missing Value - {{ $batch->tanggal->format('d M Y') }}
                </h2>
                <p class="text-sm text-slate-500 mt-1">
                    Batch #{{ $batch->id }}{{ $batch->nama_file ? ' · ' . $batch->nama_file : '' }}
                </p>
            </div>
            <div class="flex gap-2">
                <button wire:click="kembaliKeDashboard"
                        class="px-4 py-2 rounded-lg {{ $view === 'dashboard' ? 'bg-orange-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }} text-sm font-semibold transition">
                    Dashboard
                </button>
                <button wire:click="lihatDataMikro"
                        class="px-4 py-2 rounded-lg {{ $view === 'mikro' ? 'bg-orange-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }} text-sm font-semibold transition">
                    Data Mikro
                </button>
            </div>
        </div>
    </div>

    <!-- Content -->
    @if($view === 'dashboard')
        @include('livewire.missing-value._dashboard', ['dash' => $dash])
    @else
        @include('livewire.missing-value._data-mikro', [
            'mikros' => $mikros,
            'petugasMap' => $petugasMap ?? [],
            'kecamatanOptions' => $kecamatanOptions ?? collect(),
            'desaOptions' => $desaOptions ?? collect(),
            'variabelOptions' => $variabelOptions ?? collect(),
            'statusPerbaikanOptions' => $statusPerbaikanOptions ?? collect(),
            'statusOptions' => $statusOptions ?? [],
        ])
    @endif
</div>
