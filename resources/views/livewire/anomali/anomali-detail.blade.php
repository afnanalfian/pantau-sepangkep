<div>
    <!-- Header dengan Navigasi -->
    <div class="bg-white rounded-xl sm:rounded-2xl border border-slate-200 p-4 sm:p-6 mb-4 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl sm:text-2xl font-bold text-slate-800">
                    Detail Anomali - {{ $batch->tanggal->format('d M Y') }}
                </h2>
                <p class="text-sm text-slate-500 mt-1">
                    Batch #{{ $batch->id }} · {{ $batch->judul ?? 'Batch ' . $batch->id }}
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
        @include('livewire.anomali._dashboard-anomali', ['dash' => $dash])
    @else
        {{-- Kirim variable modal secara eksplisit supaya tidak pernah "Undefined variable"
             walaupun ada masalah cache/scope pada @include --}}
        @include('livewire.anomali._data-mikro', [
            'mikros' => $mikros,
            'mitraMap' => $mitraMap,
            'kecamatanOptions' => $kecamatanOptions,
            'desaOptions' => $desaOptions,
            'statusOptions' => $statusOptions ?? [],
            'showModal' => $showModal ?? false,
            'selectedStatus' => $selectedStatus ?? null,
            'modalAnomaliName' => $modalAnomaliName ?? null,
        ])
    @endif
</div>