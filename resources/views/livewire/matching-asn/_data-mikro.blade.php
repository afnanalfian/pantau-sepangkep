@php
    $selectCls = 'px-3 py-2 rounded-lg border border-slate-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition text-sm bg-white';
    $adaFilter = $filterKecamatan || $filterDesa || $filterInstansi || $filterDuplikat || $filterIdentitas || $filterStatus || $filterHasil || $search !== '';
    $linkCls = [
        'detail' => 'bg-orange-50 text-orange-700 border-orange-200 hover:bg-orange-100',
        'nik' => 'bg-sky-50 text-sky-700 border-sky-200 hover:bg-sky-100',
        'kode' => 'bg-violet-50 text-violet-700 border-violet-200 hover:bg-violet-100',
        'assignment' => 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100',
    ];
    $konfirmasiDup = 'Apakah data ini telah DIHAPUS dari FASIH-SM?';
@endphp
<div wire:key="asn-mikro">

<!-- FILTERS -->
<div class="bg-white rounded-xl sm:rounded-2xl border border-slate-200 p-3 sm:p-4 mb-4 shadow-sm">
    <div class="flex flex-col gap-2">
        <!-- Tab duplikat -->
        <div class="flex flex-wrap gap-1.5">
            @foreach(['' => 'Semua', 'ya' => 'Duplikat (HAPUS)', 'tidak' => 'Tidak Duplikat'] as $val => $lbl)
                <button type="button" wire:click="$set('filterDuplikat', '{{ $val }}')"
                        class="px-3 py-1.5 rounded-lg text-xs sm:text-sm font-semibold transition
                        {{ $filterDuplikat === $val ? ($val === 'ya' ? 'bg-red-600 text-white' : 'bg-orange-600 text-white') : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    {{ $lbl }}
                </button>
            @endforeach
        </div>

        <div class="flex flex-col sm:flex-row gap-2">
            <input type="text" wire:model.live.debounce.400ms="search"
                   placeholder="Cari nama / NIK / No KK / assignment / kode identitas / alamat / instansi..."
                   class="flex-1 px-3 py-2 rounded-lg border border-slate-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition text-sm">
            <button wire:click="exportMikro" wire:loading.attr="disabled" wire:target="exportMikro"
                    title="Mengekspor SEMUA baris yang cocok dengan filter saat ini"
                    class="w-full sm:w-auto px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold transition active:scale-95 disabled:opacity-50 whitespace-nowrap">
                <span wire:loading.remove wire:target="exportMikro">Export Excel ({{ number_format($totalFilter) }} baris)</span>
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
            <select wire:model.live="filterInstansi" class="{{ $selectCls }} col-span-2 sm:max-w-xs">
                <option value="">Semua Instansi</option>
                @foreach($instansiOptions as $val => $lbl)<option value="{{ $val }}">{{ $lbl }}</option>@endforeach
            </select>
            <select wire:model.live="filterDuplikat" class="{{ $selectCls }}">
                <option value="">Duplikat & tidak</option>
                <option value="ya">Duplikat (HAPUS)</option>
                <option value="tidak">Tidak duplikat</option>
            </select>
            <select wire:model.live="filterIdentitas" class="{{ $selectCls }}">
                <option value="">Semua identitas</option>
                @foreach($identitasOptions as $val => $lbl)<option value="{{ $val }}">{{ $lbl }}</option>@endforeach
            </select>
            <select wire:model.live="filterStatus" class="{{ $selectCls }}">
                <option value="">Semua Status</option>
                <option value="belum">Belum Diselesaikan</option>
                <option value="sudah">Sudah Diselesaikan</option>
            </select>
            @if($filterStatus !== 'belum')
                <select wire:model.live="filterHasil" class="{{ $selectCls }}">
                    <option value="">Semua Penyelesaian</option>
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
        <p class="text-xs text-slate-400">{{ number_format($totalFilter) }} ASN ditemukan · Export Excel berisi semua baris ini (bukan hanya halaman yang tampil).</p>
    </div>
</div>

{{-- Potongan tampilan yang dipakai kartu mobile & tabel --}}
@php
    $badges = function ($m) {
        $out = [];
        $out[] = $m->is_duplikat
            ? ['DUPLIKAT', 'bg-red-50 text-red-700 border-red-200']
            : ['Tidak duplikat', 'bg-emerald-50 text-emerald-700 border-emerald-200'];
        if (!$m->nik) $out[] = ['Tanpa NIK', 'bg-amber-50 text-amber-700 border-amber-200'];
        if (!$m->nomor_kk) $out[] = ['Tanpa KK', 'bg-amber-50 text-amber-700 border-amber-200'];
        return $out;
    };
@endphp

<!-- MOBILE CARD VIEW -->
<div class="sm:hidden space-y-3">
    @forelse($mikros as $m)
        <div class="bg-white rounded-xl border {{ $m->is_duplikat ? 'border-red-200' : 'border-slate-200' }} p-4 shadow-sm" wire:key="asn-card-{{ $m->id }}">
            <div class="flex flex-wrap gap-1 mb-1">
                @foreach($badges($m) as [$t, $c])<span class="text-[10px] font-bold px-1.5 py-0.5 rounded border {{ $c }}">{{ $t }}</span>@endforeach
            </div>
            <div class="flex items-start justify-between gap-2">
                <div class="font-semibold text-slate-800 text-sm">{{ $m->nama_display }}</div>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full whitespace-nowrap {{ $m->status_color }}">{{ $m->tindak_lanjut === 'sudah' ? '✓ ' . $m->status_label : '⏳ Belum' }}</span>
            </div>
            <div class="text-[11px] text-slate-500 mt-0.5">{{ $m->alamat_prelist ?? '-' }}</div>
            <div class="text-[11px] text-slate-500">{{ $m->nmkec ?? '-' }} · {{ $m->nmdesa ?? '-' }}</div>

            <dl class="mt-2 p-2 rounded-lg bg-slate-50 text-[11px] space-y-0.5">
                <div class="flex justify-between gap-2"><dt class="text-slate-500">NIK</dt><dd class="font-mono text-slate-800">{{ $m->nik ?? '—' }}</dd></div>
                <div class="flex justify-between gap-2"><dt class="text-slate-500">No KK</dt><dd class="font-mono text-slate-800">{{ $m->nomor_kk ?? '—' }}</dd></div>
                <div class="flex justify-between gap-2"><dt class="text-slate-500">Instansi</dt><dd class="text-right {{ $m->instansi_kerja ? 'text-slate-800' : 'text-amber-700 font-semibold' }}">{{ $m->instansi_display }}</dd></div>
            </dl>

            @if($m->catatan)
                <div class="mt-2 text-xs text-slate-600 bg-amber-50 border border-amber-100 rounded-md px-2 py-1 whitespace-pre-line">📝 {{ $m->catatan }}</div>
            @endif

            <div class="flex flex-wrap gap-1 mt-2">
                @foreach($m->fasih_links as $key => [$lbl, $url])
                    @if($url)
                        <a href="{{ $url }}" target="_blank" rel="noopener" class="text-[10px] font-medium px-2 py-1 rounded border {{ $linkCls[$key] }}">🔗 {{ $lbl }}</a>
                    @endif
                @endforeach
            </div>

            <div class="flex items-center gap-3 mt-3 pt-2 border-t border-slate-100 flex-wrap">
                @if($m->tindak_lanjut === 'sudah')
                    @unless($m->is_duplikat)
                        <button wire:click="bukaModalTindakLanjut({{ $m->id }})" class="text-xs font-medium text-slate-600 hover:text-slate-800">Ubah</button>
                    @endunless
                    <button wire:click="batalkanTindakLanjut({{ $m->id }})" wire:confirm="Batalkan penyelesaian untuk data ini?"
                            class="text-xs font-medium text-amber-600 hover:text-amber-700">Batalkan</button>
                @elseif($m->is_duplikat)
                    <button wire:click="konfirmasiDihapus({{ $m->id }})" wire:confirm="{{ $konfirmasiDup }}"
                            class="text-xs font-semibold text-red-600 hover:text-red-700">✓ Konfirmasi Dihapus</button>
                @else
                    <button wire:click="bukaModalTindakLanjut({{ $m->id }})" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700">Tandai Selesai</button>
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
                    <th class="px-3 py-3 text-left min-w-[220px]">Nama Prelist</th>
                    <th class="px-3 py-3 text-left">NIK / No KK</th>
                    <th class="px-3 py-3 text-left min-w-[170px]">Wilayah</th>
                    <th class="px-3 py-3 text-left min-w-[200px]">Instansi</th>
                    <th class="px-3 py-3 text-center">Status</th>
                    <th class="px-3 py-3 text-left min-w-[150px]">Link FASIH-SM</th>
                    <th class="px-3 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($mikros as $m)
                    <tr class="hover:bg-slate-50 transition align-top {{ $m->is_duplikat ? 'bg-red-50/30' : '' }}" wire:key="asn-row-{{ $m->id }}">
                        <td class="px-3 py-3 text-slate-500 text-center">{{ $m->no }}</td>
                        <td class="px-3 py-3">
                            <div class="flex flex-wrap gap-1 mb-1">
                                @foreach($badges($m) as [$t, $c])<span class="text-[10px] font-bold px-1.5 py-0.5 rounded border {{ $c }}">{{ $t }}</span>@endforeach
                            </div>
                            <div class="font-semibold text-slate-700">{{ $m->nama_display }}</div>
                            <div class="text-[11px] text-slate-500">{{ $m->alamat_prelist ?? '-' }}</div>
                            <div class="text-[10px] text-slate-400 mt-0.5">{{ $m->jenis_prelist ?? '' }}{{ $m->assignment_status ? ' · ' . $m->assignment_status : '' }}</div>
                            <div class="text-[10px] text-slate-300 font-mono">{{ $m->code_identity }}</div>
                        </td>
                        <td class="px-3 py-3 text-xs font-mono whitespace-nowrap">
                            <div class="{{ $m->nik ? 'text-slate-800' : 'text-amber-600 font-sans font-semibold' }}">{{ $m->nik ?? 'Tanpa NIK' }}</div>
                            <div class="{{ $m->nomor_kk ? 'text-slate-500' : 'text-amber-600 font-sans font-semibold' }}">{{ $m->nomor_kk ?? 'Tanpa KK' }}</div>
                        </td>
                        <td class="px-3 py-3 text-xs text-slate-600">
                            <div class="font-medium">{{ $m->nmkec ?? '-' }}</div>
                            <div class="text-slate-500">{{ $m->nmdesa ?? '-' }}</div>
                        </td>
                        <td class="px-3 py-3 text-xs">
                            <div class="{{ $m->instansi_kerja ? 'text-slate-700' : 'text-amber-700 font-semibold' }}">{{ $m->instansi_display }}</div>
                            @if($m->instansi_lokasi)<div class="text-[11px] text-slate-400">{{ $m->instansi_lokasi }}</div>@endif
                        </td>
                        <td class="px-3 py-3 text-center">
                            <span class="inline-block px-2 py-1 rounded-full text-[11px] font-bold {{ $m->status_color }}">{{ $m->status_label }}</span>
                            @if($m->tindak_lanjut_at)
                                <div class="text-[10px] text-slate-400 mt-1">{{ $m->tindak_lanjut_at->format('d/m H:i') }}{{ $m->tindak_lanjut_by ? ' · ' . $m->tindak_lanjut_by : '' }}</div>
                            @endif
                            @if($m->catatan)
                                <div class="mt-1 text-[11px] text-left text-slate-600 bg-amber-50 border border-amber-100 rounded px-1.5 py-1 whitespace-pre-line max-w-[200px]">📝 {{ $m->catatan }}</div>
                            @endif
                        </td>
                        <td class="px-3 py-3">
                            <div class="flex flex-col gap-1">
                                @foreach($m->fasih_links as $key => [$lbl, $url])
                                    @if($url)
                                        <a href="{{ $url }}" target="_blank" rel="noopener" class="text-[11px] font-medium px-2 py-0.5 rounded border whitespace-nowrap {{ $linkCls[$key] }}">🔗 {{ $lbl }}</a>
                                    @endif
                                @endforeach
                            </div>
                        </td>
                        <td class="px-3 py-3 text-right whitespace-nowrap">
                            @if($m->tindak_lanjut === 'sudah')
                                @unless($m->is_duplikat)
                                    <button wire:click="bukaModalTindakLanjut({{ $m->id }})" class="text-xs font-medium text-slate-600 hover:text-slate-800 mr-2">Ubah</button>
                                @endunless
                                <button wire:click="batalkanTindakLanjut({{ $m->id }})" wire:confirm="Batalkan penyelesaian untuk data ini?"
                                        class="text-xs font-medium text-amber-600 hover:text-amber-700">Batalkan</button>
                            @elseif($m->is_duplikat)
                                <button wire:click="konfirmasiDihapus({{ $m->id }})" wire:confirm="{{ $konfirmasiDup }}"
                                        class="text-xs font-semibold text-red-600 hover:text-red-700">✓ Konfirmasi Dihapus</button>
                            @else
                                <button wire:click="bukaModalTindakLanjut({{ $m->id }})" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700">Tandai Selesai</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-10 text-center text-slate-400">Tidak ada data yang cocok dengan filter.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-4 py-3 border-t border-slate-100 bg-slate-50">{{ $mikros->links() }}</div>
</div>

<!-- MODAL PENYELESAIAN (TIDAK DUPLIKAT) -->
@if($showModal)
<div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" wire:keydown.escape.window="tutupModal">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden" @click.outside="$wire.tutupModal()">
        <div class="px-6 py-4 border-b border-slate-200 bg-gradient-to-r from-orange-50 to-amber-50">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-slate-800">{{ $modalEdit ? 'Ubah Penyelesaian' : 'Tandai Selesai' }}</h3>
                <button wire:click="tutupModal" class="text-slate-400 hover:text-slate-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <p class="text-sm text-slate-700 font-semibold mt-1">{{ $modalNama }}</p>
            <p class="text-xs text-slate-500">{{ $modalInfo }}</p>
        </div>

        <div class="px-6 py-4 space-y-4">
            <div class="space-y-2">
                <p class="text-xs font-semibold text-slate-600 uppercase tracking-wide">Penyelesaian <span class="text-red-500">*</span></p>
                @foreach($statusOptions as $value => $label)
                    @php
                        $active = ['didata' => 'border-emerald-400 bg-emerald-50', 'dihapus' => 'border-slate-500 bg-slate-50'][$value] ?? 'border-orange-400 bg-orange-50';
                        $desc = [
                            'didata' => 'ASN ini sudah didata di FASIH-SM.',
                            'dihapus' => 'Assignment ini sudah dihapus dari FASIH-SM.',
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
