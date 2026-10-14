<div>
    <!-- Header dengan Navigasi -->
    <div class="bg-white rounded-xl sm:rounded-2xl border border-slate-200 p-4 sm:p-6 mb-4 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <a href="{{ route('pegawai.anomali24') }}" class="text-xs text-slate-400 hover:text-orange-600 transition">← Semua batch</a>
                <div class="mt-2"><span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-amber-50 text-amber-800 border border-amber-200 text-xs font-semibold">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Anomali KBLI Tidak Sesuai 01122
                </span></div>
                <h2 class="text-xl sm:text-2xl font-bold text-slate-800 mt-1">
                    Anomali 24 - {{ $batch->tanggal->translatedFormat('d F Y') }}
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
        @include('livewire.anomali24._dashboard', ['dash' => $dash, 'statusOptions' => $statusOptions])
    @else
        @include('livewire.anomali24._data-mikro', [
            'mikros' => $mikros,
            'kecamatanOptions' => $kecamatanOptions,
            'desaOptions' => $desaOptions,
            'kbliOptions' => $kbliOptions,
            'picOptions' => $picOptions,
            'statusOptions' => $statusOptions,
        ])
    @endif
</div>
