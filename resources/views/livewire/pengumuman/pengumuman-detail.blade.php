<div>
judul">

<div class="max-w-3xl mx-auto px-5 lg:px-8 py-10">
    <a href="{{ route('pengumuman.publik') }}" class="text-xs font-semibold text-slate-400 hover:text-[#0F7B8A] transition">&larr; Kembali ke daftar pengumuman</a>

    <div class="bg-white rounded-2xl border border-slate-200 p-8 mt-4">
        <div class="flex items-center gap-2 text-xs text-slate-400 mb-2">
            <span>{{ $pengumuman->created_at->translatedFormat('d F Y, H:i') }}</span>
            <span>&middot;</span>
            <span>{{ $pengumuman->dibuat_oleh }}</span>
        </div>
        <h1 class="font-display font-bold text-2xl text-[#0B2A4A] mb-6">{{ $pengumuman->judul }}</h1>

        <div class="prose prose-slate max-w-none prose-headings:font-display">
            {!! $pengumuman->konten !!}
        </div>

        @if(!empty($pengumuman->lampiran))
            <div class="mt-8 pt-6 border-t border-slate-100">
                <h3 class="text-sm font-semibold text-slate-700 mb-3">Lampiran</h3>
                <div class="space-y-2">
                    @foreach($pengumuman->lampiran as $l)
                        <a href="{{ $l['url'] }}" target="_blank" rel="noopener"
                           class="flex items-center gap-3 px-4 py-3 bg-slate-50 rounded-lg hover:bg-slate-100 transition">
                            <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded bg-slate-200 text-slate-600">{{ $l['type'] }}</span>
                            <span class="text-sm text-slate-700 font-medium">{{ $l['name'] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>

</div>
