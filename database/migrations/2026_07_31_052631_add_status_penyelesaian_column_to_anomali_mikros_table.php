<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('anomali_mikros', function (Blueprint $table) {
            $table->enum('status_penyelesaian', ['revoked_pml', 'diselesaikan_admin', 'reject_admin'])
                  ->nullable()
                  ->after('tindak_lanjut_at');
        });
    }

    public function down(): void
    {
        Schema::table('anomali_mikros', function (Blueprint $table) {
            $table->dropColumn('status_penyelesaian');
        });
    }
};