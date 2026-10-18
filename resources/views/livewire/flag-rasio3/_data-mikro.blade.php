@php
    $M = \App\Models\FlagRasio3Mikro::class;
    $rp = fn ($v) => $M::rupiah($v);
    $selectCls = 'px-3 py-2 rounded-lg border border-slate-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition text-sm bg-white';
    $adaFilter = $filterJenis || $filterKategori || $filterKbli || $filterStatus || $filterHasil || $search !== '';
@endphp
<div wire:key="fr3-mikro">

<!-- FILTERS -->
<div class="bg-white rounded-xl sm:rounded-2xl border border-slate-200 p-3 sm:p-4 mb-4 shadow-sm">
    <div class="flex flex-col gap-2">
        <!-- Tab UB / UM -->
        <div class="flex flex-wrap gap-1.5">
            @foreach(['' => 'Semua', 'UB' => 'UB · Anomali 172', 'UM' => 'UM · Anomali 173'] as $val => $lbl)
                <button type="button" wire:click="$set('filterJenis', '{{ $val }}')"
                        class="px-3 py-1.5 rounded-lg text-xs sm:text-sm font-semibold transition
                        {{ $filterJenis === $val ? 'bg-orange-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    {{ $lbl }}
                </button>
            @endforeach
        </div>

        <div class="flex flex-col sm:flex-row gap-2">
            <input type="text" wire:model.live.debounce.400ms="search"
                   placeholder="Cari nama usaha / assignment / KBLI / catatan..."
                   class="flex-1 px-3 py-2 rounded-lg border border-slate-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition text-sm">
            <button wire:click="exportMikro" wire:loading.attr="disabled" wire:target="exportMikro"
                    class="w-full sm:w-auto px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold transition active:scale-95 disabled:opacity-50">
                <span wire:loading.remove wire:target="exportMikro">Export Excel</span>
                <span wire:loading wire:target="exportMikro">Menyiapkan...</span>
            </button>
        </div>

        <div class="grid grid-cols-2 sm:flex sm:flex-wrap gap-2">
            <select wire:model.live="filterJenis" class="{{ $selectCls }}">
                <option value="">UB & UM</option>
                @foreach($jenisOptions as $k => $j)<option value="{{ $k }}">{{ $j['label'] }}</option>@endforeach
            </select>
            <select wire:model.live="filterKategori" class="{{ $selectCls }}">
                <option value="">Semua Kategori</option>
                @foreach($kategoriOptions as $k)<option value="{{ $k }}">Kategori {{ $k }}</option>@endforeach
            </select>
            <select wire:model.live="filterKbli" class="{{ $selectCls }} col-span-2 sm:max-w-xs">
                <option value="">Semua KBLI</option>
                @foreach($kbliOptions as $kode => $label)<option value="{{ $kode }}">{{ $label }}</option>@endforeach
            </select>
            <select wire:model.live="filterStatus" class="{{ $selectCls }}">
                <option value="">Semua Status</option>
                <option value="belum">Belum Tindak Lanjut</option>
                <option value="sudah">Sudah Tindak Lanjut</option>
            </select>
            @if($filterStatus !== 'belum')
                <select wire:model.live="filterHasil" class="{{ $selectCls }}">
                    <option value="">Semua Hasil</option>
                    @foreach($statusOptions as $val => $lbl)<option value="{{ $val }}">{{ $lbl }}</option>@endforeach
                </select>
            @endif
            <select wire:model.live="perPage" class="{{ $selectCls }}">
                <option value="10">10 / hal</option>
                <option value="20">20 / hal</option>
                <option value="50">50 / hal</option>
                <option value="100">100 / hal</option>
            </select>
            @if($adaFilter)
                <button wire:click="resetFilter" class="px-3 py-2 rounded-lg text-sm text-slate-500 hover:text-red-600 hover:bg-red-50 transition">Reset filter</button>
            @endif
        </div>
        <p class="text-xs text-slate-400">{{ number_format($mikros->total()) }} usaha ditemukan</p>
    </div>
</div>

{{-- Rincian metrik (dipakai di kartu mobile & baris tabel) --}}
@php
    $rincian = function ($m) use ($M, $rp) {
        $items = [['Tenaga Kerja', $m->tenaga_kerja ?? '-']];
        foreach ($M::KOLOM_RUPIAH as $f => $lbl) $items[] = [$lbl, $rp($m->{$f})];
        $items[] = ['Rasio Biaya Pembelian thd Omzet', $M::rasio($m->rasio_biaya_pembelian)];
        $items[] = ['Rasio NTB', $M::rasio($m->rasio_ntb, 6)];
        $items[] = ['Produktivitas', $rp($m->produktivitas)];
        $items[] = ['Reklasifikasi Skala Usaha', $m->reklasifikasi_skala_usaha ?? '-'];
        return $items;
    };
@endphp

<!-- MOBILE CARD VIEW -->
<div class="sm:hidden space-y-3">
    @forelse($mikros as $m)
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm" wire:key="fr3-card-{{ $m->id }}" x-data="{ open: false }">
            <div class="flex items-start justify-between gap-2">
                <div class="flex-1 min-w-0">
                    <span class="inline-block text-[10px] font-bold px-1.5 py-0.5 rounded mb-1 {{ $m->jenis_badge }}">{{ $m->jenis }}</span>
                    <div class="font-semibold text-slate-800 text-sm {{ $m->nama_usaha ? '' : 'italic text-slate-400' }}">{{ $m->nama_display }}</div>
                </div>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full whitespace-nowrap {{ $m->tindak_lanjut === 'sudah' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-red-50 text-red-700 border border-red-200' }}">
                    {{ $m->tindak_lanjut === 'sudah' ? '✓ Selesai' : '⏳ Belum' }}
                </span>
            </div>

            <div class="mt-2 p-2 rounded-lg bg-slate-50">
                <span class="font-mono font-bold text-sm text-slate-800">{{ $m->kode_kbli ?? '-' }}</span>
                <span class="text-[11px] text-slate-400">· Kategori {{ $m->kategori ?? '-' }}</span>
                <div class="text-[11px] text-slate-500 leading-snug">{{ $m->kbli_label }}</div>
            </div>

            <div class="grid grid-cols-3 gap-1 mt-2 text-[11px]">
                <div><div class="text-slate-400">Output</div><div class="font-semibold text-slate-700">{{ $rp($m->output) }}</div></div>
                <div><div class="text-slate-400">NTB</div><div class="font-semibold text-slate-700">{{ $rp($m->nilai_tambah) }}</div></div>
                <div><div class="text-slate-400">Rasio NTB</div><div class="font-bold text-orange-700">{{ $M::rasio($m->rasio_ntb) }}</div></div>
            </div>

            <button type="button" @click="open = !open" class="mt-2 text-[11px] font-medium text-slate-500 hover:text-slate-700">
                <span x-text="open ? '▲ Sembunyikan rincian' : '▼ Rincian metrik'"></span>
            </button>
            <dl x-show="open" x-cloak class="mt-1 text-[11px] divide-y divide-slate-100 border border-slate-100 rounded-lg">
                @foreach($rincian($m) as [$lbl, $val])
                    <div class="flex justify-between gap-2 px-2 py-1"><dt class="text-slate-500">{{ $lbl }}</dt><dd class="text-slate-800 font-medium text-right">{{ $val }}</dd></div>
                @endforeach
            </dl>

            @if($m->tindak_lanjut === 'sudah')
                <div class="mt-2 text-xs border-t border-slate-100 pt-2 space-y-1">
                    <span class="inline-block text-[10px] px-2 py-0.5 rounded-full font-medium {{ $m->status_color }}">{{ $m->status_label }}</span>
                    @if($m->catatan)
                        <div class="text-slate-600 bg-amber-50 border border-amber-100 rounded-md px-2 py-1 whitespace-pre-line">📝 {{ $m->catatan }}</div>
                    @endif
                </div>
            @endif

            <div class="flex items-center gap-3 mt-3 pt-2 border-t border-slate-100 flex-wrap">
                @if($m->fasih_link)
                    <a href="{{ $m->fasih_link }}" target="_blank" rel="noopener"
                       class="text-xs font-medium text-orange-600 hover:text-orange-700 hover:underline">🔗 {{ $m->fasih_link_short }}</a>
                @endif
                @if($m->tindak_lanjut === 'sudah')
                    <button wire:click="bukaModalTindakLanjut({{ $m->id }})" class="text-xs font-medium text-slate-600 hover:text-slate-800">Ubah</button>
                    <button wire:click="batalkanTindakLanjut({{ $m->id }})" wire:confirm="Batalkan tindak lanjut untuk usaha ini?"
                            class="text-xs font-medium text-amber-600 hover:text-amber-700">Batalkan</button>
                @else
                    <button wire:click="bukaModalTindakLanjut({{ $m->id }})" class="text-xs font-medium text-emerald-600 hover:text-emerald-700">Tandai Selesai</button>
                @endif
            </div>
        </div>
    @empty
        <div class="text-center py-8 bg-white rounded-xl border border-dashed border-slate-300">
            <p class="text-sm text-slate-400">Tidak ada data yang cocok dengan filter.</p>
        </div>
    @endforelse

    <div class="pt-2">{{ $mikros->links() }}</div>
</div>

<!-- DESKTOP TABLE -->
<div class="hidden sm:block bg-white rounded-xl sm:rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-xs uppercase text-slate-500 font-semibold border-b border-slate-200">
                <tr>
                    <th class="px-3 py-3 text-center">No</th>
                    <th class="px-3 py-3 text-left min-w-[220px]">Usaha</th>
                    <th class="px-3 py-3 text-left min-w-[200px]">KBLI</th>
                    <th class="px-3 py-3 text-right min-w-[190px]">Output · NTB · Rasio</th>
                    <th class="px-3 py-3 text-center">Status</th>
                    <th class="px-3 py-3 text-left min-w-[180px]">Catatan</th>
                    <th class="px-3 py-3 text-left">Fasih</th>
                    <th class="px-3 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            @forelse($mikros as $m)
                <tbody x-data="{ open: false }" class="border-b border-slate-100" wire:key="fr3-row-{{ $m->id }}">
                    <tr class="hover:bg-slate-50 transition align-top">
                        <td class="px-3 py-3 text-slate-500 text-center">{{ $m->no }}</td>
                        <td class="px-3 py-3">
                            <span class="inline-block text-[10px] font-bold px-1.5 py-0.5 rounded mb-1 {{ $m->jenis_badge }}">{{ $m->jenis }}</span>
                            <div class="font-semibold text-slate-700 {{ $m->nama_usaha ? '' : 'italic font-normal text-slate-400' }}">{{ $m->nama_display }}</div>
                            <div class="text-[11px] text-slate-400">TK {{ $m->tenaga_kerja ?? '-' }} · Skala {{ $m->reklasifikasi_skala_usaha ?? '-' }}</div>
                            <div class="text-[10px] text-slate-300 font-mono mt-0.5">{{ $m->assignment_id }}</div>
                        </td>
                        <td class="px-3 py-3">
                            <div class="font-mono font-bold text-slate-800">{{ $m->kode_kbli ?? '-' }} <span class="font-sans font-normal text-[11px] text-slate-400">Kat. {{ $m->kategori ?? '-' }}</span></div>
                            <div class="text-[11px] text-slate-500 leading-snug">{{ $m->kbli_label }}</div>
                        </td>
                        <td class="px-3 py-3 text-right text-xs whitespace-nowrap">
                            <div class="text-slate-700">Output {{ $rp($m->output) }}</div>
                            <div class="text-slate-700">NTB {{ $rp($m->nilai_tambah) }}</div>
                            <div class="font-bold text-orange-700">Rasio NTB {{ $M::rasio($m->rasio_ntb) }}</div>
                            <button type="button" @click="open = !open" class="mt-1 text-[11px] font-medium text-slate-500 hover:text-slate-700">
                                <span x-text="open ? '▲ Tutup' : '▼ Rincian metrik'"></span>
                            </button>
                        </td>
                        <td class="px-3 py-3 text-center">
                            @if($m->tindak_lanjut === 'sudah')
                                <span class="inline-block px-2 py-1 rounded-full text-[11px] font-bold {{ $m->status_color }}">{{ $m->status_label }}</span>
                                @if($m->tindak_lanjut_at)
                                    <div class="text-[10px] text-slate-400 mt-1">{{ $m->tindak_lanjut_at->format('d/m H:i') }}{{ $m->tindak_lanjut_by ? ' · ' . $m->tindak_lanjut_by : '' }}</div>
                                @endif
                            @else
                                <span class="px-2 py-1 rounded-full text-xs font-bold bg-red-50 text-red-700 border border-red-200">Belum</span>
                            @endif
                        </td>
                        <td class="px-3 py-3 text-xs text-slate-600 whitespace-pre-line">{{ $m->catatan ?: '–' }}</td>
                        <td class="px-3 py-3">
                            @if($m->fasih_link)
                                <a href="{{ $m->fasih_link }}" target="_blank" rel="noopener"
                                   class="text-xs font-medium text-orange-600 hover:text-orange-700 hover:underline whitespace-nowrap">{{ $m->fasih_link_short }}</a>
                            @else
                                <span class="text-xs text-slate-300">-</span>
                            @endif
                        </td>
                        <td class="px-3 py-3 text-right whitespace-nowrap">
                            @if($m->tindak_lanjut === 'sudah')
                                <button wire:click="bukaModalTindakLanjut({{ $m->id }})" class="text-xs font-medium text-slate-600 hover:text-slate-800 mr-2">Ubah</button>
                                <button wire:click="batalkanTindakLanjut({{ $m->id }})" wire:confirm="Batalkan tindak lanjut untuk usaha ini?"
                                        class="text-xs font-medium text-amber-600 hover:text-amber-700">Batalkan</button>
                            @else
                                <button wire:click="bukaModalTindakLanjut({{ $m->id }})" class="text-xs font-medium text-emerald-600 hover:text-emerald-700">Tandai Selesai</button>
                            @endif
                        </td>
                    </tr>
                    <tr x-show="open" x-cloak class="bg-slate-50/60">
                        <td></td>
                        <td colspan="7" class="px-3 pb-3">
                            <dl class="grid grid-cols-2 lg:grid-cols-4 gap-x-6 gap-y-1 text-[11px] pt-1">
                                @foreach($rincian($m) as [$lbl, $val])
                                    <div class="flex justify-between gap-2 border-b border-slate-100 py-0.5"><dt class="text-slate-500">{{ $lbl }}</dt><dd class="text-slate-800 font-medium text-right">{{ $val }}</dd></div>
                                @endforeach
                            </dl>
                        </td>
                    </tr>
                </tbody>
            @empty
                <tbody>
                    <tr>
                        <td colspan="8" class="px-4 py-10 text-center text-slate-400">Tidak ada data yang cocok dengan filter.</td>
                    </tr>
                </tbody>
            @endforelse
        </table>
    </div>
    <div class="px-4 py-3 border-t border-slate-100 bg-slate-50">{{ $mikros->links() }}</div>
</div>

<!-- MODAL TINDAK LANJUT -->
@if($showModal)
<div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" wire:keydown.escape.window="tutupModal">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden" @click.outside="$wire.tutupModal()">
        <div class="px-6 py-4 border-b border-slate-200 bg-gradient-to-r from-orange-50 to-amber-50">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-slate-800">{{ $modalEdit ? 'Ubah Tindak Lanjut' : 'Tandai Selesai' }}</h3>
                <button wire:click="tutupModal" class="text-slate-400 hover:text-slate-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <p class="text-sm text-slate-700 font-semibold mt-1">{{ $modalNama }}</p>
            <p class="text-xs text-slate-500">{{ $modalInfo }}</p>
        </div>

        <div class="px-6 py-4 space-y-4">
            <div class="space-y-2">
                <p class="text-xs font-semibold text-slate-600 uppercase tracking-wide">Hasil pemeriksaan <span class="text-red-500">*</span></p>
                @foreach($statusOptions as $value => $label)
                    @php
                        $active = ['sesuai_lapangan' => 'border-emerald-400 bg-emerald-50', 'diperbaiki' => 'border-blue-400 bg-blue-50'][$value] ?? 'border-orange-400 bg-orange-50';
                        $desc = [
                            'sesuai_lapangan' => 'Data sudah sesuai dengan kondisi usaha di lapangan.',
                            'diperbaiki' => 'Data tidak sesuai, sudah dikoreksi di FASIH.',
                        ][$value] ?? '';
                    @endphp
                    <label class="flex items-start gap-3 p-3 rounded-lg border-2 transition cursor-pointer {{ $selectedStatus === $value ? $active : 'border-slate-200 hover:border-slate-300' }}">
                        <input type="radio" wire:model.live="selectedStatus" value="{{ $value }}" class="mt-1 w-4 h-4 accent-orange-600 cursor-pointer">
                        <div>
                            <span class="font-semibold text-sm text-slate-800 block">{{ $label }}</span>
                            <span class="text-xs text-slate-500">{{ $desc }}</span>
                        </div>
                    </label>
                @endforeach
                @error('selectedStatus') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1.5">Catatan <span class="normal-case font-normal text-slate-400">(opsional)</span></label>
                <textarea wire:model="catatan" rows="3" maxlength="1000"
                          placeholder="{{ $selectedStatus === 'diperbaiki' ? 'Mis. Output dikoreksi, salah input satuan ribuan' : 'Tambahkan keterangan bila perlu...' }}"
                          class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition text-sm"></textarea>
                @error('catatan') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="px-6 py-4 border-t border-slate-200 bg-slate-50 flex gap-2 justify-end">
            <button wire:click="tutupModal"
                    class="px-4 py-2 rounded-lg border border-slate-300 hover:bg-slate-100 text-slate-600 text-sm font-medium transition active:scale-95">Batal</button>
            <button wire:click="prosesTandaiSelesai" wire:loading.attr="disabled" wire:target="prosesTandaiSelesai"
                    class="px-5 py-2 rounded-lg bg-orange-600 hover:bg-orange-700 text-white text-sm font-semibold transition active:scale-95 disabled:opacity-50">
                <span wire:loading.remove wire:target="prosesTandaiSelesai">{{ $modalEdit ? 'Simpan Perubahan' : 'Konfirmasi' }}</span>
                <span wire:loading wire:target="prosesTandaiSelesai">Menyimpan...</span>
            </button>
        </div>
    </div>
</div>
@endif

<!-- FLASH -->
@foreach(['success' => 'bg-emerald-50 border-emerald-200 text-emerald-700', 'info' => 'bg-blue-50 border-blue-200 text-blue-700'] as $key => $cls)
    @if(session()->has($key))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3500)" x-show="show" x-transition
             class="fixed top-4 right-4 z-50 max-w-sm border px-4 py-3 rounded-lg shadow-lg text-sm {{ $cls }}">
            {{ session($key) }}
        </div>
    @endif
@endforeach

</div>
