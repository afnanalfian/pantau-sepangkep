<?php

namespace App\Livewire\PemindahanPendapatan;

use App\Models\PemindahanBatch;
use App\Models\PemindahanMikro;
use App\Services\SimpleExcelExporter;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class PemindahanDetail extends Component
{
    use WithPagination;

    public PemindahanBatch $batch;
    public string $view = 'dashboard'; // dashboard | mikro

    public string $filterKecamatan = '';
    public string $filterDesa = '';
    public string $filterJalur = '';
    public string $filterStatus = '';
    public string $search = '';
    public int $perPage = 10;

    public function mount(PemindahanBatch $batch)
    {
        $this->batch = $batch;
    }

    public function updatingSearch() { $this->resetPage(); }
    public function updatingFilterKecamatan() { $this->resetPage(); }
    public function updatingFilterDesa() { $this->resetPage(); }
    public function updatingFilterJalur() { $this->resetPage(); }
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
        $this->reset(['filterKecamatan', 'filterDesa', 'filterJalur', 'filterStatus', 'search']);
        $this->resetPage();
    }

    // =================================================================
    // AKSI TINDAK LANJUT (langsung, tanpa pilihan & catatan)
    // =================================================================

    public function tandaiSelesai(int $id)
    {
        abort_unless(session('role'), 403);

        $updated = $this->baseQuery()->whereKey($id)->where('tindak_lanjut', 'belum')->update([
            'tindak_lanjut' => 'sudah',
            'tindak_lanjut_at' => now(),
            'tindak_lanjut_by' => session('role_label'),
        ]);

        if ($updated) session()->flash('success', 'Usaha ditandai telah diselesaikan.');
    }

    public function batalkanTindakLanjut(int $id)
    {
        abort_unless(session('role'), 403);

        $this->baseQuery()->whereKey($id)->update([
            'tindak_lanjut' => 'belum',
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
        return PemindahanMikro::where('pemindahan_batch_id', $this->batch->id);
    }

    protected function mikroQuery()
    {
        $q = $this->baseQuery();

        if ($this->filterKecamatan) $q->where('nmkec', $this->filterKecamatan);
        if ($this->filterDesa) $q->where('nmdesa', $this->filterDesa);
        if ($this->filterJalur) $q->where('jalur_pelaporan', $this->filterJalur);
        if ($this->filterStatus) $q->where('tindak_lanjut', $this->filterStatus);

        if ($this->search !== '') {
            $s = $this->search;
            $q->where(function ($qq) use ($s) {
                $qq->where('nama_usaha', 'like', "%{$s}%")
                    ->orWhere('assignment_id', 'like', "%{$s}%")
                    ->orWhere('nama_sls', 'like', "%{$s}%")
                    ->orWhere('kode_sls', 'like', "%{$s}%");
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

        $group = fn (string $expr) => $this->baseQuery()
            ->select(DB::raw("$expr as grp"), DB::raw('COUNT(*) as total'), DB::raw("$sudah as selesai"))
            ->groupBy('grp')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($r) => [
                'nama' => $r->grp,
                'total' => (int) $r->total,
                'selesai' => (int) $r->selesai,
                'persen' => $r->total > 0 ? round($r->selesai / $r->total * 100, 1) : 0,
            ])
            ->all();

        return [
            'total' => $total,
            'selesai' => $selesai,
            'belum' => $total - $selesai,
            'persen' => $total > 0 ? round($selesai / $total * 100, 1) : 0,
            'byKecamatan' => $group("COALESCE(NULLIF(nmkec, ''), '(Tidak diketahui)')"),
            'byJalur' => $group("COALESCE(NULLIF(jalur_pelaporan, ''), '(Kosong)')"),
            'topDesa' => array_slice($group("CONCAT(COALESCE(nmdesa, '-'), ' · ', COALESCE(nmkec, '-'))"), 0, 10),
        ];
    }

    // =================================================================
    // EXPORT
    // =================================================================

    public function exportMikro()
    {
        $rows = $this->mikroQuery()->orderBy('nmkec')->orderBy('nmdesa')->orderBy('no')->get();

        $mapped = $rows->map(fn (PemindahanMikro $m) => [
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
            $m->pendapatan_utama,
            $m->pendapatan_lain,
            $m->total_pengeluaran,
            $m->tindak_lanjut === 'sudah' ? 'Sudah' : 'Belum',
            $m->tindak_lanjut_by ?? '',
            $m->tindak_lanjut_at?->format('Y-m-d H:i') ?? '',
            $m->fasih_link ?? '',
        ]);

        return SimpleExcelExporter::export(
            'pemindahan-pendapatan-' . $this->batch->tanggal->format('Y-m-d'),
            [
                'No', 'Assignment ID', 'No Usaha', 'Nama Usaha', 'Kode Prov', 'Provinsi', 'Kode Kab', 'Kab/Kota',
                'Kode Kec', 'Kecamatan', 'Kode Desa', 'Desa/Kel', 'Kode SLS', 'Nama SLS', 'Sub SLS',
                'Jalur Pelaporan', 'Pendapatan Utama Setahun', 'Pendapatan Lain Setahun', 'Total Pengeluaran Setahun',
                'Tindak Lanjut', 'Ditandai Oleh', 'Waktu Tindak Lanjut', 'Link Edit FASIH',
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
        } else {
            $data['dash'] = $this->dashboardData();
        }

        return view('livewire.pemindahan-pendapatan.pemindahan-detail', $data);
    }
}
