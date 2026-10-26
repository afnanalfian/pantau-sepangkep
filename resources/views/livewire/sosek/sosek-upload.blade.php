<div class="space-y-5 max-w-3xl">

@if($successMessage)
    <div class="px-4 py-3 rounded-lg bg-emerald-50 text-emerald-700 text-sm font-medium border border-emerald-200 flex items-start gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div>
            <span>{{ $successMessage }} <a href="{{ route('pegawai.sosek') }}" class="underline font-semibold">Lihat daftar</a></span>
            @if($dilewati)
                <ul class="mt-1 text-xs font-normal text-slate-600">
                    @foreach($dilewati as $sheet => $alasan)
                        <li>Sheet "{{ $sheet }}" dilewati: {{ $alasan }}.</li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
@endif

<div class="bg-white rounded-xl sm:rounded-2xl border border-slate-200 p-4 sm:p-6 shadow-sm">
    <h3 class="font-semibold text-slate-800 mb-1">Unggah Data Mikro Anomali Sosek</h3>
    <p class="text-xs sm:text-sm text-slate-500 mb-4 sm:mb-5">
        Cukup <b>satu file Excel</b> berisi semua sheet anomali (template "Data Mikro Anomali Sosek").
        Setiap sheet dikenali otomatis dari kolom & judulnya — urutan dan nama sheet bebas.
        Jika tanggal ini sudah pernah diunggah, jenis yang ada di file diganti (beserta status tindak lanjutnya), jenis lain tetap.
    </p>

    <form wire:submit="simpanUpload" class="space-y-4 sm:space-y-5">
        <div>
            <label class="block text-xs sm:text-sm font-semibold text-slate-700 mb-1.5">Tanggal <span class="text-red-500">*</span></label>
            <input type="date" wire:model="tanggal"
                   class="w-full px-3 sm:px-4 py-2.5 rounded-lg border border-slate-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition text-sm">
            @error('tanggal') <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-xs sm:text-sm font-semibold text-slate-700 mb-1.5">File Excel <span class="text-red-500">*</span></label>
            <input type="file" wire:model="file" accept=".xlsx,.xls"
                   class="w-full text-xs sm:text-sm border border-slate-300 rounded-lg px-3 py-2.5 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-orange-50 file:text-orange-600 hover:file:bg-orange-100">
            <div wire:loading wire:target="file" class="text-xs text-slate-400 mt-1">Mengunggah...</div>
            @error('file') <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p> @enderror
        </div>

        <div class="rounded-xl bg-slate-50 border border-slate-200 p-3">
            <p class="text-xs font-semibold text-slate-600 mb-2">Sheet yang akan diimpor</p>
            <ul class="grid grid-cols-1 sm:grid-cols-2 gap-1.5">
                @foreach($jenisOptions as $j)
                    <li class="flex items-start gap-2 text-xs text-slate-600">
                        <span class="inline-flex items-center justify-center w-5 h-5 shrink-0 rounded-full text-[10px] font-bold border {{ $j['warna'] }}">{{ $j['no'] }}</span>
                        <span>{{ $j['label'] }}</span>
                    </li>
                @endforeach
            </ul>
            @if($nonaktif)
                <p class="text-[11px] text-slate-400 mt-2">
                    Dilewati (nonaktif di config/sosek.php): {{ implode(', ', array_column($nonaktif, 'label')) }}.
                </p>
            @endif
        </div>

        <button type="submit" wire:loading.attr="disabled" wire:target="simpanUpload, file"
                class="w-full sm:w-auto px-5 py-2.5 rounded-lg bg-orange-600 hover:bg-orange-700 text-white font-semibold text-sm transition active:scale-95 disabled:opacity-50 shadow-sm shadow-orange-600/20">
            <span wire:loading.remove wire:target="simpanUpload">Proses & Simpan Batch</span>
            <span wire:loading wire:target="simpanUpload" class="inline-flex items-center gap-2">
                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                Memproses semua sheet...
            </span>
        </button>
    </form>
</div>

</div>
