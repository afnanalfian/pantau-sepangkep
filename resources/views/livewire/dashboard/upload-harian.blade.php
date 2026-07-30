<div>

<!-- ============================================ -->
<!-- PAGE HEADER -->
<!-- ============================================ -->
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-10">
    
    <h1 class="font-display font-bold text-xl sm:text-2xl text-slate-900 mb-1">Unggah Data Harian Dashboard</h1>
    <p class="text-xs sm:text-sm text-slate-500 mb-5 sm:mb-6">Unggah file excel template 50 kolom. Data pada tanggal yang sama akan digantikan oleh unggahan terbaru.</p>

    <!-- ============================================ -->
    <!-- SUCCESS MESSAGE -->
    <!-- ============================================ -->
    @if($successMessage)
        <div class="mb-5 px-4 py-3 rounded-lg bg-emerald-50 text-emerald-700 text-sm font-medium border border-emerald-200 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ $successMessage }}
        </div>
    @endif

    <!-- ============================================ -->
    <!-- UPLOAD FORM -->
    <!-- ============================================ -->
    <div class="bg-white rounded-xl sm:rounded-2xl border border-slate-200 p-4 sm:p-6 mb-6 sm:mb-8 shadow-sm">
        <form wire:submit="simpanUpload" class="space-y-4 sm:space-y-5">
            
            <div>
                <label class="block text-xs sm:text-sm font-semibold text-slate-700 mb-1.5">Tanggal Progres <span class="text-red-500">*</span></label>
                <input type="date" 
                       wire:model="tanggal" 
                       class="w-full px-3 sm:px-4 py-2.5 rounded-lg border border-slate-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition text-sm">
                @error('tanggal') <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p> @enderror
            </div>
            
            <div>
                <label class="block text-xs sm:text-sm font-semibold text-slate-700 mb-1.5">File Excel (.xlsx) <span class="text-red-500">*</span></label>
                <input type="file" 
                       wire:model="file" 
                       accept=".xlsx,.xls" 
                       class="w-full text-xs sm:text-sm border border-slate-300 rounded-lg px-3 py-2.5 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-orange-50 file:text-orange-600 hover:file:bg-orange-100">
                <div wire:loading wire:target="file" class="text-xs text-slate-400 mt-1">Mengunggah file...</div>
                @error('file') <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p> @enderror
            </div>
            
            <button type="submit" 
                    wire:loading.attr="disabled" 
                    wire:target="upload"
                    class="w-full sm:w-auto px-5 py-2.5 rounded-lg bg-orange-600 hover:bg-orange-700 text-white font-semibold text-sm transition active:scale-95 disabled:opacity-50 shadow-sm shadow-orange-600/20">
                <span wire:loading.remove wire:target="upload">Proses & Simpan</span>
                <span wire:loading wire:target="upload" class="inline-flex items-center gap-2">
                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Memproses...
                </span>
            </button>
        </form>
    </div>

    <!-- ============================================ -->
    <!-- RIWAYAT UPLOAD -->
    <!-- ============================================ -->
    <h2 class="font-display font-semibold text-lg text-slate-800 mb-3">Riwayat Unggahan</h2>
    
    <!-- Mobile: Card View -->
    <div class="sm:hidden space-y-3">
        @forelse($riwayat as $r)
            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="font-semibold text-slate-800 text-sm">{{ $r->tanggal->translatedFormat('d F Y') }}</span>
                    <button wire:click="deleteUpload({{ $r->id }})" wire:confirm="Hapus data tanggal ini?" 
                            class="text-xs font-medium text-red-500 hover:text-red-600 transition">
                        Hapus
                    </button>
                </div>
                <p class="text-xs text-slate-500 mt-1">{{ $r->nama_file }}</p>
                <p class="text-xs text-slate-400 mt-0.5">{{ $r->slsDailies()->count() }} baris</p>
            </div>
        @empty
            <div class="text-center py-8 bg-white rounded-xl border border-dashed border-slate-300">
                <p class="text-sm text-slate-400">Belum ada riwayat.</p>
            </div>
        @endforelse
    </div>

    <!-- Desktop: Table View -->
    <div class="hidden sm:block bg-white rounded-xl sm:rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-xs uppercase text-slate-500 font-semibold border-b border-slate-200">
                <tr>
                    <th class="px-4 py-3 text-left">Tanggal</th>
                    <th class="px-4 py-3 text-left">File</th>
                    <th class="px-4 py-3 text-center">Baris</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($riwayat as $r)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-4 py-3 font-semibold text-slate-700">{{ $r->tanggal->translatedFormat('d F Y') }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $r->nama_file }}</td>
                        <td class="px-4 py-3 text-center text-slate-500">{{ $r->slsDailies()->count() }}</td>
                        <td class="px-4 py-3 text-right">
                            <button wire:click="deleteUpload({{ $r->id }})" wire:confirm="Hapus data tanggal ini?" 
                                    class="text-xs font-semibold text-red-500 hover:text-red-600 transition">
                                Hapus
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-8 text-center text-slate-400">Belum ada riwayat.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

</div>