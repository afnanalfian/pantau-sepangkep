<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Memperlebar kolom yang menyimpan nama file asli / path / link.
 *
 * Root cause: AppServiceProvider memanggil Schema::defaultStringLength(191),
 * yang membuat SEMUA kolom string() tanpa panjang eksplisit di seluruh
 * migration jadi VARCHAR(191), bukan VARCHAR(255) seperti anggapan umum.
 * Nama file asli hasil upload (mis. judul referensi/skripsi yang panjang)
 * dengan mudah melebihi 191 karakter dan menyebabkan error:
 * "Data too long for column 'file_asli'".
 *
 * Migration ini pakai raw SQL (bukan ->change()) supaya TIDAK perlu
 * dependency tambahan (doctrine/dbal) yang belum ter-install di project ini.
 */
return new class extends Migration
{
    protected array $columns = [
        'arsips' => ['file_asli' => 500, 'file_path' => 500],
        'daily_uploads' => ['nama_file' => 500],
        'anomali_mikros' => ['link_fasih' => 500],
        'qg_aksi_preventifs' => ['template_path' => 500, 'laporan_path' => 500, 'link_bukti_dukung' => 500],
    ];

    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        foreach ($this->columns as $table => $cols) {
            if (!Schema::hasTable($table)) continue;

            foreach ($cols as $column => $length) {
                if (!Schema::hasColumn($table, $column)) continue;

                if ($driver === 'mysql') {
                    // nullable semua kolom di atas, jadi aman pakai NULL di sini
                    DB::statement("ALTER TABLE `{$table}` MODIFY `{$column}` VARCHAR({$length}) NULL");
                } elseif ($driver === 'sqlite') {
                    // SQLite tidak menegakkan panjang VARCHAR, jadi tidak perlu diubah.
                    continue;
                } elseif ($driver === 'pgsql') {
                    DB::statement("ALTER TABLE \"{$table}\" ALTER COLUMN \"{$column}\" TYPE VARCHAR({$length})");
                }
            }
        }
    }

    public function down(): void
    {
        $driver = DB::connection()->getDriverName();

        foreach ($this->columns as $table => $cols) {
            if (!Schema::hasTable($table)) continue;

            foreach ($cols as $column => $length) {
                if (!Schema::hasColumn($table, $column)) continue;

                if ($driver === 'mysql') {
                    DB::statement("ALTER TABLE `{$table}` MODIFY `{$column}` VARCHAR(191) NULL");
                } elseif ($driver === 'pgsql') {
                    DB::statement("ALTER TABLE \"{$table}\" ALTER COLUMN \"{$column}\" TYPE VARCHAR(191)");
                }
            }
        }
    }
};
