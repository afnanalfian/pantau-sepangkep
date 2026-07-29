<div>

<div class="flex items-center gap-2 mb-5">
    @foreach(['menunggu' => 'Menunggu', 'dijawab' => 'Dijawab', 'semua' => 'Semua'] as $key => $label)
        <button wire:click="$set('filterStatus', '{{ $key }}')"
            class="px-4 py-2 rounded-lg text-sm font-semibold transition {{ $filterStatus === $key ? 'bg-[#0B2A4A] text-white' : 'bg-white border border-slate-200 text-slate-500' }}">
            {{ $label }}
            @if($key === 'menunggu' && $jumlahMenunggu > 0)
                <span class="ml-1 px-1.5 py-0.5 rounded-full bg-red-500 text-white text-[10px]">{{ $jumlahMenunggu }}</span>
            @endif
        </button>
    @endforeach
</div>

<div class="space-y-4">
    @forelse($daftar as $q)
        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold text-white bg-[#0F7B8A] px-2 py-0.5 rounded-full">Q</span>
                    <span class="text-sm font-semibold text-slate-700">{{ $q->nama ?: 'Anonim' }}</span>
                    <span class="text-xs text-slate-400">&middot; {{ $q->created_at->diffForHumans() }}</span>
                </div>
                <span class="text-xs font-semibold px-2 py-1 rounded-full {{ $q->status === 'dijawab' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                    {{ $q->status === 'dijawab' ? 'Dijawab' : 'Menunggu' }}
                </span>
            </div>
            <p class="text-slate-600 text-sm mb-3">{{ $q->pertanyaan }}</p>

            @if($q->status === 'dijawab')
                <div class="bg-slate-50 rounded-xl p-4">
                    <div class="flex items-center gap-2 mb-1.5">
                        <span class="text-xs font-bold text-white bg-emerald-600 px-2 py-0.5 rounded-full">A</span>
                        <span class="text-sm font-semibold text-slate-700">{{ $q->dijawab_oleh }}</span>
                    </div>
                    <p class="text-slate-600 text-sm">{{ $q->jawaban }}</p>
                </div>
            @else
                <div class="flex gap-2">
                    <textarea wire:model="jawabanDraft.{{ $q->id }}" rows="2" placeholder="Tulis jawaban..."
                              class="flex-1 px-3 py-2 rounded-lg border border-slate-300 text-sm outline-none focus:border-[#0F7B8A]"></textarea>
                    <button wire:click="simpanJawaban({{ $q->id }})" class="px-4 py-2 rounded-lg bg-[#0F7B8A] hover:bg-[#0d6b78] text-white text-sm font-semibold self-end">Jawab</button>
                </div>
            @endif
        </div>
    @empty
        <p class="text-sm text-slate-400 text-center py-10 bg-white rounded-2xl border border-dashed border-slate-300">Tidak ada pertanyaan.</p>
    @endforelse
</div>
<div class="mt-4">{{ $daftar->links() }}</div>

</div>
