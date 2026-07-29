<div>

<div class="max-w-3xl mx-auto px-5 lg:px-8 py-10">
    <h1 class="font-display font-bold text-2xl text-[#0B2A4A] mb-1">Unggah Data Harian Dashboard</h1>
    <p class="text-sm text-slate-500 mb-6">Unggah file excel template 50 kolom. Data pada tanggal yang sama akan digantikan oleh unggahan terbaru.</p>

    @if($successMessage)
        <div class="mb-5 px-4 py-3 rounded-lg bg-emerald-50 text-emerald-700 text-sm font-medium border border-emerald-200">{{ $successMessage }}</div>
    @endif

    <div class="bg-white rounded-2xl border border-slate-200 p-6 mb-8">
        <form wire:submit="simpanUpload" class="space-y-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Tanggal Progres</label>
                <input type="date" wire:model="tanggal" class="w-full px-4 py-2.5 rounded-lg border border-slate-300 outline-none focus:border-[#0F7B8A]">
                @error('tanggal') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">File Excel (.xlsx)</label>
                <input type="file" wire:model="file" accept=".xlsx,.xls" class="w-full text-sm border border-slate-300 rounded-lg px-4 py-2.5">
                <div wire:loading wire:target="file" class="text-xs text-slate-400 mt-1">Mengunggah file...</div>
                @error('file') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <button type="submit" wire:loading.attr="disabled" wire:target="upload"
                    class="px-5 py-2.5 rounded-lg bg-[#0B2A4A] hover:bg-[#0d3760] text-white font-semibold text-sm transition disabled:opacity-50">
                <span wire:loading.remove wire:target="upload">Proses & Simpan</span>
                <span wire:loading wire:target="upload">Memproses...</span>
            </button>
        </form>
    </div>

    <h2 class="font-display font-bold text-lg text-[#0B2A4A] mb-3">Riwayat Unggahan</h2>
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-xs uppercase text-slate-500 font-semibold">
                <tr>
                    <th class="px-4 py-3 text-left">Tanggal</th>
                    <th class="px-4 py-3 text-left">File</th>
                    <th class="px-4 py-3 text-center">Baris</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($riwayat as $r)
                    <tr>
                        <td class="px-4 py-3 font-semibold text-slate-700">{{ $r->tanggal->translatedFormat('d F Y') }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $r->nama_file }}</td>
                        <td class="px-4 py-3 text-center text-slate-500">{{ $r->slsDailies()->count() }}</td>
                        <td class="px-4 py-3 text-right">
                            <button wire:click="deleteUpload({{ $r->id }})" wire:confirm="Hapus data tanggal ini?" class="text-xs font-semibold text-red-500 hover:underline">Hapus</button>
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
