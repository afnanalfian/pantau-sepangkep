<div>

<!-- ============================================ -->
<!-- DETAIL PENGUMUMAN -->
<!-- ============================================ -->
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-10">
    
    <!-- Back Button -->
    <a href="{{ route('pengumuman.publik') }}" 
       class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-medium text-slate-400 hover:text-orange-600 transition group mb-4 sm:mb-5">
        <span class="group-hover:-translate-x-1 transition">←</span>
        Kembali ke daftar pengumuman
    </a>

    <!-- ============================================ -->
    <!-- CONTENT CARD -->
    <!-- ============================================ -->
    <div class="bg-white rounded-xl sm:rounded-2xl border border-slate-200 p-5 sm:p-6 lg:p-8 shadow-sm">
        
        <!-- Meta Info -->
        <div class="flex flex-wrap items-center gap-1.5 sm:gap-2 text-[10px] sm:text-xs text-slate-400 mb-2 sm:mb-3">
            <span>{{ $pengumuman->created_at->translatedFormat('d F Y, H:i') }}</span>
            <span class="text-slate-300">·</span>
            <span class="inline-flex items-center gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                {{ $pengumuman->dibuat_oleh }}
            </span>
            @if($pengumuman->isBaru())
                <span class="text-[8px] sm:text-[10px] font-bold uppercase px-1.5 sm:px-2 py-0.5 rounded-full bg-red-100 text-red-600 border border-red-200">
                    Baru
                </span>
            @endif
        </div>

        <!-- Title -->
        <h1 class="font-display font-bold text-xl sm:text-2xl lg:text-3xl text-slate-900 mb-5 sm:mb-6 leading-tight">
            {{ $pengumuman->judul }}
        </h1>

        <!-- Content -->
        <div class="prose prose-slate max-w-none prose-headings:font-display prose-p:text-sm sm:prose-p:text-base prose-a:text-orange-600 hover:prose-a:text-orange-700">
            {!! $pengumuman->konten !!}
        </div>

        <!-- ============================================ -->
        <!-- LAMPIRAN -->
        <!-- ============================================ -->
        @if(!empty($pengumuman->lampiran))
            <div class="mt-6 sm:mt-8 pt-5 sm:pt-6 border-t border-slate-200">
                <h3 class="text-sm sm:text-base font-semibold text-slate-700 mb-3 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 sm:w-5 h-4 sm:h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                    </svg>
                    Lampiran
                </h3>
                <div class="space-y-2 sm:space-y-2.5">
                    @foreach($pengumuman->lampiran as $l)
                        <a href="{{ $l['url'] }}" 
                           target="_blank" 
                           rel="noopener"
                           class="flex flex-wrap items-center gap-2 sm:gap-3 px-3 sm:px-4 py-2.5 sm:py-3 bg-slate-50 rounded-lg hover:bg-orange-50 hover:border-orange-200 transition border border-transparent group">
                            <span class="text-[9px] sm:text-[10px] font-bold uppercase px-1.5 sm:px-2 py-0.5 rounded bg-slate-200 text-slate-600 group-hover:bg-orange-200 group-hover:text-orange-700 transition">
                                {{ $l['type'] }}
                            </span>
                            <span class="text-xs sm:text-sm text-slate-700 font-medium group-hover:text-orange-600 transition flex-1">
                                {{ $l['name'] }}
                            </span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 sm:w-4 h-3 sm:h-4 text-slate-400 group-hover:text-orange-600 transition flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                            </svg>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <!-- ============================================ -->
    <!-- BACK TO TOP / NAVIGATION -->
    <!-- ============================================ -->
    <div class="mt-5 sm:mt-6 flex justify-between items-center">
        {{-- <a href="{{ route('pengumuman.publik') }}" 
           class="text-xs sm:text-sm text-slate-400 hover:text-orange-600 transition group inline-flex items-center gap-1.5">
            <span class="group-hover:-translate-x-1 transition">←</span>
            Kembali
        </a> --}}
        <div></div>
        <button onclick="window.scrollTo({top:0,behavior:'smooth'})" 
                class="text-xs sm:text-sm text-slate-400 hover:text-orange-600 transition group inline-flex items-center gap-1.5">
            Ke atas
            <span class="group-hover:translate-y-[-2px] transition">↑</span>
        </button>
    </div>
</div>

</div>