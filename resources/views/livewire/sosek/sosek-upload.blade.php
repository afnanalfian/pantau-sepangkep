<div class="space-y-5 max-w-3xl">

@if($successMessage)
    <div class="px-4 py-3 rounded-lg bg-emerald-50 text-emerald-700 text-sm font-medium border border-emerald-200 flex items-start gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span>{{ $successMessage }} <a href="{{ route('pegawai.sosek') }}" class="underline font-semibold">Lihat daftar</a></span>
    </div>
@endif

<div class="bg-white rounded-xl sm:rounded-2xl border border-slate-200 p-4 sm:p-6 shadow-sm">
    <h3 class="font-semibold text-slate-800 mb-1">Unggah File Anomali Sosek</h3>
    <p class="text-xs sm:text-sm text-slate-500 mb-4 sm:mb-5">
        Ada 7 slot file; isi yang tersedia saja. Jika tanggal ini sudah pernah diunggah, <b>hanya jenis yang diunggah ulang</b>
        yang diganti (beserta status tindak lanjutnya), jenis lain tetap. PIC diambil dari <b>PML organik</b> data harian
        berdasarkan ID SubSLS (level_6_full_code).
    </p>

    <form wire:submit="simpanUpload" class="space-y-4 sm:space-y-5">
        <div>
            <label class="block text-xs sm:text-sm font-semibold text-slate-700 mb-1.5">Tanggal <span class="text-red-500">*</span></label>
            <input type="date" wire:model="tanggal"
                   class="w-full px-3 sm:px-4 py-2.5 rounded-lg border border-slate-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition text-sm">
            @error('tanggal') <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-1 gap-3">
            @foreach($jenisOptions as $kode => $j)
                @php $prop = 'file_' . $kode; @endphp
                <div class="rounded-xl border border-slate-200 p-3">
                    <label class="flex items-start gap-2 text-xs sm:text-sm font-semibold text-slate-700 mb-1.5">
                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full text-[11px] font-bold border {{ $j['warna'] }}">{{ $j['no'] }}</span>
                        <span class="pt-0.5">{{ $j['label'] }}</span>
                    </label>
                    <input type="file" wire:model="{{ $prop }}" accept=".xlsx,.xls,.csv"
                           class="w-full text-xs sm:text-sm border border-slate-300 rounded-lg px-3 py-2 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-orange-50 file:text-orange-600 hover:file:bg-orange-100">
                    <div wire:loading wire:target="{{ $prop }}" class="text-xs text-slate-400 mt-1">Mengunggah...</div>
                    @error($prop) <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
            @endforeach
        </div>
        @error('files') <p class="text-sm text-red-600 bg-red-50 border border-red-200 rounded-lg px-3 py-2">{{ $message }}</p> @enderror

        <p class="text-[11px] text-slate-400 leading-relaxed">
            Setiap slot diperiksa: file yang tertukar slot (mis. file Umur di slot AC) akan ditolak.
            Untuk file tanpa kolom level_6 (Umur, Profesi, NIK prov), PIC & wilayah dicari lewat assignment_id yang sama
            di jenis lain atau di modul Anomali.
        </p>

        <button type="submit" wire:loading.attr="disabled"
                wire:target="simpanUpload, file_umur, file_profesi, file_kk_prov, file_kk_kembar, file_nik_prov, file_nik_kembar, file_ac_450"
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

</div>
