<?php

namespace App\Services;

use App\Models\PemindahanBatch;
use App\Models\PemindahanMikro;

class PemindahanUploadService extends BasePendapatanUploadService
{
    protected function batchModel(): string { return PemindahanBatch::class; }
    protected function mikroModel(): string { return PemindahanMikro::class; }
    protected function batchForeignKey(): string { return 'pemindahan_batch_id'; }

    protected function extraSpec(): array
    {
        return [];
    }

    protected function extraFields(callable $get): array
    {
        return [];
    }
}
