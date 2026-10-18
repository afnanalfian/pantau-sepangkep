<?php

namespace App\Livewire\FlagRasio3;

use App\Models\FlagRasio3Batch;
use App\Models\FlagRasio3Mikro;
use App\Services\SimpleExcelExporter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class FlagRasio3Detail extends Component
{
    use WithPagination;

    public FlagRasio3Batch $batch;
    public string $view = 'dashboard'; // dashboard | mikro

    public string $filterJenis = '';     // UB | UM
    public string $filterKategori = '';
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
    public ?string $modalInfo = null;
    public bool $modalEdit = false;

    public function mount(FlagRasio3Batch $batch)
    {
        $this->batch = $batch;
    }

    public function updatingSearch() { $this->resetPage(); }
    public function updatingFilterJenis() { $this->resetPage(); }
    public function updatingFilterKategori() { $this->resetPage(); }
    public function updatingFilterKbli() { $this->resetPage(); }
    public function updatingFilterStatus() { $this->resetPage(); }
    public function updatingFilterHasil() { $this->resetPage(); }
    public function updatingPerPage() { $this->resetPage(); }

    public function updatedFilterJenis() { $this->filterKbli = ''; }
    public function updatedFilterKategori() { $this->filterKbli = ''; }

    public function updatedFilterStatus()
    {
        if ($this->filterStatus === 'belum') $this->filterHasil = '';
    }

    public function lihatDataMikro() { $this->view = 'mikro'; }
    public function kembaliKeDashboard() { $this->view = 'dashboard'; }

    /** Klik kartu UB/UM di dashboard -> langsung ke data mikro jenis itu */
    public function lihatJenis(string $jenis)
    {
        $this->filterJenis = array_key_exists($jenis, FlagRasio3Mikro::jenisOptions()) ? $jenis : '';
        $this->view = 'mikro';
        $this->resetPage();
    }

    public function resetFilter()
    {
        $this->reset(['filterJenis', 'filterKategori', 'filterKbli', 'filterStatus', 'filterHasil', 'search']);
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
        $this->modalInfo = $m->jenis_label . ' · KBLI ' . ($m->kode_kbli ?? '-') . ' · Rasio NTB ' . FlagRasio3Mikro::rasio($m->rasio_ntb);
        $this->modalEdit = $m->tindak_lanjut === 'sudah';
        $this->selectedStatus = $m->status_penyelesaian;
        $this->catatan = (string) $m->catatan;
        $this->showModal = true;
    }

    public function tutupModal()
    {
        $this->reset(['showModal', 'selectedId', 'selectedStatus', 'catatan', 'modalNama', 'modalInfo', 'modalEdit']);
        $this->resetValidation();
    }

    public function prosesTandaiSelesai()
    {
        abort_unless(session('role'), 403);

        $this->validate([
            'selectedStatus' => 'required|in:' . implode(',', array_keys(FlagRasio3Mikro::statusOptions())),
            'catatan' => 'nullable|string|max:1000',
        ], [
            'selectedStatus.required' => 'Silakan pilih hasil pemeriksaan.',
            'catatan.max' => 'Catatan maksimal 1000 karakter.',
        ]);

        $m = $this->selectedId ? $this->baseQuery()->find($this->selectedId) : null;
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

        $label = FlagRasio3Mikro::statusOptions()[$this->selectedStatus];
        $edit = $this->modalEdit;
        $this->tutupModal();
        session()->flash('success', ($edit ? 'Tindak lanjut diperbarui: ' : 'Ditandai selesai: ') . $label);
    }

    public function batalkanTindakLanjut(int $id)
    {
        abort_unless(session('role'), 403);

        $this->baseQuery()->whereKey($id)->update([
            'tindak_lanjut' => 'belum',
            'status_penyelesaian' => null,
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
        return FlagRasio3Mikro::where('flag_rasio3_batch_id', $this->batch->id);
    }

    protected function mikroQuery()
    {
        $q = $this->baseQuery();

        if ($this->filterJenis) $q->where('jenis', $this->filterJenis);
        if ($this->filterKategori) $q->where('kategori', $this->filterKategori);
        if ($this->filterKbli) $q->where('kode_kbli', $this->filterKbli);
        if ($this->filterStatus) $q->where('tindak_lanjut', $this->filterStatus);
        if ($this->filterHasil) $q->where('status_penyelesaian', $this->filterHasil);

        if ($this->search !== '') {
            $s = $this->search;
            $q->where(function ($qq) use ($s) {
                $qq->where('nama_usaha', 'like', "%{$s}%")
                    ->orWhere('assignment_id', 'like', "%{$s}%")
                    ->orWhere('kode_kbli', 'like', "%{$s}%")
                    ->orWhere('catatan', 'like', "%{$s}%");
            });
        }

        return $q;
    }

    protected function kategoriOptions()
    {
        $q = $this->baseQuery()->whereNotNull('kategori');
        if ($this->filterJenis) $q->where('jenis', $this->filterJenis);

        return $q->distinct()->orderBy('kategori')->pluck('kategori');
    }

    /** kode => "kode – judul (jumlah)" */
    protected function kbliOptions(): array
    {
        $q = $this->baseQuery()->whereNotNull('kode_kbli');
        if ($this->filterJenis) $q->where('jenis', $this->filterJenis);
        if ($this->filterKategori) $q->where('kategori', $this->filterKategori);

        return $q->select('kode_kbli', DB::raw('COUNT(*) as jml'))
            ->groupBy('kode_kbli')
            ->orderBy('kode_kbli')
            ->get()
            ->mapWithKeys(function ($r) {
                $label = (new FlagRasio3Mikro(['kode_kbli' => $r->kode_kbli]))->kbli_label;

                return [$r->kode_kbli => $r->kode_kbli . ' – ' . Str::limit($label, 55) . ' (' . $r->jml . ')'];
            })
            ->all();
    }

    // =================================================================
    // DASHBOARD
    // =================================================================

    protected function dashboardData(): array
    {
        $sudah = "SUM(CASE WHEN tindak_lanjut = 'sudah' THEN 1 ELSE 0 END)";
        $progress = fn ($r) => [
            'total' => (int) $r->total,
            'selesai' => (int) $r->selesai,
            'persen' => $r->total > 0 ? round($r->selesai / $r->total * 100, 1) : 0,
        ];

        $total = $this->baseQuery()->count();
        $selesai = $this->baseQuery()->where('tindak_lanjut', 'sudah')->count();

        $hasilCount = $this->baseQuery()->whereNotNull('status_penyelesaian')
            ->select('status_penyelesaian', DB::raw('COUNT(*) as jml'))
            ->groupBy('status_penyelesaian')
            ->pluck('jml', 'status_penyelesaian')
            ->all();
        $hasil = [];
        foreach (FlagRasio3Mikro::statusOptions() as $k => $label) {
            $hasil[$k] = ['label' => $label, 'count' => (int) ($hasilCount[$k] ?? 0)];
        }

        $perJenis = $this->baseQuery()
            ->select('jenis', DB::raw('COUNT(*) as total'), DB::raw("$sudah as selesai"))
            ->groupBy('jenis')
            ->get()
            ->keyBy('jenis');
        $byJenis = [];
        foreach (FlagRasio3Mikro::jenisOptions() as $j => $info) {
            $r = $perJenis[$j] ?? (object) ['total' => 0, 'selesai' => 0];
            $byJenis[$j] = $info + $progress($r);
        }

        $byKategori = $this->baseQuery()
            ->select(DB::raw("COALESCE(kategori, '?') as kat"), DB::raw('COUNT(*) as total'), DB::raw("$sudah as selesai"))
            ->groupBy('kat')
            ->orderBy('kat')
            ->get()
            ->map(fn ($r) => ['nama' => $r->kat] + $progress($r))
            ->all();

        $topKbli = $this->baseQuery()->whereNotNull('kode_kbli')
            ->select('kode_kbli', DB::raw('COUNT(*) as total'), DB::raw("$sudah as selesai"))
            ->groupBy('kode_kbli')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(fn ($r) => [
                'kode' => $r->kode_kbli,
                'label' => (new FlagRasio3Mikro(['kode_kbli' => $r->kode_kbli]))->kbli_label,
            ] + $progress($r))
            ->all();

        return [
            'total' => $total,
            'selesai' => $selesai,
            'belum' => $total - $selesai,
            'persen' => $total > 0 ? round($selesai / $total * 100, 1) : 0,
            'hasil' => $hasil,
            'byJenis' => $byJenis,
            'byKategori' => $byKategori,
            'topKbli' => $topKbli,
        ];
    }

    // =================================================================
    // EXPORT
    // =================================================================

    public function exportMikro()
    {
        $rows = $this->mikroQuery()->orderBy('jenis')->orderBy('no')->get();

        $mapped = $rows->map(function (FlagRasio3Mikro $m) {
            $row = [
                $m->jenis,
                FlagRasio3Mikro::jenisOptions()[$m->jenis]['kode'] ?? '',
                $m->no,
                $m->assignment_id,
                $m->kdkab,
                $m->kategori,
                $m->kode_kbli,
                $m->kbli_label,
                $m->nama_usaha,
                $m->tenaga_kerja,
            ];
            foreach (array_keys(FlagRasio3Mikro::KOLOM_RUPIAH) as $f) {
                $row[] = $m->{$f};
            }

            return array_merge($row, [
                $m->rasio_biaya_pembelian,
                $m->reklasifikasi_skala_usaha,
                $m->rasio_ntb,
                $m->produktivitas,
                $m->tindak_lanjut === 'sudah' ? 'Sudah' : 'Belum',
                $m->tindak_lanjut === 'sudah' ? $m->status_label : '',
                $m->catatan ?? '',
                $m->tindak_lanjut_by ?? '',
                $m->tindak_lanjut_at?->format('Y-m-d H:i') ?? '',
                $m->fasih_link ?? '',
            ]);
        });

        return SimpleExcelExporter::export(
            'anomali-flag-rasio-3-' . $this->batch->tanggal->format('Y-m-d'),
            array_merge(
                ['Jenis', 'Kode Anomali', 'No', 'Assignment ID', 'Kode Kab', 'Kategori', 'Kode KBLI', 'Judul KBLI', 'Nama Usaha', 'Tenaga Kerja'],
                array_values(FlagRasio3Mikro::KOLOM_RUPIAH),
                [
                    'Rasio Biaya Pembelian thd Omzet', 'Reklasifikasi Skala Usaha', 'Rasio NTB', 'Produktivitas',
                    'Tindak Lanjut', 'Hasil Pemeriksaan', 'Catatan', 'Ditandai Oleh', 'Waktu Tindak Lanjut', 'Link Edit FASIH',
                ]
            ),
            $mapped->all()
        );
    }

    // =================================================================
    // RENDER
    // =================================================================

    public function render()
    {
        $data = [
            'statusOptions' => FlagRasio3Mikro::statusOptions(),
            'jenisOptions' => FlagRasio3Mikro::jenisOptions(),
        ];

        if ($this->view === 'mikro') {
            $data['mikros'] = $this->mikroQuery()->orderBy('jenis')->orderBy('no')->paginate($this->perPage);
            $data['kategoriOptions'] = $this->kategoriOptions();
            $data['kbliOptions'] = $this->kbliOptions();
        } else {
            $data['dash'] = $this->dashboardData();
        }

        return view('livewire.flag-rasio3.flag-rasio3-detail', $data);
    }
}
