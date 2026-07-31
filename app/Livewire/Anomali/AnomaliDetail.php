<?php

namespace App\Livewire\Anomali;

use App\Models\AnomaliBatch;
use App\Models\AnomaliMikro;
use App\Models\Mitra;
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

    public function mount(AnomaliBatch $batch)
    {
        $this->batch = $batch;
    }

    public function updatingSearch() { $this->resetPage(); }
    public function updatingFilterJenis() { $this->resetPage(); }
    public function updatingFilterKecamatan() { $this->resetPage(); }
    public function updatingFilterDesa() { $this->resetPage(); }
    public function updatingFilterStatus() { $this->resetPage(); }

    public function lihatDataMikro()
    {
        $this->view = 'mikro';
    }

    public function kembaliKeDashboard()
    {
        $this->view = 'dashboard';
    }

    // Buka modal untuk memilih status
    public function bukaModalTindakLanjut(int $id)
    {
        $this->authorizeAksi();
        $anomali = AnomaliMikro::find($id);
        if ($anomali) {
            $this->selectedAnomaliId = $id;
            $this->modalAnomaliName = $anomali->nama;
            $this->selectedStatus = null;
            $this->showModal = true;
        }
    }

    // Tutup modal
    public function tutupModal()
    {
        $this->showModal = false;
        $this->selectedAnomaliId = null;
        $this->selectedStatus = null;
        $this->modalAnomaliName = null;
    }

    // Proses tandai selesai dengan status
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

        // Ambil label status dengan aman
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

    protected function mikroQuery()
    {
        $q = AnomaliMikro::where('anomali_batch_id', $this->batch->id);

        if ($this->filterJenis) $q->where('jenis', $this->filterJenis);
        if ($this->filterKecamatan) $q->where('nmkec', $this->filterKecamatan);
        if ($this->filterDesa) $q->where('nmdesa', $this->filterDesa);
        if ($this->filterStatus) $q->where('tindak_lanjut', $this->filterStatus);
        if ($this->search) {
            $s = $this->search;
            $q->where(function ($qq) use ($s) {
                $qq->where('nama', 'like', "%{$s}%")
                    ->orWhere('assignment_id', 'like', "%{$s}%")
                    ->orWhere('kode_sls', 'like', "%{$s}%")
                    ->orWhere('status_penyelesaian', 'like', "%{$s}%");
            });
        }

        return $q;
    }

    protected function dashboardData(): array
    {
        $mikros = AnomaliMikro::where('anomali_batch_id', $this->batch->id)->get();

        $byJenis = $mikros->groupBy('jenis')->map->count();
        $byAnomali = $mikros->groupBy('nama_anomali')->map->count()->sortDesc()->take(10);
        $byStatus = $mikros->groupBy('tindak_lanjut')->map->count();
        
        // Tambahkan statistik status penyelesaian
        $byStatusPenyelesaian = $mikros->whereNotNull('status_penyelesaian')
            ->groupBy('status_penyelesaian')
            ->map(fn($g) => [
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

        return [
            'total' => $mikros->count(),
            'selesai' => $mikros->where('tindak_lanjut', 'sudah')->count(),
            'byJenis' => $byJenis,
            'byAnomali' => $byAnomali,
            'byStatus' => $byStatus,
            'byStatusPenyelesaian' => $byStatusPenyelesaian,
            'byKecamatan' => $byKecamatan,
        ];
    }

    protected function kecamatanOptions()
    {
        return AnomaliMikro::where('anomali_batch_id', $this->batch->id)->pluck('nmkec')->filter()->unique()->sort()->values();
    }

    protected function desaOptions()
    {
        $q = AnomaliMikro::where('anomali_batch_id', $this->batch->id);
        if ($this->filterKecamatan) $q->where('nmkec', $this->filterKecamatan);
        return $q->pluck('nmdesa')->filter()->unique()->sort()->values();
    }

    public function exportMikro()
    {
        $rows = $this->mikroQuery()->get()->map(function ($m) {
            $mitra = Mitra::where('email', $m->email_petugas)->first();
            $statusLabel = $m->tindak_lanjut === 'sudah' 
                ? 'Sudah (' . ($m->status_label ?? 'Tidak diketahui') . ')' 
                : 'Belum';
            return [
                $m->no, $m->nama, $m->nmkec, $m->nmdesa, $m->kode_sls, $m->sub_sls, $m->assignment_id,
                $mitra->nama_ppl ?? '-', $mitra->nama_pml ?? '-', $mitra->pml_organik ?? '-',
                $m->nama_anomali, $statusLabel, $m->fasih_link ?? '',
            ];
        });

        return SimpleExcelExporter::export('data-mikro-anomali-' . $this->batch->tanggal->format('Y-m-d'),
            ['No', 'Nama KRT/Usaha', 'Kecamatan', 'Desa/Kel', 'Kode SLS', 'Sub SLS', 'Assignment ID', 'Nama PPL', 'Nama PML', 'PML Organik', 'Nama Anomali', 'Tindak Lanjut', 'Link Fasih'],
            $rows->all()
        );
    }

    public function render()
    {
        $viewData = [
            'kecamatanOptions' => $this->kecamatanOptions(),
            'desaOptions' => $this->desaOptions(),
            'statusOptions' => AnomaliMikro::statusOptions(),
            // Kirim semua properti modal ke view
            'showModal' => $this->showModal,
            'selectedStatus' => $this->selectedStatus,
            'modalAnomaliName' => $this->modalAnomaliName,
        ];

        if ($this->view === 'mikro') {
            $viewData['mikros'] = $this->mikroQuery()
                ->with([])
                ->orderBy('nmkec')->orderBy('nama')
                ->paginate($this->perPage);

            $emails = $viewData['mikros']->pluck('email_petugas')->filter()->unique();
            $viewData['mitraMap'] = Mitra::whereIn('email', $emails)->get()->keyBy('email');
        } else {
            $viewData['dash'] = $this->dashboardData();
        }

        return view('livewire.anomali.anomali-detail', $viewData);
    }
}