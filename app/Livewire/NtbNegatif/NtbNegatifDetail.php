<?php

namespace App\Livewire\NtbNegatif;

use App\Models\NtbNegatifBatch;
use App\Models\NtbNegatifMikro;
use App\Models\SlsDaily;
use App\Services\PetugasResolver;
use App\Services\SimpleExcelExporter;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Detail NTB Negatif')]
class NtbNegatifDetail extends Component
{
    use WithPagination;

    public NtbNegatifBatch $batch;
    public string $view = 'dashboard'; // dashboard | mikro

    // filter data mikro
    public string $filterKategori = '';
    public string $filterKecamatan = '';
    public string $filterDesa = '';
    public string $filterStatus = '';
    public string $search = '';
    public int $perPage = 10;

    // Modal tandai selesai
    public bool $showModal = false;
    public ?int $selectedId = null;
    public ?string $selectedStatus = null;
    public string $catatan = '';
    public ?string $modalNama = null;

    protected ?PetugasResolver $resolver = null;

    public function mount(NtbNegatifBatch $batch)
    {
        $this->batch = $batch;
    }

    public function updatingSearch() { $this->resetPage(); }
    public function updatingFilterKategori() { $this->resetPage(); }
    public function updatingFilterKecamatan() { $this->resetPage(); }
    public function updatingFilterDesa() { $this->resetPage(); }
    public function updatingFilterStatus() { $this->resetPage(); }
    public function updatingPerPage() { $this->resetPage(); }

    public function updatedFilterKecamatan()
    {
        $this->filterDesa = '';
    }

    public function lihatDataMikro()
    {
        $this->view = 'mikro';
    }

    public function kembaliKeDashboard()
    {
        $this->view = 'dashboard';
    }

    protected function resolver(): PetugasResolver
    {
        return $this->resolver ??= PetugasResolver::forDate($this->batch->tanggal);
    }

    // =================================================================
    // AKSI TINDAK LANJUT
    // =================================================================

    public function bukaModalTindakLanjut(int $id)
    {
        $this->authorizeAksi();
        $row = $this->baseQuery()->find($id);
        if ($row) {
            $this->resetErrorBag();
            $this->selectedId = $id;
            $this->modalNama = $row->nama_display;
            $this->selectedStatus = null;
            $this->catatan = '';
            $this->showModal = true;
        }
    }

    public function tutupModal()
    {
        $this->showModal = false;
        $this->selectedId = null;
        $this->selectedStatus = null;
        $this->catatan = '';
        $this->modalNama = null;
        $this->resetErrorBag();
    }

    public function prosesTandaiSelesai()
    {
        $this->authorizeAksi();

        $this->validate([
            'selectedStatus' => 'required|in:' . implode(',', array_keys(NtbNegatifMikro::statusOptions())),
            'catatan' => 'nullable|string|max:1000',
        ], [
            'selectedStatus.required' => 'Silakan pilih metode penyelesaian.',
            'catatan.max' => 'Catatan maksimal 1000 karakter.',
        ]);

        $catatan = trim($this->catatan);

        $this->baseQuery()->whereKey($this->selectedId)->update([
            'tindak_lanjut' => 'sudah',
            'tindak_lanjut_at' => now(),
            'status_penyelesaian' => $this->selectedStatus,
            'catatan' => $catatan !== '' ? $catatan : null,
            'diselesaikan_oleh' => session('role_label'),
        ]);

        $label = NtbNegatifMikro::statusOptions()[$this->selectedStatus] ?? $this->selectedStatus;

        $this->tutupModal();
        session()->flash('success', 'NTB negatif ditandai selesai: ' . $label);
    }

    public function batalkanTindakLanjut(int $id)
    {
        $this->authorizeAksi();
        $this->baseQuery()->whereKey($id)->update([
            'tindak_lanjut' => 'belum',
            'tindak_lanjut_at' => null,
            'status_penyelesaian' => null,
            'catatan' => null,
            'diselesaikan_oleh' => null,
        ]);
        session()->flash('info', 'Tindak lanjut berhasil dibatalkan.');
    }

    protected function authorizeAksi()
    {
        abort_unless(session('role'), 403);
    }

    // =================================================================
    // QUERY
    // =================================================================

    protected function baseQuery()
    {
        return NtbNegatifMikro::where('ntb_negatif_batch_id', $this->batch->id);
    }

    protected function mikroQuery()
    {
        $q = $this->baseQuery();

        if ($this->filterKategori !== '') $q->where('kategori', $this->filterKategori);
        if ($this->filterKecamatan) $q->where('nmkec', $this->filterKecamatan);
        if ($this->filterDesa) $q->where('nmdesa', $this->filterDesa);
        if ($this->filterStatus) $q->where('tindak_lanjut', $this->filterStatus);

        if ($this->search) {
            $s = $this->search;
            $regionCodes = $this->regionCodesByPetugas($s);

            $q->where(function ($qq) use ($s, $regionCodes) {
                $qq->where('nama_usaha', 'like', "%{$s}%")
                    ->orWhere('assignment_id', 'like', "%{$s}%")
                    ->orWhere('kode_kbli', 'like', "%{$s}%")
                    ->orWhere('des_kbli', 'like', "%{$s}%")
                    ->orWhere('kode_sls', 'like', "%{$s}%")
                    ->orWhere('region_code', 'like', "%{$s}%")
                    ->orWhere('catatan', 'like', "%{$s}%");

                if (!empty($regionCodes)) {
                    $qq->orWhereIn('region_code', $regionCodes);
                }
            });
        }

        return $q;
    }

    protected function orderedQuery()
    {
        return $this->mikroQuery()
            ->orderBy('kategori')->orderBy('nmkec')->orderBy('nmdesa')->orderBy('kode_sls')->orderBy('id');
    }

    /** @return array<int, string> region_code milik PPL/PML yang namanya cocok. */
    protected function regionCodesByPetugas(string $keyword): array
    {
        $uploadId = $this->resolver()->uploadId();
        if (!$uploadId) return [];

        return SlsDaily::query()
            ->where('daily_upload_id', $uploadId)
            ->where(function ($q) use ($keyword) {
                $q->where('nama_ppl', 'like', "%{$keyword}%")
                    ->orWhere('nama_pml', 'like', "%{$keyword}%")
                    ->orWhere('pml_organik', 'like', "%{$keyword}%");
            })
            ->limit(5000)
            ->pluck('region_code')
            ->filter()
            ->map(fn ($c) => PetugasResolver::normalizeRegionCode($c))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    // =================================================================
    // DASHBOARD
    // =================================================================

    protected function dashboardData(): array
    {
        $rows = $this->baseQuery()->get();
        $total = $rows->count();
        $selesai = $rows->where('tindak_lanjut', 'sudah')->count();

        $progressGroup = function ($g, $label) {
            $t = $g->count();
            $s = $g->where('tindak_lanjut', 'sudah')->count();

            return [
                'label' => $label,
                'total' => $t,
                'selesai' => $s,
                'belum' => $t - $s,
                'persen' => $t > 0 ? round($s / $t * 100, 1) : 0,
            ];
        };

        $byKategori = $rows->groupBy(fn ($r) => $r->kategori ?: '-')
            ->map(fn ($g, $k) => $progressGroup($g, 'Kategori ' . $k))
            ->sortKeys()->values();

        $byKecamatan = $rows->groupBy(fn ($r) => $r->nmkec ?: '(Tidak diketahui)')
            ->map(fn ($g, $k) => $progressGroup($g, $k))
            ->sortByDesc('total')->values();

        $byMetode = collect(NtbNegatifMikro::statusOptions())
            ->map(fn ($label, $key) => [
                'label' => $label,
                'count' => $rows->where('status_penyelesaian', $key)->count(),
            ]);

        $byFlag = collect(NtbNegatifMikro::FLAGS)
            ->map(fn ($label, $col) => $rows->where($col, 1)->count());

        $bySkala = $rows->groupBy(fn ($r) => $r->reklasifikasi_skala_usaha ?: '(Kosong)')
            ->map->count()->sortDesc();

        $byKbli = $rows->groupBy(fn ($r) => $r->kode_kbli ? ($r->kode_kbli . ' ' . $r->des_kbli) : '(Tanpa KBLI)')
            ->map->count()->sortDesc()->take(10);

        // Rekap per PPL (petugas via region_code -> sls_dailies)
        $petugasMap = $this->resolver()->resolveMany($rows);
        $terpetakan = 0;
        $byPpl = [];
        foreach ($rows as $r) {
            $p = $petugasMap[$r->id] ?? null;
            if ($p) $terpetakan++;
            $key = ($p['nama_ppl'] ?? null) ?: '(Petugas tidak ditemukan)';
            $byPpl[$key] ??= ['ppl' => $key, 'pml' => $p['nama_pml'] ?? '-', 'total' => 0, 'selesai' => 0];
            $byPpl[$key]['total']++;
            if ($r->tindak_lanjut === 'sudah') $byPpl[$key]['selesai']++;
        }
        $byPpl = collect($byPpl)
            ->map(fn ($x) => $x + [
                'belum' => $x['total'] - $x['selesai'],
                'persen' => $x['total'] > 0 ? round($x['selesai'] / $x['total'] * 100, 1) : 0,
            ])
            ->sortByDesc('belum')->take(15)->values();

        return [
            'total' => $total,
            'selesai' => $selesai,
            'denganCatatan' => $rows->whereNotNull('catatan')->count(),
            'terpetakan' => $terpetakan,
            'totalNtbNegatif' => (float) $rows->where('metrik_nilai_tambah', '<', 0)->sum('metrik_nilai_tambah'),
            'byKategori' => $byKategori,
            'byKecamatan' => $byKecamatan,
            'byMetode' => $byMetode,
            'byFlag' => $byFlag,
            'bySkala' => $bySkala,
            'byKbli' => $byKbli,
            'byPpl' => $byPpl,
        ];
    }

    protected function kategoriOptions()
    {
        return $this->baseQuery()->pluck('kategori')->filter(fn ($v) => $v !== null && $v !== '')->unique()->sort()->values();
    }

    protected function kecamatanOptions()
    {
        return $this->baseQuery()->pluck('nmkec')->filter()->unique()->sort()->values();
    }

    protected function desaOptions()
    {
        $q = $this->baseQuery();
        if ($this->filterKecamatan) $q->where('nmkec', $this->filterKecamatan);

        return $q->pluck('nmdesa')->filter()->unique()->sort()->values();
    }

    // =================================================================
    // EXPORT EXCEL (mengikuti filter aktif)
    // =================================================================

    public function exportMikro()
    {
        $rows = $this->orderedQuery()->get();
        $petugasMap = $this->resolver()->resolveMany($rows);

        $mapped = $rows->map(function ($m) use ($petugasMap) {
            $p = $petugasMap[$m->id] ?? null;

            return [
                // --- kolom asli excel ---
                $m->kategori,
                $m->assignment_id,
                $m->kdprov,
                $m->nmprov,
                $m->kdkab,
                $m->nmkab,
                $m->kdkec,
                $m->nmkec,
                $m->kddesa,
                $m->nmdesa,
                $m->kode_sls,
                $m->sub_sls,
                $m->kode_kbli,
                $m->des_kbli,
                $m->pjk,
                $m->nama_usaha,
                $m->metrik_biaya_produksi,
                $m->metrik_biaya_pembelian,
                $m->metrik_biaya_operasional,
                $m->metrik_biaya_non_operasional,
                $m->metrik_total_pengeluaran,
                $m->metrik_pendapatan_barang_jasa,
                $m->metrik_pendapatan_lainnya,
                $m->metrik_nilai_tambah,
                $m->metrik_total_aset,
                $m->metrik_output,
                $m->survey_period_id,
                $m->rasio_biaya_pembelian_omzet,
                $m->reklasifikasi_skala_usaha,
                $m->rasio_ntb,
                $m->produktivitas,
                $m->flag_rasio_1_output_aset,
                $m->flag_rasio_2_upah_ntb,
                $m->flag_rasio_3_ntb_output,
                $m->fasih_link ?? '',        // link mode edit
                // --- tambahan pengelolaan ---
                $m->region_key,
                $p['nama_sls'] ?? '-',
                $p['nama_ppl'] ?? '-',
                $p['username'] ?? '-',
                $p['nama_pml'] ?? '-',
                $p['pml_organik'] ?? '-',
                $m->tindak_lanjut === 'sudah' ? 'Sudah' : 'Belum',
                $m->tindak_lanjut === 'sudah' ? $m->status_label : '',
                $m->catatan ?? '',
                $m->diselesaikan_oleh ?? '',
                $m->tindak_lanjut_at?->format('Y-m-d H:i') ?? '',
            ];
        });

        return SimpleExcelExporter::export(
            'data-mikro-ntb-negatif-' . $this->batch->tanggal->format('Y-m-d'),
            [
                'kategori', 'assignment_id', 'kode_prov', 'nama_provinsi', 'kode_kab', 'nama_kab_kota',
                'kode_kec', 'nama_kecamatan', 'kode_desa', 'nama_desa_kel', 'kode_sls', 'sub_sls',
                'kode_kbli', 'des_kbli', 'PJK', 'nama_usaha',
                'metrik_biaya_produksi', 'metrik_biaya_pembelian', 'metrik_biaya_operasional',
                'metrik_biaya_non_operasional', 'metrik_total_pengeluaran', 'metrik_pendapatan_barang_jasa',
                'metrik_pendapatan_lainnya', 'metrik_nilai_tambah', 'metrik_total_aset', 'metrik_output',
                'survey_period_id', 'rasio_biaya_pembelian_barang_terhadap_omzet', 'reklasifikasi_skala_usaha',
                'rasio_ntb', 'produktivitas', 'flag_rasio_1_output_aset', 'flag_rasio_2_upah_ntb',
                'flag_rasio_3_ntb_output', 'link_fasih_edit',
                'Region Code', 'Nama SLS', 'Nama PPL', 'Username PPL', 'Nama PML', 'PML Organik',
                'Tindak Lanjut', 'Metode Penyelesaian', 'Catatan', 'Diselesaikan Oleh', 'Waktu Selesai',
            ],
            $mapped->all()
        );
    }

    // =================================================================
    // RENDER
    // =================================================================

    public function render()
    {
        $viewData = [
            'statusOptions' => NtbNegatifMikro::statusOptions(),
            'statusDescriptions' => NtbNegatifMikro::statusDescriptions(),
        ];

        if ($this->view === 'mikro') {
            $mikros = $this->orderedQuery()->paginate($this->perPage);

            $viewData += [
                'mikros' => $mikros,
                'petugasMap' => $this->resolver()->resolveMany($mikros->getCollection()),
                'kategoriOptions' => $this->kategoriOptions(),
                'kecamatanOptions' => $this->kecamatanOptions(),
                'desaOptions' => $this->desaOptions(),
            ];
        } else {
            $viewData['dash'] = $this->dashboardData();
        }

        return view('livewire.ntb-negatif.ntb-negatif-detail', $viewData);
    }
}
