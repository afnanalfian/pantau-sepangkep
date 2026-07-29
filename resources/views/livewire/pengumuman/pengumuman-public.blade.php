<div>

<div class="max-w-3xl mx-auto px-5 lg:px-8 py-10">
    <h1 class="font-display font-bold text-2xl text-[#0B2A4A] mb-1">Pengumuman</h1>
    <p class="text-sm text-slate-500 mb-6">Informasi resmi untuk petugas dan pegawai Sensus Ekonomi 2026.</p>

    <div class="space-y-3">
        @forelse($daftar as $p)
            <a href="{{ route('pengumuman.detail', $p) }}" class="block bg-white rounded-2xl border border-slate-200 p-5 hover:border-[#0F7B8A] hover:shadow-sm transition">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <h3 class="font-display font-bold text-slate-800">{{ $p->judul }}</h3>
                            @if($p->isBaru())
                                <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-full bg-red-50 text-red-600">Baru</span>
                            @endif
                        </div>
                        <p class="text-sm text-slate-500 line-clamp-2">{{ $p->ringkasan ?: strip_tags($p->konten) }}</p>
                    </div>
                    <span class="text-xs text-slate-400 whitespace-nowrap">{{ $p->created_at->translatedFormat('d M Y') }}</span>
                </div>
            </a>
        @empty
            <p class="text-sm text-slate-400 text-center py-16 bg-white rounded-2xl border border-dashed border-slate-300">Belum ada pengumuman.</p>
        @endforelse
    </div>
    <div class="mt-4">{{ $daftar->links() }}</div>
</div>

</div>
