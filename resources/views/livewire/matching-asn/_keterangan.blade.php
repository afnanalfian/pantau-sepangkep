<div class="rounded-xl border border-sky-200 bg-sky-50/70 p-3 sm:p-4 flex items-start gap-2.5">
    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-sky-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
    </svg>
    <div class="text-xs sm:text-sm text-sky-900">
        <p class="font-semibold">{{ config('matching-asn.keterangan') }}</p>
        <p class="mt-0.5 text-sky-800/90">
            Baris <b>duplikat</b> (kolom DUPLIKAT = HAPUS) ditangani dengan <b>menghapusnya dari FASIH-SM</b>, lalu dikonfirmasi di sini.
            Baris tidak duplikat diselesaikan sebagai <b>Telah Didata</b> atau <b>Telah Dihapus</b>.
        </p>
    </div>
</div>
