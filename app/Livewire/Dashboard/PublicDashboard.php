<?php

namespace App\Livewire\Dashboard;

use App\Models\DailyUpload;
use App\Models\SlsDaily;
use App\Services\SimpleExcelExporter;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.public')]
class PublicDashboard extends Component
{
    use WithPagination;

    public string $tab = 'utama';

    // filters shared across tabs
    public string $filterKecamatan = '';
    public string $search = '';
    public int $perPage = 10;
    public string $sortField = 'progress';
    public string $sortDir = 'desc';

    // produktivitas modal
    public ?string $modalPpl = null;

    protected $queryString = ['tab'];

    public function updatingSearch() { $this->resetPage(); }
    public function updatingFilterKecamatan() { $this->resetPage(); }

    public function setTab(string $tab)
    {
        $this->tab = $tab;
        $this->resetPage();
    }

    public function sortBy(string $field)
    {
        if ($this->sortField === $field) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDir = 'desc';
        }
    }

    protected function latestUpload(): ?DailyUpload
    {
        return DailyUpload::orderByDesc('tanggal')->first();
    }

    protected function latestRows()
    {
        $latest = $this->latestUpload();
        if (!$latest) return collect();
        return SlsDaily::where('daily_upload_id', $latest->id)->get();
    }

    protected function kecamatanList()
    {
        return $this->latestRows()->pluck('nmkec')->filter()->unique()->sort()->values();
    }

    // ---------------------------------------------------------------
    // TAB: DASHBOARD UTAMA
    // ---------------------------------------------------------------
    protected function dataUtama(): array
    {
        $rows = $this->latestRows();
        $totalRegion = $rows->sum('total_region');
        $selesai = $rows->sum(fn ($r) => $r->selesai);
        $progresTotal = $totalRegion > 0 ? round($selesai / $totalRegion * 100, 1) : 0;

        // PPL lolos termin 2: progress per PPL == 100%
        $perPpl = $rows->groupBy('username')->map(function ($g) {
            $total = $g->sum('total_region');
            $sel = $g->sum(fn ($r) => $r->selesai);
            return $total > 0 ? round($sel / $total * 100, 2) : 0;
        });
        $lolosTermin2 = $perPpl->filter(fn ($p) => $p >= 100)->count();

        $selesaiPml = $rows->sum(fn ($r) => $r->selesai_pml);
        $realisasiPml = $totalRegion > 0 ? round($selesaiPml / $totalRegion * 100, 1) : 0;

        $jumlahOpenDraft = $rows->sum('open') + $rows->sum('draft');

        $perKecamatan = $rows->groupBy('nmkec')->map(function ($g, $kec) {
            $total = $g->sum('total_region');
            $sel = $g->sum(fn ($r) => $r->selesai);
            return [
                'kecamatan' => $kec,
                'total' => $total,
                'selesai' => $sel,
                'progres' => $total > 0 ? round($sel / $total * 100, 1) : 0,
            ];
        })->sortByDesc('progres')->values();

        $statusFields = [
            'approved_pengawas' => 'Approved by Pengawas',
            'open' => 'Open',
            'draft' => 'Draft',
            'submitted_pencacah' => 'Submitted by Pencacah',
            'rejected_pengawas' => 'Rejected by Pengawas',
            'edited_admin_kab' => 'Edited by Admin Kabupaten',
            'revoked_pengawas' => 'Revoked by Pengawas',
            'submitted_respondent' => 'Submitted Respondent',
            'rejected_admin_kab' => 'Rejected by Admin Kabupaten',
            'completed_admin_kab' => 'Completed by Admin Kabupaten',
            'edited_pengawas' => 'Edited by Pengawas',
        ];
        $komposisi = [];
        foreach ($statusFields as $field => $label) {
            $komposisi[] = ['label' => $label, 'value' => $rows->sum($field)];
        }

        return compact('totalRegion', 'selesai', 'progresTotal', 'lolosTermin2', 'realisasiPml', 'jumlahOpenDraft', 'perKecamatan', 'komposisi');
    }

    // ---------------------------------------------------------------
    // TAB: KINERJA PPL
    // ---------------------------------------------------------------
    protected function dataKinerjaPpl()
    {
        $rows = $this->latestRows();

        if ($this->filterKecamatan) {
            $rows = $rows->where('nmkec', $this->filterKecamatan);
        }

        $grouped = $rows->groupBy('username')->map(function ($g, $email) {
            $total = $g->sum('total_region');
            $sel = $g->sum(fn ($r) => $r->selesai);
            $tidakDitemukanPct = $g->map(function ($r) {
                $unit = $r->kk_prelist_awal + $r->usaha_prelist_awal;
                $td = $r->kk_tidak_ditemukan + $r->usaha_tidak_ditemukan;
                return $unit > 0 ? ($td / $unit * 100) : 0;
            })->avg();

            return [
                'email' => $email,
                'nama' => $g->first()->nama_ppl ?: $email,
                'pml' => $g->first()->nama_pml,
                'progres' => $total > 0 ? round($sel / $total * 100, 1) : 0,
                'tidak_ditemukan' => round($tidakDitemukanPct ?? 0, 1),
                'muatan' => $g->sum('muatan_total'),
                'kecamatan' => $g->pluck('nmkec')->filter()->unique()->values()->all(),
            ];
        })->filter(fn ($r) => $r['email']);

        if ($this->search) {
            $s = mb_strtolower($this->search);
            $grouped = $grouped->filter(fn ($r) => str_contains(mb_strtolower($r['nama']), $s) || str_contains(mb_strtolower($r['email']), $s));
        }

        return $this->sortAndPaginate($grouped);
    }

    // ---------------------------------------------------------------
    // TAB: KINERJA PML
    // ---------------------------------------------------------------
    protected function dataKinerjaPml()
    {
        $rows = $this->latestRows();
        if ($this->filterKecamatan) {
            $rows = $rows->where('nmkec', $this->filterKecamatan);
        }

        $grouped = $rows->groupBy('nama_pml')->map(function ($g, $nama) {
            $total = $g->sum('total_region');
            $sel = $g->sum(fn ($r) => $r->selesai_pml);
            return [
                'nama' => $nama ?: '(Tidak diketahui)',
                'jumlah_ppl' => $g->pluck('username')->filter()->unique()->count(),
                'progres' => $total > 0 ? round($sel / $total * 100, 1) : 0,
                'muatan' => $g->sum('muatan_total'),
                'kecamatan' => $g->pluck('nmkec')->filter()->unique()->values()->all(),
            ];
        })->filter(fn ($r) => $r['nama'] !== '');

        if ($this->search) {
            $s = mb_strtolower($this->search);
            $grouped = $grouped->filter(fn ($r) => str_contains(mb_strtolower($r['nama']), $s));
        }

        return $this->sortAndPaginate($grouped, 'nama');
    }

    // ---------------------------------------------------------------
    // TAB: DETAIL SLS / BLOK SENSUS
    // ---------------------------------------------------------------
    protected function dataDetailSls()
    {
        $rows = $this->latestRows();
        if ($this->filterKecamatan) {
            $rows = $rows->where('nmkec', $this->filterKecamatan);
        }

        $mapped = $rows->map(fn ($r) => [
            'region_code' => $r->region_code,
            'nama_sls' => $r->nama_sls,
            'kecamatan' => $r->nmkec,
            'desa' => $r->nmdes,
            'ppl' => $r->nama_ppl,
            'pml' => $r->nama_pml,
            'total' => $r->total_region,
            'progres' => $r->total_region > 0 ? round($r->selesai / $r->total_region * 100, 1) : 0,
        ]);

        if ($this->search) {
            $s = mb_strtolower($this->search);
            $mapped = $mapped->filter(fn ($r) => str_contains(mb_strtolower($r['nama_sls'] ?? ''), $s)
                || str_contains(mb_strtolower($r['ppl'] ?? ''), $s)
                || str_contains((string) $r['region_code'], $s));
        }

        return $this->sortAndPaginate($mapped, 'progres');
    }

    // ---------------------------------------------------------------
    // TAB: TIDAK DITEMUKAN
    // ---------------------------------------------------------------
    protected function dataTidakDitemukan(): array
    {
        $rows = $this->latestRows();

        $perKecamatan = $rows->groupBy('nmkec')->map(function ($g, $kec) {
            return [
                'kecamatan' => $kec,
                'keluarga_td' => $g->sum('kk_tidak_ditemukan'),
                'usaha_td' => $g->sum('usaha_tidak_ditemukan'),
                'ukdk_td' => $g->sum('ukdk_tidak_ditemukan'),
            ];
        })->sortByDesc(fn ($r) => $r['keluarga_td'] + $r['usaha_td'] + $r['ukdk_td'])->values();

        return [
            'perKecamatan' => $perKecamatan,
            'totalKeluargaTd' => $rows->sum('kk_tidak_ditemukan'),
            'totalUsahaTd' => $rows->sum('usaha_tidak_ditemukan'),
            'totalUkdkTd' => $rows->sum('ukdk_tidak_ditemukan'),
        ];
    }

    // ---------------------------------------------------------------
    // TAB: GABUNGAN
    // ---------------------------------------------------------------
    protected function dataGabungan(): array
    {
        $rows = $this->latestRows();

        $keluargaTotal = $rows->sum(fn ($r) => $r->kk_ditemukan + $r->kk_baru + $r->kk_meninggal + $r->kk_tidak_eligible + $r->kk_tidak_dapat_ditemui + $r->kk_tidak_ditemukan);
        $keluargaTdBaru = $rows->sum(fn ($r) => $r->kk_meninggal + $r->kk_tidak_eligible + $r->kk_tidak_dapat_ditemui + $r->kk_tidak_ditemukan);

        $usahaTotal = $rows->sum(fn ($r) => $r->usaha_ditemukan + $r->usaha_tutup + $r->usaha_ganda + $r->usaha_tidak_ditemukan + $r->usaha_baru);
        $usahaTdBaru = $rows->sum(fn ($r) => $r->usaha_tutup + $r->usaha_ganda + $r->usaha_tidak_ditemukan);

        $ukdkTotal = $rows->sum(fn ($r) => $r->ukdk_ditemukan + $r->ukdk_tutup + $r->ukdk_ganda + $r->ukdk_tidak_ditemukan + $r->ukdk_baru);
        $ukdkTdBaru = $rows->sum(fn ($r) => $r->ukdk_tutup + $r->ukdk_ganda + $r->ukdk_tidak_ditemukan);

        $totalUniverse = $keluargaTotal + $usahaTotal + $ukdkTotal;
        $totalTidakDitemukan = $keluargaTdBaru + $usahaTdBaru + $ukdkTdBaru;

        $persenTidakDitemukan = $totalUniverse > 0 ? round($totalTidakDitemukan / $totalUniverse * 100, 1) : 0;
        $muatan = $rows->sum('muatan_total');

        return compact('persenTidakDitemukan', 'muatan', 'keluargaTotal', 'usahaTotal', 'ukdkTotal', 'totalTidakDitemukan');
    }

    // ---------------------------------------------------------------
    // TAB: PRODUKTIVITAS HARIAN
    // ---------------------------------------------------------------
    protected function dataProduktivitas(): array
    {
        $uploads = DailyUpload::orderBy('tanggal')->get();
        if ($uploads->count() < 2) {
            return ['tanggalList' => [], 'data' => collect()];
        }

        // selesai per PPL per tanggal
        $selesaiPerTanggal = []; // [tanggal => [email => selesai]]
        $namaPpl = [];
        foreach ($uploads as $u) {
            $rows = SlsDaily::where('daily_upload_id', $u->id)->get();
            $byPpl = $rows->groupBy('username');
            foreach ($byPpl as $email => $g) {
                if (!$email) continue;
                $selesaiPerTanggal[$u->tanggal->format('Y-m-d')][$email] = $g->sum(fn ($r) => $r->selesai);
                $namaPpl[$email] = $g->first()->nama_ppl ?: $email;
            }
        }

        $tanggalUrut = $uploads->pluck('tanggal')->map(fn ($t) => $t->format('Y-m-d'))->values()->all();
        $tanggalKolom = array_slice($tanggalUrut, 1); // kolom = tanggal ke-2 dst (hasil selisih terhadap hari sebelumnya)

        $data = collect();
        foreach ($namaPpl as $email => $nama) {
            $row = ['email' => $email, 'nama' => $nama, 'harian' => [], 'riwayat' => []];
            foreach ($tanggalUrut as $t) {
                $row['riwayat'][$t] = $selesaiPerTanggal[$t][$email] ?? null;
            }
            foreach ($tanggalKolom as $i => $t) {
                $prevT = $tanggalUrut[$i]; // index i in tanggalUrut is previous date (since tanggalKolom starts at index1)
                $today = $selesaiPerTanggal[$t][$email] ?? null;
                $prev = $selesaiPerTanggal[$prevT][$email] ?? null;
                $row['harian'][$t] = ($today !== null && $prev !== null) ? ($today - $prev) : null;
            }
            $data->push($row);
        }

        if ($this->search) {
            $s = mb_strtolower($this->search);
            $data = $data->filter(fn ($r) => str_contains(mb_strtolower($r['nama']), $s));
        }

        return ['tanggalList' => $tanggalKolom, 'data' => $data->sortBy('nama')->values()];
    }

    protected function sortAndPaginate($collection, string $defaultField = 'progres')
    {
        $field = $this->sortField === 'progress' ? $defaultField : $this->sortField;
        $sorted = $this->sortDir === 'asc'
            ? $collection->sortBy($field)
            : $collection->sortByDesc($field);

        $items = $sorted->values();
        $page = $this->getPage();
        $slice = $items->slice(($page - 1) * $this->perPage, $this->perPage)->values();

        return new \Illuminate\Pagination\LengthAwarePaginator(
            $slice, $items->count(), $this->perPage, $page,
            ['path' => request()->url(), 'pageName' => 'page']
        );
    }

    public function exportDetailSls()
    {
        $rows = $this->latestRows();
        $mapped = $rows->map(fn ($r) => [
            $r->region_code, $r->nama_sls, $r->nmkec, $r->nmdes, $r->nama_ppl, $r->nama_pml,
            $r->total_region, $r->total_region > 0 ? round($r->selesai / $r->total_region * 100, 1) : 0,
        ]);

        return SimpleExcelExporter::export('detail-sls-blok-sensus',
            ['Kode Region', 'Nama SLS', 'Kecamatan', 'Desa', 'PPL', 'PML', 'Total', 'Progres (%)'],
            $mapped->all()
        );
    }

    public function exportKinerjaPpl()
    {
        $data = $this->dataKinerjaPpl()->getCollection();
        $mapped = $data->map(fn ($r) => [
            $r['nama'], $r['email'], $r['progres'], $r['tidak_ditemukan'], $r['muatan'], $r['pml'], implode(', ', $r['kecamatan']),
        ]);
        return SimpleExcelExporter::export('kinerja-ppl',
            ['Nama PPL', 'Email', 'Progres (%)', 'Rata2 Tidak Ditemukan (%)', 'Muatan', 'PML', 'Kecamatan'],
            $mapped->all()
        );
    }

    public function exportKinerjaPml()
    {
        $data = $this->dataKinerjaPml()->getCollection();
        $mapped = $data->map(fn ($r) => [
            $r['nama'], $r['jumlah_ppl'], $r['progres'], $r['muatan'], implode(', ', $r['kecamatan']),
        ]);
        return SimpleExcelExporter::export('kinerja-pml',
            ['Nama PML', 'Jumlah PPL', 'Progres (%)', 'Muatan', 'Kecamatan'],
            $mapped->all()
        );
    }

    public function exportProduktivitas()
    {
        $prod = $this->dataProduktivitas();
        $headers = array_merge(['Nama PPL'], array_map(fn ($t) => \Carbon\Carbon::parse($t)->translatedFormat('d M'), $prod['tanggalList']));
        $rows = $prod['data']->map(function ($r) use ($prod) {
            $row = [$r['nama']];
            foreach ($prod['tanggalList'] as $t) {
                $row[] = $r['harian'][$t] ?? '-';
            }
            return $row;
        });
        return SimpleExcelExporter::export('produktivitas-harian', $headers, $rows->all());
    }

    public function openPplChart(string $email)
    {
        $prod = $this->dataProduktivitas();
        $row = $prod['data']->firstWhere('email', $email);
        if (!$row) return;

        $uploads = DailyUpload::orderBy('tanggal')->pluck('tanggal')->map(fn ($t) => $t->format('Y-m-d'));
        $labels = $uploads->map(fn ($t) => \Carbon\Carbon::parse($t)->translatedFormat('d M'))->values()->all();
        $values = $uploads->map(fn ($t) => $row['riwayat'][$t] ?? null)->values()->all();

        $this->dispatch('open-ppl-chart', labels: $labels, values: $values, nama: $row['nama']);
    }

    public function render()
    {
        $latest = $this->latestUpload();

        $viewData = [
            'latest' => $latest,
            'kecamatanList' => $this->kecamatanList(),
        ];

        switch ($this->tab) {
            case 'ppl':
                $viewData['ppl'] = $this->dataKinerjaPpl();
                break;
            case 'pml':
                $viewData['pml'] = $this->dataKinerjaPml();
                break;
            case 'sls':
                $viewData['sls'] = $this->dataDetailSls();
                break;
            case 'tidak-ditemukan':
                $viewData['td'] = $this->dataTidakDitemukan();
                break;
            case 'gabungan':
                $viewData['gabungan'] = $this->dataGabungan();
                break;
            case 'produktivitas':
                $viewData['prod'] = $this->dataProduktivitas();
                break;
            default:
                $viewData['utama'] = $this->dataUtama();
        }

        return view('livewire.dashboard.public-dashboard', $viewData);
    }
}
