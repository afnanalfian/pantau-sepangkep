<?php

namespace App\Livewire\Anomali;

use App\Models\AnomaliBatch;
use App\Models\AnomaliMikro;
use App\Models\SlsDaily;
use App\Services\PetugasResolver;
use App\Services\SimpleExcelExporter;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class AnomaliDetail extends Component
{
    use WithPagination;

    public AnomaliBatch $batch;
    public string $view = 'dashboard'; // dashboard | mikro

    // filter data mikro
    public string $filterJenis = '';
    public string $filterKecamatan = '';
    public string $filterDesa = '';
    public string $filterStatus = '';
    public string $search = '';
    public int $perPage = 10;

    // Modal properties
    public bool $showModal = false;
    public ?int $selectedAnomaliId = null;
    public ?string $selectedStatus = null;
    public ?string $modalAnomaliName = null;

    /** Cache resolver petugas selama satu request. */
    protected ?PetugasResolver $resolver = null;

    public function mount(AnomaliBatch $batch)
    {
        $this->batch = $batch;
    }

    public function updatingSearch() { $this->resetPage(); }
    public function updatingFilterJenis() { $this->resetPage(); }
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
        // Petugas diambil dari upload harian terakhir sebelum/pada tanggal batch,
        // supaya batch lama tetap menunjuk petugas yang benar saat itu.
        return $this->resolver ??= PetugasResolver::forDate($this->batch->tanggal);
    }

    // =================================================================
    // AKSI TINDAK LANJUT
    // =================================================================

    public function bukaModalTindakLanjut(int $id)
    {
        $this->authorizeAksi();
        $anomali = AnomaliMikro::find($id);
        if ($anomali) {
            $this->selectedAnomaliId = $id;
            $this->modalAnomaliName = $anomali->nama_display;
            $this->selectedStatus = null;
            $this->showModal = true;
        }
    }

    public function tutupModal()
    {
        $this->showModal = false;
        $this->selectedAnomaliId = null;
        $this->selectedStatus = null;
        $this->modalAnomaliName = null;
    }

    public function prosesTandaiSelesai()
    {
        $this->authorizeAksi();

        $this->validate([
            'selectedStatus' => 'required|in:revoked_pml,diselesaikan_admin,reject_admin',
        ], [
            'selectedStatus.required' => 'Silakan pilih status penyelesaian.',
        ]);

        AnomaliMikro::whereKey($this->selectedAnomaliId)->update([
            'tindak_lanjut' => 'sudah',
            'tindak_lanjut_at' => now(),
            'status_penyelesaian' => $this->selectedStatus,
        ]);

        $statusLabel = AnomaliMikro::statusOptions()[$this->selectedStatus] ?? $this->selectedStatus;

        $this->tutupModal();
        session()->flash('success', 'Anomali berhasil ditandai selesai dengan status: ' . $statusLabel);
    }

    public function batalkanTindakLanjut(int $id)
    {
        $this->authorizeAksi();
        AnomaliMikro::whereKey($id)->update([
            'tindak_lanjut' => 'belum',
            'tindak_lanjut_at' => null,
            'status_penyelesaian' => null,
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

    protected function mikroQuery()
    {
        $q = AnomaliMikro::where('anomali_batch_id', $this->batch->id);

        if ($this->filterJenis) $q->where('jenis', $this->filterJenis);
        if ($this->filterKecamatan) $q->where('nmkec', $this->filterKecamatan);
        if ($this->filterDesa) $q->where('nmdesa', $this->filterDesa);
        if ($this->filterStatus) $q->where('tindak_lanjut', $this->filterStatus);

        if ($this->search) {
            $s = $this->search;
            // Nama PPL/PML tidak tersimpan di tabel anomali, jadi kita cari dulu
            // region_code milik petugas yang namanya cocok, lalu ikut disertakan.
            $regionCodes = $this->regionCodesByPetugas($s);

            $q->where(function ($qq) use ($s, $regionCodes) {
                $qq->where('nama', 'like', "%{$s}%")
                    ->orWhere('assignment_id', 'like', "%{$s}%")
                    ->orWhere('kode_sls', 'like', "%{$s}%")
                    ->orWhere('region_code', 'like', "%{$s}%")
                    ->orWhere('status_penyelesaian', 'like', "%{$s}%");

                if (!empty($regionCodes)) {
                    $qq->orWhereIn('region_code', $regionCodes);
                }
            });
        }

        return $q;
    }

    /**
     * Daftar region_code untuk PPL/PML yang namanya cocok dengan kata kunci.
     *
     * @return array<int, string>
     */
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
    // DASHBOARD RINGKASAN
    // =================================================================

    protected function dashboardData(): array
    {
        $mikros = AnomaliMikro::where('anomali_batch_id', $this->batch->id)->get();

        $byJenis = $mikros->groupBy('jenis')->map->count();
        $byAnomali = $mikros->groupBy('nama_anomali')->map->count()->sortDesc()->take(10);
        $byStatus = $mikros->groupBy('tindak_lanjut')->map->count();

        $byStatusPenyelesaian = $mikros->whereNotNull('status_penyelesaian')
            ->groupBy('status_penyelesaian')
            ->map(fn ($g) => [
                'count' => $g->count(),
                'label' => AnomaliMikro::statusOptions()[$g->first()->status_penyelesaian] ?? $g->first()->status_penyelesaian,
            ]);

        $byKecamatan = $mikros->groupBy('nmkec')->map(function ($g, $kec) {
            $total = $g->count();
            $selesai = $g->where('tindak_lanjut', 'sudah')->count();

            return [
                'kecamatan' => $kec ?: '(Tidak diketahui)',
                'total' => $total,
                'selesai' => $selesai,
                'persen' => $total > 0 ? round($selesai / $total * 100, 1) : 0,
            ];
        })->sortByDesc('total')->values();

        // Berapa anomali yang berhasil dipetakan ke petugas lewat region_code
        $statPetugas = $this->resolver()->statistik($mikros);

        return [
            'total' => $mikros->count(),
            'selesai' => $mikros->where('tindak_lanjut', 'sudah')->count(),
            'byJenis' => $byJenis,
            'byAnomali' => $byAnomali,
            'byStatus' => $byStatus,
            'byStatusPenyelesaian' => $byStatusPenyelesaian,
            'byKecamatan' => $byKecamatan,
            'statPetugas' => $statPetugas,
        ];
    }

    protected function kecamatanOptions()
    {
        return AnomaliMikro::where('anomali_batch_id', $this->batch->id)
            ->pluck('nmkec')->filter()->unique()->sort()->values();
    }

    protected function desaOptions()
    {
        $q = AnomaliMikro::where('anomali_batch_id', $this->batch->id);
        if ($this->filterKecamatan) $q->where('nmkec', $this->filterKecamatan);

        return $q->pluck('nmdesa')->filter()->unique()->sort()->values();
    }

    // =================================================================
    // EXPORT
    // =================================================================

    public function exportMikro()
    {
        $rows = $this->mikroQuery()->orderBy('nmkec')->orderBy('nmdesa')->orderBy('no')->get();
        $petugasMap = $this->resolver()->resolveMany($rows);

        $mapped = $rows->map(function ($m) use ($petugasMap) {
            $p = $petugasMap[$m->id] ?? null;
            $statusLabel = $m->tindak_lanjut === 'sudah'
                ? 'Sudah (' . $m->status_label . ')'
                : 'Belum';

            return [
                $m->no,
                $m->nama_display,
                $m->jenis,
                $m->kdkec,
                $m->nmkec,
                $m->kddesa,
                $m->nmdesa,
                $m->kode_sls,
                $m->sub_sls,
                $m->region_key,
                $p['nama_sls'] ?? '-',
                $m->assignment_id,
                $p['nama_ppl'] ?? '-',
                $p['username'] ?? '-',
                $p['nama_pml'] ?? '-',
                $p['pml_organik'] ?? '-',
                $m->nama_anomali,
                $statusLabel,
                $m->fasih_link ?? '',
            ];
        });

        return SimpleExcelExporter::export(
            'data-mikro-anomali-' . $this->batch->tanggal->format('Y-m-d'),
            [
                'No', 'Nama KRT/Usaha', 'Jenis', 'Kode Kec', 'Kecamatan', 'Kode Desa', 'Desa/Kel',
                'Kode SLS', 'Sub SLS', 'Region Code', 'Nama SLS', 'Assignment ID',
                'Nama PPL', 'Username PPL', 'Nama PML', 'PML Organik',
                'Nama Anomali', 'Tindak Lanjut', 'Link Fasih',
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
            'kecamatanOptions' => $this->kecamatanOptions(),
            'desaOptions' => $this->desaOptions(),
            'statusOptions' => AnomaliMikro::statusOptions(),
            'showModal' => $this->showModal,
            'selectedStatus' => $this->selectedStatus,
            'modalAnomaliName' => $this->modalAnomaliName,
        ];

        if ($this->view === 'mikro') {
            $mikros = $this->mikroQuery()
                ->orderBy('nmkec')->orderBy('nmdesa')->orderBy('no')
                ->paginate($this->perPage);

            $viewData['mikros'] = $mikros;
            $viewData['petugasMap'] = $this->resolver()->resolveMany($mikros->getCollection());
        } else {
            $viewData['dash'] = $this->dashboardData();
        }

        return view('livewire.anomali.anomali-detail', $viewData);
    }
}
