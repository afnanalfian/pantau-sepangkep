<div>

<div class="max-w-3xl mx-auto px-5 lg:px-8 py-10">
    <h1 class="font-display font-bold text-2xl text-[#0B2A4A] mb-1">Tanya Jawab</h1>
    <p class="text-sm text-slate-500 mb-6">Punya pertanyaan seputar Sensus Ekonomi 2026? Tanyakan di sini &mdash; dijawab langsung oleh Admin atau Instruktur Daerah (INDA).</p>

    <div class="bg-white rounded-2xl border border-slate-200 p-6 mb-10">
        @if($terkirim)
            <div class="text-center py-6">
                <div class="w-14 h-14 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
                <p class="font-semibold text-slate-700">Pertanyaan Anda telah terkirim!</p>
                <p class="text-sm text-slate-500 mt-1">Silakan pantau halaman ini untuk jawaban dari Admin/INDA.</p>
                <button wire:click="$set('terkirim', false)" class="mt-4 text-sm font-semibold text-[#0F7B8A] hover:underline">Ajukan pertanyaan lain</button>
            </div>
        @else
            <form wire:submit="kirim" class="space-y-4">
                <div class="flex items-center gap-2">
                    <input type="checkbox" wire:model.live="anonim" id="anonim" class="rounded border-slate-300 text-[#0F7B8A] focus:ring-[#0F7B8A]">
                    <label for="anonim" class="text-sm text-slate-600">Kirim sebagai anonim</label>
                </div>
                @if(!$anonim)
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Nama (opsional)</label>
                    <input type="text" wire:model="nama" placeholder="Nama Anda" class="w-full px-4 py-2.5 rounded-lg border border-slate-300 outline-none focus:border-[#0F7B8A]">
                </div>
                @endif
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Pertanyaan</label>
                    <textarea wire:model="pertanyaan" rows="4" placeholder="Tuliskan pertanyaan Anda..." class="w-full px-4 py-2.5 rounded-lg border border-slate-300 outline-none focus:border-[#0F7B8A]"></textarea>
                    @error('pertanyaan') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <button type="submit" class="px-5 py-2.5 rounded-lg bg-[#0B2A4A] hover:bg-[#0d3760] text-white font-semibold text-sm transition">Kirim Pertanyaan</button>
            </form>
        @endif
    </div>

    <h2 class="font-display font-bold text-lg text-[#0B2A4A] mb-4">Pertanyaan yang Sudah Dijawab</h2>
    <div class="space-y-4">
        @forelse($daftar as $q)
            <div class="bg-white rounded-2xl border border-slate-200 p-5">
                <div class="flex items-center gap-2 mb-2">
                    <span class="text-xs font-bold text-white bg-[#0F7B8A] px-2 py-0.5 rounded-full">Q</span>
                    <span class="text-sm font-semibold text-slate-700">{{ $q->nama ?: 'Anonim' }}</span>
                    <span class="text-xs text-slate-400">&middot; {{ $q->created_at->diffForHumans() }}</span>
                </div>
                <p class="text-slate-600 text-sm mb-3">{{ $q->pertanyaan }}</p>
                <div class="bg-slate-50 rounded-xl p-4">
                    <div class="flex items-center gap-2 mb-1.5">
                        <span class="text-xs font-bold text-white bg-emerald-600 px-2 py-0.5 rounded-full">A</span>
                        <span class="text-sm font-semibold text-slate-700">{{ $q->dijawab_oleh }}</span>
                    </div>
                    <p class="text-slate-600 text-sm">{{ $q->jawaban }}</p>
                </div>
            </div>
        @empty
            <p class="text-sm text-slate-400 text-center py-10">Belum ada pertanyaan yang dijawab.</p>
        @endforelse
    </div>
    <div class="mt-4">{{ $daftar->links() }}</div>
</div>

</div>
