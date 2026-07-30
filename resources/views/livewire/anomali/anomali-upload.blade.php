<div>

<!-- ============================================ -->
<!-- SUCCESS MESSAGE -->
<!-- ============================================ -->
@if($successMessage)
    <div class="mb-5 px-4 py-3 rounded-lg bg-emerald-50 text-emerald-700 text-sm font-medium border border-emerald-200 flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        {{ $successMessage }}
    </div>
@endif

<!-- ============================================ -->
<!-- UPLOAD FORM -->
<!-- ============================================ -->
<div class="bg-white rounded-xl sm:rounded-2xl border border-slate-200 p-4 sm:p-6 max-w-2xl shadow-sm">
    
    <p class="text-xs sm:text-sm text-slate-500 mb-4 sm:mb-5">Unggah keempat file sekaligus untuk satu tanggal pekanan. Jika tanggal ini pernah diunggah sebelumnya, data lama akan digantikan.</p>

    <form wire:submit="simpanUpload" class="space-y-4 sm:space-y-5">
        
        <!-- Tanggal -->
        <div>
            <label class="block text-xs sm:text-sm font-semibold text-slate-700 mb-1.5">Tanggal Anomali (Pekanan) <span class="text-red-500">*</span></label>
            <input type="date" 
                   wire:model="tanggal" 
                   class="w-full px-3 sm:px-4 py-2.5 rounded-lg border border-slate-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition text-sm">
            @error('tanggal') <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p> @enderror
        </div>

        <!-- File Uploads -->
        @foreach([
            'radarUsaha' => 'Excel 1: Radar Anomali Usaha',
            'radarKeluarga' => 'Excel 2: Radar Anomali Keluarga',
            'mikroUsaha' => 'Excel 3: Data Mikro Anomali Usaha',
            'mikroKeluarga' => 'Excel 4: Data Mikro Anomali Keluarga',
        ] as $field => $label)
            <div>
                <label class="block text-xs sm:text-sm font-semibold text-slate-700 mb-1.5">{{ $label }}</label>
                <input type="file" 
                       wire:model="{{ $field }}" 
                       accept=".xlsx,.xls" 
                       class="w-full text-xs sm:text-sm border border-slate-300 rounded-lg px-3 py-2.5 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-orange-50 file:text-orange-600 hover:file:bg-orange-100">
                <div wire:loading wire:target="{{ $field }}" class="text-xs text-slate-400 mt-1">Mengunggah...</div>
                @error($field) <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p> @enderror
            </div>
        @endforeach

        <!-- Submit -->
        <button type="submit" 
                wire:loading.attr="disabled" 
                wire:target="upload"
                class="w-full sm:w-auto px-5 py-2.5 rounded-lg bg-orange-600 hover:bg-orange-700 text-white font-semibold text-sm transition active:scale-95 disabled:opacity-50 shadow-sm shadow-orange-600/20">
            <span wire:loading.remove wire:target="upload">Proses & Simpan Batch</span>
            <span wire:loading wire:target="upload" class="inline-flex items-center gap-2">
                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Memproses keempat file...
            </span>
        </button>
    </form>
</div>

</div>