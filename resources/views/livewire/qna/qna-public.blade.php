<div>

<!-- ============================================ -->
<!-- QNA PAGE - Mobile First -->
<!-- ============================================ -->
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-10">
    
    <!-- Header -->
    <div class="mb-6 sm:mb-8">
        <h1 class="font-display font-bold text-xl sm:text-2xl text-slate-900 mb-1">Tanya Jawab</h1>
        <p class="text-xs sm:text-sm text-slate-500">Punya pertanyaan seputar Sensus Ekonomi 2026? Tanyakan di sini — dijawab langsung oleh Admin atau Instruktur Daerah (INDA).</p>
    </div>

    <!-- ============================================ -->
    <!-- FORM SECTION -->
    <!-- ============================================ -->
    <div class="bg-white rounded-xl sm:rounded-2xl border border-slate-200 p-4 sm:p-6 mb-6 sm:mb-10 shadow-sm">
        
        @if($terkirim)
            <!-- Success State -->
            <div class="text-center py-4 sm:py-6">
                <div class="w-12 sm:w-14 h-12 sm:h-14 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 sm:w-7 h-6 sm:h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <p class="font-semibold text-slate-800 text-sm sm:text-base">Pertanyaan Anda telah terkirim!</p>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">Silakan pantau halaman ini untuk jawaban dari Admin/INDA.</p>
                <button wire:click="$set('terkirim', false)" 
                        class="mt-3 sm:mt-4 text-sm font-semibold text-orange-600 hover:text-orange-700 transition inline-flex items-center gap-1.5">
                    Ajukan pertanyaan lain →
                </button>
            </div>
        @else
            <!-- Form -->
            <form wire:submit="kirim" class="space-y-4 sm:space-y-5">
                
                <!-- Anonim Checkbox -->
                <div class="flex items-center gap-2">
                    <input type="checkbox" 
                           wire:model.live="anonim" 
                           id="anonim" 
                           class="w-4 h-4 rounded border-slate-300 text-orange-600 focus:ring-orange-500 focus:ring-2">
                    <label for="anonim" class="text-xs sm:text-sm text-slate-600">Kirim sebagai anonim</label>
                </div>

                <!-- Nama Field -->
                @if(!$anonim)
                <div>
                    <label class="block text-xs sm:text-sm font-semibold text-slate-700 mb-1.5">Nama <span class="text-slate-400 font-normal">(opsional)</span></label>
                    <input type="text" 
                           wire:model="nama" 
                           placeholder="Nama Anda" 
                           class="w-full px-3 sm:px-4 py-2.5 sm:py-3 rounded-lg border border-slate-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition text-sm sm:text-base">
                </div>
                @endif

                <!-- Pertanyaan Field -->
                <div>
                    <label class="block text-xs sm:text-sm font-semibold text-slate-700 mb-1.5">Pertanyaan <span class="text-red-500">*</span></label>
                    <textarea wire:model="pertanyaan" 
                              rows="4" 
                              placeholder="Tuliskan pertanyaan Anda..." 
                              class="w-full px-3 sm:px-4 py-2.5 sm:py-3 rounded-lg border border-slate-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition text-sm sm:text-base resize-y"></textarea>
                    @error('pertanyaan') 
                        <p class="text-xs text-red-500 mt-1.5 flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Submit Button -->
                <button type="submit" 
                        class="w-full sm:w-auto px-5 sm:px-6 py-2.5 sm:py-3 rounded-lg bg-orange-600 hover:bg-orange-700 text-white font-semibold text-sm sm:text-base transition active:scale-[0.98]">
                    Kirim Pertanyaan
                </button>
            </form>
        @endif
    </div>

    <!-- ============================================ -->
    <!-- ANSWERED QUESTIONS LIST -->
    <!-- ============================================ -->
    <div>
        <h2 class="font-display font-semibold text-lg sm:text-xl text-slate-900 mb-4 flex items-center gap-2">
            Pertanyaan yang Sudah Dijawab
            <span class="text-xs font-medium text-slate-400 bg-slate-100 px-2.5 py-0.5 rounded-full">{{ $daftar->total() }}</span>
        </h2>
        
        <div class="space-y-3 sm:space-y-4">
            @forelse($daftar as $q)
                <!-- Question Card -->
                <div class="bg-white rounded-xl sm:rounded-2xl border border-slate-200 p-4 sm:p-5 shadow-sm hover:shadow-md transition">
                    
                    <!-- Question Header -->
                    <div class="flex flex-wrap items-center gap-1.5 sm:gap-2 mb-2">
                        <span class="text-xs font-bold text-white bg-orange-600 px-2 py-0.5 rounded-full">Q</span>
                        <span class="text-sm font-semibold text-slate-700">{{ $q->nama ?: 'Anonim' }}</span>
                        <span class="text-xs text-slate-400">· {{ $q->created_at->diffForHumans() }}</span>
                    </div>
                    
                    <!-- Question Content -->
                    <p class="text-slate-700 text-sm sm:text-base mb-3">{{ $q->pertanyaan }}</p>
                    
                    <!-- Answer Box -->
                    <div class="bg-slate-50 rounded-xl p-3 sm:p-4 border border-slate-100">
                        <div class="flex items-center gap-1.5 sm:gap-2 mb-1.5">
                            <span class="text-xs font-bold text-white bg-emerald-600 px-2 py-0.5 rounded-full">A</span>
                            <span class="text-sm font-semibold text-slate-700">{{ $q->dijawab_oleh }}</span>
                        </div>
                        <p class="text-slate-600 text-sm sm:text-base">{{ $q->jawaban }}</p>
                    </div>
                </div>
            @empty
                <!-- Empty State -->
                <div class="text-center py-10 sm:py-16">
                    <div class="w-12 sm:w-16 h-12 sm:h-16 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 sm:w-8 h-6 sm:h-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                    </div>
                    <p class="text-sm text-slate-400">Belum ada pertanyaan yang dijawab.</p>
                    <p class="text-xs text-slate-400 mt-1">Jadilah yang pertama bertanya!</p>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($daftar->hasPages())
            <div class="mt-5 sm:mt-6">
                {{ $daftar->links() }}
            </div>
        @endif
    </div>
</div>

</div>