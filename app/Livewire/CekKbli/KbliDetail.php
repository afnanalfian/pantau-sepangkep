<?php

namespace App\Livewire\CekKbli;

use App\Models\KbliBatch;
use App\Models\KbliMaster;
use App\Models\KbliMikro;
use App\Services\SimpleExcelExporter;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class KbliDetail extends Component
{
    use WithPagination;

    public KbliBatch $batch;
    public string $view = 'dashboard'; // dashboard | mikro

    // filter data mikro
    public string $filterKecamatan = '';
    public string $filterDesa = '';
    public string $filterKbli = '';
    public string $filterStatus = '';
    public string $filterHasil = '';
    public string $search = '';
    public int $perPage = 10;

    // modal tindak lanjut
    public bool $showModal = false;
    public ?int $selectedId = null;
    public ?string $selectedStatus = null;
    public string $catatan = '';
    public ?string $modalNama = null;
    public ?string $modalKbli = null;
    public bool $modalEdit = false;

    public function mount(KbliBatch $batch)
    {
        $this->batch = $batch;
    }

    public function updatingSearch() { $this->resetPage(); }
    public function updatingFilterKecamatan() { $this->resetPage(); }
    public function updatingFilterDesa() { $this->resetPage(); }
    public function updatingFilterKbli() { $this->resetPage(); }
    public function updatingFilterStatus() { $this->resetPage(); }
    public function updatingFilterHasil() { $this->resetPage(); }
    public function updatingPerPage() { $this->resetPage(); }

    public function updatedFilterKecamatan()
    {
        $this->filterDesa = '';
    }

    public function updatedFilterStatus()
    {
        // filter hasil hanya relevan untuk yang sudah ditindaklanjuti
        if ($this->filterStatus === 'belum') $this->filterHasil = '';
    }

    public function lihatDataMikro() { $this->view = 'mikro'; }
    public function kembaliKeDashboard() { $this->view = 'dashboard'; }

    public function resetFilter()
    {
        $this->reset(['filterKecamatan', 'filterDesa', 'filterKbli', 'filterStatus', 'filterHasil', 'search']);
        $this->resetPage();
    }

    // =================================================================
    // AKSI TINDAK LANJUT
    // =================================================================

    public function bukaModalTindakLanjut(int $id)
    {
        $this->authorizeAksi();
        $m = $this->findMikro($id);
        if (!$m) return;

        $this->resetValidation();
        $this->selectedId = $m->id;
        $this->modalNama = $m->nama_display;
        $this->modalKbli = $m->kbli_akhir . ' – ' . $m->kbli_label;
        $this->modalEdit = $m->tindak_lanjut === 'sudah';
        $this->selectedStatus = $m->status_penyelesaian;
        $this->catatan = (string) $m->catatan;
        $this->showModal = true;
    }

    public function tutupModal()
    {
        $this->reset(['showModal', 'selectedId', 'selectedStatus', 'catatan', 'modalNama', 'modalKbli', 'modalEdit']);
        $this->resetValidation();
    }

    public function prosesTandaiSelesai()
    {
        $this->authorizeAksi();

        $this->validate([
            'selectedStatus' => 'required|in:' . implode(',', array_keys(KbliMikro::statusOptions())),
            'catatan' => 'nullable|string|max:1000',
        ], [
            'selectedStatus.required' => 'Silakan pilih hasil pemeriksaan KBLI.',
            'catatan.max' => 'Catatan maksimal 1000 karakter.',
        ]);

        $m = $this->findMikro($this->selectedId);
        if (!$m) {
            $this->tutupModal();
            return;
        }

        $m->update([
            'tindak_lanjut' => 'sudah',
            'status_penyelesaian' => $this->selectedStatus,
            'catatan' => trim($this->catatan) !== '' ? trim($this->catatan) : null,
            'tindak_lanjut_at' => now(),
            'tindak_lanjut_by' => session('role_label'),
        ]);

        $label = KbliMikro::statusOptions()[$this->selectedStatus];
        $edit = $this->modalEdit;

        $this->tutupModal();
        session()->flash('success', ($edit ? 'Tindak lanjut diperbarui: ' : 'KBLI ditandai selesai: ') . $label);
    }

    public function batalkanTindakLanjut(int $id)
    {
        $this->authorizeAksi();
        KbliMikro::where('kbli_batch_id', $this->batch->id)->whereKey($id)->update([
            'tindak_lanjut' => 'belum',
            'status_penyelesaian' => null,
            'catatan' => null,
            'tindak_lanjut_at' => null,
            'tindak_lanjut_by' => null,
        ]);
        session()->flash('info', 'Tindak lanjut berhasil dibatalkan.');
    }

    protected function authorizeAksi()
    {
        abort_unless(session('role'), 403);
    }

    protected function findMikro(?int $id): ?KbliMikro
    {
        if (!$id) return null;

        return KbliMikro::where('kbli_batch_id', $this->batch->id)->find($id);
    }

    // =================================================================
    // QUERY
    // =================================================================

    protected function mikroQuery()
    {
        $q = KbliMikro::where('kbli_batch_id', $this->batch->id);

        if ($this->filterKecamatan) $q->where('nmkec', $this->filterKecamatan);
        if ($this->filterDesa) $q->where('nmdesa', $this->filterDesa);
        if ($this->filterKbli) $q->where('kbli_akhir', $this->filterKbli);
        if ($this->filterStatus) $q->where('tindak_lanjut', $this->filterStatus);
        if ($this->filterHasil) $q->where('status_penyelesaian', $this->filterHasil);

        if ($this->search !== '') {
            $s = $this->search;
            $q->where(function ($qq) use ($s) {
                $qq->where('nama_usaha', 'like', "%{$s}%")
                    ->orWhere('assignment_id', 'like', "%{$s}%")
                    ->orWhere('keg_utama', 'like', "%{$s}%")
                    ->orWhere('produk', 'like', "%{$s}%")
                    ->orWhere('kbli_akhir', 'like', "%{$s}%")
                    ->orWhere('index1', 'like', "%{$s}%")
                    ->orWhere('catatan', 'like', "%{$s}%");
            });
        }

        return $q;
    }

    protected function baseQuery()
    {
        return KbliMikro::where('kbli_batch_id', $this->batch->id);
    }

    protected function kecamatanOptions()
    {
        return $this->baseQuery()->whereNotNull('nmkec')->distinct()->orderBy('nmkec')->pluck('nmkec');
    }

    protected function desaOptions()
    {
        $q = $this->baseQuery()->whereNotNull('nmdesa');
        if ($this->filterKecamatan) $q->where('nmkec', $this->filterKecamatan);

        return $q->distinct()->orderBy('nmdesa')->pluck('nmdesa');
    }

    /** kode => "kode – judul (jumlah)" */
    protected function kbliOptions(): array
    {
        $q = $this->baseQuery()->whereNotNull('kbli_akhir');
        if ($this->filterKecamatan) $q->where('nmkec', $this->filterKecamatan);
        if ($this->filterDesa) $q->where('nmdesa', $this->filterDesa);

        return $q->select('kbli_akhir', DB::raw('COUNT(*) as jml'))
            ->groupBy('kbli_akhir')
            ->orderBy('kbli_akhir')
            ->get()
            ->mapWithKeys(fn ($r) => [
                $r->kbli_akhir => $r->kbli_akhir . ' – ' . \Illuminate\Support\Str::limit(KbliMaster::label($r->kbli_akhir), 60) . ' (' . $r->jml . ')',
            ])
            ->all();
    }

    // =================================================================
    // DASHBOARD
    // =================================================================

    protected function dashboardData(): array
    {
        $sudahExpr = "SUM(CASE WHEN tindak_lanjut = 'sudah' THEN 1 ELSE 0 END)";

        $total = $this->baseQuery()->count();
        $selesai = $this->baseQuery()->where('tindak_lanjut', 'sudah')->count();

        $byHasil = $this->baseQuery()->whereNotNull('status_penyelesaian')
            ->select('status_penyelesaian', DB::raw('COUNT(*) as jml'))
            ->groupBy('status_penyelesaian')
            ->pluck('jml', 'status_penyelesaian')
            ->all();

        $hasil = [];
        foreach (KbliMikro::statusOptions() as $key => $label) {
            $hasil[$key] = ['label' => $label, 'count' => (int) ($byHasil[$key] ?? 0)];
        }

        $byKecamatan = $this->baseQuery()
            ->select(DB::raw("COALESCE(nmkec, kdkec, '(Tidak diketahui)') as kec"), DB::raw('COUNT(*) as total'), DB::raw("$sudahExpr as selesai"))
            ->groupBy('kec')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($r) => [
                'kecamatan' => $r->kec,
                'total' => (int) $r->total,
                'selesai' => (int) $r->selesai,
                'persen' => $r->total > 0 ? round($r->selesai / $r->total * 100, 1) : 0,
            ])
            ->values()
            ->all();

        $topKbli = $this->baseQuery()->whereNotNull('kbli_akhir')
            ->select('kbli_akhir', DB::raw('COUNT(*) as total'), DB::raw("$sudahExpr as selesai"))
            ->groupBy('kbli_akhir')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(fn ($r) => [
                'kode' => $r->kbli_akhir,
                'label' => KbliMaster::label($r->kbli_akhir),
                'total' => (int) $r->total,
                'selesai' => (int) $r->selesai,
            ])
            ->all();

        $byKategori = $this->baseQuery()
            ->select(DB::raw("COALESCE(kbli_kategori, '?') as kat"), DB::raw('COUNT(*) as total'))
            ->groupBy('kat')
            ->orderBy('kat')
            ->get()
            ->map(fn ($r) => [
                'kode' => $r->kat,
                'label' => KbliMaster::KATEGORI[$r->kat] ?? 'Tidak terklasifikasi',
                'total' => (int) $r->total,
            ])
            ->all();

        return [
            'total' => $total,
            'selesai' => $selesai,
            'belum' => $total - $selesai,
            'persen' => $total > 0 ? round($selesai / $total * 100, 1) : 0,
            'hasil' => $hasil,
            'byKecamatan' => $byKecamatan,
            'topKbli' => $topKbli,
            'byKategori' => $byKategori,
            'jumlahKodeKbli' => $this->baseQuery()->whereNotNull('kbli_akhir')->distinct()->count('kbli_akhir'),
        ];
    }

    // =================================================================
    // EXPORT
    // =================================================================

    public function exportMikro()
    {
        $rows = $this->mikroQuery()->orderBy('nmkec')->orderBy('nmdesa')->orderBy('no')->get();

        $mapped = $rows->map(fn (KbliMikro $m) => [
            $m->no,
            $m->assignment_id,
            $m->index1,
            $m->kdprov,
            $m->kdkab,
            $m->kdkec,
            $m->nmkec_display,
            $m->kddesa,
            $m->nmdesa_display,
            $m->kbli_akhir,
            $m->kbli_label,
            $m->kbli_kategori,
            $m->nama_usaha,
            $m->keg_utama,
            $m->produk,
            $m->tindak_lanjut === 'sudah' ? 'Sudah' : 'Belum',
            $m->tindak_lanjut === 'sudah' ? $m->status_label : '',
            $m->catatan ?? '',
            $m->tindak_lanjut_by ?? '',
            $m->tindak_lanjut_at?->format('Y-m-d H:i') ?? '',
            $m->fasih_link ?? '',
        ]);

        return SimpleExcelExporter::export(
            'cek-kbli-' . $this->batch->tanggal->format('Y-m-d'),
            [
                'No', 'Assignment ID', 'Index1', 'Kode Prov', 'Kode Kab', 'Kode Kec', 'Kecamatan',
                'Kode Desa', 'Desa/Kel', 'KBLI Akhir', 'Judul KBLI', 'Kategori',
                'Nama Usaha', 'Kegiatan Utama', 'Produk',
                'Tindak Lanjut', 'Hasil Pemeriksaan', 'Catatan', 'Ditandai Oleh', 'Waktu Tindak Lanjut',
                'Link Edit FASIH',
            ],
            $mapped->all()
        );
    }

    // =================================================================
    // RENDER
    // =================================================================

    public function render()
    {
        $data = [
            'statusOptions' => KbliMikro::statusOptions(),
        ];

        if ($this->view === 'mikro') {
            $data['mikros'] = $this->mikroQuery()
                ->orderBy('nmkec')->orderBy('nmdesa')->orderBy('no')
                ->paginate($this->perPage);
            $data['kecamatanOptions'] = $this->kecamatanOptions();
            $data['desaOptions'] = $this->desaOptions();
            $data['kbliOptions'] = $this->kbliOptions();
        } else {
            $data['dash'] = $this->dashboardData();
        }

        return view('livewire.cek-kbli.kbli-detail', $data);
    }
}
