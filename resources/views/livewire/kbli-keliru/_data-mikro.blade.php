@php
    $selectCls = 'px-3 py-2 rounded-lg border border-slate-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition text-sm bg-white';
    $adaFilter = $filterPic || $filterKategori || $filterRekomendasi || $filterAlasan || $filterDasar || $filterStatus || $filterHasil || $search !== '';
    $chip = fn ($ya) => $ya ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-50 text-slate-500 border-slate-200';
@endphp
<div wire:key="kk-mikro">

<!-- FILTERS -->
<div class="bg-white rounded-xl sm:rounded-2xl border border-slate-200 p-3 sm:p-4 mb-4 shadow-sm">
    <div class="flex flex-col gap-2">
        <div class="flex flex-col sm:flex-row gap-2">
            <input type="text" wire:model.live.debounce.400ms="search"
                   placeholder="Cari kegiatan utama / assignment / KBLI / PIC / rekomendasi / catatan..."
                   class="flex-1 px-3 py-2 rounded-lg border border-slate-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition text-sm">
            <button wire:click="exportMikro" wire:loading.attr="disabled" wire:target="exportMikro"
                    class="w-full sm:w-auto px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold transition active:scale-95 disabled:opacity-50 whitespace-nowrap">
                <span wire:loading.remove wire:target="exportMikro">Export Excel ({{ number_format($mikros->total()) }})</span>
                <span wire:loading wire:target="exportMikro">Menyiapkan...</span>
            </button>
        </div>

        <div class="grid grid-cols-2 sm:flex sm:flex-wrap gap-2">
            <select wire:model.live="filterPic" class="{{ $selectCls }} col-span-2 sm:max-w-xs">
                <option value="">Semua PIC</option>
                @foreach($picOptions as $v => $l)<option value="{{ $v }}">{{ $l }}</option>@endforeach
            </select>
            <select wire:model.live="filterKategori" class="{{ $selectCls }}">
                <option value="">Kategori saat ini</option>
                @foreach($kategoriOptions as $v => $l)<option value="{{ $v }}">{{ $l }}</option>@endforeach
            </select>
            <select wire:model.live="filterRekomendasi" class="{{ $selectCls }}">
                <option value="">Rekomendasi kategori</option>
                @foreach($rekomendasiOptions as $v => $l)<option value="{{ $v }}">{{ $l }}</option>@endforeach
            </select>
            <select wire:model.live="filterAlasan" class="{{ $selectCls }} col-span-2 sm:max-w-xs">
                <option value="">Semua alasan tidak sesuai</option>
                @foreach($alasanOptions as $v => $l)<option value="{{ $v }}">{{ $l }}</option>@endforeach
            </select>
            <select wire:model.live="filterDasar" class="{{ $selectCls }}">
                <option value="">Semua dasar rule</option>
                @foreach($dasarOptions as $v => $l)<option value="{{ $v }}">{{ $l }}</option>@endforeach
            </select>
            <select wire:model.live="filterStatus" class="{{ $selectCls }}">
                <option value="">Semua Status</option>
                <option value="belum">Belum Tindak Lanjut</option>
                <option value="sudah">Sudah Tindak Lanjut</option>
            </select>
            @if($filterStatus !== 'belum')
                <select wire:model.live="filterHasil" class="{{ $selectCls }}">
                    <option value="">Semua Hasil</option>
                    @foreach($statusOptions as $v => $l)<option value="{{ $v }}">{{ $l }}</option>@endforeach
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
        <p class="text-xs text-slate-400">{{ number_format($mikros->total()) }} usaha ditemukan · Export berisi semua baris & kolom sesuai filter.</p>
    </div>
</div>

<!-- MOBILE CARD VIEW -->
<div class="sm:hidden space-y-3">
    @forelse($mikros as $m)
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm" wire:key="kk-card-{{ $m->id }}">
            <div class="flex items-start justify-between gap-2">
                <div class="font-semibold text-slate-800 text-sm">{{ $m->nama_display }}</div>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full whitespace-nowrap {{ $m->tindak_lanjut === 'sudah' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-red-50 text-red-700 border border-red-200' }}">
                    {{ $m->tindak_lanjut === 'sudah' ? '✓ Selesai' : '⏳ Belum' }}
                </span>
            </div>
            <div class="text-[11px] text-slate-500 mt-0.5">PIC: <b class="{{ $m->pic ? 'text-slate-700' : 'text-amber-600' }}">{{ $m->pic ?? 'Tanpa PIC' }}</b></div>

            <div class="mt-2 p-2 rounded-lg bg-slate-50 text-xs">
                <span class="font-bold text-slate-700">Kat. {{ $m->kategori ?? '-' }}</span>
                <span class="font-mono font-bold text-slate-800 ml-1">{{ $m->kbli_akhir ?? '-' }}</span>
                @if($m->kbli_label)<div class="text-[11px] text-slate-500 leading-snug">{{ $m->kbli_label }}</div>@endif
                <div class="flex flex-wrap gap-1 mt-1">
                    @foreach($m->rincian_13b as [$l, $v, $ya])
                        <span class="text-[10px] px-1.5 py-0.5 rounded border {{ $chip($ya) }}">{{ $l }}: {{ $v }}</span>
                    @endforeach
                </div>
            </div>
            @if($m->alasan_tidak_sesuai)<div class="mt-2 text-[11px] text-rose-700">⚠️ {{ $m->alasan_tidak_sesuai }}</div>@endif
            @if($m->keterangan_rekomendasi)
                <div class="mt-1 text-[11px] text-indigo-800 bg-indigo-50 border border-indigo-100 rounded-md px-2 py-1">💡 {{ $m->keterangan_rekomendasi }}</div>
            @endif

            @if($m->tindak_lanjut === 'sudah')
                <div class="mt-2 text-xs border-t border-slate-100 pt-2 space-y-1">
                    <span class="inline-block text-[10px] px-2 py-0.5 rounded-full font-medium {{ $m->status_color }}">{{ $m->status_label }}</span>
                    @if($m->catatan)<div class="text-slate-600 bg-amber-50 border border-amber-100 rounded-md px-2 py-1 whitespace-pre-line">📝 {{ $m->catatan }}</div>@endif
                </div>
            @endif

            <div class="flex items-center gap-3 mt-3 pt-2 border-t border-slate-100 flex-wrap">
                @if($m->fasih_link)
                    <a href="{{ $m->fasih_link }}" target="_blank" rel="noopener" class="text-xs font-medium text-orange-600 hover:text-orange-700 hover:underline">🔗 {{ $m->fasih_link_short }}</a>
                @endif
                @if($m->tindak_lanjut === 'sudah')
                    <button wire:click="bukaModalTindakLanjut({{ $m->id }})" class="text-xs font-medium text-slate-600 hover:text-slate-800">Ubah</button>
                    <button wire:click="batalkanTindakLanjut({{ $m->id }})" wire:confirm="Batalkan tindak lanjut untuk usaha ini?" class="text-xs font-medium text-amber-600 hover:text-amber-700">Batalkan</button>
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
                    <th class="px-3 py-3 text-left min-w-[190px]">Usaha</th>
                    <th class="px-3 py-3 text-left min-w-[190px]">KBLI Saat Ini &amp; Rincian 13b</th>
                    <th class="px-3 py-3 text-left min-w-[200px]">Rule &amp; Alasan</th>
                    <th class="px-3 py-3 text-left min-w-[220px]">Rekomendasi</th>
                    <th class="px-3 py-3 text-left">PIC</th>
                    <th class="px-3 py-3 text-center">Status</th>
                    <th class="px-3 py-3 text-left min-w-[150px]">Catatan</th>
                    <th class="px-3 py-3 text-left">Fasih</th>
                    <th class="px-3 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($mikros as $m)
                    <tr class="hover:bg-slate-50 transition align-top" wire:key="kk-row-{{ $m->id }}">
                        <td class="px-3 py-3">
                            <div class="font-semibold text-slate-700">{{ $m->nama_display }}</div>
                            <div class="text-[11px] text-slate-400">Index1 {{ $m->index1 ?? '-' }}{{ $m->jenis_usaha ? ' · Jenis usaha ' . $m->jenis_usaha : '' }}</div>
                            <div class="text-[10px] text-slate-300 font-mono mt-0.5">{{ $m->assignment_id }}</div>
                        </td>
                        <td class="px-3 py-3 text-xs">
                            <div>
                                <span class="inline-block px-1.5 py-0.5 rounded bg-slate-800 text-white text-[11px] font-bold">{{ $m->kategori ?? '?' }}</span>
                                <span class="font-mono font-bold text-slate-800">{{ $m->kbli_akhir ?? '-' }}</span>
                            </div>
                            @if($m->kbli_label)<div class="text-[11px] text-slate-500 leading-snug mt-0.5">{{ $m->kbli_label }}</div>@endif
                            <div class="text-[10px] text-slate-400 mt-0.5">
                                {{ $m->label_sumber_kbli ?? '' }}{{ $m->kbli_genai ? ' · GenAI ' . $m->kbli_genai : '' }}
                            </div>
                            <div class="flex flex-wrap gap-1 mt-1">
                                @foreach($m->rincian_13b as [$l, $v, $ya])
                                    <span class="text-[10px] px-1.5 py-0.5 rounded border {{ $chip($ya) }}">{{ $l }}: {{ $v }}</span>
                                @endforeach
                            </div>
                        </td>
                        <td class="px-3 py-3 text-xs">
                            @if($m->alasan_tidak_sesuai)<div class="font-medium text-rose-700">{{ $m->alasan_tidak_sesuai }}</div>@endif
                            <div class="text-[11px] text-slate-500 mt-0.5">{{ $m->kategori_rule_desc ?? $m->kategori_rule }}</div>
                            <div class="text-[10px] text-slate-400 mt-0.5">
                                {{ $m->dasar_rule ? 'Dasar: ' . $m->dasar_rule : '' }}
                                @if($m->rule_13b) · 13b: {{ $m->rule_13b }} @endif
                                @if($m->rule_ju) · JU: {{ $m->rule_ju }} @endif
                            </div>
                            @if($m->kat_val_kbli)
                                <span class="inline-block mt-1 text-[10px] font-bold px-1.5 py-0.5 rounded {{ str_contains(mb_strtolower($m->kat_val_kbli), 'tidak') ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' }}">{{ $m->kat_val_kbli }}</span>
                            @endif
                        </td>
                        <td class="px-3 py-3 text-xs">
                            @if($m->kategori_rekomendasi)<div class="font-bold text-indigo-700">{{ $m->kategori_rekomendasi }}</div>@endif
                            @if($m->kategori_rekomendasi_desc)<div class="text-[11px] text-slate-500">{{ $m->kategori_rekomendasi_desc }}</div>@endif
                            @if($m->keterangan_rekomendasi)
                                <div class="mt-1 text-[11px] text-indigo-800 bg-indigo-50 border border-indigo-100 rounded px-1.5 py-1">💡 {{ $m->keterangan_rekomendasi }}</div>
                            @endif
                        </td>
                        <td class="px-3 py-3 text-xs whitespace-nowrap">
                            @if($m->pic)<span class="font-semibold text-slate-700">{{ $m->pic }}</span>@else<span class="text-amber-600 font-medium">Tanpa PIC</span>@endif
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
                                <a href="{{ $m->fasih_link }}" target="_blank" rel="noopener" class="text-xs font-medium text-orange-600 hover:text-orange-700 hover:underline whitespace-nowrap">{{ $m->fasih_link_short }}</a>
                            @else
                                <span class="text-xs text-slate-300">-</span>
                            @endif
                        </td>
                        <td class="px-3 py-3 text-right whitespace-nowrap">
                            @if($m->tindak_lanjut === 'sudah')
                                <button wire:click="bukaModalTindakLanjut({{ $m->id }})" class="text-xs font-medium text-slate-600 hover:text-slate-800 mr-2">Ubah</button>
                                <button wire:click="batalkanTindakLanjut({{ $m->id }})" wire:confirm="Batalkan tindak lanjut untuk usaha ini?" class="text-xs font-medium text-amber-600 hover:text-amber-700">Batalkan</button>
                            @else
                                <button wire:click="bukaModalTindakLanjut({{ $m->id }})" class="text-xs font-medium text-emerald-600 hover:text-emerald-700">Tandai Selesai</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="px-4 py-10 text-center text-slate-400">Tidak ada data yang cocok dengan filter.</td></tr>
                @endforelse
            </tbody>
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
            @if($modalSaran)
                <p class="mt-2 text-xs text-indigo-800 bg-indigo-50 border border-indigo-100 rounded-md px-2 py-1">💡 {{ $modalSaran }}</p>
            @endif
        </div>

        <div class="px-6 py-4 space-y-4">
            <div class="space-y-2">
                <p class="text-xs font-semibold text-slate-600 uppercase tracking-wide">Hasil pemeriksaan <span class="text-red-500">*</span></p>
                @foreach($statusOptions as $value => $label)
                    @php
                        $active = ['sudah_sesuai' => 'border-emerald-400 bg-emerald-50', 'diperbaiki' => 'border-blue-400 bg-blue-50'][$value] ?? 'border-orange-400 bg-orange-50';
                        $desc = [
                            'sudah_sesuai' => 'KBLI/kategori memang sudah benar sesuai kondisi usaha.',
                            'diperbaiki' => 'KBLI/kategori keliru dan sudah dikoreksi di FASIH.',
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
                          placeholder="{{ $selectedStatus === 'diperbaiki' ? 'Mis. KBLI diganti dari 46734 (G) ke 43301 (F)' : 'Tambahkan keterangan bila perlu...' }}"
                          class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition text-sm"></textarea>
                @error('catatan') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="px-6 py-4 border-t border-slate-200 bg-slate-50 flex gap-2 justify-end">
            <button wire:click="tutupModal" class="px-4 py-2 rounded-lg border border-slate-300 hover:bg-slate-100 text-slate-600 text-sm font-medium transition active:scale-95">Batal</button>
            <button wire:click="prosesTandaiSelesai" wire:loading.attr="disabled" wire:target="prosesTandaiSelesai"
                    class="px-5 py-2 rounded-lg bg-orange-600 hover:bg-orange-700 text-white text-sm font-semibold transition active:scale-95 disabled:opacity-50">
                <span wire:loading.remove wire:target="prosesTandaiSelesai">{{ $modalEdit ? 'Simpan Perubahan' : 'Konfirmasi' }}</span>
                <span wire:loading wire:target="prosesTandaiSelesai">Menyimpan...</span>
            </button>
        </div>
    </div>
</div>
@endif

</div>
