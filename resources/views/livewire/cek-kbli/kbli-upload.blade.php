<div class="space-y-5 max-w-2xl">

@if($successMessage)
    <div class="px-4 py-3 rounded-lg bg-emerald-50 text-emerald-700 text-sm font-medium border border-emerald-200 flex items-start gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span>{{ $successMessage }} <a href="{{ route('pegawai.cek-kbli') }}" class="underline font-semibold">Lihat daftar</a></span>
    </div>
@endif

<!-- ============================================ -->
<!-- UPLOAD FILE CEK KBLI -->
<!-- ============================================ -->
<div class="bg-white rounded-xl sm:rounded-2xl border border-slate-200 p-4 sm:p-6 shadow-sm">
    <h3 class="font-semibold text-slate-800 mb-1">Unggah File Cek KBLI</h3>
    <p class="text-xs sm:text-sm text-slate-500 mb-4 sm:mb-5">
        Satu file per tanggal. Jika tanggal ini pernah diunggah sebelumnya, data lama (termasuk status tindak lanjutnya) akan digantikan.
    </p>

    <form wire:submit="simpanUpload" class="space-y-4 sm:space-y-5">
        <div>
            <label class="block text-xs sm:text-sm font-semibold text-slate-700 mb-1.5">Tanggal <span class="text-red-500">*</span></label>
            <input type="date" wire:model="tanggal"
                   class="w-full px-3 sm:px-4 py-2.5 rounded-lg border border-slate-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition text-sm">
            @error('tanggal') <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-xs sm:text-sm font-semibold text-slate-700 mb-1.5">File Excel Cek KBLI <span class="text-red-500">*</span></label>
            <input type="file" wire:model="fileKbli" accept=".xlsx,.xls,.csv"
                   class="w-full text-xs sm:text-sm border border-slate-300 rounded-lg px-3 py-2.5 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-orange-50 file:text-orange-600 hover:file:bg-orange-100">
            <div wire:loading wire:target="fileKbli" class="text-xs text-slate-400 mt-1">Mengunggah...</div>
            @error('fileKbli') <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p> @enderror
            <p class="text-[11px] text-slate-400 mt-1.5 leading-relaxed">
                Kolom: assignment_id, index1, level_1_full_code, level_2_full_code, level_3_full_code (kecamatan),
                level_4_full_code (desa/kel), kbli_akhir, nama_usaha, keg_utama, produk, link_fasih_sm.
            </p>
        </div>

        <button type="submit"
                wire:loading.attr="disabled"
                wire:target="simpanUpload, fileKbli"
                class="w-full sm:w-auto px-5 py-2.5 rounded-lg bg-orange-600 hover:bg-orange-700 text-white font-semibold text-sm transition active:scale-95 disabled:opacity-50 shadow-sm shadow-orange-600/20">
            <span wire:loading.remove wire:target="simpanUpload">Proses & Simpan Batch</span>
            <span wire:loading wire:target="simpanUpload" class="inline-flex items-center gap-2">
                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                Memproses file...
            </span>
        </button>
    </form>
</div>

<!-- ============================================ -->
<!-- MASTER JUDUL KBLI (opsional) -->
<!-- ============================================ -->
<div class="bg-white rounded-xl sm:rounded-2xl border border-slate-200 p-4 sm:p-6 shadow-sm">
    <div class="flex items-start justify-between gap-3 mb-1">
        <h3 class="font-semibold text-slate-800">Master Judul KBLI</h3>
        <span class="text-[10px] sm:text-xs font-semibold px-2 py-0.5 rounded-full {{ $jumlahMaster > 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
            {{ number_format($jumlahMaster) }} kode tersimpan
        </span>
    </div>
    <p class="text-xs sm:text-sm text-slate-500 mb-4">
        Dipakai untuk menampilkan label/judul tiap kode KBLI. Cukup diunggah sekali (unggah ulang akan memperbarui).
        Isi file: kolom <b>kode</b> (5 digit) dan <b>judul</b>. Selama kode belum ada di master, label yang tampil adalah nama kategorinya (A–U).
    </p>

    @if($masterMessage)
        <div class="mb-4 px-3 py-2 rounded-lg bg-emerald-50 text-emerald-700 text-xs sm:text-sm border border-emerald-200">{{ $masterMessage }}</div>
    @endif

    <form wire:submit="simpanMaster" class="flex flex-col sm:flex-row gap-2 sm:items-start">
        <div class="flex-1">
            <input type="file" wire:model="fileMaster" accept=".xlsx,.xls,.csv"
                   class="w-full text-xs sm:text-sm border border-slate-300 rounded-lg px-3 py-2.5 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-slate-100 file:text-slate-600 hover:file:bg-slate-200">
            <div wire:loading wire:target="fileMaster" class="text-xs text-slate-400 mt-1">Mengunggah...</div>
            @error('fileMaster') <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p> @enderror
        </div>
        <button type="submit" wire:loading.attr="disabled" wire:target="simpanMaster, fileMaster"
                class="px-4 py-2.5 rounded-lg bg-slate-800 hover:bg-slate-900 text-white text-sm font-semibold transition active:scale-95 disabled:opacity-50">
            <span wire:loading.remove wire:target="simpanMaster">Simpan Master</span>
            <span wire:loading wire:target="simpanMaster">Memproses...</span>
        </button>
    </form>
</div>

</div>
