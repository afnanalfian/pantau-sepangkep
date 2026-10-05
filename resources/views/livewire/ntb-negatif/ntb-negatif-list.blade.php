<div>

<!-- ============================================ -->
<!-- HEADER -->
<!-- ============================================ -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-5 sm:mb-6">
    <p class="text-xs sm:text-sm text-slate-500">NTB Negatif dibuat per tanggal unggah. Setiap batch bersifat independen satu sama lain.</p>
    @if($canUpload)
        <a href="{{ route('pegawai.ntb-negatif.upload') }}"
           class="w-full sm:w-auto px-4 sm:px-5 py-2.5 rounded-lg bg-orange-600 hover:bg-orange-700 text-white text-sm font-semibold transition active:scale-95 shadow-sm shadow-orange-600/20 text-center">
            <span class="inline-flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Unggah NTB Negatif Baru
            </span>
        </a>
    @endif
</div>

<!-- ============================================ -->
<!-- BATCH CARDS -->
<!-- ============================================ -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
    @forelse($batches as $b)
        <a href="{{ route('pegawai.ntb-negatif.detail', $b) }}"
           class="group bg-white rounded-xl sm:rounded-2xl border border-slate-200 p-4 sm:p-5 hover:border-orange-300 hover:shadow-md transition active:scale-[0.98]">

            <div class="flex items-center justify-between mb-3">
                <span class="w-9 sm:w-10 h-9 sm:h-10 rounded-xl bg-amber-100 flex items-center justify-center text-amber-600 group-hover:bg-amber-600 group-hover:text-white transition">
                    {{-- ikon tabel dengan sel kosong --}}
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 sm:w-5 h-4 sm:h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 10h18M3 14h18M10 3v18M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z"/>
                    </svg>
                </span>
                <span class="text-[10px] sm:text-xs font-bold px-2 py-0.5 sm:py-1 rounded-full
                    {{ $b->persen >= 80 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : ($b->persen >= 40 ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-red-50 text-red-700 border border-red-200') }}">
                    {{ $b->persen }}% selesai
                </span>
            </div>

            <h3 class="font-display font-semibold text-slate-800 text-sm sm:text-base">NTB Negatif {{ $b->tanggal->translatedFormat('d F Y') }}</h3>
            <p class="text-xs text-slate-400 mt-1">{{ number_format($b->mikros_count) }} kasus · {{ number_format($b->selesai_count) }} selesai</p>

            <div class="mt-3 h-1.5 rounded-full bg-slate-100 overflow-hidden">
                <div class="h-full bg-emerald-500 rounded-full" style="width: {{ $b->persen }}%"></div>
            </div>

            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between">
                <span class="text-xs text-slate-400">{{ $b->created_at->diffForHumans() }}</span>
                <span class="text-xs font-medium text-orange-600 group-hover:translate-x-1 transition inline-flex items-center gap-1">
                    Detail <span>→</span>
                </span>
            </div>
        </a>
    @empty
        <div class="col-span-full text-center py-10 sm:py-16 bg-white rounded-xl sm:rounded-2xl border border-dashed border-slate-300">
            <div class="w-12 sm:w-16 h-12 sm:h-16 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 sm:w-8 h-6 sm:h-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M3 14h18M10 3v18M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z"/>
                </svg>
            </div>
            <p class="text-sm text-slate-400 font-medium">Belum ada data NTB negatif yang diunggah.</p>
            <p class="text-xs text-slate-400 mt-1">Unggah file NTB negatif untuk memulai monitoring.</p>
        </div>
    @endforelse
</div>

</div>
