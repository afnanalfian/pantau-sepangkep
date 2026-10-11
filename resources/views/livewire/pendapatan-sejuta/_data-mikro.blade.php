@php
    $rp = fn ($v) => \App\Models\SejutaMikro::rupiah($v);
    $selectCls = 'px-3 py-2 rounded-lg border border-slate-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition text-sm bg-white';
    $adaFilter = $filterKecamatan || $filterDesa || $filterJalur || $filterKeberadaan || $filterStatus || $search !== '';
@endphp
<div wire:key="sejuta-mikro">

<!-- FILTERS -->
<div class="bg-white rounded-xl sm:rounded-2xl border border-slate-200 p-3 sm:p-4 mb-4 shadow-sm">
    <div class="flex flex-col gap-2">
        <div class="flex flex-col sm:flex-row gap-2">
            <input type="text" wire:model.live.debounce.400ms="search"
                   placeholder="Cari nama usaha / assignment / SLS / catatan..."
                   class="flex-1 px-3 py-2 rounded-lg border border-slate-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition text-sm">
            <button wire:click="exportMikro" wire:loading.attr="disabled" wire:target="exportMikro"
                    class="w-full sm:w-auto px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold transition active:scale-95 disabled:opacity-50">
                <span wire:loading.remove wire:target="exportMikro">Export Excel</span>
                <span wire:loading wire:target="exportMikro">Menyiapkan...</span>
            </button>
        </div>

        <div class="grid grid-cols-2 sm:flex sm:flex-wrap gap-2">
            <select wire:model.live="filterKecamatan" class="{{ $selectCls }}">
                <option value="">Semua Kecamatan</option>
                @foreach($kecamatanOptions as $k)<option value="{{ $k }}">{{ $k }}</option>@endforeach
            </select>
            <select wire:model.live="filterDesa" class="{{ $selectCls }}">
                <option value="">Semua Desa/Kel</option>
                @foreach($desaOptions as $k)<option value="{{ $k }}">{{ $k }}</option>@endforeach
            </select>
            <select wire:model.live="filterKeberadaan" class="{{ $selectCls }}">
                <option value="">Semua Keberadaan</option>
                @foreach($keberadaanOptions as $k)<option value="{{ $k }}">{{ $k }}</option>@endforeach
            </select>
            <select wire:model.live="filterJalur" class="{{ $selectCls }}">
                <option value="">Semua Jalur</option>
                @foreach($jalurOptions as $k)<option value="{{ $k }}">{{ $k }}</option>@endforeach
            </select>
            <select wire:model.live="filterStatus" class="{{ $selectCls }}">
                <option value="">Semua Status</option>
                <option value="belum">Belum Diselesaikan</option>
                <option value="sudah">Sudah Diselesaikan</option>
            </select>
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

<!-- MOBILE CARD VIEW -->
<div class="sm:hidden space-y-3">
    @forelse($mikros as $m)
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm" wire:key="sj-card-{{ $m->id }}">
            <div class="flex items-start justify-between gap-2">
                <div class="flex-1 min-w-0">
                    <div class="font-semibold text-slate-800 text-sm {{ $m->nama_usaha ? '' : 'italic text-slate-400' }}">{{ $m->nama_display }}</div>
                    <div class="text-[11px] text-slate-500 mt-0.5">{{ $m->nmkec ?? '-' }} · {{ $m->nmdesa ?? '-' }}</div>
                    <div class="text-[11px] text-slate-400">SLS {{ $m->sls_display }}</div>
                </div>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full whitespace-nowrap {{ $m->tindak_lanjut === 'sudah' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-red-50 text-red-700 border border-red-200' }}">
                    {{ $m->tindak_lanjut === 'sudah' ? '✓ Selesai' : '⏳ Belum' }}
                </span>
            </div>

            <div class="mt-2 p-2 rounded-lg bg-slate-50 text-xs space-y-0.5">
                <div class="flex justify-between"><span class="text-slate-500">Total pendapatan</span><span class="font-bold text-slate-800">{{ $rp($m->total_pendapatan) }}</span></div>
                <div class="flex justify-between"><span class="text-slate-500">Pendapatan utama</span><span class="text-slate-700">{{ $rp($m->pendapatan_utama) }}</span></div>
                <div class="flex justify-between"><span class="text-slate-500">Pendapatan lain</span><span class="text-slate-700">{{ $rp($m->pendapatan_lain) }}</span></div>
                <div class="flex justify-between"><span class="text-slate-500">Total pengeluaran</span><span class="text-slate-700">{{ $rp($m->total_pengeluaran) }}</span></div>
                <div class="flex justify-between"><span class="text-slate-500">Tenaga kerja</span><span class="text-slate-700">{{ $m->jml_tenaga_kerja ?? '-' }}</span></div>
            </div>

            <div class="flex flex-wrap gap-1 mt-2">
                @if($m->keberadaan)<span class="text-[10px] px-2 py-0.5 rounded-full font-medium {{ $m->keberadaan_color }}">{{ $m->keberadaan }}</span>@endif
                @if($m->jalur_pelaporan)<span class="text-[10px] px-2 py-0.5 rounded-full font-medium bg-slate-100 text-slate-600 border border-slate-200">{{ $m->jalur_pelaporan }}</span>@endif
            </div>

            @if($m->catatan)
                <div class="mt-2 text-xs text-slate-600 bg-amber-50 border border-amber-100 rounded-md px-2 py-1 whitespace-pre-line">📝 {{ $m->catatan }}</div>
            @endif

            <div class="flex items-center gap-3 mt-3 pt-2 border-t border-slate-100 flex-wrap">
                @if($m->fasih_link)
                    <a href="{{ $m->fasih_link }}" target="_blank" rel="noopener"
                       class="text-xs font-medium text-orange-600 hover:text-orange-700 hover:underline">🔗 {{ $m->fasih_link_short }}</a>
                @endif
                @if($m->tindak_lanjut === 'sudah')
                    <button wire:click="bukaModalTindakLanjut({{ $m->id }})" class="text-xs font-medium text-slate-600 hover:text-slate-800">Ubah Catatan</button>
                    <button wire:click="batalkanTindakLanjut({{ $m->id }})" wire:confirm="Batalkan status selesai untuk usaha ini? Catatannya ikut terhapus."
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
                    <th class="px-3 py-3 text-left min-w-[200px]">Usaha</th>
                    <th class="px-3 py-3 text-left min-w-[170px]">Wilayah</th>
                    <th class="px-3 py-3 text-right min-w-[170px]">Pendapatan & Pengeluaran</th>
                    <th class="px-3 py-3 text-center">TK</th>
                    <th class="px-3 py-3 text-left">Keberadaan</th>
                    <th class="px-3 py-3 text-center">Status</th>
                    <th class="px-3 py-3 text-left min-w-[180px]">Catatan</th>
                    <th class="px-3 py-3 text-left">Fasih</th>
                    <th class="px-3 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($mikros as $m)
                    <tr class="hover:bg-slate-50 transition align-top" wire:key="sj-row-{{ $m->id }}">
                        <td class="px-3 py-3 text-slate-500 text-center">{{ $m->no }}</td>
                        <td class="px-3 py-3">
                            <div class="font-semibold text-slate-700 {{ $m->nama_usaha ? '' : 'italic font-normal text-slate-400' }}">{{ $m->nama_display }}</div>
                            <div class="text-[11px] text-slate-400">No usaha {{ $m->no_usaha ?? '-' }} · {{ $m->jalur_pelaporan ?? '-' }}</div>
                            <div class="text-[10px] text-slate-300 font-mono mt-0.5">{{ $m->assignment_id }}</div>
                        </td>
                        <td class="px-3 py-3 text-xs text-slate-600">
                            <div class="font-medium">{{ $m->nmkec ?? '-' }}</div>
                            <div class="text-slate-500">{{ $m->nmdesa ?? '-' }}</div>
                            <div class="text-[11px] text-slate-400">SLS {{ $m->sls_display }}</div>
                        </td>
                        <td class="px-3 py-3 text-right text-xs whitespace-nowrap">
                            <div class="font-bold text-slate-800">{{ $rp($m->total_pendapatan) }}</div>
                            <div class="text-slate-500">Utama {{ $rp($m->pendapatan_utama) }}</div>
                            <div class="text-slate-500">Lain {{ $rp($m->pendapatan_lain) }}</div>
                            <div class="text-slate-400">Keluar {{ $rp($m->total_pengeluaran) }}</div>
                        </td>
                        <td class="px-3 py-3 text-center text-xs text-slate-600">{{ $m->jml_tenaga_kerja ?? '-' }}</td>
                        <td class="px-3 py-3">
                            @if($m->keberadaan)
                                <span class="inline-block text-[11px] px-2 py-0.5 rounded-full font-medium whitespace-nowrap {{ $m->keberadaan_color }}">{{ $m->keberadaan }}</span>
                            @else
                                <span class="text-xs text-slate-300">-</span>
                            @endif
                        </td>
                        <td class="px-3 py-3 text-center">
                            @if($m->tindak_lanjut === 'sudah')
                                <span class="inline-block px-2 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Selesai</span>
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
                                <button wire:click="batalkanTindakLanjut({{ $m->id }})" wire:confirm="Batalkan status selesai untuk usaha ini? Catatannya ikut terhapus."
                                        class="text-xs font-medium text-amber-600 hover:text-amber-700">Batalkan</button>
                            @else
                                <button wire:click="bukaModalTindakLanjut({{ $m->id }})" class="text-xs font-medium text-emerald-600 hover:text-emerald-700">Tandai Selesai</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="px-4 py-10 text-center text-slate-400">Tidak ada data yang cocok dengan filter.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-4 py-3 border-t border-slate-100 bg-slate-50">{{ $mikros->links() }}</div>
</div>

<!-- MODAL TANDAI SELESAI + CATATAN -->
@if($showModal)
<div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" wire:keydown.escape.window="tutupModal">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden" @click.outside="$wire.tutupModal()">
        <div class="px-6 py-4 border-b border-slate-200 bg-gradient-to-r from-orange-50 to-amber-50">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-slate-800">{{ $modalEdit ? 'Ubah Catatan' : 'Tandai Selesai' }}</h3>
                <button wire:click="tutupModal" class="text-slate-400 hover:text-slate-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <p class="text-sm text-slate-700 font-semibold mt-1">{{ $modalNama }}</p>
            <p class="text-xs text-slate-500">{{ $modalInfo }}</p>
        </div>

        <div class="px-6 py-4">
            <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1.5">Catatan <span class="normal-case font-normal text-slate-400">(opsional)</span></label>
            <textarea wire:model="catatan" rows="4" maxlength="1000"
                      placeholder="Mis. Usaha musiman, pendapatan setahun memang kecil / sudah dikoreksi menjadi Rp 12 jt"
                      class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition text-sm"></textarea>
            @error('catatan') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="px-6 py-4 border-t border-slate-200 bg-slate-50 flex gap-2 justify-end">
            <button wire:click="tutupModal"
                    class="px-4 py-2 rounded-lg border border-slate-300 hover:bg-slate-100 text-slate-600 text-sm font-medium transition active:scale-95">Batal</button>
            <button wire:click="prosesTandaiSelesai" wire:loading.attr="disabled" wire:target="prosesTandaiSelesai"
                    class="px-5 py-2 rounded-lg bg-orange-600 hover:bg-orange-700 text-white text-sm font-semibold transition active:scale-95 disabled:opacity-50">
                <span wire:loading.remove wire:target="prosesTandaiSelesai">{{ $modalEdit ? 'Simpan Catatan' : 'Tandai Selesai' }}</span>
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
