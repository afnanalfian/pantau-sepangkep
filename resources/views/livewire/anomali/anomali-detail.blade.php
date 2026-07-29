<div>
tanggal->translatedFormat('d F Y')">

<div class="flex items-center justify-between mb-5">
    <a href="{{ route('pegawai.anomali') }}" class="text-xs font-semibold text-slate-400 hover:text-[#0F7B8A] transition">&larr; Kembali ke daftar anomali</a>
    @if($view === 'mikro')
        <button wire:click="kembaliKeDashboard" class="text-sm font-semibold text-[#0F7B8A] hover:underline">&larr; Kembali ke Dashboard Visualisasi</button>
    @else
        <button wire:click="lihatDataMikro" class="px-4 py-2.5 rounded-lg bg-[#0B2A4A] hover:bg-[#0d3760] text-white text-sm font-semibold">Lihat Data Mikro</button>
    @endif
</div>

@if($view === 'dashboard')
    @include('livewire.anomali._dashboard', ['d' => $dash])
@else
    @include('livewire.anomali._data-mikro')
@endif

</div>
