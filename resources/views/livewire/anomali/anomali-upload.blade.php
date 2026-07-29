<div>

@if($successMessage)
    <div class="mb-5 px-4 py-3 rounded-lg bg-emerald-50 text-emerald-700 text-sm font-medium border border-emerald-200">{{ $successMessage }}</div>
@endif

<div class="bg-white rounded-2xl border border-slate-200 p-6 max-w-2xl">
    <p class="text-sm text-slate-500 mb-5">Unggah keempat file sekaligus untuk satu tanggal pekanan. Jika tanggal ini pernah diunggah sebelumnya, data lama akan digantikan.</p>

    <form wire:submit="simpanUpload" class="space-y-4">
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Tanggal Anomali (Pekanan)</label>
            <input type="date" wire:model="tanggal" class="w-full px-4 py-2.5 rounded-lg border border-slate-300 outline-none focus:border-[#0F7B8A]">
            @error('tanggal') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>

        @foreach([
            'radarUsaha' => 'Excel 1: Radar Anomali Usaha',
            'radarKeluarga' => 'Excel 2: Radar Anomali Keluarga',
            'mikroUsaha' => 'Excel 3: Data Mikro Anomali Usaha',
            'mikroKeluarga' => 'Excel 4: Data Mikro Anomali Keluarga',
        ] as $field => $label)
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">{{ $label }}</label>
                <input type="file" wire:model="{{ $field }}" accept=".xlsx,.xls" class="w-full text-sm border border-slate-300 rounded-lg px-4 py-2.5">
                <div wire:loading wire:target="{{ $field }}" class="text-xs text-slate-400 mt-1">Mengunggah...</div>
                @error($field) <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
        @endforeach

        <button type="submit" wire:loading.attr="disabled" wire:target="upload"
                class="px-5 py-2.5 rounded-lg bg-[#0B2A4A] hover:bg-[#0d3760] text-white font-semibold text-sm transition disabled:opacity-50">
            <span wire:loading.remove wire:target="upload">Proses & Simpan Batch</span>
            <span wire:loading wire:target="upload">Memproses keempat file...</span>
        </button>
    </form>
</div>

</div>
