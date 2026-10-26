<?php

namespace App\Livewire\Sosek;

use App\Models\SosekBatch;
use App\Models\SosekMikro as M;
use App\Services\SosekUploadService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

#[Layout('components.layouts.app')]
class SosekDetail extends Component
{
    use WithPagination;

    public const TANPA_PIC = '__tanpa__';

    public SosekBatch $batch;
    public string $view = 'dashboard'; // dashboard | mikro

    public string $filterJenis = '';
    public string $filterKecamatan = '';
    public string $filterDesa = '';
    public string $filterPic = '';      // nama PIC | TANPA_PIC
    public string $filterStatus = '';   // belum | sudah
    public string $filterHasil = '';    // sesuai_lapangan | diperbaiki
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

    public function mount(SosekBatch $batch)
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

    /** Pintasan dari dashboard: buka data mikro dengan satu filter */
    public function lihat(string $filter, string $nilai)
    {
        if (!in_array($filter, ['filterJenis', 'filterPic', 'filterKecamatan'], true)) return;

        $this->resetFilter();
        $this->{$filter} = $nilai;
        $this->view = 'mikro';
    }

    public function resetFilter()
    {
        $this->reset(['filterJenis', 'filterKecamatan', 'filterDesa', 'filterPic', 'filterStatus', 'filterHasil', 'search']);
        $this->resetPage();
    }

    /** Admin: hitung ulang wilayah & PIC (mis. setelah ada upload data harian baru). */
    public function perbaruiPic(SosekUploadService $service)
    {
        abort_unless(session('role') === 'admin', 403);

        $r = $service->lengkapiWilayahDanPic($this->batch, $this->batch->tanggal->format('Y-m-d'));
        session()->flash('success', 'PIC diperbarui: ' . number_format($r['ada_pic']) . ' dari ' . number_format($r['total']) . ' baris punya PIC.');
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
        $this->modalInfo = $m->jenis_info['no'] . '. ' . $m->jenis_info['label'] . ' · ' . $m->nilai_anomali
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
        return M::where('sosek_batch_id', $this->batch->id);
    }

    protected function mikroQuery()
    {
        $q = $this->baseQuery();

        if ($this->filterJenis) $q->where('jenis', $this->filterJenis);
        if ($this->filterKecamatan) $q->where('nmkec', $this->filterKecamatan);
        if ($this->filterDesa) $q->where('nmdesa', $this->filterDesa);
        if ($this->filterPic === self::TANPA_PIC) {
            $q->whereNull('pic');
        } elseif ($this->filterPic) {
            $q->where('pic', $this->filterPic);
        }
        if ($this->filterStatus) $q->where('tindak_lanjut', $this->filterStatus);
        if ($this->filterHasil) $q->where('status_penyelesaian', $this->filterHasil);

        if ($this->search !== '') {
            $s = $this->search;
            $q->where(function ($qq) use ($s) {
                foreach (['nama_art', 'nama_kk', 'nik', 'no_kk', 'assignment_id', 'nilai_anomali', 'pic', 'catatan'] as $col) {
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

    /** nama => "NAMA (jumlah)" diawali "Tanpa PIC" bila ada */
    protected function picOptions(): array
    {
        $opts = [];
        $tanpa = $this->baseQuery()->whereNull('pic')->count();
        if ($tanpa) $opts[self::TANPA_PIC] = 'Tanpa PIC (' . number_format($tanpa) . ')';

        $this->baseQuery()->whereNotNull('pic')
            ->select('pic', DB::raw('COUNT(*) as jml'))->groupBy('pic')->orderBy('pic')->get()
            ->each(function ($r) use (&$opts) { $opts[$r->pic] = $r->pic . ' (' . number_format($r->jml) . ')'; });

        return $opts;
    }

    // =================================================================
    // DASHBOARD
    // =================================================================

    protected function dashboardData(): array
    {
        $sudah = "SUM(CASE WHEN tindak_lanjut = 'sudah' THEN 1 ELSE 0 END)";
        $prog = fn ($total, $selesai) => [
            'total' => (int) $total,
            'selesai' => (int) $selesai,
            'persen' => $total > 0 ? round($selesai / $total * 100, 1) : 0,
        ];
        $group = fn (string $expr) => $this->baseQuery()
            ->select(DB::raw("$expr as nama"), DB::raw('COUNT(*) as total'), DB::raw("$sudah as selesai"))
            ->groupBy('nama')->orderByDesc('total')->get()
            ->map(fn ($r) => ['nama' => $r->nama] + $prog($r->total, $r->selesai))
            ->all();

        $total = $this->baseQuery()->count();
        $selesai = $this->baseQuery()->where('tindak_lanjut', 'sudah')->count();

        $hasilCount = $this->baseQuery()->whereNotNull('status_penyelesaian')
            ->select('status_penyelesaian', DB::raw('COUNT(*) as jml'))->groupBy('status_penyelesaian')
            ->pluck('jml', 'status_penyelesaian');
        $hasil = [];
        foreach (M::statusOptions() as $k => $label) {
            $hasil[$k] = ['label' => $label, 'count' => (int) ($hasilCount[$k] ?? 0)];
        }

        $perJenis = $this->baseQuery()
            ->select('jenis', DB::raw('COUNT(*) as total'), DB::raw("$sudah as selesai"))
            ->groupBy('jenis')->get()->keyBy('jenis');
        $byJenis = [];
        foreach (M::jenisOptions() as $j => $info) {
            $r = $perJenis[$j] ?? null;
            $byJenis[$j] = $info + $prog($r->total ?? 0, $r->selesai ?? 0);
        }

        return [
            'total' => $total,
            'selesai' => $selesai,
            'belum' => $total - $selesai,
            'persen' => $total > 0 ? round($selesai / $total * 100, 1) : 0,
            'hasil' => $hasil,
            'byJenis' => $byJenis,
            'byPic' => $group("COALESCE(pic, '(Tanpa PIC)')"),
            'byKecamatan' => $group("COALESCE(NULLIF(nmkec, ''), '(Tidak diketahui)')"),
            'podium' => $this->podium(),
        ];
    }

    /**
     * Peringkat PIC (maks. 5: 3 podium + 2 penantang):
     *   1. persentase selesai tertinggi
     *   2. jika sama: paling cepat (batch diunggah -> penyelesaian terakhir)
     *   3. jika masih sama: jumlah selesai terbanyak
     */
    protected function podium(int $limit = 5): array
    {
        $mulai = $this->batch->created_at;

        return $this->baseQuery()
            ->whereNotNull('pic')
            ->select(
                'pic',
                DB::raw('COUNT(*) as total'),
                DB::raw("SUM(CASE WHEN tindak_lanjut = 'sudah' THEN 1 ELSE 0 END) as selesai"),
                DB::raw("MAX(CASE WHEN tindak_lanjut = 'sudah' THEN tindak_lanjut_at END) as terakhir")
            )
            ->groupBy('pic')->get()
            ->filter(fn ($r) => (int) $r->selesai > 0)
            ->map(function ($r) use ($mulai) {
                $terakhir = $r->terakhir ? Carbon::parse($r->terakhir) : null;
                $detik = ($terakhir && $mulai) ? max(0, $terakhir->getTimestamp() - $mulai->getTimestamp()) : PHP_INT_MAX;

                return [
                    'nama' => $r->pic,
                    'total' => (int) $r->total,
                    'selesai' => (int) $r->selesai,
                    'persen' => round($r->selesai / $r->total * 100, 1),
                    'tuntas' => (int) $r->selesai >= (int) $r->total,
                    'detik' => $detik,
                    'durasi' => $detik === PHP_INT_MAX ? null : $this->durasi($detik),
                ];
            })
            ->sort(fn ($a, $b) => [$b['persen'], $a['detik'], $b['selesai']] <=> [$a['persen'], $b['detik'], $a['selesai']])
            ->take($limit)->values()->all();
    }

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
    // EXPORT — semua baris sesuai filter aktif; NIK/KK ditulis sebagai teks
    // =================================================================

    public function exportMikro()
    {
        $rows = $this->mikroQuery()->orderBy('jenis')->orderBy('nmkec')->orderBy('nmdesa')->orderBy('no')->get();
        $jenisOpt = M::jenisOptions();

        $headers = [
            'No', 'Jenis Anomali', 'Keterangan Anomali', 'Assignment ID', 'Kecamatan', 'Desa/Kel', 'Nama SLS', 'ID SubSLS (Level 6)',
            'Nama KK', 'No KK', 'Nama ART', 'NIK', 'Umur', 'Keberadaan', 'Profesi', 'Tanggal Lahir',
            'Daya Terpasang', 'Jumlah Meteran', 'Jumlah AC', 'Daftar Remark', 'Running Time',
            'PIC (PML Organik)', 'Tindak Lanjut', 'Hasil Pemeriksaan', 'Catatan', 'Ditandai Oleh', 'Waktu Tindak Lanjut', 'Link Edit FASIH',
        ];
        $teks = ['Assignment ID', 'ID SubSLS (Level 6)', 'No KK', 'NIK'];
        $teksIdx = array_keys(array_intersect($headers, $teks));

        $sheet = ($spreadsheet = new Spreadsheet())->getActiveSheet();
        $sheet->setTitle('Anomali Sosek');
        $sheet->fromArray($headers, null, 'A1');
        $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->getFont()->setBold(true);

        $r = 2;
        foreach ($rows as $i => $m) {
            $j = $jenisOpt[$m->jenis] ?? null;
            $vals = [
                $i + 1, $j ? $j['no'] . '. ' . $j['label'] : $m->jenis, $m->nilai_anomali, $m->assignment_id,
                $m->nmkec_display, $m->nmdesa_display, $m->nama_sls, $m->region_code,
                $m->nama_kk, $m->no_kk, $m->nama_art, $m->nik, $m->umur, $m->keberadaan, $m->profesi, $m->tgl_lahir,
                $m->daya_terpasang, $m->jml_meteran, $m->jumlah_ac, $m->daftar_remark, $m->running_time,
                $m->pic ?? 'Tanpa PIC',
                $m->tindak_lanjut === 'sudah' ? 'Sudah' : 'Belum',
                $m->tindak_lanjut === 'sudah' ? $m->status_label : '',
                $m->catatan, $m->tindak_lanjut_by, $m->tindak_lanjut_at?->format('Y-m-d H:i'), $m->fasih_link,
            ];
            foreach ($vals as $c => $v) {
                if ($v === null || $v === '') continue;
                $coord = Coordinate::stringFromColumnIndex($c + 1) . $r;
                in_array($c, $teksIdx, true)
                    ? $sheet->setCellValueExplicit($coord, (string) $v, DataType::TYPE_STRING)
                    : $sheet->setCellValue($coord, $v);
            }
            $r++;
        }

        foreach (range(1, count($headers)) as $c) {
            $sheet->getColumnDimensionByColumn($c)->setAutoSize(true);
        }
        $sheet->freezePane('A2');
        $sheet->setAutoFilter($sheet->calculateWorksheetDimension());

        $nama = 'anomali-sosek-' . $this->batch->tanggal->format('Y-m-d') . ($this->filterJenis ? '-' . $this->filterJenis : '') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
        }, $nama, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    // =================================================================
    // RENDER
    // =================================================================

    public function render()
    {
        $data = [
            'statusOptions' => M::statusOptions(),
            'jenisOptions' => M::jenisOptions(),
        ];

        if ($this->view === 'mikro') {
            $data['mikros'] = $this->mikroQuery()->orderBy('jenis')->orderBy('nmkec')->orderBy('nmdesa')->orderBy('no')->paginate($this->perPage);
            $data['kecamatanOptions'] = $this->distinctOptions('nmkec');
            $data['desaOptions'] = $this->distinctOptions('nmdesa', ['nmkec' => $this->filterKecamatan]);
            $data['picOptions'] = $this->picOptions();
        } else {
            $data['dash'] = $this->dashboardData();
        }

        return view('livewire.sosek.sosek-detail', $data);
    }
}
