<div>

<div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-2xl p-5 border border-slate-200">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Jumlah Gate</p>
        <p class="font-display font-bold text-3xl text-[#0B2A4A] mt-2">{{ $stats['totalGate'] }}</p>
    </div>
    <div class="bg-white rounded-2xl p-5 border border-slate-200">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Jumlah UK</p>
        <p class="font-display font-bold text-3xl text-[#0F7B8A] mt-2">{{ $stats['totalUk'] }}</p>
    </div>
    <div class="bg-white rounded-2xl p-5 border border-slate-200">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Aksi Preventif Selesai</p>
        <p class="font-display font-bold text-3xl text-emerald-600 mt-2">{{ $stats['selesai'] }} / {{ $stats['totalAksi'] }}</p>
    </div>
    <div class="bg-white rounded-2xl p-5 border border-slate-200">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Anomali Berjalan</p>
        @if($anomaliBerjalan)
            <p class="font-display font-bold text-lg text-amber-600 mt-2">{{ $anomaliBerjalan->tanggal->translatedFormat('d M Y') }}</p>
            <p class="text-xs text-slate-400">{{ $anomaliBerjalan->persenSelesai() }}% selesai pekan ini</p>
        @else
            <p class="text-sm text-slate-400 mt-2">Belum ada batch anomali</p>
        @endif
    </div>
</div>

@if($isQg)
    <button wire:click="bukaFormGate" class="mb-5 px-4 py-2.5 rounded-lg bg-[#0B2A4A] hover:bg-[#0d3760] text-white text-sm font-semibold">+ Tambah Gate</button>
@endif

<div class="space-y-3">
    @forelse($gates as $gate)
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 cursor-pointer" wire:click="toggleGate({{ $gate->id }})">
                <div class="flex items-center gap-3">
                    <x-icon name="shield" class="w-5 h-5 text-indigo-600" />
                    <span class="font-display font-bold text-slate-800">{{ $gate->nama }}</span>
                    <span class="text-xs text-slate-400">{{ $gate->uks->count() }} UK</span>
                </div>
                @if($isQg)
                <div class="flex items-center gap-3" wire:click.stop>
                    <button wire:click="bukaFormGate({{ $gate->id }})" class="text-xs font-semibold text-amber-600 hover:underline">Edit</button>
                    <button wire:click="hapusGate({{ $gate->id }})" wire:confirm="Hapus gate ini beserta seluruh UK dan aksi preventifnya?" class="text-xs font-semibold text-red-500 hover:underline">Hapus</button>
                </div>
                @endif
            </div>

            @if($expandedGate === $gate->id)
                <div class="border-t border-slate-100 p-5 space-y-3 bg-slate-50/50">
                    @if($isQg)
                        <button wire:click="bukaFormUk({{ $gate->id }})" class="text-xs font-semibold text-[#0F7B8A] hover:underline">+ Tambah UK</button>
                    @endif

                    @foreach($gate->uks as $uk)
                        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                            <div class="flex items-center justify-between px-4 py-3 cursor-pointer" wire:click="toggleUk({{ $uk->id }})">
                                <span class="font-semibold text-slate-700 text-sm">{{ $uk->nama }}</span>
                                @if($isQg)
                                <div class="flex items-center gap-3" wire:click.stop>
                                    <button wire:click="bukaFormUk({{ $gate->id }}, {{ $uk->id }})" class="text-xs font-semibold text-amber-600 hover:underline">Edit</button>
                                    <button wire:click="hapusUk({{ $uk->id }})" wire:confirm="Hapus UK ini?" class="text-xs font-semibold text-red-500 hover:underline">Hapus</button>
                                </div>
                                @endif
                            </div>

                            @if($expandedUk === $uk->id)
                                <div class="border-t border-slate-100 p-4 space-y-3">
                                    @if($isQg)
                                        <button wire:click="bukaFormAksi({{ $uk->id }})" class="text-xs font-semibold text-[#0F7B8A] hover:underline">+ Tambah Aksi Preventif</button>
                                    @endif

                                    @forelse($uk->aksiPreventifs as $aksi)
                                        <div class="border border-slate-200 rounded-lg p-4">
                                            <div class="flex items-start justify-between gap-3">
                                                <div class="flex-1">
                                                    <span class="text-xs font-bold text-slate-400">Aksi Preventif {{ $aksi->urutan }}</span>
                                                    <p class="text-sm text-slate-700 mt-0.5">{{ $aksi->deskripsi }}</p>
                                                    @if(!empty($aksi->pelaksana))
                                                        <p class="text-xs text-slate-400 mt-1">Pelaksana: {{ implode(', ', $aksi->pelaksana) }}</p>
                                                    @endif
                                                </div>
                                                <span class="text-xs font-bold px-2 py-1 rounded-full whitespace-nowrap {{ $aksi->isSelesai() ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700' }}">
                                                    {{ $aksi->isSelesai() ? 'Selesai' : 'Belum Selesai' }}
                                                </span>
                                            </div>

                                            <div class="grid sm:grid-cols-2 gap-3 mt-3 text-xs">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-slate-400 w-24 shrink-0">Template:</span>
                                                    @if($aksi->template_path)
                                                        <a href="{{ $aksi->template_path }}" target="_blank" class="font-semibold text-[#0F7B8A] hover:underline">Unduh Template</a>
                                                    @else
                                                        <span class="text-slate-300">Belum ada</span>
                                                    @endif
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <span class="text-slate-400 w-24 shrink-0">Bukti Dukung:</span>
                                                    @if($aksi->link_bukti_dukung)
                                                        <a href="{{ $aksi->link_bukti_dukung }}" target="_blank" rel="noopener" class="font-semibold text-[#0F7B8A] hover:underline">Buka Link</a>
                                                    @else
                                                        <span class="text-slate-300">Belum ada</span>
                                                    @endif
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <span class="text-slate-400 w-24 shrink-0">Laporan:</span>
                                                    @if($aksi->laporan_path)
                                                        <a href="{{ $aksi->laporan_path }}" target="_blank" class="font-semibold text-emerald-600 hover:underline">Unduh Laporan</a>
                                                    @else
                                                        <span class="text-slate-300">Belum diunggah</span>
                                                    @endif
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <span class="text-slate-400 w-24 shrink-0">Ceklis Bukti:</span>
                                                    <button wire:click="toggleChecklist({{ $aksi->id }})"
                                                        class="w-5 h-5 rounded border-2 flex items-center justify-center {{ $aksi->bukti_dukung_checklist ? 'bg-emerald-500 border-emerald-500' : 'border-slate-300' }}">
                                                        @if($aksi->bukti_dukung_checklist)<span class="text-white text-[10px]">&check;</span>@endif
                                                    </button>
                                                </div>
                                            </div>

                                            <div class="flex items-center gap-2 mt-3 pt-3 border-t border-slate-100">
                                                <input type="file" wire:model="laporanUpload.{{ $aksi->id }}" class="text-xs flex-1 border border-slate-300 rounded-lg px-2 py-1.5">
                                                <button wire:click="unggahLaporan({{ $aksi->id }})" class="text-xs font-semibold text-white bg-[#0F7B8A] px-3 py-1.5 rounded-lg whitespace-nowrap">Unggah Laporan</button>
                                                @if($isQg)
                                                    <button wire:click="bukaFormAksi({{ $uk->id }}, {{ $aksi->id }})" class="text-xs font-semibold text-amber-600 hover:underline whitespace-nowrap">Edit</button>
                                                    <button wire:click="hapusAksi({{ $aksi->id }})" wire:confirm="Hapus aksi preventif ini?" class="text-xs font-semibold text-red-500 hover:underline whitespace-nowrap">Hapus</button>
                                                @endif
                                            </div>
                                        </div>
                                    @empty
                                        <p class="text-xs text-slate-400 text-center py-4">Belum ada aksi preventif.</p>
                                    @endforelse
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @empty
        <div class="bg-white rounded-2xl border border-dashed border-slate-300 p-16 text-center">
            <p class="text-slate-400 font-medium">Belum ada Gate yang dibuat.</p>
        </div>
    @endforelse
</div>

{{-- MODAL: GATE --}}
@if($showGateForm)
<div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50">
    <div class="bg-white rounded-2xl w-full max-w-md p-6">
        <h3 class="font-display font-bold text-lg text-[#0B2A4A] mb-4">{{ $editingGateId ? 'Edit Gate' : 'Tambah Gate' }}</h3>
        <form wire:submit="simpanGate" class="space-y-3">
            <input type="text" wire:model="gateNama" placeholder="Nama Gate" class="w-full px-4 py-2.5 rounded-lg border border-slate-300 outline-none">
            @error('gateNama') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
            <div class="flex justify-end gap-2">
                <button type="button" wire:click="$set('showGateForm', false)" class="px-4 py-2 text-sm text-slate-500 font-semibold">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-[#0B2A4A] text-white text-sm font-semibold">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endif

{{-- MODAL: UK --}}
@if($showUkForm)
<div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50">
    <div class="bg-white rounded-2xl w-full max-w-md p-6">
        <h3 class="font-display font-bold text-lg text-[#0B2A4A] mb-4">{{ $editingUkId ? 'Edit UK' : 'Tambah UK' }}</h3>
        <form wire:submit="simpanUk" class="space-y-3">
            <input type="text" wire:model="ukNama" placeholder="Nama UK (Ukuran Kualitas)" class="w-full px-4 py-2.5 rounded-lg border border-slate-300 outline-none">
            @error('ukNama') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
            <div class="flex justify-end gap-2">
                <button type="button" wire:click="$set('showUkForm', false)" class="px-4 py-2 text-sm text-slate-500 font-semibold">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-[#0B2A4A] text-white text-sm font-semibold">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endif

{{-- MODAL: AKSI PREVENTIF --}}
@if($showAksiForm)
<div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50">
    <div class="bg-white rounded-2xl w-full max-w-lg p-6">
        <h3 class="font-display font-bold text-lg text-[#0B2A4A] mb-4">{{ $editingAksiId ? 'Edit Aksi Preventif' : 'Tambah Aksi Preventif' }}</h3>
        <form wire:submit="simpanAksi" class="space-y-3">
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Deskripsi</label>
                <textarea wire:model="aksiDeskripsi" rows="3" class="w-full px-4 py-2.5 rounded-lg border border-slate-300 outline-none"></textarea>
                @error('aksiDeskripsi') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Pelaksana (pisahkan dengan koma)</label>
                <input type="text" wire:model="aksiPelaksanaText" placeholder="Nama 1, Nama 2, ..." class="w-full px-4 py-2.5 rounded-lg border border-slate-300 outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Link Bukti Dukung</label>
                <input type="url" wire:model="aksiLinkBukti" placeholder="https://..." class="w-full px-4 py-2.5 rounded-lg border border-slate-300 outline-none">
                @error('aksiLinkBukti') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Template Laporan (opsional)</label>
                <input type="file" wire:model="aksiTemplateFile" class="w-full text-sm border border-slate-300 rounded-lg px-4 py-2">
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" wire:click="$set('showAksiForm', false)" class="px-4 py-2 text-sm text-slate-500 font-semibold">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-[#0B2A4A] text-white text-sm font-semibold">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endif

</div>
