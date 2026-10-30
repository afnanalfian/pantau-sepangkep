<?php

namespace App\Livewire\KbliKeliru;

use App\Models\KbliKeliruBatch;
use App\Models\KbliKeliruMikro as M;
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
class KbliKeliruDetail extends Component
{
    use WithPagination;

    public const TANPA_PIC = '__tanpa__';

    public KbliKeliruBatch $batch;
    public string $view = 'dashboard'; // dashboard | mikro

    public string $filterPic = '';
    public string $filterKategori = '';      // kategori saat ini
    public string $filterRekomendasi = '';   // kategori_rekomendasi
    public string $filterAlasan = '';        // alasan_tidak_sesuai
    public string $filterDasar = '';         // dasar_rule
    public string $filterStatus = '';        // belum | sudah
    public string $filterHasil = '';         // sudah_sesuai | diperbaiki
    public string $search = '';
    public int $perPage = 10;

    // modal tindak lanjut
    public bool $showModal = false;
    public ?int $selectedId = null;
    public ?string $selectedStatus = null;
    public string $catatan = '';
    public ?string $modalNama = null;
    public ?string $modalInfo = null;
    public ?string $modalSaran = null;
    public bool $modalEdit = false;

    public function mount(KbliKeliruBatch $batch)
    {
        $this->batch = $batch;
    }

    public function updating($name)
    {
        if (str_starts_with($name, 'filter') || in_array($name, ['search', 'perPage'], true)) {
            $this->resetPage();
        }
    }

    public function updatedFilterStatus()
    {
        if ($this->filterStatus === 'belum') $this->filterHasil = '';
    }

    public function lihatDataMikro() { $this->view = 'mikro'; }
    public function kembaliKeDashboard() { $this->view = 'dashboard'; }

    /** Pintasan dari dashboard: buka data mikro dengan satu filter */
    public function lihat(string $filter, string $nilai, ?string $filter2 = null, ?string $nilai2 = null)
    {
        $boleh = ['filterPic', 'filterKategori', 'filterRekomendasi', 'filterAlasan', 'filterDasar', 'filterStatus'];
        if (!in_array($filter, $boleh, true) || ($filter2 && !in_array($filter2, $boleh, true))) return;

        $this->resetFilter();
        $this->{$filter} = $nilai;
        if ($filter2) $this->{$filter2} = (string) $nilai2;
        $this->view = 'mikro';
    }

    public function resetFilter()
    {
        $this->reset(['filterPic', 'filterKategori', 'filterRekomendasi', 'filterAlasan', 'filterDasar', 'filterStatus', 'filterHasil', 'search']);
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
        $this->modalInfo = 'KBLI ' . ($m->kbli_akhir ?? '-') . ' · Kategori ' . ($m->kategori ?? '-') . ($m->pic ? ' · PIC: ' . $m->pic : '');
        $this->modalSaran = $m->keterangan_rekomendasi;
        $this->modalEdit = $m->tindak_lanjut === 'sudah';
        $this->selectedStatus = $m->status_penyelesaian;
        $this->catatan = (string) $m->catatan;
        $this->showModal = true;
    }

    public function tutupModal()
    {
        $this->reset(['showModal', 'selectedId', 'selectedStatus', 'catatan', 'modalNama', 'modalInfo', 'modalSaran', 'modalEdit']);
        $this->resetValidation();
    }

    public function prosesTandaiSelesai()
    {
        abort_unless(session('role'), 403);

        $this->validate([
            'selectedStatus' => 'required|in:' . implode(',', array_keys(M::statusOptions())),
            'catatan' => 'nullable|string|max:1000',
        ], [
            'selectedStatus.required' => 'Pilih: Sudah sesuai atau Keliru dan telah diperbaiki.',
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
        return M::where('kbli_keliru_batch_id', $this->batch->id);
    }

    protected function mikroQuery()
    {
        $q = $this->baseQuery();

        if ($this->filterPic === self::TANPA_PIC) {
            $q->whereNull('pic');
        } elseif ($this->filterPic) {
            $q->where('pic', $this->filterPic);
        }
        foreach ([
            'filterKategori' => 'kategori', 'filterRekomendasi' => 'kategori_rekomendasi',
            'filterAlasan' => 'alasan_tidak_sesuai', 'filterDasar' => 'dasar_rule',
            'filterStatus' => 'tindak_lanjut', 'filterHasil' => 'status_penyelesaian',
        ] as $prop => $col) {
            if ($this->{$prop} !== '') $q->where($col, $this->{$prop});
        }

        if ($this->search !== '') {
            $s = $this->search;
            $q->where(function ($qq) use ($s) {
                foreach (['keg_utama', 'assignment_id', 'kbli_akhir', 'pic', 'keterangan_rekomendasi', 'catatan'] as $col) {
                    $qq->orWhere($col, 'like', "%{$s}%");
                }
            });
        }

        return $q;
    }

    /** nilai => "nilai (jumlah)" */
    protected function opsiHitung(string $col): array
    {
        return $this->baseQuery()->whereNotNull($col)->where($col, '!=', '')
            ->select($col . ' as v', DB::raw('COUNT(*) as jml'))->groupBy($col)->orderBy($col)->get()
            ->mapWithKeys(fn ($r) => [$r->v => $r->v . ' (' . number_format($r->jml) . ')'])
            ->all();
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
        $group = fn (string $expr, ?int $limit = null) => $this->baseQuery()
            ->select(DB::raw("$expr as nama"), DB::raw('COUNT(*) as total'), DB::raw("$sudah as selesai"))
            ->groupBy('nama')->orderByDesc('total')
            ->when($limit, fn ($q) => $q->limit($limit))
            ->get()
            ->map(fn ($r) => ['nama' => $r->nama] + $prog($r->total, $r->selesai))
            ->all();

        $total = $this->baseQuery()->count();
        $selesai = $this->baseQuery()->where('tindak_lanjut', 'sudah')->count();

        $hasilCount = $this->baseQuery()->whereNotNull('status_penyelesaian')
            ->select('status_penyelesaian', DB::raw('COUNT(*) as jml'))->groupBy('status_penyelesaian')
            ->pluck('jml', 'status_penyelesaian');

        // pergeseran kategori: kategori saat ini -> kategori rekomendasi
        $pergeseran = $this->baseQuery()
            ->select('kategori', 'kategori_rekomendasi',
                DB::raw('MAX(kategori_rekomendasi_desc) as deskripsi'),
                DB::raw('COUNT(*) as total'), DB::raw("$sudah as selesai"))
            ->groupBy('kategori', 'kategori_rekomendasi')
            ->orderByDesc('total')->limit(8)->get()
            ->map(fn ($r) => [
                'dari' => $r->kategori ?? '?',
                'ke' => $r->kategori_rekomendasi ?? '?',
                'deskripsi' => $r->deskripsi,
            ] + $prog($r->total, $r->selesai))
            ->all();

        return [
            'total' => $total,
            'selesai' => $selesai,
            'belum' => $total - $selesai,
            'persen' => $total > 0 ? round($selesai / $total * 100, 1) : 0,
            'sudahSesuai' => (int) ($hasilCount['sudah_sesuai'] ?? 0),
            'diperbaiki' => (int) ($hasilCount['diperbaiki'] ?? 0),
            'pic' => $this->baseQuery()->whereNotNull('pic')->distinct()->count('pic'),
            'peringkat' => $this->peringkat(),
            'byAlasan' => $group("COALESCE(NULLIF(alasan_tidak_sesuai, ''), '(Tanpa alasan)')"),
            'byDasar' => $group("COALESCE(NULLIF(dasar_rule, ''), '(Tanpa dasar rule)')"),
            'byKategori' => $group("COALESCE(NULLIF(kategori, ''), '?')"),
            'pergeseran' => $pergeseran,
            'byPic' => $group("COALESCE(pic, '(Tanpa PIC)')"),
        ];
    }

    /**
     * Peringkat PIC (10 teratas: 3 di panggung + 7 di lintasan):
     *   1. persentase selesai tertinggi
     *   2. jika sama: paling cepat (batch diunggah -> penyelesaian terakhir)
     *   3. jika masih sama: jumlah selesai terbanyak
     */
    protected function peringkat(int $limit = 10): array
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
    // EXPORT — semua baris sesuai filter aktif, semua kolom file
    // =================================================================

    public function exportMikro()
    {
        $rows = $this->mikroQuery()->orderBy('pic')->orderBy('no')->get();
        $yt = config('kbli-keliru.kode_ya_tidak', ['1' => 'Ya', '2' => 'Tidak']);
        $ytl = fn ($v) => $v === null ? null : ($yt[$v] ?? $v);

        $headers = [
            'No', 'Kode Kab', 'Assignment ID', 'Index1', 'Kegiatan Utama',
            'Produk Sendiri (b1)', 'Layanan Mamin (b2)', 'Kegiatan Penjualan (b3)', 'Kegiatan Jasa (b4)', 'Jenis Usaha',
            'Kategori', 'KBLI GenAI', 'KBLI Akhir', 'Judul KBLI Akhir', 'Sumber KBLI',
            'Kategori Rule', 'Deskripsi Kategori Rule', 'Rule 13b', 'Rule JU', 'Validasi KBLI', 'Dasar Rule', 'Alasan Tidak Sesuai',
            'Kategori Rekomendasi', 'Deskripsi Rekomendasi', 'Keterangan Rekomendasi',
            'PIC', 'Tindak Lanjut', 'Hasil', 'Catatan', 'Ditandai Oleh', 'Waktu Tindak Lanjut', 'Link Edit FASIH',
        ];
        $teks = ['Assignment ID', 'Index1', 'KBLI GenAI', 'KBLI Akhir', 'Kode Kab'];
        $teksIdx = array_keys(array_intersect($headers, $teks));

        $sheet = ($spreadsheet = new Spreadsheet())->getActiveSheet();
        $sheet->setTitle('KBLI Keliru');
        $sheet->fromArray($headers, null, 'A1');
        $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->getFont()->setBold(true);

        $r = 2;
        foreach ($rows as $i => $m) {
            $vals = [
                $i + 1, $m->kdkab, $m->assignment_id, $m->index1, $m->keg_utama,
                $ytl($m->produk_sendiri), $ytl($m->layanan_mamin), $ytl($m->keg_penjualan), $ytl($m->keg_jasa), $m->jenis_usaha,
                $m->kategori, $m->kbli_genai, $m->kbli_akhir, $m->kbli_label, $m->label_sumber_kbli,
                $m->kategori_rule, $m->kategori_rule_desc, $m->rule_13b, $m->rule_ju, $m->kat_val_kbli, $m->dasar_rule, $m->alasan_tidak_sesuai,
                $m->kategori_rekomendasi, $m->kategori_rekomendasi_desc, $m->keterangan_rekomendasi,
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

        $nama = 'kbli-keliru-' . $this->batch->tanggal->format('Y-m-d') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
        }, $nama, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    // =================================================================
    // RENDER
    // =================================================================

    public function render()
    {
        $data = ['statusOptions' => M::statusOptions()];

        if ($this->view === 'mikro') {
            $data['mikros'] = $this->mikroQuery()->orderBy('pic')->orderBy('no')->paginate($this->perPage);

            $pic = [];
            $tanpa = $this->baseQuery()->whereNull('pic')->count();
            if ($tanpa) $pic[self::TANPA_PIC] = 'Tanpa PIC (' . number_format($tanpa) . ')';
            $data['picOptions'] = $pic + $this->opsiHitung('pic');

            $data['kategoriOptions'] = $this->opsiHitung('kategori');
            $data['rekomendasiOptions'] = $this->opsiHitung('kategori_rekomendasi');
            $data['alasanOptions'] = $this->opsiHitung('alasan_tidak_sesuai');
            $data['dasarOptions'] = $this->opsiHitung('dasar_rule');
        } else {
            $data['dash'] = $this->dashboardData();
        }

        return view('livewire.kbli-keliru.kbli-keliru-detail', $data);
    }
}
