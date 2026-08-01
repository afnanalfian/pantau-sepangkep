<?php

namespace App\Console\Commands;

use App\Models\AnomaliMikro;
use App\Services\PetugasResolver;
use Illuminate\Console\Command;

/**
 * Mengisi kolom `region_code` untuk data anomali yang sudah terlanjur masuk
 * sebelum fitur ini ada.
 *
 *   php artisan anomali:backfill-region
 */
class BackfillAnomaliRegionCode extends Command
{
    protected $signature = 'anomali:backfill-region {--force : Timpa region_code yang sudah terisi}';

    protected $description = 'Isi ulang kolom region_code pada tabel anomali_mikros dari kode wilayah (kec/desa/sls/sub sls).';

    public function handle(): int
    {
        $query = AnomaliMikro::query();
        if (!$this->option('force')) {
            $query->where(function ($q) {
                $q->whereNull('region_code')->orWhere('region_code', '');
            });
        }

        $total = (clone $query)->count();
        if ($total === 0) {
            $this->info('Tidak ada baris yang perlu diperbarui.');

            return self::SUCCESS;
        }

        $this->info("Memproses {$total} baris...");
        $bar = $this->output->createProgressBar($total);

        $berhasil = 0;
        $gagal = 0;

        $query->chunkById(500, function ($rows) use (&$berhasil, &$gagal, $bar) {
            foreach ($rows as $m) {
                $code = PetugasResolver::buildRegionCode(
                    $m->kdkab, $m->kdkec, $m->kddesa, $m->kode_sls, $m->sub_sls
                );

                if ($code) {
                    $m->newQuery()->whereKey($m->id)->update(['region_code' => $code]);
                    $berhasil++;
                } else {
                    $gagal++;
                }
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine(2);
        $this->info("Selesai. Berhasil: {$berhasil}, tidak bisa dihitung: {$gagal}.");

        return self::SUCCESS;
    }
}
