<?php

namespace App\Livewire\MatchingAsn;

use App\Models\MatchingAsnBatch;
use App\Models\MatchingAsnMikro as M;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

#[Layout('components.layouts.app')]
class MatchingAsnDetail extends Component
{
    use WithPagination;

    public MatchingAsnBatch $batch;
    public string $view = 'dashboard'; // dashboard | mikro

    public string $filterKecamatan = '';
    public string $filterDesa = '';
    public string $filterInstansi = '';    // nama instansi | M::FILTER_INSTANSI_NA
    public string $filterDuplikat = '';    // '' | ya | tidak
    public string $filterIdentitas = '';   // lengkap | tanpa_nik | tanpa_kk | tanpa_keduanya
    public string $filterStatus = '';      // belum | sudah
    public string $filterHasil = '';       // didata | dihapus
    public string $search = '';
    public int $perPage = 10;

    // modal penyelesaian (khusus baris TIDAK duplikat)
    public bool $showModal = false;
    public ?int $selectedId = null;
    public ?string $selectedStatus = null;
    public string $catatan = '';
    public ?string $modalNama = null;
    public ?string $modalInfo = null;
    public bool $modalEdit = false;

    public function mount(MatchingAsnBatch $batch)
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

    /** Pintasan dari dashboard: buka data mikro dengan satu filter terpasang */
    public function lihat(string $filter, string $nilai)
    {
        $allowed = ['filterDuplikat', 'filterInstansi', 'filterIdentitas', 'filterKecamatan', 'filterStatus'];
        if (!in_array($filter, $allowed, true)) return;

        $this->resetFilter();
        $this->{$filter} = $nilai;
        $this->view = 'mikro';
    }

    public function resetFilter()
    {
        $this->reset(['filterKecamatan', 'filterDesa', 'filterInstansi', 'filterDuplikat', 'filterIdentitas', 'filterStatus', 'filterHasil', 'search']);
        $this->resetPage();
    }

    // =================================================================
    // PENYELESAIAN
    // =================================================================

    /** Baris DUPLIKAT: cukup konfirmasi sudah dihapus dari FASIH-SM. */
    public function konfirmasiDihapus(int $id)
    {
        abort_unless(session('role'), 403);

        $n = $this->baseQuery()->whereKey($id)->where('is_duplikat', true)->update([
            'tindak_lanjut' => 'sudah',
            'status_penyelesaian' => 'dihapus',
            'tindak_lanjut_at' => now(),
            'tindak_lanjut_by' => session('role_label'),
        ]);

        if ($n) session()->flash('success', 'Duplikat dikonfirmasi telah dihapus dari FASIH-SM.');
    }

    /** Baris TIDAK duplikat: pilih Telah Didata / Telah Dihapus (+ catatan opsional). */
    public function bukaModalTindakLanjut(int $id)
    {
        abort_unless(session('role'), 403);
        $m = $this->baseQuery()->find($id);
        if (!$m || $m->is_duplikat) return;

        $this->resetValidation();
        $this->selectedId = $m->id;
        $this->modalNama = $m->nama_display;
        $this->modalInfo = 'NIK ' . ($m->nik ?? '-') . ' · ' . $m->instansi_display;
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
            'selectedStatus.required' => 'Pilih: Telah Didata atau Telah Dihapus.',
        ]);

        $m = $this->selectedId ? $this->baseQuery()->where('is_duplikat', false)->find($this->selectedId) : null;
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
        session()->flash('success', ($edit ? 'Penyelesaian diperbarui: ' : 'Ditandai selesai: ') . $label);
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
        session()->flash('info', 'Penyelesaian dibatalkan.');
    }

    // =================================================================
    // QUERY
    // =================================================================

    protected function baseQuery()
    {
        return M::where('matching_asn_batch_id', $this->batch->id);
    }

    protected function applyIdentitas($q, string $v)
    {
        return match ($v) {
            'lengkap' => $q->whereNotNull('nik')->whereNotNull('nomor_kk'),
            'tanpa_nik' => $q->whereNull('nik'),
            'tanpa_kk' => $q->whereNull('nomor_kk'),
            'tanpa_keduanya' => $q->whereNull('nik')->whereNull('nomor_kk'),
            default => $q,
        };
    }

    /** Query data mikro sesuai SEMUA filter aktif (dipakai tabel & export). */
    protected function mikroQuery()
    {
        $q = $this->baseQuery();

        if ($this->filterKecamatan) $q->where('nmkec', $this->filterKecamatan);
        if ($this->filterDesa) $q->where('nmdesa', $this->filterDesa);
        if ($this->filterInstansi === M::FILTER_INSTANSI_NA) {
            $q->whereNull('instansi_kerja');
        } elseif ($this->filterInstansi) {
            $q->where('instansi_kerja', $this->filterInstansi);
        }
        if ($this->filterDuplikat === 'ya') $q->where('is_duplikat', true);
        if ($this->filterDuplikat === 'tidak') $q->where('is_duplikat', false);
        if ($this->filterIdentitas) $this->applyIdentitas($q, $this->filterIdentitas);
        if ($this->filterStatus) $q->where('tindak_lanjut', $this->filterStatus);
        if ($this->filterHasil) $q->where('status_penyelesaian', $this->filterHasil);

        if ($this->search !== '') {
            $s = $this->search;
            $q->where(function ($qq) use ($s) {
                foreach (['nama_prelist', 'nik', 'nomor_kk', 'assignment_id', 'code_identity', 'alamat_prelist', 'instansi_kerja', 'catatan'] as $col) {
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

    /** nilai => "Nama instansi (jumlah)", diawali "Instansi Tidak Ditemukan" */
    protected function instansiOptions(): array
    {
        $opts = [];
        $na = $this->baseQuery()->whereNull('instansi_kerja')->count();
        if ($na > 0) {
            $opts[M::FILTER_INSTANSI_NA] = M::INSTANSI_TIDAK_DITEMUKAN . ' (' . number_format($na) . ')';
        }

        $this->baseQuery()->whereNotNull('instansi_kerja')
            ->select('instansi_kerja', DB::raw('COUNT(*) as jml'))
            ->groupBy('instansi_kerja')
            ->orderBy('instansi_kerja')
            ->get()
            ->each(function ($r) use (&$opts) {
                $opts[$r->instansi_kerja] = $r->instansi_kerja . ' (' . number_format($r->jml) . ')';
            });

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

        $total = $this->baseQuery()->count();
        $selesai = $this->baseQuery()->where('tindak_lanjut', 'sudah')->count();

        // duplikat vs tidak
        $dup = $this->baseQuery()->select('is_duplikat', DB::raw('COUNT(*) as total'), DB::raw("$sudah as selesai"))
            ->groupBy('is_duplikat')->get()->keyBy(fn ($r) => (int) $r->is_duplikat);
        $hasil = $this->baseQuery()->where('is_duplikat', false)->whereNotNull('status_penyelesaian')
            ->select('status_penyelesaian', DB::raw('COUNT(*) as jml'))->groupBy('status_penyelesaian')
            ->pluck('jml', 'status_penyelesaian');

        // kelengkapan identitas
        $identitas = [];
        foreach (M::identitasOptions() as $k => $label) {
            $identitas[$k] = ['label' => $label, 'count' => $this->applyIdentitas($this->baseQuery(), $k)->count()];
        }

        $byInstansi = $this->baseQuery()
            ->select(DB::raw("COALESCE(instansi_kerja, '" . M::FILTER_INSTANSI_NA . "') as nama"), DB::raw('COUNT(*) as total'), DB::raw("$sudah as selesai"))
            ->groupBy('nama')->orderByDesc('total')->get()
            ->map(fn ($r) => ['nilai' => $r->nama, 'nama' => $r->nama === M::FILTER_INSTANSI_NA ? M::INSTANSI_TIDAK_DITEMUKAN : $r->nama] + $prog($r->total, $r->selesai))
            ->all();

        $byKecamatan = $this->baseQuery()
            ->select(DB::raw("COALESCE(nmkec, '(Tidak diketahui)') as nama"), DB::raw('COUNT(*) as total'), DB::raw("$sudah as selesai"))
            ->groupBy('nama')->orderByDesc('total')->get()
            ->map(fn ($r) => ['nama' => $r->nama] + $prog($r->total, $r->selesai))
            ->all();

        return [
            'total' => $total,
            'selesai' => $selesai,
            'belum' => $total - $selesai,
            'persen' => $total > 0 ? round($selesai / $total * 100, 1) : 0,
            'duplikat' => $prog($dup[1]->total ?? 0, $dup[1]->selesai ?? 0),
            'nonDuplikat' => $prog($dup[0]->total ?? 0, $dup[0]->selesai ?? 0) + [
                'didata' => (int) ($hasil['didata'] ?? 0),
                'dihapus' => (int) ($hasil['dihapus'] ?? 0),
            ],
            'identitas' => $identitas,
            'byInstansi' => $byInstansi,
            'byKecamatan' => $byKecamatan,
        ];
    }

    // =================================================================
    // EXPORT — semua baris yang cocok dengan filter aktif (bukan hanya 1 halaman)
    // =================================================================

    public function exportMikro()
    {
        $rows = $this->mikroQuery()->orderBy('nmkec')->orderBy('nmdesa')->orderBy('no')->get();

        $headers = [
            'No', 'Assignment ID', 'Kode Identitas', 'Kode Kab', 'Kode Kec', 'Kode Desa', 'Kabupaten/Kota', 'Kecamatan', 'Desa/Kelurahan',
            'Nomor KK', 'NIK', 'Sumber NIK', 'Jumlah Kandidat KK', 'Nama Prelist', 'Alamat Prelist', 'Jenis Prelist',
            'Status Assignment', 'Petugas', 'Tanggal Modifikasi', 'Instansi Kerja', 'Instansi Lokasi', 'Duplikat',
            'Tindak Lanjut', 'Penyelesaian', 'Catatan', 'Ditandai Oleh', 'Waktu Tindak Lanjut',
            'Link Detail FASIH', 'Link Cari NIK', 'Link Cari Kode Identitas', 'Link Cari Assignment',
        ];
        // kolom yang wajib ditulis sebagai teks (NIK/KK 16 digit, kode berawalan 0)
        $teks = ['Assignment ID', 'Kode Identitas', 'Kode Kab', 'Kode Kec', 'Kode Desa', 'Nomor KK', 'NIK'];
        $teksIdx = array_keys(array_intersect($headers, $teks));

        $sheet = ($spreadsheet = new Spreadsheet())->getActiveSheet();
        $sheet->setTitle('Matching ASN');
        $sheet->fromArray($headers, null, 'A1');
        $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->getFont()->setBold(true);

        $r = 2;
        foreach ($rows as $m) {
            $vals = [
                $m->no, $m->assignment_id, $m->code_identity, $m->kdkab, $m->kdkec, $m->kddesa, $m->nmkab, $m->nmkec, $m->nmdesa,
                $m->nomor_kk, $m->nik, $m->sumber_nik, $m->jumlah_kandidat_kk, $m->nama_prelist, $m->alamat_prelist, $m->jenis_prelist,
                $m->assignment_status, $m->petugas, $m->tanggal_modifikasi, $m->instansi_display, $m->instansi_lokasi,
                $m->is_duplikat ? 'Duplikat (HAPUS)' : 'Tidak duplikat (' . ($m->duplikat_label ?: '-') . ')',
                $m->tindak_lanjut === 'sudah' ? 'Sudah' : 'Belum',
                $m->tindak_lanjut === 'sudah' ? $m->status_label : '',
                $m->catatan, $m->tindak_lanjut_by, $m->tindak_lanjut_at?->format('Y-m-d H:i'),
                $m->link_detail, $m->link_cari_nik, $m->link_cari_kode, $m->link_cari_assignment,
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

        $nama = 'matching-asn-pangkep-' . $this->batch->tanggal->format('Y-m-d') . '.xlsx';

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
            'identitasOptions' => M::identitasOptions(),
        ];

        if ($this->view === 'mikro') {
            $data['mikros'] = $this->mikroQuery()->orderBy('nmkec')->orderBy('nmdesa')->orderBy('no')->paginate($this->perPage);
            $data['kecamatanOptions'] = $this->distinctOptions('nmkec');
            $data['desaOptions'] = $this->distinctOptions('nmdesa', ['nmkec' => $this->filterKecamatan]);
            $data['instansiOptions'] = $this->instansiOptions();
            $data['totalFilter'] = $data['mikros']->total();
        } else {
            $data['dash'] = $this->dashboardData();
        }

        return view('livewire.matching-asn.matching-asn-detail', $data);
    }
}
