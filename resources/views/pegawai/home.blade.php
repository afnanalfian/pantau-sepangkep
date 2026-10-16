<x-layouts.app :title="'Beranda'">

@php
    $roleLabel = session('role_label');
    $jam = now()->hour;
    $sapaan = $jam < 11 ? 'Selamat pagi' : ($jam < 15 ? 'Selamat siang' : ($jam < 18 ? 'Selamat sore' : 'Selamat malam'));

    // Foto 2–9 diacak setiap halaman dimuat
    $fotoAcak = collect(range(2, 9))->shuffle();
@endphp

<style>
    @keyframes fotoMuncul {
        from { opacity: 0; transform: translateY(16px) scale(.98); }
        to   { opacity: 1; transform: translateY(0) scale(1); }
    }
    .foto-muncul { opacity: 0; animation: fotoMuncul .6s ease-out forwards; }
</style>

<!-- ============================================ -->
<!-- HERO: FOTO BERSAMA (1.png) -->
<!-- ============================================ -->
<section class="relative overflow-hidden rounded-2xl shadow-xl shadow-orange-600/10 mb-6 sm:mb-8 foto-muncul">
    <img src="{{ asset('images/random/1.png') }}"
         alt="Foto bersama tim Sensus Ekonomi 2026"
         data-lightbox
         class="w-full h-64 sm:h-80 lg:h-[28rem] object-cover object-center cursor-zoom-in">

    <!-- Gradasi agar teks terbaca -->
    <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-black/75 via-black/20 to-transparent"></div>

    <div class="pointer-events-none absolute inset-x-0 bottom-0 p-5 sm:p-8 lg:p-10 text-white">
        <p class="text-orange-200 text-xs sm:text-sm font-medium tracking-wide">
            {{ $sapaan }}{{ $roleLabel ? ', ' . $roleLabel : '' }} 👋
        </p>
        <h1 class="font-display font-bold text-2xl sm:text-4xl lg:text-5xl mt-1 leading-tight drop-shadow">
            Selamat Datang di <span class="text-orange-400">Pantau Sepangkep</span>
        </h1>
        <p class="mt-2 text-white/80 text-xs sm:text-sm max-w-xl">
            Bersama menyukseskan Sensus Ekonomi 2026 di Kabupaten Pangkep.
        </p>
    </div>
</section>

<!-- ============================================ -->
<!-- GALERI FOTO ACAK (2.png – 9.png) -->
<!-- ============================================ -->
<section>
    <div class="flex items-end justify-between mb-3 sm:mb-4">
        <h2 class="font-display font-semibold text-slate-800 text-base sm:text-lg">Momen Kebersamaan</h2>
        <span class="text-[10px] sm:text-xs text-slate-400">Klik foto untuk memperbesar</span>
    </div>

    <div class="columns-2 md:columns-3 xl:columns-4 gap-3 sm:gap-4">
        @foreach($fotoAcak as $i => $no)
            <figure class="foto-muncul group relative mb-3 sm:mb-4 break-inside-avoid overflow-hidden rounded-xl bg-slate-100 shadow-sm hover:shadow-lg transition"
                    style="animation-delay: {{ 0.15 + $loop->index * 0.08 }}s">
                <img src="{{ asset('images/random/' . $no . '.png') }}"
                     alt="Dokumentasi kegiatan {{ $no }}"
                     loading="lazy"
                     data-lightbox
                     class="w-full h-auto block cursor-zoom-in transition duration-500 group-hover:scale-105">
                <div class="pointer-events-none absolute inset-0 ring-1 ring-inset ring-black/5 rounded-xl group-hover:ring-orange-400/60 transition"></div>
            </figure>
        @endforeach
    </div>
</section>

<!-- ============================================ -->
<!-- LIGHTBOX -->
<!-- ============================================ -->
<div id="lightbox" class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/90 p-4">
    <button type="button" id="lb-close"
            class="absolute top-4 right-4 w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 text-white text-2xl leading-none">&times;</button>
    <button type="button" id="lb-prev"
            class="absolute left-2 sm:left-6 w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 text-white text-2xl leading-none">&#8249;</button>
    <img id="lb-img" src="" alt="" class="max-h-[85vh] max-w-full rounded-lg shadow-2xl">
    <button type="button" id="lb-next"
            class="absolute right-2 sm:right-6 w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 text-white text-2xl leading-none">&#8250;</button>
</div>

<script>
    (function () {
        const fotos = Array.from(document.querySelectorAll('[data-lightbox]'));
        const box = document.getElementById('lightbox');
        const img = document.getElementById('lb-img');
        let idx = 0;

        const tampil = (i) => {
            idx = (i + fotos.length) % fotos.length;
            img.src = fotos[idx].src;
            img.alt = fotos[idx].alt;
        };
        const buka = (i) => { tampil(i); box.classList.remove('hidden'); box.classList.add('flex'); document.body.style.overflow = 'hidden'; };
        const tutup = () => { box.classList.add('hidden'); box.classList.remove('flex'); document.body.style.overflow = ''; };

        fotos.forEach((f, i) => f.addEventListener('click', () => buka(i)));
        document.getElementById('lb-close').addEventListener('click', tutup);
        document.getElementById('lb-prev').addEventListener('click', (e) => { e.stopPropagation(); tampil(idx - 1); });
        document.getElementById('lb-next').addEventListener('click', (e) => { e.stopPropagation(); tampil(idx + 1); });
        box.addEventListener('click', (e) => { if (e.target === box) tutup(); });
        document.addEventListener('keydown', (e) => {
            if (box.classList.contains('hidden')) return;
            if (e.key === 'Escape') tutup();
            if (e.key === 'ArrowLeft') tampil(idx - 1);
            if (e.key === 'ArrowRight') tampil(idx + 1);
        });
    })();
</script>

</x-layouts.app>
