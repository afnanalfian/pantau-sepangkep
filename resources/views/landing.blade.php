<x-layouts.public :title="'Beranda'">

<!-- ============================================ -->
<!-- HERO SECTION - Mobile First -->
<!-- ============================================ -->
<section class="bg-slate-900 border-b border-orange-800">
    <div class="px-4 sm:px-6 lg:px-8">
        <div class="py-12 sm:py-16 lg:py-20 text-center">
            
            <!-- Badge - Compact mobile -->
            <div class="inline-flex items-center gap-1.5 sm:gap-2 px-2.5 sm:px-3 py-1 rounded-full bg-orange-600/20 border border-orange-600/30 text-orange-400 text-[10px] sm:text-xs font-medium uppercase tracking-wider mb-4 sm:mb-6">
                <span class="relative flex h-1.5 w-1.5">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-orange-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-orange-400"></span>
                </span>
                <span class="hidden sm:inline">Sensus Ekonomi 2026</span>
                <span class="sm:hidden">SE 2026</span>
            </div>

            <!-- Title - Responsive font -->
            <h1 class="font-display font-bold text-3xl sm:text-4xl lg:text-5xl text-white tracking-tight">
                PANTAU <span class="text-orange-400">SEPANGKEP</span>
            </h1>
            
            <p class="mt-2 sm:mt-3 text-slate-400 text-xs sm:text-sm max-w-2xl mx-auto px-2">
                Portal pemantauan Sensus Ekonomi 2026 di BPS Kabupaten Pangkajene dan Kepulauan.
            </p>

            <!-- CTA - Touch friendly -->
            <div class="mt-6 sm:mt-8 flex flex-col sm:flex-row items-center justify-center gap-2.5 sm:gap-3 px-4 sm:px-0">
                <a href="{{ route('dashboard.publik') }}" 
                   class="w-full sm:w-auto px-4 sm:px-6 py-2.5 sm:py-2.5 bg-orange-600 text-white rounded-lg text-sm font-medium hover:bg-orange-700 transition active:scale-95 text-center">
                    Dashboard
                </a>
                <a href="{{ route('qna.publik') }}" 
                   class="w-full sm:w-auto px-4 sm:px-6 py-2.5 sm:py-2.5 border border-slate-700 text-slate-300 rounded-lg text-sm font-medium hover:bg-slate-800 transition active:scale-95 text-center">
                    Tanya Jawab
                </a>
            </div>
        </div>
    </div>
</section>

<!-- ============================================ -->
<!-- CARDS SECTION - Mobile First -->
<!-- ============================================ -->
<section class="py-8 sm:py-12 lg:py-16 bg-white">
    <div class="px-4 sm:px-6 lg:px-8">
        
        <!-- Section Header -->
        <div class="text-center mb-6 sm:mb-8">
            <span class="inline-block px-2.5 sm:px-3 py-0.5 sm:py-1 rounded-full bg-orange-100 text-orange-700 text-[10px] sm:text-xs font-semibold uppercase tracking-wider mb-2 sm:mb-3">
                Akses Cepat
            </span>
            <h2 class="font-display font-semibold text-xl sm:text-2xl text-slate-800">Layanan Informasi</h2>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">Akses cepat informasi seputar Sensus Ekonomi 2026</p>
        </div>

        <!-- Cards Grid - Stack on mobile -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4 lg:gap-6">

            <!-- Card 1 -->
            <a href="{{ route('dashboard.publik') }}" 
               class="group bg-white rounded-xl p-4 sm:p-5 lg:p-6 border border-slate-200 hover:border-orange-300 hover:shadow-md transition active:scale-[0.98]">
                <div class="w-9 sm:w-10 h-9 sm:h-10 rounded-lg bg-orange-100 flex items-center justify-center text-orange-600 mb-3 sm:mb-4 group-hover:bg-orange-600 group-hover:text-white transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 sm:w-5 h-4 sm:h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/>
                    </svg>
                </div>
                <h3 class="font-display font-semibold text-slate-900 text-sm sm:text-base mb-0.5 sm:mb-1 group-hover:text-orange-600 transition">Dashboard Publik</h3>
                <p class="text-xs sm:text-sm text-slate-500 leading-relaxed">Progres pendataan dan kinerja petugas secara real-time.</p>
                <span class="inline-block mt-2 sm:mt-3 text-xs sm:text-sm font-medium text-orange-600 group-hover:translate-x-1 transition">Lihat →</span>
            </a>

            <!-- Card 2 -->
            <a href="{{ route('qna.publik') }}" 
               class="group bg-white rounded-xl p-4 sm:p-5 lg:p-6 border border-slate-200 hover:border-orange-300 hover:shadow-md transition active:scale-[0.98]">
                <div class="w-9 sm:w-10 h-9 sm:h-10 rounded-lg bg-orange-100 flex items-center justify-center text-orange-600 mb-3 sm:mb-4 group-hover:bg-orange-600 group-hover:text-white transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 sm:w-5 h-4 sm:h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                </div>
                <h3 class="font-display font-semibold text-slate-900 text-sm sm:text-base mb-0.5 sm:mb-1 group-hover:text-orange-600 transition">Tanya Jawab</h3>
                <p class="text-xs sm:text-sm text-slate-500 leading-relaxed">Ajukan pertanyaan seputar sensus kepada admin.</p>
                <span class="inline-block mt-2 sm:mt-3 text-xs sm:text-sm font-medium text-orange-600 group-hover:translate-x-1 transition">Lihat →</span>
            </a>

            <!-- Card 3 -->
            <a href="{{ route('pengumuman.publik') }}" 
               class="group bg-white rounded-xl p-4 sm:p-5 lg:p-6 border border-slate-200 hover:border-orange-300 hover:shadow-md transition active:scale-[0.98]">
                <div class="w-9 sm:w-10 h-9 sm:h-10 rounded-lg bg-orange-100 flex items-center justify-center text-orange-600 mb-3 sm:mb-4 group-hover:bg-orange-600 group-hover:text-white transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 sm:w-5 h-4 sm:h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/>
                    </svg>
                </div>
                <h3 class="font-display font-semibold text-slate-900 text-sm sm:text-base mb-0.5 sm:mb-1 group-hover:text-orange-600 transition">Pengumuman</h3>
                <p class="text-xs sm:text-sm text-slate-500 leading-relaxed">Informasi resmi terbaru untuk pegawai.</p>
                <span class="inline-block mt-2 sm:mt-3 text-xs sm:text-sm font-medium text-orange-600 group-hover:translate-x-1 transition">Lihat →</span>
            </a>
        </div>

        <!-- Login Link -->
        <div class="mt-6 sm:mt-8 text-center">
            <a href="{{ route('login') }}" class="text-xs sm:text-sm text-slate-400 hover:text-orange-600 transition group">
                Login Pegawai 
                <span class="inline-block group-hover:translate-x-1 transition">→</span>
            </a>
        </div>
    </div>
</section>

</x-layouts.public>