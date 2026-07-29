<div wire:key="anomali-mikro">

<div class="bg-white rounded-2xl border border-slate-200 p-4 mb-4 flex flex-wrap gap-3 items-center">
    <div class="relative flex-1 min-w-[180px]">
        <input type="text" wire:model.live.debounce.400ms="search" placeholder="Cari nama/assignment/SLS..."
               class="w-full px-3 py-2 rounded-lg border border-slate-200 text-sm outline-none focus:border-[#0F7B8A]">
    </div>
    <select wire:model.live="filterJenis" class="px-3 py-2 rounded-lg border border-slate-200 text-sm outline-none">
        <option value="">Usaha/Keluarga</option>
        <option value="usaha">Usaha</option>
        <option value="keluarga">Keluarga</option>
    </select>
    <select wire:model.live="filterKecamatan" class="px-3 py-2 rounded-lg border border-slate-200 text-sm outline-none">
        <option value="">Semua Kecamatan</option>
        @foreach($kecamatanOptions as $k)<option value="{{ $k }}">{{ $k }}</option>@endforeach
    </select>
    <select wire:model.live="filterDesa" class="px-3 py-2 rounded-lg border border-slate-200 text-sm outline-none">
        <option value="">Semua Desa/Kel</option>
        @foreach($desaOptions as $k)<option value="{{ $k }}">{{ $k }}</option>@endforeach
    </select>
    <select wire:model.live="filterStatus" class="px-3 py-2 rounded-lg border border-slate-200 text-sm outline-none">
        <option value="">Semua Status</option>
        <option value="belum">Belum Tindak Lanjut</option>
        <option value="sudah">Sudah Tindak Lanjut</option>
    </select>
    <select wire:model.live="perPage" class="px-3 py-2 rounded-lg border border-slate-200 text-sm outline-none">
        <option value="10">10 / halaman</option>
        <option value="20">20 / halaman</option>
        <option value="50">50 / halaman</option>
        <option value="100">100 / halaman</option>
    </select>
    <button wire:click="exportMikro" class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold whitespace-nowrap">Export Excel</button>
</div>

<div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-xs uppercase text-slate-500 font-semibold">
                <tr>
                    <th class="px-3 py-3 text-left">No</th>
                    <th class="px-3 py-3 text-left">Nama KRT/Usaha</th>
                    <th class="px-3 py-3 text-left">Kecamatan</th>
                    <th class="px-3 py-3 text-left">Desa/Kel</th>
                    <th class="px-3 py-3 text-left">SLS</th>
                    <th class="px-3 py-3 text-left">Assignment ID</th>
                    <th class="px-3 py-3 text-left">PPL / PML</th>
                    <th class="px-3 py-3 text-left">Link Fasih</th>
                    <th class="px-3 py-3 text-center">Tindak Lanjut</th>
                    <th class="px-3 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($mikros as $m)
                    @php $mitra = $mitraMap->get($m->email_petugas); @endphp
                    <tr class="hover:bg-slate-50">
                        <td class="px-3 py-3 text-slate-500">{{ $m->no }}</td>
                        <td class="px-3 py-3">
                            <div class="font-semibold text-slate-700">{{ $m->nama }}</div>
                            <div class="text-[10px] text-slate-400">{{ $m->nama_anomali }}</div>
                        </td>
                        <td class="px-3 py-3 text-slate-600">{{ $m->nmkec }}</td>
                        <td class="px-3 py-3 text-slate-600">{{ $m->nmdesa }}</td>
                        <td class="px-3 py-3 text-slate-500 font-mono text-xs">{{ $m->kode_sls }}/{{ $m->sub_sls }}</td>
                        <td class="px-3 py-3 text-slate-400 font-mono text-[10px]">{{ $m->assignment_id }}</td>
                        <td class="px-3 py-3 text-xs">
                            @if($mitra)
                                <div class="text-slate-700 font-medium">{{ $mitra->nama_ppl }}</div>
                                <div class="text-slate-400">{{ $mitra->nama_pml }} &middot; {{ $mitra->pml_organik }}</div>
                            @else
                                <span class="text-slate-300">Tidak ada petugas</span>
                            @endif
                        </td>
                        <td class="px-3 py-3">
                            @if($m->link_fasih)
                                <a href="{{ $m->link_fasih }}" target="_blank" rel="noopener" class="text-xs font-semibold text-white bg-[#0F7B8A] px-2.5 py-1 rounded-lg hover:bg-[#0d6b78]">Buka Fasih</a>
                            @endif
                        </td>
                        <td class="px-3 py-3 text-center">
                            <span class="px-2 py-1 rounded-full text-xs font-bold {{ $m->tindak_lanjut === 'sudah' ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700' }}">
                                {{ $m->tindak_lanjut === 'sudah' ? 'Sudah' : 'Belum' }}
                            </span>
                        </td>
                        <td class="px-3 py-3 text-right">
                            @if($m->tindak_lanjut === 'sudah')
                                <button wire:click="batalkanTindakLanjut({{ $m->id }})" class="text-xs font-semibold text-amber-600 hover:underline whitespace-nowrap">Batalkan</button>
                            @else
                                <button wire:click="tandaiSelesai({{ $m->id }})" class="text-xs font-semibold text-emerald-600 hover:underline whitespace-nowrap">Tandai Selesai</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="px-4 py-10 text-center text-slate-400">Tidak ada data yang cocok dengan filter.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-4 py-3 border-t border-slate-100">{{ $mikros->links() }}</div>
</div>

</div>
