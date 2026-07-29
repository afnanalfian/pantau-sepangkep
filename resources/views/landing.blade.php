<x-layouts.public :title="'Beranda'">

<section class="relative overflow-hidden bg-[#0B2A4A]">
    <div class="absolute inset-0 opacity-[0.07]" style="background-image:radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 28px 28px;"></div>

    <div class="relative max-w-5xl mx-auto px-5 lg:px-8 pt-20 pb-28 text-center">
        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/10 border border-white/15 text-white/80 text-xs font-semibold uppercase tracking-wider mb-7">
            <span class="relative flex h-2 w-2">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#E2A63B] opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 bg-[#E2A63B]"></span>
            </span>
            Sensus Ekonomi 2026 &middot; Live Monitoring
        </div>

        <h1 class="font-display font-bold text-4xl sm:text-6xl text-white tracking-tight leading-[1.05]">
            PANTAU SEPANGKEP
        </h1>
        <p class="mt-5 text-white/70 text-base sm:text-lg max-w-2xl mx-auto leading-relaxed">
            Portal informasi dan pemantauan kegiatan Sensus Ekonomi 2026
            di BPS Kabupaten Pangkajene dan Kepulauan &mdash; progres lapangan, kinerja petugas,
            dan kualitas data dalam satu tempat.
        </p>
    </div>
</section>

<section class="relative -mt-16 pb-20">
    <div class="max-w-5xl mx-auto px-5 lg:px-8">
        <div class="grid sm:grid-cols-3 gap-5">

            <a href="{{ route('dashboard.publik') }}" class="group bg-white rounded-2xl p-7 shadow-lg shadow-slate-900/5 border border-slate-100 hover:-translate-y-1 hover:shadow-xl transition-all duration-300">
                <div class="w-11 h-11 rounded-xl bg-[#0F7B8A]/10 flex items-center justify-center text-[#0F7B8A] mb-5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
                </div>
                <h3 class="font-display font-bold text-lg text-[#0B2A4A] mb-1.5">Dashboard Publik</h3>
                <p class="text-sm text-slate-500 leading-relaxed">Progres pendataan, kinerja PPL/PML, dan produktivitas harian secara terbuka.</p>
                <span class="inline-flex items-center gap-1.5 mt-4 text-sm font-semibold text-[#0F7B8A]">Buka dashboard <span class="group-hover:translate-x-1 transition-transform">&rarr;</span></span>
            </a>

            <a href="{{ route('qna.publik') }}" class="group bg-white rounded-2xl p-7 shadow-lg shadow-slate-900/5 border border-slate-100 hover:-translate-y-1 hover:shadow-xl transition-all duration-300">
                <div class="w-11 h-11 rounded-xl bg-[#E2A63B]/10 flex items-center justify-center text-[#E2A63B] mb-5">
                    <x-icon name="chat" class="w-6 h-6" />
                </div>
                <h3 class="font-display font-bold text-lg text-[#0B2A4A] mb-1.5">QnA</h3>
                <p class="text-sm text-slate-500 leading-relaxed">Punya pertanyaan seputar sensus? Tanyakan, dijawab langsung oleh admin/INDA.</p>
                <span class="inline-flex items-center gap-1.5 mt-4 text-sm font-semibold text-[#E2A63B]">Ajukan pertanyaan <span class="group-hover:translate-x-1 transition-transform">&rarr;</span></span>
            </a>

            @php $pengumumanBaru = \App\Models\Pengumuman::where('created_at', '>=', now()->subDays(3))->count(); @endphp
            <a href="{{ route('pengumuman.publik') }}" class="group relative bg-white rounded-2xl p-7 shadow-lg shadow-slate-900/5 border border-slate-100 hover:-translate-y-1 hover:shadow-xl transition-all duration-300">
                @if($pengumumanBaru > 0)
                    <span class="absolute -top-2.5 -right-2.5 min-w-[26px] h-[26px] px-1.5 rounded-full bg-red-500 text-white text-xs font-bold flex items-center justify-center shadow-md shadow-red-500/30 ring-2 ring-white">{{ $pengumumanBaru }}</span>
                @endif
                <div class="w-11 h-11 rounded-xl bg-emerald-500/10 flex items-center justify-center text-emerald-600 mb-5">
                    <x-icon name="megaphone" class="w-6 h-6" />
                </div>
                <h3 class="font-display font-bold text-lg text-[#0B2A4A] mb-1.5">Pengumuman</h3>
                <p class="text-sm text-slate-500 leading-relaxed">Informasi resmi terbaru untuk petugas maupun pegawai.</p>
                <span class="inline-flex items-center gap-1.5 mt-4 text-sm font-semibold text-emerald-600">Lihat pengumuman <span class="group-hover:translate-x-1 transition-transform">&rarr;</span></span>
            </a>
        </div>

        <div class="mt-5 text-center">
            <a href="{{ route('login') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-[#0B2A4A] hover:text-[#0F7B8A] transition">
                Login sebagai Pegawai <span>&rarr;</span>
            </a>
        </div>
    </div>
</section>

</x-layouts.public>
