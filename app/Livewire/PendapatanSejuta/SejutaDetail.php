<?php

namespace App\Livewire\PendapatanSejuta;

use App\Models\SejutaBatch;
use App\Models\SejutaMikro;
use App\Services\SimpleExcelExporter;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class SejutaDetail extends Component
{
    use WithPagination;

    public SejutaBatch $batch;
    public string $view = 'dashboard'; // dashboard | mikro

    public string $filterKecamatan = '';
    public string $filterDesa = '';
    public string $filterJalur = '';
    public string $filterKeberadaan = '';
    public string $filterStatus = '';
    public string $search = '';
    public int $perPage = 10;

    // modal tindak lanjut (tanpa pilihan, hanya catatan opsional)
    public bool $showModal = false;
    public ?int $selectedId = null;
    public string $catatan = '';
    public ?string $modalNama = null;
    public ?string $modalInfo = null;
    public bool $modalEdit = false;

    public function mount(SejutaBatch $batch)
    {
        $this->batch = $batch;
    }

    public function updatingSearch() { $this->resetPage(); }
    public function updatingFilterKecamatan() { $this->resetPage(); }
    public function updatingFilterDesa() { $this->resetPage(); }
    public function updatingFilterJalur() { $this->resetPage(); }
    public function updatingFilterKeberadaan() { $this->resetPage(); }
    public function updatingFilterStatus() { $this->resetPage(); }
    public function updatingPerPage() { $this->resetPage(); }

    public function updatedFilterKecamatan()
    {
        $this->filterDesa = '';
    }

    public function lihatDataMikro() { $this->view = 'mikro'; }
    public function kembaliKeDashboard() { $this->view = 'dashboard'; }

    public function resetFilter()
    {
        $this->reset(['filterKecamatan', 'filterDesa', 'filterJalur', 'filterKeberadaan', 'filterStatus', 'search']);
        $this->resetPage();
    }

    // =================================================================
    // AKSI TINDAK LANJUT
    // =================================================================

    public function bukaModalTindakLanjut(int $id)
    {
        abort_unless(session('role'), 403);
        $m = $this->baseQuery()->find($id);
        if (!$m) return;

        $this->resetValidation();
        $this->selectedId = $m->id;
        $this->modalNama = $m->nama_display;
        $this->modalInfo = 'Total pendapatan setahun: ' . SejutaMikro::rupiah($m->total_pendapatan);
        $this->modalEdit = $m->tindak_lanjut === 'sudah';
        $this->catatan = (string) $m->catatan;
        $this->showModal = true;
    }

    public function tutupModal()
    {
        $this->reset(['showModal', 'selectedId', 'catatan', 'modalNama', 'modalInfo', 'modalEdit']);
        $this->resetValidation();
    }

    public function prosesTandaiSelesai()
    {
        abort_unless(session('role'), 403);

        $this->validate(
            ['catatan' => 'nullable|string|max:1000'],
            ['catatan.max' => 'Catatan maksimal 1000 karakter.']
        );

        $m = $this->selectedId ? $this->baseQuery()->find($this->selectedId) : null;
        if (!$m) {
            $this->tutupModal();
            return;
        }

        $m->update([
            'tindak_lanjut' => 'sudah',
            'catatan' => trim($this->catatan) !== '' ? trim($this->catatan) : null,
            'tindak_lanjut_at' => $m->tindak_lanjut === 'sudah' ? $m->tindak_lanjut_at : now(),
            'tindak_lanjut_by' => session('role_label'),
        ]);

        $edit = $this->modalEdit;
        $this->tutupModal();
        session()->flash('success', $edit ? 'Catatan berhasil diperbarui.' : 'Usaha ditandai telah diselesaikan.');
    }

    public function batalkanTindakLanjut(int $id)
    {
        abort_unless(session('role'), 403);

        $this->baseQuery()->whereKey($id)->update([
            'tindak_lanjut' => 'belum',
            'catatan' => null,
            'tindak_lanjut_at' => null,
            'tindak_lanjut_by' => null,
        ]);
        session()->flash('info', 'Tindak lanjut berhasil dibatalkan.');
    }

    // =================================================================
    // QUERY
    // =================================================================

    protected function baseQuery()
    {
        return SejutaMikro::where('sejuta_batch_id', $this->batch->id);
    }

    protected function mikroQuery()
    {
        $q = $this->baseQuery();

        if ($this->filterKecamatan) $q->where('nmkec', $this->filterKecamatan);
        if ($this->filterDesa) $q->where('nmdesa', $this->filterDesa);
        if ($this->filterJalur) $q->where('jalur_pelaporan', $this->filterJalur);
        if ($this->filterKeberadaan) $q->where('keberadaan', $this->filterKeberadaan);
        if ($this->filterStatus) $q->where('tindak_lanjut', $this->filterStatus);

        if ($this->search !== '') {
            $s = $this->search;
            $q->where(function ($qq) use ($s) {
                $qq->where('nama_usaha', 'like', "%{$s}%")
                    ->orWhere('assignment_id', 'like', "%{$s}%")
                    ->orWhere('nama_sls', 'like', "%{$s}%")
                    ->orWhere('kode_sls', 'like', "%{$s}%")
                    ->orWhere('catatan', 'like', "%{$s}%");
            });
        }

        return $q;
    }

    protected function distinctOptions(string $col, array $where = [])
    {
        $q = $this->baseQuery()->whereNotNull($col)->where($col, '!=', '');
        foreach ($where as $c => $v) {
            if ($v) $q->where($c, $v);
        }

        return $q->distinct()->orderBy($col)->pluck($col);
    }

    // =================================================================
    // DASHBOARD
    // =================================================================

    protected function dashboardData(): array
    {
        $sudah = "SUM(CASE WHEN tindak_lanjut = 'sudah' THEN 1 ELSE 0 END)";

        $total = $this->baseQuery()->count();
        $selesai = $this->baseQuery()->where('tindak_lanjut', 'sudah')->count();

        $group = fn (string $expr, bool $byName = false) => $this->baseQuery()
            ->select(DB::raw("$expr as grp"), DB::raw('COUNT(*) as total'), DB::raw("$sudah as selesai"))
            ->groupBy('grp')
            ->when($byName, fn ($q) => $q->orderBy('grp'), fn ($q) => $q->orderByDesc('total'))
            ->get()
            ->map(fn ($r) => [
                'nama' => $r->grp,
                'total' => (int) $r->total,
                'selesai' => (int) $r->selesai,
                'persen' => $r->total > 0 ? round($r->selesai / $r->total * 100, 1) : 0,
            ])
            ->all();

        // buang prefix urutan "1. " pada label kelompok pendapatan
        $kelompok = array_map(function ($r) {
            $r['nama'] = preg_replace('/^\d+\.\s*/', '', $r['nama']);
            return $r;
        }, $group(SejutaMikro::kelompokPendapatanSql(), true));

        return [
            'total' => $total,
            'selesai' => $selesai,
            'belum' => $total - $selesai,
            'persen' => $total > 0 ? round($selesai / $total * 100, 1) : 0,
            'denganCatatan' => $this->baseQuery()->whereNotNull('catatan')->count(),
            'byKecamatan' => $group("COALESCE(NULLIF(nmkec, ''), '(Tidak diketahui)')"),
            'byJalur' => $group("COALESCE(NULLIF(jalur_pelaporan, ''), '(Kosong)')"),
            'byKeberadaan' => $group("COALESCE(NULLIF(keberadaan, ''), '(Kosong)')", true),
            'byKelompok' => $kelompok,
        ];
    }

    // =================================================================
    // EXPORT
    // =================================================================

    public function exportMikro()
    {
        $rows = $this->mikroQuery()->orderBy('nmkec')->orderBy('nmdesa')->orderBy('no')->get();

        $mapped = $rows->map(fn (SejutaMikro $m) => [
            $m->no,
            $m->assignment_id,
            $m->no_usaha,
            $m->nama_usaha,
            $m->kdprov,
            $m->nmprov,
            $m->kdkab,
            $m->nmkab,
            $m->kdkec,
            $m->nmkec,
            $m->kddesa,
            $m->nmdesa,
            $m->kode_sls,
            $m->nama_sls,
            $m->sub_sls,
            $m->jalur_pelaporan,
            $m->total_pendapatan,
            $m->pendapatan_utama,
            $m->pendapatan_lain,
            $m->total_pengeluaran,
            $m->jml_tenaga_kerja,
            $m->keberadaan,
            $m->tindak_lanjut === 'sudah' ? 'Sudah' : 'Belum',
            $m->catatan ?? '',
            $m->tindak_lanjut_by ?? '',
            $m->tindak_lanjut_at?->format('Y-m-d H:i') ?? '',
            $m->fasih_link ?? '',
        ]);

        return SimpleExcelExporter::export(
            'pendapatan-1-juta-ke-bawah-' . $this->batch->tanggal->format('Y-m-d'),
            [
                'No', 'Assignment ID', 'No Usaha', 'Nama Usaha', 'Kode Prov', 'Provinsi', 'Kode Kab', 'Kab/Kota',
                'Kode Kec', 'Kecamatan', 'Kode Desa', 'Desa/Kel', 'Kode SLS', 'Nama SLS', 'Sub SLS',
                'Jalur Pelaporan', 'Total Pendapatan Setahun', 'Pendapatan Utama Setahun', 'Pendapatan Lain Setahun',
                'Total Pengeluaran Setahun', 'Jml Tenaga Kerja', 'Keberadaan',
                'Tindak Lanjut', 'Catatan', 'Ditandai Oleh', 'Waktu Tindak Lanjut', 'Link Edit FASIH',
            ],
            $mapped->all()
        );
    }

    // =================================================================
    // RENDER
    // =================================================================

    public function render()
    {
        $data = [];

        if ($this->view === 'mikro') {
            $data['mikros'] = $this->mikroQuery()
                ->orderBy('nmkec')->orderBy('nmdesa')->orderBy('no')
                ->paginate($this->perPage);
            $data['kecamatanOptions'] = $this->distinctOptions('nmkec');
            $data['desaOptions'] = $this->distinctOptions('nmdesa', ['nmkec' => $this->filterKecamatan]);
            $data['jalurOptions'] = $this->distinctOptions('jalur_pelaporan');
            $data['keberadaanOptions'] = $this->distinctOptions('keberadaan');
        } else {
            $data['dash'] = $this->dashboardData();
        }

        return view('livewire.pendapatan-sejuta.sejuta-detail', $data);
    }
}
