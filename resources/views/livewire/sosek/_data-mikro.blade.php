@php
    $selectCls = 'px-3 py-2 rounded-lg border border-slate-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition text-sm bg-white';
    $adaFilter = $filterJenis || $filterKecamatan || $filterDesa || $filterPic || $filterStatus || $filterHasil || $search !== '';
@endphp
<div wire:key="sosek-mikro">

<!-- FILTERS -->
<div class="bg-white rounded-xl sm:rounded-2xl border border-slate-200 p-3 sm:p-4 mb-4 shadow-sm">
    <div class="flex flex-col gap-2">
        <!-- Tab jenis anomali -->
        <div class="flex gap-1.5 overflow-x-auto pb-1 -mx-1 px-1">
            <button type="button" wire:click="$set('filterJenis', '')"
                    class="shrink-0 px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ $filterJenis === '' ? 'bg-orange-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Semua
            </button>
            @foreach($jenisOptions as $kode => $j)
                <button type="button" wire:click="$set('filterJenis', '{{ $kode }}')" title="{{ $j['label'] }}"
                        class="shrink-0 px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ $filterJenis === $kode ? 'bg-orange-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    {{ $j['no'] }}. {{ $j['singkat'] }}
                </button>
            @endforeach
        </div>

        <div class="flex flex-col sm:flex-row gap-2">
            <input type="text" wire:model.live.debounce.400ms="search"
                   placeholder="Cari nama / NIK / No KK / assignment / keterangan / PIC / catatan..."
                   class="flex-1 px-3 py-2 rounded-lg border border-slate-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition text-sm">
            <button wire:click="exportMikro" wire:loading.attr="disabled" wire:target="exportMikro"
                    class="w-full sm:w-auto px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold transition active:scale-95 disabled:opacity-50 whitespace-nowrap">
                <span wire:loading.remove wire:target="exportMikro">Export Excel ({{ number_format($mikros->total()) }})</span>
                <span wire:loading wire:target="exportMikro">Menyiapkan...</span>
            </button>
        </div>

        <div class="grid grid-cols-2 sm:flex sm:flex-wrap gap-2">
            <select wire:model.live="filterJenis" class="{{ $selectCls }} col-span-2 sm:max-w-xs">
                <option value="">Semua jenis anomali</option>
                @foreach($jenisOptions as $kode => $j)<option value="{{ $kode }}">{{ $j['no'] }}. {{ $j['label'] }}</option>@endforeach
            </select>
            <select wire:model.live="filterKecamatan" class="{{ $selectCls }}">
                <option value="">Semua Kecamatan</option>
                @foreach($kecamatanOptions as $k)<option value="{{ $k }}">{{ $k }}</option>@endforeach
            </select>
            <select wire:model.live="filterDesa" class="{{ $selectCls }}">
                <option value="">Semua Desa/Kel</option>
                @foreach($desaOptions as $k)<option value="{{ $k }}">{{ $k }}</option>@endforeach
            </select>
            <select wire:model.live="filterPic" class="{{ $selectCls }}">
                <option value="">Semua PIC</option>
                @foreach($picOptions as $val => $lbl)<option value="{{ $val }}">{{ $lbl }}</option>@endforeach
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
        <p class="text-xs text-slate-400">{{ number_format($mikros->total()) }} anomali ditemukan · Export berisi semua baris sesuai filter.</p>
    </div>
</div>

{{-- potongan: identitas ringkas --}}
@php
    $identitas = function ($m) {
        $out = [];
        if ($m->nik) $out[] = ['NIK', $m->nik];
        if ($m->no_kk) $out[] = ['KK', $m->no_kk];
        if ($m->nama_art && $m->nama_kk && $m->nama_art !== $m->nama_kk) $out[] = ['KRT', $m->nama_kk];
        return $out;
    };
@endphp

<!-- MOBILE CARD VIEW -->
<div class="sm:hidden space-y-3">
    @forelse($mikros as $m)
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm" wire:key="sosek-card-{{ $m->id }}">
            <div class="flex items-start justify-between gap-2">
                <span class="inline-block text-[10px] font-bold px-1.5 py-0.5 rounded border {{ $m->jenis_info['warna'] }}">{{ $m->jenis_info['no'] }}. {{ $m->jenis_info['singkat'] }}</span>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full whitespace-nowrap {{ $m->tindak_lanjut === 'sudah' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-red-50 text-red-700 border border-red-200' }}">
                    {{ $m->tindak_lanjut === 'sudah' ? '✓ Selesai' : '⏳ Belum' }}
                </span>
            </div>
            <div class="font-semibold text-slate-800 text-sm mt-1">{{ $m->nama_display }}</div>
            <div class="text-xs font-medium text-orange-700 mt-0.5">{{ $m->nilai_anomali }}</div>
            <dl class="mt-2 text-[11px] space-y-0.5">
                @foreach($identitas($m) as [$l, $v])
                    <div class="flex justify-between gap-2"><dt class="text-slate-400">{{ $l }}</dt><dd class="font-mono text-slate-700">{{ $v }}</dd></div>
                @endforeach
                <div class="flex justify-between gap-2"><dt class="text-slate-400">Wilayah</dt><dd class="text-right text-slate-700">{{ $m->nmkec_display }} · {{ $m->nmdesa_display }}</dd></div>
                <div class="flex justify-between gap-2"><dt class="text-slate-400">PIC</dt><dd class="text-right font-semibold {{ $m->pic ? 'text-slate-800' : 'text-amber-600' }}">{{ $m->pic ?? 'Tanpa PIC' }}</dd></div>
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
                    <a href="{{ $m->fasih_link }}" target="_blank" rel="noopener" class="text-xs font-medium text-orange-600 hover:text-orange-700 hover:underline">🔗 {{ $m->fasih_link_short }}</a>
                @endif
                @if($m->tindak_lanjut === 'sudah')
                    <button wire:click="bukaModalTindakLanjut({{ $m->id }})" class="text-xs font-medium text-slate-600 hover:text-slate-800">Ubah</button>
                    <button wire:click="batalkanTindakLanjut({{ $m->id }})" wire:confirm="Batalkan tindak lanjut untuk anomali ini?" class="text-xs font-medium text-amber-600 hover:text-amber-700">Batalkan</button>
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
                    <th class="px-3 py-3 text-left">Jenis</th>
                    <th class="px-3 py-3 text-left min-w-[200px]">Nama &amp; Identitas</th>
                    <th class="px-3 py-3 text-left min-w-[210px]">Keterangan Anomali</th>
                    <th class="px-3 py-3 text-left min-w-[150px]">Wilayah</th>
                    <th class="px-3 py-3 text-left">PIC</th>
                    <th class="px-3 py-3 text-center">Status</th>
                    <th class="px-3 py-3 text-left min-w-[160px]">Catatan</th>
                    <th class="px-3 py-3 text-left">Fasih</th>
                    <th class="px-3 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($mikros as $m)
                    <tr class="hover:bg-slate-50 transition align-top" wire:key="sosek-row-{{ $m->id }}">
                        <td class="px-3 py-3">
                            <span class="inline-block text-[10px] font-bold px-1.5 py-0.5 rounded border whitespace-nowrap {{ $m->jenis_info['warna'] }}" title="{{ $m->jenis_info['label'] }}">
                                {{ $m->jenis_info['no'] }}. {{ $m->jenis_info['singkat'] }}
                            </span>
                        </td>
                        <td class="px-3 py-3">
                            <div class="font-semibold text-slate-700">{{ $m->nama_display }}</div>
                            @foreach($identitas($m) as [$l, $v])
                                <div class="text-[11px] text-slate-500"><span class="text-slate-400">{{ $l }}</span> <span class="font-mono">{{ $v }}</span></div>
                            @endforeach
                        </td>
                        <td class="px-3 py-3 text-xs">
                            <div class="font-medium text-orange-700">{{ $m->nilai_anomali }}</div>
                            @if($m->keberadaan && in_array($m->jenis, ['umur', 'profesi'], true))
                                <div class="text-[11px] text-slate-400 mt-0.5">{{ $m->keberadaan }}</div>
                            @endif
                            @if($m->daftar_remark)
                                <div class="text-[11px] text-slate-500 mt-0.5">Remark: {{ $m->daftar_remark }}</div>
                            @endif
                        </td>
                        <td class="px-3 py-3 text-xs text-slate-600">
                            <div class="font-medium">{{ $m->nmkec_display }}</div>
                            <div class="text-slate-500">{{ $m->nmdesa_display }}</div>
                            @if($m->nama_sls)<div class="text-[11px] text-slate-400">{{ $m->nama_sls }}</div>@endif
                        </td>
                        <td class="px-3 py-3 text-xs whitespace-nowrap">
                            @if($m->pic)
                                <span class="font-semibold text-slate-700">{{ $m->pic }}</span>
                            @else
                                <span class="text-amber-600 font-medium" title="Wilayah (ID SubSLS) tidak terlacak">Tanpa PIC</span>
                            @endif
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
                                <button wire:click="batalkanTindakLanjut({{ $m->id }})" wire:confirm="Batalkan tindak lanjut untuk anomali ini?" class="text-xs font-medium text-amber-600 hover:text-amber-700">Batalkan</button>
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
        </div>

        <div class="px-6 py-4 space-y-4">
            <div class="space-y-2">
                <p class="text-xs font-semibold text-slate-600 uppercase tracking-wide">Hasil pemeriksaan <span class="text-red-500">*</span></p>
                @foreach($statusOptions as $value => $label)
                    @php
                        $active = ['sesuai_lapangan' => 'border-emerald-400 bg-emerald-50', 'diperbaiki' => 'border-blue-400 bg-blue-50'][$value] ?? 'border-orange-400 bg-orange-50';
                        $desc = [
                            'sesuai_lapangan' => 'Data memang sesuai dengan kondisi di lapangan.',
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
                <textarea wire:model="catatan" rows="3" maxlength="1000" placeholder="Tambahkan keterangan bila perlu..."
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
