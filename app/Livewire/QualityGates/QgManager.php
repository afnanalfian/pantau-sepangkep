<?php

namespace App\Livewire\QualityGates;

use App\Models\AnomaliBatch;
use App\Models\QgAksiPreventif;
use App\Models\QgGate;
use App\Models\QgUk;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
class QgManager extends Component
{
    use WithFileUploads;

    public ?int $expandedGate = null;
    public ?int $expandedUk = null;

    // form: gate
    public bool $showGateForm = false;
    public ?int $editingGateId = null;
    public string $gateNama = '';

    // form: uk
    public bool $showUkForm = false;
    public ?int $editingUkId = null;
    public ?int $ukGateId = null;
    public string $ukNama = '';

    // form: aksi preventif
    public bool $showAksiForm = false;
    public ?int $editingAksiId = null;
    public ?int $aksiUkId = null;
    public string $aksiDeskripsi = '';
    public string $aksiPelaksanaText = '';
    public string $aksiLinkBukti = '';
    public $aksiTemplateFile = null;

    public array $laporanUpload = []; // [aksiId => TemporaryUploadedFile]

    protected function isQg(): bool
    {
        return in_array(session('role'), ['admin', 'qg']);
    }

    public function toggleGate(int $id)
    {
        $this->expandedGate = $this->expandedGate === $id ? null : $id;
    }

    public function toggleUk(int $id)
    {
        $this->expandedUk = $this->expandedUk === $id ? null : $id;
    }

    // --------------- GATE CRUD ---------------
    public function bukaFormGate(?int $id = null)
    {
        abort_unless($this->isQg(), 403);
        $this->editingGateId = $id;
        $this->gateNama = $id ? QgGate::findOrFail($id)->nama : '';
        $this->showGateForm = true;
    }

    public function simpanGate()
    {
        abort_unless($this->isQg(), 403);
        $this->validate(['gateNama' => 'required|string|max:150']);

        if ($this->editingGateId) {
            QgGate::whereKey($this->editingGateId)->update(['nama' => $this->gateNama]);
        } else {
            QgGate::create(['nama' => $this->gateNama, 'urutan' => QgGate::max('urutan') + 1]);
        }
        $this->showGateForm = false;
        $this->reset(['gateNama', 'editingGateId']);
    }

    public function hapusGate(int $id)
    {
        abort_unless($this->isQg(), 403);
        QgGate::findOrFail($id)->delete();
    }

    // --------------- UK CRUD ---------------
    public function bukaFormUk(int $gateId, ?int $id = null)
    {
        abort_unless($this->isQg(), 403);
        $this->ukGateId = $gateId;
        $this->editingUkId = $id;
        $this->ukNama = $id ? QgUk::findOrFail($id)->nama : '';
        $this->showUkForm = true;
    }

    public function simpanUk()
    {
        abort_unless($this->isQg(), 403);
        $this->validate(['ukNama' => 'required|string|max:150']);

        if ($this->editingUkId) {
            QgUk::whereKey($this->editingUkId)->update(['nama' => $this->ukNama]);
        } else {
            QgUk::create([
                'qg_gate_id' => $this->ukGateId,
                'nama' => $this->ukNama,
                'urutan' => QgUk::where('qg_gate_id', $this->ukGateId)->max('urutan') + 1,
            ]);
        }
        $this->showUkForm = false;
        $this->reset(['ukNama', 'editingUkId', 'ukGateId']);
    }

    public function hapusUk(int $id)
    {
        abort_unless($this->isQg(), 403);
        QgUk::findOrFail($id)->delete();
    }

    // --------------- AKSI PREVENTIF CRUD ---------------
    public function bukaFormAksi(int $ukId, ?int $id = null)
    {
        abort_unless($this->isQg(), 403);
        $this->aksiUkId = $ukId;
        $this->editingAksiId = $id;
        if ($id) {
            $a = QgAksiPreventif::findOrFail($id);
            $this->aksiDeskripsi = $a->deskripsi;
            $this->aksiPelaksanaText = implode(', ', $a->pelaksana ?? []);
            $this->aksiLinkBukti = $a->link_bukti_dukung ?? '';
        } else {
            $this->aksiDeskripsi = '';
            $this->aksiPelaksanaText = '';
            $this->aksiLinkBukti = '';
        }
        $this->showAksiForm = true;
    }

    public function simpanAksi()
    {
        abort_unless($this->isQg(), 403);
        $this->validate([
            'aksiDeskripsi' => 'required|string',
            'aksiLinkBukti' => 'nullable|url',
        ]);

        $pelaksana = array_values(array_filter(array_map('trim', explode(',', $this->aksiPelaksanaText))));

        $data = [
            'deskripsi' => $this->aksiDeskripsi,
            'pelaksana' => $pelaksana,
            'link_bukti_dukung' => $this->aksiLinkBukti ?: null,
        ];

        if ($this->aksiTemplateFile) {
            $data['template_path'] = Storage::url($this->aksiTemplateFile->store('qg-template', 'public'));
        }

        if ($this->editingAksiId) {
            QgAksiPreventif::whereKey($this->editingAksiId)->update($data);
        } else {
            $data['qg_uk_id'] = $this->aksiUkId;
            $data['urutan'] = QgAksiPreventif::where('qg_uk_id', $this->aksiUkId)->max('urutan') + 1;
            QgAksiPreventif::create($data);
        }

        $this->showAksiForm = false;
        $this->reset(['aksiDeskripsi', 'aksiPelaksanaText', 'aksiLinkBukti', 'aksiTemplateFile', 'editingAksiId', 'aksiUkId']);
    }

    public function hapusAksi(int $id)
    {
        abort_unless($this->isQg(), 403);
        QgAksiPreventif::findOrFail($id)->delete();
    }

    // Ceklis bukti dukung bisa ditandai oleh semua role yang login,
    // sama seperti unggah laporan (bukan hanya admin/qg).
    public function toggleChecklist(int $id)
    {
        abort_unless(session('role'), 403);
        $a = QgAksiPreventif::findOrFail($id);
        $a->update(['bukti_dukung_checklist' => !$a->bukti_dukung_checklist]);
    }

    // laporan bisa diunggah pelaksana siapapun dari role pegawai/lainnya
    public function unggahLaporan(int $id)
    {
        abort_unless(session('role'), 403);
        if (empty($this->laporanUpload[$id])) return;

        $path = Storage::url($this->laporanUpload[$id]->store('qg-laporan', 'public'));
        QgAksiPreventif::whereKey($id)->update(['laporan_path' => $path]);
        unset($this->laporanUpload[$id]);
    }

    public function render()
    {
        $gates = QgGate::with('uks.aksiPreventifs')->orderBy('urutan')->get();

        $semuaAksi = $gates->flatMap(fn ($g) => $g->uks->flatMap->aksiPreventifs);

        $stats = [
            'totalGate' => $gates->count(),
            'totalUk' => $gates->sum(fn ($g) => $g->uks->count()),
            'totalAksi' => $semuaAksi->count(),
            'selesai' => $semuaAksi->filter->isSelesai()->count(),
        ];

        $anomaliBerjalan = AnomaliBatch::orderByDesc('tanggal')->first();

        return view('livewire.qualitygates.qg-manager', [
            'gates' => $gates,
            'stats' => $stats,
            'isQg' => $this->isQg(),
            'anomaliBerjalan' => $anomaliBerjalan,
        ]);
    }
}
