<?php

namespace App\Livewire\MissingValue;

use App\Models\MissingValueBatch;
use App\Models\MissingValueMikro;
use App\Models\SlsDaily;
use App\Services\PetugasResolver;
use App\Services\SimpleExcelExporter;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Detail Missing Value')]
class MissingValueDetail extends Component
{
    use WithPagination;

    public MissingValueBatch $batch;
    public string $view = 'dashboard'; // dashboard | mikro

    // filter data mikro
    public string $filterVariabel = '';
    public string $filterKecamatan = '';
    public string $filterDesa = '';
    public string $filterStatus = '';
    public string $filterStatusPerbaikan = '';
    public string $search = '';
    public int $perPage = 10;

    // Modal tandai selesai
    public bool $showModal = false;
    public ?int $selectedId = null;
    public ?string $selectedStatus = null;
    public string $catatan = '';
    public ?string $modalNama = null;

    protected ?PetugasResolver $resolver = null;

    public function mount(MissingValueBatch $batch)
    {
        $this->batch = $batch;
    }

    public function updatingSearch() { $this->resetPage(); }
    public function updatingFilterVariabel() { $this->resetPage(); }
    public function updatingFilterKecamatan() { $this->resetPage(); }
    public function updatingFilterDesa() { $this->resetPage(); }
    public function updatingFilterStatus() { $this->resetPage(); }
    public function updatingFilterStatusPerbaikan() { $this->resetPage(); }
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
        // Petugas diambil dari upload harian terakhir sebelum/pada tanggal batch
        // (metode sama dengan modul anomali).
        return $this->resolver ??= PetugasResolver::forDate($this->batch->tanggal);
    }

    // =================================================================
    // AKSI TINDAK LANJUT
    // =================================================================

    public function bukaModalTindakLanjut(int $id)
    {
        $this->authorizeAksi();
        $row = MissingValueMikro::where('missing_value_batch_id', $this->batch->id)->find($id);
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
            'selectedStatus' => 'required|in:' . implode(',', array_keys(MissingValueMikro::statusOptions())),
            'catatan' => 'nullable|string|max:1000',
        ], [
            'selectedStatus.required' => 'Silakan pilih metode penyelesaian.',
            'catatan.max' => 'Catatan maksimal 1000 karakter.',
        ]);

        $catatan = trim($this->catatan);

        MissingValueMikro::where('missing_value_batch_id', $this->batch->id)
            ->whereKey($this->selectedId)
            ->update([
                'tindak_lanjut' => 'sudah',
                'tindak_lanjut_at' => now(),
                'status_penyelesaian' => $this->selectedStatus,
                'catatan' => $catatan !== '' ? $catatan : null,
                'diselesaikan_oleh' => session('role_label'),
            ]);

        $label = MissingValueMikro::statusOptions()[$this->selectedStatus] ?? $this->selectedStatus;

        $this->tutupModal();
        session()->flash('success', 'Missing value berhasil ditandai selesai dengan metode: ' . $label);
    }

    public function batalkanTindakLanjut(int $id)
    {
        $this->authorizeAksi();
        MissingValueMikro::where('missing_value_batch_id', $this->batch->id)
            ->whereKey($id)
            ->update([
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
        return MissingValueMikro::where('missing_value_batch_id', $this->batch->id);
    }

    protected function mikroQuery()
    {
        $q = $this->baseQuery();

        if ($this->filterVariabel) $q->where('variabel_missing', 'like', "%{$this->filterVariabel}%");
        if ($this->filterKecamatan) $q->where('nmkec', $this->filterKecamatan);
        if ($this->filterDesa) $q->where('nmdesa', $this->filterDesa);
        if ($this->filterStatus) $q->where('tindak_lanjut', $this->filterStatus);
        if ($this->filterStatusPerbaikan) $q->where('status_perbaikan', $this->filterStatusPerbaikan);

        if ($this->search) {
            $s = $this->search;
            $regionCodes = $this->regionCodesByPetugas($s);

            $q->where(function ($qq) use ($s, $regionCodes) {
                $qq->where('nama', 'like', "%{$s}%")
                    ->orWhere('nama_dtsen', 'like', "%{$s}%")
                    ->orWhere('nik_dtsen', 'like', "%{$s}%")
                    ->orWhere('assignment_id', 'like', "%{$s}%")
                    ->orWhere('kode_sls', 'like', "%{$s}%")
                    ->orWhere('region_code', 'like', "%{$s}%")
                    ->orWhere('variabel_missing', 'like', "%{$s}%")
                    ->orWhere('email_petugas', 'like', "%{$s}%")
                    ->orWhere('catatan', 'like', "%{$s}%");

                if (!empty($regionCodes)) {
                    $qq->orWhereIn('region_code', $regionCodes);
                }
            });
        }

        return $q;
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

        // Variabel missing (satu baris bisa berisi beberapa variabel)
        $byVariabel = $rows
            ->flatMap(fn ($r) => $r->variabel_list ?: ['(Tidak disebutkan)'])
            ->countBy()
            ->sortDesc()
            ->take(10);

        $byStatus = [
            'sudah' => $selesai,
            'belum' => $total - $selesai,
        ];

        $byStatusPerbaikan = $rows
            ->groupBy(fn ($r) => $r->status_perbaikan ?: '(Kosong)')
            ->map->count()
            ->sortDesc();

        $byStatusPenyelesaian = $rows->whereNotNull('status_penyelesaian')
            ->groupBy('status_penyelesaian')
            ->map(fn ($g, $key) => [
                'count' => $g->count(),
                'label' => MissingValueMikro::statusOptions()[$key] ?? $key,
            ]);

        // Distribusi skor kemiripan DTSEN
        $skorBuckets = ['< 50%' => 0, '50–69%' => 0, '70–89%' => 0, '≥ 90%' => 0, 'Tanpa skor' => 0];
        foreach ($rows as $r) {
            $s = $r->skor_normal;
            if ($s === null) $skorBuckets['Tanpa skor']++;
            elseif ($s < 0.5) $skorBuckets['< 50%']++;
            elseif ($s < 0.7) $skorBuckets['50–69%']++;
            elseif ($s < 0.9) $skorBuckets['70–89%']++;
            else $skorBuckets['≥ 90%']++;
        }

        $byKecamatan = $rows->groupBy('nmkec')->map(function ($g, $kec) {
            $t = $g->count();
            $s = $g->where('tindak_lanjut', 'sudah')->count();

            return [
                'kecamatan' => $kec ?: '(Tidak diketahui)',
                'total' => $t,
                'selesai' => $s,
                'belum' => $t - $s,
                'persen' => $t > 0 ? round($s / $t * 100, 1) : 0,
            ];
        })->sortByDesc('total')->values();

        // Rekap per PPL (petugas dicari via region_code -> sls_dailies)
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
            ->sortByDesc('belum')
            ->take(15)
            ->values();

        return [
            'total' => $total,
            'selesai' => $selesai,
            'denganCatatan' => $rows->whereNotNull('catatan')->count(),
            'terpetakan' => $terpetakan,
            'byVariabel' => $byVariabel,
            'byStatus' => $byStatus,
            'byStatusPerbaikan' => $byStatusPerbaikan,
            'byStatusPenyelesaian' => $byStatusPenyelesaian,
            'skorBuckets' => $skorBuckets,
            'byKecamatan' => $byKecamatan,
            'byPpl' => $byPpl,
        ];
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

    protected function variabelOptions()
    {
        return $this->baseQuery()->pluck('variabel_missing')
            ->flatMap(fn ($v) => MissingValueMikro::splitVariabel($v))
            ->unique()->sort()->values();
    }

    protected function statusPerbaikanOptions()
    {
        return $this->baseQuery()->pluck('status_perbaikan')->filter()->unique()->sort()->values();
    }

    // =================================================================
    // EXPORT EXCEL (mengikuti filter yang sedang aktif)
    // =================================================================

    public function exportMikro()
    {
        $rows = $this->mikroQuery()->orderBy('nmkec')->orderBy('nmdesa')->orderBy('no')->get();
        $petugasMap = $this->resolver()->resolveMany($rows);

        $mapped = $rows->map(function ($m) use ($petugasMap) {
            $p = $petugasMap[$m->id] ?? null;

            return [
                // --- 27 kolom asli ---
                $m->no,
                $m->nama,
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
                $m->assignment_id,
                $m->variabel_missing,
                $m->id_petugas,
                $m->email_petugas,
                $m->jk_se,
                $m->tgl_lahir_se,
                $m->bln_lahir_se,
                $m->thn_lahir_se,
                $m->nik_dtsen,
                $m->nama_dtsen,
                $m->jk_dtsen,
                $m->tgl_lahir_dtsen,
                $m->skor_kemiripan,
                $m->status_perbaikan,
                $m->fasih_link ?? '',      // link mode edit (bukan link dari excel)
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
            'data-mikro-missing-value-' . $this->batch->tanggal->format('Y-m-d'),
            [
                'No', 'Nama Anggota Keluarga', 'Kode Prov', 'Nama Provinsi', 'Kode Kab/Kota', 'Nama Kab/Kota',
                'Kode Kec', 'Nama Kecamatan', 'Kode Desa', 'Nama Desa/Kel', 'Kode SLS', 'Sub SLS',
                'Assignment ID', 'Variabel Missing', 'ID Petugas', 'Email Petugas',
                'Jenis Kelamin SE', 'Tgl Lahir SE', 'Bln Lahir SE', 'Thn Lahir SE',
                'NIK hasil matched DTSEN', 'Nama hasil matched DTSEN', 'Jenis Kelamin hasil matched DTSEN',
                'Tanggal lahir hasil matched DTSEN', 'Skor Kemiripan', 'Status Perbaikan', 'Link Fasih (Edit)',
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
            'statusOptions' => MissingValueMikro::statusOptions(),
        ];

        if ($this->view === 'mikro') {
            $mikros = $this->mikroQuery()
                ->orderBy('nmkec')->orderBy('nmdesa')->orderBy('no')
                ->paginate($this->perPage);

            $viewData += [
                'mikros' => $mikros,
                'petugasMap' => $this->resolver()->resolveMany($mikros->getCollection()),
                'kecamatanOptions' => $this->kecamatanOptions(),
                'desaOptions' => $this->desaOptions(),
                'variabelOptions' => $this->variabelOptions(),
                'statusPerbaikanOptions' => $this->statusPerbaikanOptions(),
            ];
        } else {
            $viewData['dash'] = $this->dashboardData();
        }

        return view('livewire.missing-value.missing-value-detail', $viewData);
    }
}
