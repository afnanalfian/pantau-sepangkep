<div>

<div class="flex items-center justify-between mb-5">
    <p class="text-sm text-slate-500">Anomali dibuat per pekan. Setiap batch bersifat independen satu sama lain.</p>
    @if($canUpload)
        <a href="{{ route('pegawai.anomali.upload') }}" class="px-4 py-2.5 rounded-lg bg-[#0B2A4A] hover:bg-[#0d3760] text-white text-sm font-semibold">+ Unggah Anomali Baru</a>
    @endif
</div>

<div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse($batches as $b)
        <a href="{{ route('pegawai.anomali.detail', $b) }}" class="bg-white rounded-2xl border border-slate-200 p-5 hover:border-[#0F7B8A] hover:shadow-md transition">
            <div class="flex items-center justify-between mb-3">
                <span class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-600 flex items-center justify-center">
                    <x-icon name="alert" class="w-5 h-5" />
                </span>
                <span class="text-xs font-bold px-2 py-1 rounded-full
                    {{ $b->persen >= 80 ? 'bg-emerald-50 text-emerald-700' : ($b->persen >= 40 ? 'bg-amber-50 text-amber-700' : 'bg-red-50 text-red-700') }}">
                    {{ $b->persen }}% selesai
                </span>
            </div>
            <h3 class="font-display font-bold text-slate-800">Anomali {{ $b->tanggal->translatedFormat('d F Y') }}</h3>
            <p class="text-xs text-slate-400 mt-1">{{ $b->mikros_count }} kasus mikro tercatat</p>
        </a>
    @empty
        <div class="col-span-full bg-white rounded-2xl border border-dashed border-slate-300 p-16 text-center">
            <p class="text-slate-400 font-medium">Belum ada anomali pekanan yang diunggah.</p>
        </div>
    @endforelse
</div>

</div>
