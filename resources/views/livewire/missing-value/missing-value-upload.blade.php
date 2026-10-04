<div>

<!-- ============================================ -->
<!-- SUCCESS MESSAGE -->
<!-- ============================================ -->
@if($successMessage)
    <div class="mb-5 px-4 py-3 rounded-lg bg-emerald-50 text-emerald-700 text-sm font-medium border border-emerald-200 flex flex-col sm:flex-row sm:items-center gap-2">
        <span class="inline-flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ $successMessage }}
        </span>
        <a href="{{ route('pegawai.missing-value') }}" class="sm:ml-auto text-emerald-800 underline font-semibold">Lihat daftar →</a>
    </div>
@endif

<!-- ============================================ -->
<!-- UPLOAD FORM -->
<!-- ============================================ -->
<div class="bg-white rounded-xl sm:rounded-2xl border border-slate-200 p-4 sm:p-6 max-w-2xl shadow-sm">

    <p class="text-xs sm:text-sm text-slate-500 mb-4 sm:mb-5">
        Unggah satu file excel Missing Value untuk satu tanggal. Jika tanggal ini pernah diunggah sebelumnya, data lama (termasuk status penyelesaian &amp; catatan) akan digantikan.
    </p>

    <form wire:submit="simpanUpload" class="space-y-4 sm:space-y-5">

        <!-- Tanggal -->
        <div>
            <label class="block text-xs sm:text-sm font-semibold text-slate-700 mb-1.5">Tanggal Data <span class="text-red-500">*</span></label>
            <input type="date"
                   wire:model="tanggal"
                   class="w-full px-3 sm:px-4 py-2.5 rounded-lg border border-slate-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition text-sm">
            @error('tanggal') <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p> @enderror
        </div>

        <!-- File -->
        <div>
            <label class="block text-xs sm:text-sm font-semibold text-slate-700 mb-1.5">File Excel Missing Value <span class="text-red-500">*</span></label>
            <input type="file"
                   wire:model="fileMissing"
                   accept=".xlsx,.xls"
                   class="w-full text-xs sm:text-sm border border-slate-300 rounded-lg px-3 py-2.5 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-orange-50 file:text-orange-600 hover:file:bg-orange-100">
            <div wire:loading wire:target="fileMissing" class="text-xs text-slate-400 mt-1">Mengunggah...</div>
            @error('fileMissing') <p class="text-xs text-red-500 mt-1.5 break-words">{{ $message }}</p> @enderror
            <p class="text-[11px] text-slate-400 mt-1.5 leading-relaxed">
                Kolom yang dibaca (27): No, Nama Anggota Keluarga, Kode/Nama Prov, Kode/Nama Kab/Kota, Kode/Nama Kec, Kode/Nama Desa, Kode SLS, Sub SLS,
                Assignment ID, Variabel Missing, ID &amp; Email Petugas, Jenis Kelamin/Tgl/Bln/Thn Lahir SE, NIK/Nama/Jenis Kelamin/Tanggal lahir hasil matched DTSEN,
                Skor Kemiripan, Status Perbaikan, Link Fasih.
            </p>
        </div>

        <!-- Submit -->
        <button type="submit"
                wire:loading.attr="disabled"
                wire:target="simpanUpload,fileMissing"
                class="w-full sm:w-auto px-5 py-2.5 rounded-lg bg-orange-600 hover:bg-orange-700 text-white font-semibold text-sm transition active:scale-95 disabled:opacity-50 shadow-sm shadow-orange-600/20">
            <span wire:loading.remove wire:target="simpanUpload">Proses &amp; Simpan</span>
            <span wire:loading wire:target="simpanUpload" class="inline-flex items-center gap-2">
                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Memproses file...
            </span>
        </button>
    </form>
</div>

</div>
