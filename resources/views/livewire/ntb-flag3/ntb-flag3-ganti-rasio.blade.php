@php
    $rs = fn ($v) => $v === null ? '–' : rtrim(rtrim(number_format($v, 6, ',', '.'), '0'), ',');
@endphp
<div class="space-y-5 max-w-3xl">

<a href="{{ route('pegawai.ntb-flag3') }}" class="text-xs text-slate-400 hover:text-orange-600 transition">← Anomali NTB Flag 3</a>

@if($successMessage)
    <div class="px-4 py-3 rounded-lg bg-emerald-50 text-emerald-700 text-sm font-medium border border-emerald-200 flex items-start gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span>{{ $successMessage }}</span>
    </div>
@endif

<div class="bg-white rounded-xl sm:rounded-2xl border border-slate-200 p-4 sm:p-6 shadow-sm">
    <div class="flex items-center gap-2 mb-1">
        <h3 class="font-semibold text-slate-800">Ganti Rasio NTB Massal</h3>
        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-800 text-white">ADMIN</span>
    </div>
    <p class="text-xs sm:text-sm text-slate-500 mb-4 sm:mb-5">
        Unggah Excel berkolom <b>assignment_id</b> dan <b>rasio_ntb</b>. Nilai rasio NTB di tabel Anomali NTB Flag 3
        akan diganti untuk setiap assignment_id yang cocok. Kolom lain, status tindak lanjut, dan catatan <b>tidak</b> berubah.
        Klik <b>Periksa</b> dulu; database baru berubah setelah Anda klik <b>Terapkan</b>.
    </p>

    <form wire:submit="periksa" class="space-y-4">
        <div>
            <label class="block text-xs sm:text-sm font-semibold text-slate-700 mb-1.5">Berlaku untuk</label>
            <select wire:model.live="batchId"
                    class="w-full px-3 py-2.5 rounded-lg border border-slate-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition text-sm bg-white">
                <option value="">Semua batch (setiap assignment_id yang cocok di batch mana pun)</option>
                @foreach($batches as $b)
                    <option value="{{ $b->id }}">Hanya batch {{ $b->tanggal->translatedFormat('d F Y') }}</option>
                @endforeach
            </select>
            @error('batchId') <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-xs sm:text-sm font-semibold text-slate-700 mb-1.5">File Excel <span class="text-red-500">*</span></label>
            <input type="file" wire:model="file" accept=".xlsx,.xls,.csv"
                   class="w-full text-xs sm:text-sm border border-slate-300 rounded-lg px-3 py-2.5 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-orange-50 file:text-orange-600 hover:file:bg-orange-100">
            <div wire:loading wire:target="file" class="text-xs text-slate-400 mt-1">Mengunggah...</div>
            @error('file') <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p> @enderror
            <p class="text-[11px] text-slate-400 mt-1.5">
                Desimal boleh pakai koma (<code>0,997916667</code>) atau titik (<code>0.997916667</code>).
                Jika assignment_id muncul lebih dari sekali di file, nilai pada baris terakhir yang dipakai.
            </p>
        </div>

        @unless($ringkasan)
            <button type="submit" wire:loading.attr="disabled" wire:target="periksa, file"
                    class="w-full sm:w-auto px-5 py-2.5 rounded-lg bg-orange-600 hover:bg-orange-700 text-white font-semibold text-sm transition active:scale-95 disabled:opacity-50">
                <span wire:loading.remove wire:target="periksa">Periksa</span>
                <span wire:loading wire:target="periksa">Memeriksa file...</span>
            </button>
        @endunless
    </form>
</div>

@if($ringkasan)
    @php $r = $ringkasan; @endphp
    <div class="bg-white rounded-xl sm:rounded-2xl border-2 border-orange-200 p-4 sm:p-6 shadow-sm space-y-4" wire:key="pratinjau-{{ $batchId }}">
        <div>
            <h3 class="font-semibold text-slate-800">Pratinjau perubahan</h3>
            <p class="text-xs text-slate-500">{{ $namaFile }} · target: {{ $batchId === '' ? 'semua batch' : 'satu batch' }} · database belum diubah</p>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            @foreach([
                ['Assignment di file', $r['assignment_file'], 'text-slate-800'],
                ['Cocok di database', $r['assignment_cocok'], 'text-emerald-600'],
                ['Baris akan berubah', $r['baris_berubah'], 'text-orange-600'],
                ['Tidak ditemukan', $r['tidak_ditemukan'], $r['tidak_ditemukan'] ? 'text-red-600' : 'text-slate-400'],
            ] as [$lbl, $val, $cls])
                <div class="rounded-xl border border-slate-200 p-3">
                    <p class="text-[11px] font-medium text-slate-400 uppercase tracking-wide">{{ $lbl }}</p>
                    <p class="text-xl font-bold {{ $cls }}">{{ number_format($val) }}</p>
                </div>
            @endforeach
        </div>

        <ul class="text-xs text-slate-500 space-y-0.5">
            <li>{{ number_format($r['baris_db']) }} baris di database cocok ({{ number_format($r['baris_db'] - $r['baris_berubah']) }} di antaranya nilainya sudah sama).</li>
            @if($r['duplikat'])<li class="text-amber-700">{{ number_format($r['duplikat']) }} assignment_id muncul lebih dari sekali di file; nilai baris terakhir yang dipakai.</li>@endif
            @if($r['invalid'])<li class="text-red-600">{{ number_format($r['invalid']) }} baris dilewati karena assignment_id kosong atau rasio bukan angka.</li>@endif
        </ul>

        @if(count($r['contoh_perubahan']))
            <div class="overflow-x-auto">
                <p class="text-xs font-semibold text-slate-600 mb-1">Contoh perubahan ({{ count($r['contoh_perubahan']) }} pertama)</p>
                <table class="w-full text-xs">
                    <thead class="bg-slate-50 text-slate-500 uppercase">
                        <tr>
                            <th class="px-2 py-1.5 text-left">Usaha</th>
                            <th class="px-2 py-1.5 text-right">Rasio lama</th>
                            <th class="px-2 py-1.5 text-right">Rasio baru</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($r['contoh_perubahan'] as $c)
                            <tr>
                                <td class="px-2 py-1.5">
                                    <div class="text-slate-700">{{ $c['nama_usaha'] ?: '(Tanpa nama)' }}</div>
                                    <div class="text-[10px] text-slate-400 font-mono">{{ $c['assignment_id'] }}</div>
                                </td>
                                <td class="px-2 py-1.5 text-right font-mono text-slate-400 line-through">{{ $rs($c['lama']) }}</td>
                                <td class="px-2 py-1.5 text-right font-mono font-bold text-orange-700">{{ $rs($c['baru']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if(count($r['contoh_tidak_ditemukan']))
            <details class="text-xs">
                <summary class="cursor-pointer text-red-600 font-medium">Contoh assignment_id yang tidak ditemukan ({{ number_format($r['tidak_ditemukan']) }})</summary>
                <ul class="mt-1 font-mono text-[11px] text-slate-500 space-y-0.5">
                    @foreach($r['contoh_tidak_ditemukan'] as $id)<li>{{ $id }}</li>@endforeach
                    @if($r['tidak_ditemukan'] > count($r['contoh_tidak_ditemukan']))<li>…</li>@endif
                </ul>
            </details>
        @endif

        @if(count($r['contoh_invalid']))
            <details class="text-xs">
                <summary class="cursor-pointer text-red-600 font-medium">Baris yang dilewati ({{ number_format($r['invalid']) }})</summary>
                <ul class="mt-1 text-[11px] text-slate-500 space-y-0.5">
                    @foreach($r['contoh_invalid'] as $iv)
                        <li>Baris {{ $iv['baris'] }}: <span class="font-mono">{{ $iv['assignment_id'] ?? '(kosong)' }}</span> → "{{ $iv['nilai'] ?? '' }}"</li>
                    @endforeach
                </ul>
            </details>
        @endif

        <div class="flex flex-col sm:flex-row gap-2 pt-2 border-t border-slate-100">
            @if($r['baris_db'] > 0)
                <button wire:click="terapkan" wire:loading.attr="disabled" wire:target="terapkan"
                        wire:confirm="Ganti rasio NTB untuk {{ number_format($r['baris_db']) }} baris? Tindakan ini tidak bisa dibatalkan."
                        class="px-5 py-2.5 rounded-lg bg-orange-600 hover:bg-orange-700 text-white font-semibold text-sm transition active:scale-95 disabled:opacity-50">
                    <span wire:loading.remove wire:target="terapkan">Terapkan ({{ number_format($r['baris_db']) }} baris)</span>
                    <span wire:loading wire:target="terapkan">Menyimpan...</span>
                </button>
            @else
                <p class="text-sm text-red-600 py-2">Tidak ada assignment_id yang cocok di database, tidak ada yang bisa diterapkan.</p>
            @endif
            <button wire:click="batal" class="px-4 py-2.5 rounded-lg border border-slate-300 hover:bg-slate-100 text-slate-600 text-sm font-medium transition">Batal</button>
        </div>
    </div>
@endif

</div>
