<div>

<!-- ============================================ -->
<!-- HEADER & NAVIGATION -->
<!-- ============================================ -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-5 sm:mb-6">
    <a href="{{ route('pegawai.anomali') }}" 
       class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-medium text-slate-400 hover:text-orange-600 transition group">
        <span class="group-hover:-translate-x-1 transition">←</span>
        Kembali ke daftar anomali
    </a>
    
    @if($view === 'mikro')
        <button wire:click="kembaliKeDashboard" 
                class="w-full sm:w-auto px-4 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-semibold transition active:scale-95">
            ← Kembali ke Dashboard
        </button>
    @else
        <button wire:click="lihatDataMikro" 
                class="w-full sm:w-auto px-4 sm:px-5 py-2.5 rounded-lg bg-orange-600 hover:bg-orange-700 text-white text-sm font-semibold transition active:scale-95 shadow-sm shadow-orange-600/20">
            Lihat Data Mikro
        </button>
    @endif
</div>

<!-- ============================================ -->
<!-- CONTENT -->
<!-- ============================================ -->
@if($view === 'dashboard')
    @include('livewire.anomali._dashboard', ['d' => $dash])
@else
    @include('livewire.anomali._data-mikro')
@endif

</div>