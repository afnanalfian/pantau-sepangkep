<div>
    <div class="bg-white rounded-xl sm:rounded-2xl border border-slate-200 p-4 sm:p-6 mb-4 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <a href="{{ route('pegawai.sosek') }}" class="text-xs text-slate-400 hover:text-orange-600 transition">← Semua batch</a>
                <h2 class="text-xl sm:text-2xl font-bold text-slate-800 mt-1">
                    Anomali Sosek - {{ $batch->tanggal->translatedFormat('d F Y') }}
                </h2>
                <p class="text-sm text-slate-500 mt-1">Batch #{{ $batch->id }} · {{ count($batch->nama_file ?? []) }} dari 7 jenis diunggah</p>
            </div>
            <div class="flex flex-wrap gap-2">
                @if(session('role') === 'admin')
                    <button wire:click="perbaruiPic" wire:loading.attr="disabled" wire:target="perbaruiPic"
                            title="Hitung ulang wilayah & PIC dari data harian terbaru"
                            class="px-4 py-2 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-sm font-semibold transition disabled:opacity-50">
                        <span wire:loading.remove wire:target="perbaruiPic">↻ Perbarui PIC</span>
                        <span wire:loading wire:target="perbaruiPic">Memperbarui...</span>
                    </button>
                @endif
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
        @include('livewire.sosek._dashboard')
    @else
        @include('livewire.sosek._data-mikro')
    @endif

    <!-- FLASH -->
    @foreach(['success' => 'bg-emerald-50 border-emerald-200 text-emerald-700', 'info' => 'bg-blue-50 border-blue-200 text-blue-700'] as $key => $cls)
        @if(session()->has($key))
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3500)" x-show="show" x-transition
                 class="fixed top-4 right-4 z-50 max-w-sm border px-4 py-3 rounded-lg shadow-lg text-sm {{ $cls }}">
                {{ session($key) }}
            </div>
        @endif
    @endforeach
</div>
