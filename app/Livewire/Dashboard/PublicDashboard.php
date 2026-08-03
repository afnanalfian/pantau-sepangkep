<?php

namespace App\Livewire\Dashboard;

use App\Models\DailyUpload;
use App\Models\SlsDaily;
use App\Services\SimpleExcelExporter;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.public')]
class PublicDashboard extends Component
{
    use WithPagination;

    public string $tab = 'utama';

    // filter yang berlaku untuk SEMUA tab
    public string $filterKecamatan = '';
    public string $filterDesa = '';
    public string $filterPml = '';
    public string $filterOrganik = '';
    public string $search = '';
    public int $perPage = 10;
    public string $sortField = 'progress';
    public string $sortDir = 'desc';

    // produktivitas: kolom mana saja yang ditampilkan (default: semua)
    public array $metrikAktif = ['progres', 'draft', 'muatan'];

    // produktivitas
    public ?string $modalPpl = null;

    protected $queryString = [
        'tab',
        'filterKecamatan' => ['except' => ''],
        'filterDesa' => ['except' => ''],
        'filterPml' => ['except' => ''],
        'filterOrganik' => ['except' => ''],
    ];

    /** cache per-request supaya tidak query berulang kali dalam satu render */
    protected ?Collection $rowsCache = null;

    /** daftar metrik produktivitas yang tersedia */
    public const METRIK = [
        'progres' => 'Progres',
        'draft' => 'Draft',
        'muatan' => 'Muatan',
    ];

    public function updatingSearch() { $this->resetPage(); }
    public function updatingFilterKecamatan() { $this->resetPage(); }
    public function updatingFilterDesa() { $this->resetPage(); }
    public function updatingFilterPml() { $this->resetPage(); }
    public function updatingFilterOrganik() { $this->resetPage(); }
    public function updatingPerPage() { $this->resetPage(); }

    public function updatedFilterKecamatan()
    {
        // desa yang sebelumnya dipilih belum tentu ada di kecamatan baru
        $this->filterDesa = '';
        $this->rowsCache = null;
    }

    public function updatedFilterOrganik()
    {
        // PML yang dipilih belum tentu berada di bawah organik yang baru
        $this->filterPml = '';
        $this->rowsCache = null;
    }

    public function resetFilter()
    {
        $this->filterKecamatan = '';
        $this->filterDesa = '';
        $this->filterPml = '';
        $this->filterOrganik = '';
        $this->search = '';
        $this->rowsCache = null;
        $this->resetPage();
    }

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

    // =================================================================
    // SUMBER DATA
    // =================================================================

    protected function latestUpload(): ?DailyUpload
    {
        return DailyUpload::orderByDesc('tanggal')->first();
    }

    /** Semua baris SLS pada upload terbaru (tanpa filter). */
    protected function latestRows(): Collection
    {
        if ($this->rowsCache !== null) {
            return $this->rowsCache;
        }

        $latest = $this->latestUpload();

        return $this->rowsCache = $latest
            ? SlsDaily::where('daily_upload_id', $latest->id)->get()
            : collect();
    }

    /**
     * Terapkan seluruh filter (kecamatan, desa/kelurahan, PML, PML organik).
     * Dipakai oleh SEMUA tab supaya filter konsisten.
     */
    protected function applyFilters(Collection $rows): Collection
    {
        if ($this->filterKecamatan !== '') {
            $rows = $rows->where('nmkec', $this->filterKecamatan);
        }
        if ($this->filterDesa !== '') {
            $rows = $rows->where('nmdes', $this->filterDesa);
        }
        if ($this->filterOrganik !== '') {
            $rows = $rows->where('pml_organik', $this->filterOrganik);
        }
        if ($this->filterPml !== '') {
            $rows = $rows->where('nama_pml', $this->filterPml);
        }

        return $rows->values();
    }

    /** Baris upload terbaru yang sudah difilter. */
    protected function rows(): Collection
    {
        return $this->applyFilters($this->latestRows());
    }

    // ---- daftar opsi filter (saling menyaring) ----------------------

    /**
     * Baris upload terbaru dengan seluruh filter diterapkan KECUALI yang
     * disebutkan di $abaikan. Dipakai untuk menyusun isi dropdown supaya
     * setiap filter menyaring pilihan filter lainnya (mis. memilih PML
     * membuat dropdown kecamatan hanya berisi wilayah PML tersebut).
     *
     * @param  array<int, string>  $abaikan  kecamatan|desa|organik|pml
     */
    protected function rowsKecuali(array $abaikan = []): Collection
    {
        $rows = $this->latestRows();

        if (!in_array('kecamatan', $abaikan, true) && $this->filterKecamatan !== '') {
            $rows = $rows->where('nmkec', $this->filterKecamatan);
        }
        if (!in_array('desa', $abaikan, true) && $this->filterDesa !== '') {
            $rows = $rows->where('nmdes', $this->filterDesa);
        }
        if (!in_array('organik', $abaikan, true) && $this->filterOrganik !== '') {
            $rows = $rows->where('pml_organik', $this->filterOrganik);
        }
        if (!in_array('pml', $abaikan, true) && $this->filterPml !== '') {
            $rows = $rows->where('nama_pml', $this->filterPml);
        }

        return $rows->values();
    }

    protected function kecamatanList(): Collection
    {
        return $this->rowsKecuali(['kecamatan', 'desa'])
            ->pluck('nmkec')->filter()->unique()->sort()->values();
    }

    protected function desaList(): Collection
    {
        return $this->rowsKecuali(['desa'])
            ->pluck('nmdes')->filter()->unique()->sort()->values();
    }

    /** Opsi PML organik, mengikuti filter wilayah yang sedang aktif. */
    protected function organikList(): Collection
    {
        return $this->rowsKecuali(['organik', 'pml'])
            ->pluck('pml_organik')->filter()->unique()->sort()->values();
    }

    /** Opsi PML, mengikuti filter wilayah + organik yang sedang aktif. */
    protected function pmlList(): Collection
    {
        return $this->rowsKecuali(['pml'])
            ->pluck('nama_pml')->filter()->unique()->sort()->values();
    }

    /**
     * Kalau kombinasi filter jadi tidak masuk akal (mis. kecamatan diganti
     * sehingga PML terpilih tidak lagi ada di daftar), filter yang menggantung
     * dibersihkan otomatis supaya tabel tidak tampil kosong tanpa sebab.
     */
    protected function sanitizeFilters(array $lists): void
    {
        if ($this->filterKecamatan !== '' && !$lists['kecamatan']->contains($this->filterKecamatan)) {
            $this->filterKecamatan = '';
        }
        if ($this->filterDesa !== '' && !$lists['desa']->contains($this->filterDesa)) {
            $this->filterDesa = '';
        }
        if ($this->filterOrganik !== '' && !$lists['organik']->contains($this->filterOrganik)) {
            $this->filterOrganik = '';
        }
        if ($this->filterPml !== '' && !$lists['pml']->contains($this->filterPml)) {
            $this->filterPml = '';
        }
    }

    protected function labelWilayah(): string
    {
        if ($this->filterDesa !== '') return 'SLS';
        if ($this->filterKecamatan !== '') return 'Desa/Kelurahan';

        return 'Kecamatan';
    }

    // =================================================================
    // TAB: DASHBOARD UTAMA
    // =================================================================

    protected function dataUtama(): array
    {
        $rows = $this->rows();

        $totalRegion = $rows->sum('total_region');
        $selesai = $rows->sum(fn ($r) => $r->selesai);
        $progresTotal = $totalRegion > 0 ? round($selesai / $totalRegion * 100, 1) : 0;

        // PPL lolos termin 2: progres per PPL == 100%
        $perPpl = $rows->groupBy('username')->map(function ($g) {
            $total = $g->sum('total_region');
            $sel = $g->sum(fn ($r) => $r->selesai);

            return $total > 0 ? round($sel / $total * 100, 2) : 0;
        });
        $lolosTermin2 = $perPpl->filter(fn ($p) => $p >= 100)->count();

        $selesaiPml = $rows->sum(fn ($r) => $r->selesai_pml);
        $realisasiPml = $totalRegion > 0 ? round($selesaiPml / $totalRegion * 100, 1) : 0;

        $jumlahOpenDraft = $rows->sum('open') + $rows->sum('draft');

        // Grouping grafik menyesuaikan filter yang aktif:
        // tanpa filter -> per kecamatan, filter kecamatan -> per desa, filter desa -> per SLS
        $groupKey = $this->filterDesa !== '' ? 'nama_sls' : ($this->filterKecamatan !== '' ? 'nmdes' : 'nmkec');

        $perWilayah = $rows->groupBy($groupKey)->map(function ($g, $nama) {
            $total = $g->sum('total_region');
            $sel = $g->sum(fn ($r) => $r->selesai);

            return [
                'wilayah' => $nama !== '' && $nama !== null ? $nama : '(Tidak diketahui)',
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
            $nilai = $rows->sum($field);
            if ($nilai > 0) {
                $komposisi[] = ['label' => $label, 'value' => $nilai];
            }
        }

        return [
            'totalRegion' => $totalRegion,
            'selesai' => $selesai,
            'progresTotal' => $progresTotal,
            'lolosTermin2' => $lolosTermin2,
            'realisasiPml' => $realisasiPml,
            'jumlahOpenDraft' => $jumlahOpenDraft,
            'perWilayah' => $perWilayah,
            'labelWilayah' => $this->labelWilayah(),
            'komposisi' => $komposisi,
        ];
    }

    // =================================================================
    // TAB: PETA SLS
    // =================================================================

    /**
     * Data untuk peta choropleth. Kunci = region_code 16 digit, sama dengan
     * properti `idsubsls` pada file GeoJSON SLS.
     *
     * Warna: >=90% hijau, 50-90% oranye, <50% merah, tanpa data abu-abu.
     */
    protected function dataPeta(): array
    {
        $rows = $this->rowsPeta();

        // Kamus nama petugas: nama panjang cukup dikirim sekali, tiap SLS hanya
        // menyimpan indeksnya. Payload jadi ~4x lebih ringan untuk 1.700+ SLS.
        $kamus = ['ppl' => [], 'pml' => [], 'org' => []];
        $indeks = function (string $jenis, $nilai) use (&$kamus) {
            $nilai = trim((string) $nilai) ?: '-';
            if (!isset($kamus[$jenis][$nilai])) {
                $kamus[$jenis][$nilai] = count($kamus[$jenis]);
            }

            return $kamus[$jenis][$nilai];
        };

        $data = [];
        $hijau = $oranye = $merah = 0;

        foreach ($rows->groupBy(fn ($r) => preg_replace('/\D/', '', (string) $r->region_code)) as $code => $g) {
            if ($code === '') continue;

            $total = (int) $g->sum('total_region');
            $selesai = (int) $g->sum(fn ($r) => $r->selesai);
            $progres = $total > 0 ? round($selesai / $total * 100, 1) : 0;

            if ($progres >= 90) $hijau++;
            elseif ($progres >= 50) $oranye++;
            else $merah++;

            $first = $g->first();

            // urutan: progres, total, selesai, draft, muatan, idxPPL, idxPML, idxOrganik
            $data[$code] = [
                $progres,
                $total,
                $selesai,
                (int) $g->sum('draft'),
                (int) $g->sum('muatan_total'),
                $indeks('ppl', $first->nama_ppl),
                $indeks('pml', $first->nama_pml),
                $indeks('org', $first->pml_organik),
            ];
        }

        $totalRegion = $rows->sum('total_region');
        $totalSelesai = $rows->sum(fn ($r) => $r->selesai);

        return [
            'data' => $data,
            'kamus' => [
                'ppl' => array_keys($kamus['ppl']),
                'pml' => array_keys($kamus['pml']),
                'org' => array_keys($kamus['org']),
            ],
            'jumlahSls' => count($data),
            'hijau' => $hijau,
            'oranye' => $oranye,
            'merah' => $merah,
            'progresRata' => $totalRegion > 0 ? round($totalSelesai / $totalRegion * 100, 1) : 0,
            // kalau ada filter aktif, peta hanya menyorot SLS yang cocok
            'adaFilter' => $this->adaFilter(),
            'urlGeojson' => asset('geo/sls-7309.geojson'),
        ];
    }

    /** Baris untuk peta: filter umum + kata kunci pencarian. */
    protected function rowsPeta(): Collection
    {
        $rows = $this->rows();

        if ($this->search !== '') {
            $s = mb_strtolower($this->search);
            $rows = $rows->filter(fn ($r) => str_contains(mb_strtolower((string) $r->nama_sls), $s)
                || str_contains(mb_strtolower((string) $r->nama_ppl), $s)
                || str_contains(mb_strtolower((string) $r->nama_pml), $s)
                || str_contains((string) $r->region_code, $s))->values();
        }

        return $rows;
    }

    protected function adaFilter(): bool
    {
        return $this->filterKecamatan !== '' || $this->filterDesa !== ''
            || $this->filterPml !== '' || $this->filterOrganik !== '' || $this->search !== '';
    }

    // =================================================================
    // TAB: KINERJA PPL
    // =================================================================

    protected function dataKinerjaPpl()
    {
        $rows = $this->rows();

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
                'organik' => $g->first()->pml_organik,
                'progres' => $total > 0 ? round($sel / $total * 100, 1) : 0,
                'tidak_ditemukan' => round($tidakDitemukanPct ?? 0, 1),
                'muatan' => $g->sum('muatan_total'),
                'kecamatan' => $g->pluck('nmkec')->filter()->unique()->values()->all(),
                'desa' => $g->pluck('nmdes')->filter()->unique()->values()->all(),
            ];
        })->filter(fn ($r) => $r['email']);

        if ($this->search) {
            $s = mb_strtolower($this->search);
            $grouped = $grouped->filter(fn ($r) => str_contains(mb_strtolower($r['nama']), $s)
                || str_contains(mb_strtolower($r['email']), $s));
        }

        return $this->sortAndPaginate($grouped);
    }

    // =================================================================
    // TAB: KINERJA PML
    // =================================================================

    protected function dataKinerjaPml()
    {
        $rows = $this->rows();

        $grouped = $rows->groupBy('nama_pml')->map(function ($g, $nama) {
            $total = $g->sum('total_region');
            $sel = $g->sum(fn ($r) => $r->selesai_pml);

            return [
                'nama' => $nama ?: '(Tidak diketahui)',
                'organik' => $g->first()->pml_organik,
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

    // =================================================================
    // TAB: DETAIL SLS / BLOK SENSUS
    // =================================================================

    protected function dataDetailSls()
    {
        $rows = $this->rows();

        $mapped = $rows->map(fn ($r) => [
            'region_code' => $r->region_code,
            'nama_sls' => $r->nama_sls,
            'kecamatan' => $r->nmkec,
            'desa' => $r->nmdes,
            'ppl' => $r->nama_ppl,
            'pml' => $r->nama_pml,
            'total' => $r->total_region,
            'draft' => $r->draft,
            'muatan' => $r->muatan_total,
            'progres' => $r->total_region > 0 ? round($r->selesai / $r->total_region * 100, 1) : 0,
        ]);

        if ($this->search) {
            $s = mb_strtolower($this->search);
            $mapped = $mapped->filter(fn ($r) => str_contains(mb_strtolower($r['nama_sls'] ?? ''), $s)
                || str_contains(mb_strtolower($r['ppl'] ?? ''), $s)
                || str_contains(mb_strtolower($r['pml'] ?? ''), $s)
                || str_contains((string) $r['region_code'], $s));
        }

        return $this->sortAndPaginate($mapped, 'progres');
    }

    // =================================================================
    // TAB: TIDAK DITEMUKAN
    // =================================================================

    protected function dataTidakDitemukan(): array
    {
        $rows = $this->rows();
        $groupKey = $this->filterKecamatan !== '' ? 'nmdes' : 'nmkec';

        $perWilayah = $rows->groupBy($groupKey)->map(function ($g, $nama) {
            return [
                'wilayah' => $nama !== '' && $nama !== null ? $nama : '(Tidak diketahui)',
                'keluarga_td' => $g->sum('kk_tidak_ditemukan'),
                'usaha_td' => $g->sum('usaha_tidak_ditemukan'),
                'ukdk_td' => $g->sum('ukdk_tidak_ditemukan'),
            ];
        })->sortByDesc(fn ($r) => $r['keluarga_td'] + $r['usaha_td'] + $r['ukdk_td'])->values();

        return [
            'perWilayah' => $perWilayah,
            'labelWilayah' => $this->filterKecamatan !== '' ? 'Desa/Kelurahan' : 'Kecamatan',
            'totalKeluargaTd' => $rows->sum('kk_tidak_ditemukan'),
            'totalUsahaTd' => $rows->sum('usaha_tidak_ditemukan'),
            'totalUkdkTd' => $rows->sum('ukdk_tidak_ditemukan'),
        ];
    }

    // =================================================================
    // TAB: GABUNGAN
    // =================================================================

    protected function dataGabungan(): array
    {
        $rows = $this->rows();

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

    // =================================================================
    // TAB: PRODUKTIVITAS HARIAN
    // =================================================================

    /**
     * Metrik yang benar-benar ditampilkan, mengikuti centang pengguna.
     * Urutannya selalu mengikuti urutan baku (progres, draft, muatan)
     * berapa pun urutan centangnya.
     *
     * @return array<string, string>
     */
    public function metrikTampil(): array
    {
        $aktif = array_values(array_intersect(array_keys(self::METRIK), $this->metrikAktif));

        return collect(self::METRIK)->only($aktif)->all();
    }

    /**
     * Untuk setiap PPL dan setiap tanggal ditampilkan kolom sesuai centang:
     *   - progres : selisih jumlah assignment selesai dibanding hari sebelumnya
     *   - draft   : selisih jumlah dokumen berstatus DRAFT
     *   - muatan  : selisih muatan total (keluarga + usaha + UKDK)
     *
     * Nilai absolut hari itu tetap dibawa (key `riwayat`) untuk grafik & tooltip.
     */
    protected function dataProduktivitas(): array
    {
        $uploads = DailyUpload::orderBy('tanggal')->get();
        if ($uploads->count() < 2) {
            return [
                'tanggalList' => [],
                'tanggalSemua' => [],
                'metrik' => $this->metrikTampil(),
                'metrikSemua' => self::METRIK,
                'data' => collect(),
            ];
        }

        $nilaiPerTanggal = []; // [tanggal][email] => ['progres'=>, 'draft'=>, 'muatan'=>]
        $namaPpl = [];

        foreach ($uploads as $u) {
            $rows = $this->applyFilters(SlsDaily::where('daily_upload_id', $u->id)->get());
            $tgl = $u->tanggal->format('Y-m-d');

            foreach ($rows->groupBy('username') as $email => $g) {
                if (!$email) continue;
                $nilaiPerTanggal[$tgl][$email] = [
                    'progres' => (int) $g->sum(fn ($r) => $r->selesai),
                    'draft' => (int) $g->sum('draft'),
                    'muatan' => (int) $g->sum('muatan_total'),
                ];
                $namaPpl[$email] = $g->first()->nama_ppl ?: $email;
            }
        }

        $tanggalUrut = $uploads->pluck('tanggal')->map(fn ($t) => $t->format('Y-m-d'))->values()->all();
        $tanggalKolom = array_slice($tanggalUrut, 1); // kolom = tanggal ke-2 dst (selisih thd hari sebelumnya)

        $metrik = array_keys(self::METRIK);

        $data = collect();
        foreach ($namaPpl as $email => $nama) {
            $row = ['email' => $email, 'nama' => $nama, 'harian' => [], 'riwayat' => []];

            foreach ($tanggalUrut as $t) {
                $row['riwayat'][$t] = $nilaiPerTanggal[$t][$email] ?? null;
            }

            foreach ($tanggalKolom as $i => $t) {
                $prevT = $tanggalUrut[$i]; // index i pada $tanggalUrut = hari sebelumnya
                $today = $nilaiPerTanggal[$t][$email] ?? null;
                $prev = $nilaiPerTanggal[$prevT][$email] ?? null;

                foreach ($metrik as $m) {
                    $row['harian'][$t][$m] = ($today !== null && $prev !== null)
                        ? $today[$m] - $prev[$m]
                        : null;
                    $row['harian'][$t]['abs_' . $m] = $today[$m] ?? null;
                }
            }

            $data->push($row);
        }

        if ($this->search) {
            $s = mb_strtolower($this->search);
            $data = $data->filter(fn ($r) => str_contains(mb_strtolower($r['nama']), $s)
                || str_contains(mb_strtolower($r['email']), $s));
        }

        return [
            'tanggalList' => $tanggalKolom,
            'tanggalSemua' => $tanggalUrut,
            'metrik' => $this->metrikTampil(),
            'metrikSemua' => self::METRIK,
            'data' => $data->sortBy('nama')->values(),
        ];
    }

    // =================================================================
    // UTIL
    // =================================================================

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

    protected function suffixFile(): string
    {
        $parts = array_filter([$this->filterKecamatan, $this->filterDesa, $this->filterOrganik, $this->filterPml]);

        return empty($parts) ? '' : '-' . \Illuminate\Support\Str::slug(implode('-', $parts));
    }

    // =================================================================
    // EXPORT
    // =================================================================

    public function exportDetailSls()
    {
        $mapped = $this->rows()->map(fn ($r) => [
            $r->region_code, $r->nama_sls, $r->nmkec, $r->nmdes, $r->nama_ppl, $r->nama_pml, $r->pml_organik,
            $r->total_region, $r->draft, $r->muatan_total,
            $r->total_region > 0 ? round($r->selesai / $r->total_region * 100, 1) : 0,
        ]);

        return SimpleExcelExporter::export(
            'detail-sls-blok-sensus' . $this->suffixFile(),
            ['Kode Region', 'Nama SLS', 'Kecamatan', 'Desa', 'PPL', 'PML', 'PML Organik', 'Total', 'Draft', 'Muatan', 'Progres (%)'],
            $mapped->all()
        );
    }

    public function exportKinerjaPpl()
    {
        $data = $this->dataKinerjaPpl()->getCollection();
        $mapped = $data->map(fn ($r) => [
            $r['nama'], $r['email'], $r['progres'], $r['tidak_ditemukan'], $r['muatan'],
            $r['pml'], $r['organik'],
            implode(', ', $r['kecamatan']), implode(', ', $r['desa'] ?? []),
        ]);

        return SimpleExcelExporter::export(
            'kinerja-ppl' . $this->suffixFile(),
            ['Nama PPL', 'Email', 'Progres (%)', 'Rata2 Tidak Ditemukan (%)', 'Muatan', 'PML', 'PML Organik', 'Kecamatan', 'Desa/Kel'],
            $mapped->all()
        );
    }

    public function exportKinerjaPml()
    {
        $data = $this->dataKinerjaPml()->getCollection();
        $mapped = $data->map(fn ($r) => [
            $r['nama'], $r['organik'], $r['jumlah_ppl'], $r['progres'], $r['muatan'], implode(', ', $r['kecamatan']),
        ]);

        return SimpleExcelExporter::export(
            'kinerja-pml' . $this->suffixFile(),
            ['Nama PML', 'PML Organik', 'Jumlah PPL', 'Progres (%)', 'Muatan', 'Kecamatan'],
            $mapped->all()
        );
    }

    public function exportProduktivitas()
    {
        $prod = $this->dataProduktivitas();
        $metrik = $prod['metrik']; // hanya kolom yang dicentang

        $headers = ['Nama PPL', 'Email'];
        foreach ($prod['tanggalList'] as $t) {
            $label = \Carbon\Carbon::parse($t)->translatedFormat('d M');
            foreach ($metrik as $m => $labelMetrik) {
                $headers[] = $label . ' - ' . $labelMetrik;
            }
        }

        $rows = $prod['data']->map(function ($r) use ($prod, $metrik) {
            $row = [$r['nama'], $r['email']];
            foreach ($prod['tanggalList'] as $t) {
                foreach ($metrik as $m => $labelMetrik) {
                    $nilai = $r['harian'][$t][$m] ?? null;
                    $row[] = $nilai === null ? '-' : $nilai;
                }
            }

            return $row;
        });

        return SimpleExcelExporter::export('produktivitas-harian' . $this->suffixFile(), $headers, $rows->all());
    }

    public function exportPeta()
    {
        $rows = $this->rowsPeta()->map(function ($r) {
            $progres = $r->total_region > 0 ? round($r->selesai / $r->total_region * 100, 1) : 0;

            return [
                $r->region_code, $r->nama_sls, $r->nmkec, $r->nmdes,
                $r->nama_ppl, $r->nama_pml, $r->pml_organik,
                $r->total_region, $r->selesai, $r->draft, $r->muatan_total, $progres,
                $progres >= 90 ? 'Hijau (>=90%)' : ($progres >= 50 ? 'Oranye (50-90%)' : 'Merah (<50%)'),
            ];
        });

        return SimpleExcelExporter::export(
            'peta-progres-sls' . $this->suffixFile(),
            ['Region Code', 'Nama SLS', 'Kecamatan', 'Desa/Kel', 'PPL', 'PML', 'PML Organik',
                'Total', 'Selesai', 'Draft', 'Muatan', 'Progres (%)', 'Kategori'],
            $rows->all()
        );
    }

    // =================================================================
    // GRAFIK PPL
    // =================================================================

    public function openPplChart(string $email)
    {
        $prod = $this->dataProduktivitas();
        $row = $prod['data']->firstWhere('email', $email);
        if (!$row) return;

        $tanggal = $prod['tanggalSemua'];
        $labels = array_map(fn ($t) => \Carbon\Carbon::parse($t)->translatedFormat('d M'), $tanggal);

        // grafik mengikuti kolom yang sedang dicentang; kalau tidak ada yang
        // dicentang, tampilkan semuanya
        $metrik = $prod['metrik'] ?: self::METRIK;

        $seri = [];
        foreach ($metrik as $m => $label) {
            $seri[] = [
                'label' => $label,
                'values' => array_map(fn ($t) => $row['riwayat'][$t][$m] ?? null, $tanggal),
            ];
        }

        $this->dispatch('open-ppl-chart', labels: $labels, seri: $seri, nama: $row['nama']);
    }

    // =================================================================
    // RENDER
    // =================================================================

    public function render()
    {
        $latest = $this->latestUpload();

        $lists = [
            'kecamatan' => $this->kecamatanList(),
            'desa' => $this->desaList(),
            'organik' => $this->organikList(),
            'pml' => $this->pmlList(),
        ];
        $this->sanitizeFilters($lists);

        $viewData = [
            'latest' => $latest,
            'kecamatanList' => $lists['kecamatan'],
            'desaList' => $lists['desa'],
            'organikList' => $lists['organik'],
            'pmlList' => $lists['pml'],
        ];

        switch ($this->tab) {
            case 'peta':
                $viewData['peta'] = $this->dataPeta();
                break;
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
