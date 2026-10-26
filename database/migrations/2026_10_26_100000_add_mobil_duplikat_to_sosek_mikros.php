<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Anomali Sosek revisi: kolom untuk jenis 8 (penerangan bukan listrik + mobil)
 * dan jenis 9 (NIK duplikat nasional). Aman dijalankan walau kolom sudah ada.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sosek_mikros', function (Blueprint $t) {
            if (!Schema::hasColumn('sosek_mikros', 'sumber_penerangan')) $t->string('sumber_penerangan', 50)->nullable()->after('jumlah_ac');
            if (!Schema::hasColumn('sosek_mikros', 'jumlah_mobil')) $t->unsignedSmallInteger('jumlah_mobil')->nullable()->after('jumlah_ac');
            if (!Schema::hasColumn('sosek_mikros', 'nilai_mobil')) $t->decimal('nilai_mobil', 20, 2)->nullable()->after('jumlah_ac');
            if (!Schema::hasColumn('sosek_mikros', 'jumlah_duplikasi')) $t->unsignedInteger('jumlah_duplikasi')->nullable()->after('jumlah_ac');
            if (!Schema::hasColumn('sosek_mikros', 'sheet')) $t->string('sheet', 100)->nullable()->after('jenis');
        });
    }

    public function down(): void
    {
        Schema::table('sosek_mikros', function (Blueprint $t) {
            foreach (['sumber_penerangan', 'jumlah_mobil', 'nilai_mobil', 'jumlah_duplikasi', 'sheet'] as $c) {
                if (Schema::hasColumn('sosek_mikros', $c)) $t->dropColumn($c);
            }
        });
    }
};
