<div>

<!-- ============================================ -->
<!-- PAGE HEADER -->
<!-- ============================================ -->
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-10">
    
    <h1 class="font-display font-bold text-xl sm:text-2xl text-slate-900 mb-1">Pengumuman</h1>
    <p class="text-xs sm:text-sm text-slate-500 mb-5 sm:mb-6">Informasi resmi untuk petugas dan pegawai Sensus Ekonomi 2026.</p>

    <!-- ============================================ -->
    <!-- ANNOUNCEMENT LIST -->
    <!-- ============================================ -->
    <div class="space-y-3 sm:space-y-4">
        @forelse($daftar as $p)
            <a href="{{ route('pengumuman.detail', $p) }}" 
               class="block bg-white rounded-xl sm:rounded-2xl border border-slate-200 p-4 sm:p-5 hover:border-orange-300 hover:shadow-md transition active:scale-[0.98]">
                
                <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-2 sm:gap-4">
                    
                    <!-- Left: Content -->
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-1.5 sm:gap-2 mb-1">
                            <h3 class="font-display font-semibold text-slate-800 text-sm sm:text-base group-hover:text-orange-600 transition line-clamp-1">
                                {{ $p->judul }}
                            </h3>
                            @if($p->isBaru())
                                <span class="text-[9px] sm:text-[10px] font-bold uppercase px-1.5 sm:px-2 py-0.5 rounded-full bg-red-100 text-red-600 border border-red-200 flex-shrink-0">
                                    Baru
                                </span>
                            @endif
                        </div>
                        <p class="text-xs sm:text-sm text-slate-500 line-clamp-2 sm:line-clamp-2">
                            {{ $p->ringkasan ?: strip_tags($p->konten) }}
                        </p>
                    </div>
                    
                    <!-- Right: Date -->
                    <span class="text-[10px] sm:text-xs text-slate-400 whitespace-nowrap flex-shrink-0">
                        {{ $p->created_at->translatedFormat('d M Y') }}
                    </span>
                </div>
                
                <!-- Read more indicator -->
                <div class="mt-2 sm:mt-3 flex items-center gap-1 text-orange-600 text-xs font-medium">
                    Baca selengkapnya
                    <span class="inline-block group-hover:translate-x-1 transition">→</span>
                </div>
            </a>
        @empty
            <!-- Empty State -->
            <div class="text-center py-12 sm:py-16 bg-white rounded-xl sm:rounded-2xl border border-dashed border-slate-300">
                <div class="w-12 sm:w-16 h-12 sm:h-16 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 sm:w-8 h-6 sm:h-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/>
                    </svg>
                </div>
                <p class="text-sm text-slate-400">Belum ada pengumuman</p>
                <p class="text-xs text-slate-400 mt-1">Pengumuman akan muncul di sini.</p>
            </div>
        @endforelse
    </div>

    <!-- ============================================ -->
    <!-- PAGINATION -->
    <!-- ============================================ -->
    @if($daftar->hasPages())
        <div class="mt-4 sm:mt-6">
            {{ $daftar->links() }}
        </div>
    @endif
</div>

</div>