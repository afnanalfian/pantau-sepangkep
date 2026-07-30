<div>

<!-- ============================================ -->
<!-- FILTER BUTTONS -->
<!-- ============================================ -->
<div class="flex flex-wrap items-center gap-2 sm:gap-3 mb-5 sm:mb-6">
    @foreach(['semua' => 'Semua', 'menunggu' => 'Menunggu', 'dijawab' => 'Dijawab'] as $key => $label)
        <button wire:click="$set('filterStatus', '{{ $key }}')"
                class="px-3 sm:px-4 py-1.5 sm:py-2 rounded-lg text-xs sm:text-sm font-medium transition active:scale-95
                {{ $filterStatus === $key 
                    ? 'bg-orange-600 text-white shadow-sm shadow-orange-600/20' 
                    : 'bg-white border border-slate-200 text-slate-500 hover:border-orange-300 hover:text-orange-600' }}">
            {{ $label }}
            @if($key === 'menunggu' && $jumlahMenunggu > 0)
                <span class="ml-1 px-1.5 py-0.5 rounded-full bg-red-500 text-white text-[9px] sm:text-[10px]">{{ $jumlahMenunggu }}</span>
            @endif
        </button>
    @endforeach
</div>

<!-- ============================================ -->
<!-- QUESTIONS LIST -->
<!-- ============================================ -->
<div class="space-y-3 sm:space-y-4">
    @forelse($daftar as $q)
        <div class="bg-white rounded-xl sm:rounded-2xl border border-slate-200 p-4 sm:p-5 shadow-sm hover:shadow-md transition">
            
            <!-- Question Header -->
            <div class="flex flex-wrap items-start justify-between gap-2 mb-2">
                <div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
                    <span class="text-xs font-bold text-white bg-orange-600 px-2 py-0.5 rounded-full">Q</span>
                    <span class="text-sm font-semibold text-slate-800">{{ $q->nama ?: 'Anonim' }}</span>
                    <span class="text-xs text-slate-400">· {{ $q->created_at->diffForHumans() }}</span>
                </div>
                <!-- Status Badge -->
                <span class="text-[10px] sm:text-xs font-semibold px-2 sm:px-2.5 py-0.5 sm:py-1 rounded-full flex-shrink-0
                    {{ $q->status === 'dijawab' 
                        ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' 
                        : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                    {{ $q->status === 'dijawab' ? '✓ Dijawab' : '⏳ Menunggu' }}
                </span>
            </div>
            
            <!-- Question Content -->
            <p class="text-slate-700 text-sm sm:text-base mb-3">{{ $q->pertanyaan }}</p>

            <!-- ========================================== -->
            <!-- ANSWER SECTION -->
            <!-- ========================================== -->
            @if($q->status === 'dijawab')
                <!-- Tampilkan Jawaban -->
                <div class="bg-slate-50 rounded-xl p-3 sm:p-4 border border-slate-100">
                    <div class="flex items-center gap-1.5 sm:gap-2 mb-1.5">
                        <span class="text-xs font-bold text-white bg-emerald-600 px-2 py-0.5 rounded-full">A</span>
                        <span class="text-sm font-semibold text-slate-700">{{ $q->dijawab_oleh }}</span>
                        <span class="text-xs text-slate-400">· {{ $q->updated_at->diffForHumans() }}</span>
                    </div>
                    <p class="text-slate-600 text-sm sm:text-base">{{ $q->jawaban }}</p>
                </div>
            @else
                <!-- Form Jawaban -->
                <div class="mt-2">
                    <div class="flex flex-col sm:flex-row gap-2">
                        <textarea wire:model="jawabanDraft.{{ $q->id }}" 
                                  rows="2" 
                                  placeholder="Tulis jawaban..." 
                                  class="flex-1 px-3 sm:px-4 py-2 sm:py-2.5 rounded-lg border border-slate-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition text-sm sm:text-base resize-y"></textarea>
                        <button wire:click="simpanJawaban({{ $q->id }})" 
                                class="px-4 sm:px-5 py-2 sm:py-2.5 rounded-lg bg-orange-600 hover:bg-orange-700 text-white text-sm font-semibold transition active:scale-95 whitespace-nowrap">
                            Kirim Jawaban
                        </button>
                    </div>
                </div>
            @endif
        </div>
    @empty
        <!-- Empty State -->
        <div class="text-center py-10 sm:py-16 bg-white rounded-xl sm:rounded-2xl border border-dashed border-slate-300">
            <div class="w-12 sm:w-16 h-12 sm:h-16 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 sm:w-8 h-6 sm:h-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
            </div>
            <p class="text-sm text-slate-400">Tidak ada pertanyaan</p>
            <p class="text-xs text-slate-400 mt-1">Belum ada pertanyaan yang masuk dari publik.</p>
        </div>
    @endforelse
</div>

<!-- ============================================ -->
<!-- PAGINATION -->
<!-- ============================================ -->
@if($daftar->hasPages())
    <div class="mt-4 sm:mt-6">
        {{ $daftar->links() }}
    </div>
@endif

</div>