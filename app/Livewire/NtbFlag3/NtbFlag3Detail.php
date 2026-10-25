<?php

namespace App\Livewire\NtbFlag3;

use App\Models\NtbFlag3Batch;
use App\Models\NtbFlag3Mikro as M;
use App\Services\SimpleExcelExporter;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class NtbFlag3Detail extends Component
{
    use WithPagination;

    public NtbFlag3Batch $batch;
    public string $view = 'dashboard'; // dashboard | mikro

    public string $filterKecamatan = '';
    public string $filterDesa = '';
    public string $filterPic = '';
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

    public function mount(NtbFlag3Batch $batch)
    {
        $this->batch = $batch;
    }

    public function updating($name)
    {
        if (str_starts_with($name, 'filter') || in_array($name, ['search', 'perPage'], true)) {
            $this->resetPage();
        }
    }

    public function updatedFilterKecamatan() { $this->filterDesa = ''; }

    public function updatedFilterStatus()
    {
        if ($this->filterStatus === 'belum') $this->filterHasil = '';
    }

    public function lihatDataMikro() { $this->view = 'mikro'; }
    public function kembaliKeDashboard() { $this->view = 'dashboard'; }

    /** Pintasan dari dashboard (klik baris kecamatan / PIC) */
    public function lihat(string $filter, string $nilai)
    {
        if (!in_array($filter, ['filterKecamatan', 'filterPic'], true)) return;

        $this->resetFilter();
        $this->{$filter} = $nilai;
        $this->view = 'mikro';
    }

    public function resetFilter()
    {
        $this->reset(['filterKecamatan', 'filterDesa', 'filterPic', 'filterStatus', 'filterHasil', 'search']);
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
        $this->modalInfo = 'Rasio NTB ' . M::rasio($m->rasio_ntb) . ' · NTB ' . M::rupiah($m->nilai_tambah)
            . ($m->pic ? ' · PIC: ' . $m->pic : '');
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
            'selectedStatus' => 'required|in:' . implode(',', array_keys(M::statusOptions())),
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

        $label = M::statusOptions()[$this->selectedStatus];
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
        return M::where('ntb_flag3_batch_id', $this->batch->id);
    }

    protected function mikroQuery()
    {
        $q = $this->baseQuery();

        if ($this->filterKecamatan) $q->where('nmkec', $this->filterKecamatan);
        if ($this->filterDesa) $q->where('nmdesa', $this->filterDesa);
        if ($this->filterPic) $q->where('pic', $this->filterPic);
        if ($this->filterStatus) $q->where('tindak_lanjut', $this->filterStatus);
        if ($this->filterHasil) $q->where('status_penyelesaian', $this->filterHasil);

        if ($this->search !== '') {
            $s = $this->search;
            $q->where(function ($qq) use ($s) {
                foreach (['nama_usaha', 'assignment_id', 'pic', 'catatan'] as $col) {
                    $qq->orWhere($col, 'like', "%{$s}%");
                }
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
        $group = fn (string $expr) => $this->baseQuery()
            ->select(DB::raw("$expr as nama"), DB::raw('COUNT(*) as total'), DB::raw("$sudah as selesai"))
            ->groupBy('nama')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($r) => [
                'nama' => $r->nama,
                'total' => (int) $r->total,
                'selesai' => (int) $r->selesai,
                'persen' => $r->total > 0 ? round($r->selesai / $r->total * 100, 1) : 0,
            ])
            ->all();

        $total = $this->baseQuery()->count();
        $selesai = $this->baseQuery()->where('tindak_lanjut', 'sudah')->count();

        $hasilCount = $this->baseQuery()->whereNotNull('status_penyelesaian')
            ->select('status_penyelesaian', DB::raw('COUNT(*) as jml'))
            ->groupBy('status_penyelesaian')
            ->pluck('jml', 'status_penyelesaian');
        $hasil = [];
        foreach (M::statusOptions() as $k => $label) {
            $hasil[$k] = ['label' => $label, 'count' => (int) ($hasilCount[$k] ?? 0)];
        }

        return [
            'total' => $total,
            'selesai' => $selesai,
            'belum' => $total - $selesai,
            'persen' => $total > 0 ? round($selesai / $total * 100, 1) : 0,
            'hasil' => $hasil,
            'byKecamatan' => $group("COALESCE(NULLIF(nmkec, ''), '(Tidak diketahui)')"),
            'byPic' => $group("COALESCE(NULLIF(pic, ''), '(Tanpa PIC)')"),
            'podium' => $this->podium(),
        ];
    }

    /**
     * Peringkat PIC untuk podium (maks. 5: 3 di podium + 2 penantang).
     *   1. persentase selesai tertinggi
     *   2. jika sama: paling CEPAT -> selisih waktu batch diunggah s.d. penyelesaian terakhirnya
     *   3. jika masih sama: jumlah selesai terbanyak
     * Hanya PIC yang sudah menyelesaikan minimal 1 usaha.
     */
    protected function podium(int $limit = 5): array
    {
        $mulai = $this->batch->created_at;

        return $this->baseQuery()
            ->whereNotNull('pic')->where('pic', '!=', '')
            ->select(
                'pic',
                DB::raw('COUNT(*) as total'),
                DB::raw("SUM(CASE WHEN tindak_lanjut = 'sudah' THEN 1 ELSE 0 END) as selesai"),
                DB::raw("MAX(CASE WHEN tindak_lanjut = 'sudah' THEN tindak_lanjut_at END) as terakhir")
            )
            ->groupBy('pic')
            ->get()
            ->filter(fn ($r) => (int) $r->selesai > 0)
            ->map(function ($r) use ($mulai) {
                $terakhir = $r->terakhir ? \Illuminate\Support\Carbon::parse($r->terakhir) : null;
                $detik = ($terakhir && $mulai) ? max(0, $terakhir->getTimestamp() - $mulai->getTimestamp()) : PHP_INT_MAX;
                $tuntas = (int) $r->selesai >= (int) $r->total;

                return [
                    'nama' => $r->pic,
                    'total' => (int) $r->total,
                    'selesai' => (int) $r->selesai,
                    'persen' => round($r->selesai / $r->total * 100, 1),
                    'tuntas' => $tuntas,
                    'detik' => $detik,
                    'durasi' => $detik === PHP_INT_MAX ? null : $this->durasi($detik),
                    'terakhir' => $terakhir?->diffForHumans(),
                ];
            })
            ->sort(fn ($a, $b) => [$b['persen'], $a['detik'], $b['selesai']] <=> [$a['persen'], $b['detik'], $a['selesai']])
            ->take($limit)
            ->values()
            ->all();
    }

    /** 93784 detik -> "1 hari 2 jam" */
    protected function durasi(int $detik): string
    {
        if ($detik < 60) return 'kurang dari 1 menit';

        $bagian = [];
        foreach (['hari' => 86400, 'jam' => 3600, 'menit' => 60] as $label => $n) {
            if ($detik >= $n) {
                $bagian[] = intdiv($detik, $n) . ' ' . $label;
                $detik %= $n;
            }
            if (count($bagian) === 2) break;
        }

        return implode(' ', $bagian);
    }

    // =================================================================
    // EXPORT (semua baris sesuai filter aktif)
    // =================================================================

    public function exportMikro()
    {
        $rows = $this->mikroQuery()->orderBy('nmkec')->orderBy('nmdesa')->orderBy('no')->get();

        $mapped = $rows->map(function (M $m) {
            $row = [
                $m->no, $m->assignment_id, $m->survey_period_id,
                $m->kdprov, $m->nmprov, $m->kdkab, $m->nmkab,
                $m->kdkec, $m->nmkec_display, $m->kddesa, $m->nmdesa_display,
                $m->nama_usaha,
            ];
            foreach (array_keys(M::KOLOM_RUPIAH) as $f) {
                $row[] = $m->{$f};
            }

            return array_merge($row, [
                $m->rasio_ntb,
                $m->pic,
                $m->tindak_lanjut === 'sudah' ? 'Sudah' : 'Belum',
                $m->tindak_lanjut === 'sudah' ? $m->status_label : '',
                $m->catatan ?? '',
                $m->tindak_lanjut_by ?? '',
                $m->tindak_lanjut_at?->format('Y-m-d H:i') ?? '',
                $m->fasih_link ?? '',
            ]);
        });

        return SimpleExcelExporter::export(
            'anomali-ntb-flag-3-' . $this->batch->tanggal->format('Y-m-d'),
            array_merge(
                ['No', 'Assignment ID', 'Survey Period ID', 'Kode Prov', 'Provinsi', 'Kode Kab', 'Kab/Kota', 'Kode Kec', 'Kecamatan', 'Kode Desa', 'Desa/Kel', 'Nama Usaha'],
                array_values(M::KOLOM_RUPIAH),
                ['Rasio NTB', 'PIC', 'Tindak Lanjut', 'Hasil Pemeriksaan', 'Catatan', 'Ditandai Oleh', 'Waktu Tindak Lanjut', 'Link Edit FASIH']
            ),
            $mapped->all()
        );
    }

    // =================================================================
    // RENDER
    // =================================================================

    public function render()
    {
        $data = ['statusOptions' => M::statusOptions()];

        if ($this->view === 'mikro') {
            $data['mikros'] = $this->mikroQuery()->orderBy('nmkec')->orderBy('nmdesa')->orderBy('no')->paginate($this->perPage);
            $data['kecamatanOptions'] = $this->distinctOptions('nmkec');
            $data['desaOptions'] = $this->distinctOptions('nmdesa', ['nmkec' => $this->filterKecamatan]);
            $data['picOptions'] = $this->distinctOptions('pic');
        } else {
            $data['dash'] = $this->dashboardData();
        }

        return view('livewire.ntb-flag3.ntb-flag3-detail', $data);
    }
}
