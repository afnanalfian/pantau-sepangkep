<?php

namespace App\Services;

use App\Models\SejutaBatch;
use App\Models\SejutaMikro;

class SejutaUploadService extends BasePendapatanUploadService
{
    protected function batchModel(): string { return SejutaBatch::class; }
    protected function mikroModel(): string { return SejutaMikro::class; }
    protected function batchForeignKey(): string { return 'sejuta_batch_id'; }

    protected function extraSpec(): array
    {
        return [
            'total_pendapatan' => ['total_pendapatan_setahun', 'total_pendapatan'],
            'jml_tenaga_kerja' => ['jml_tenaga_kerja', 'jumlah_tenaga_kerja', 'tenaga_kerja'],
            'keberadaan' => ['keberadaan', 'keberadaan_usaha', 'status_keberadaan'],
        ];
    }

    protected function extraFields(callable $get): array
    {
        return [
            'total_pendapatan' => $this->number($get('total_pendapatan')),
            'jml_tenaga_kerja' => $this->integer($get('jml_tenaga_kerja')),
            'keberadaan' => $get('keberadaan'),
        ];
    }
}
