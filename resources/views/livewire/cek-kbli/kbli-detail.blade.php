<div>
    <!-- Header dengan Navigasi -->
    <div class="bg-white rounded-xl sm:rounded-2xl border border-slate-200 p-4 sm:p-6 mb-4 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <a href="{{ route('pegawai.cek-kbli') }}" class="text-xs text-slate-400 hover:text-orange-600 transition">← Semua batch</a>
                <h2 class="text-xl sm:text-2xl font-bold text-slate-800 mt-1">
                    Cek KBLI - {{ $batch->tanggal->translatedFormat('d F Y') }}
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

    @if($view === 'dashboard')
        @include('livewire.cek-kbli._dashboard', ['dash' => $dash, 'statusOptions' => $statusOptions])
    @else
        @include('livewire.cek-kbli._data-mikro', [
            'mikros' => $mikros,
            'kecamatanOptions' => $kecamatanOptions,
            'desaOptions' => $desaOptions,
            'kbliOptions' => $kbliOptions,
            'statusOptions' => $statusOptions,
        ])
    @endif
</div>
