<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('anomali_mikros', function (Blueprint $table) {
            $table->longText('nama_anomali')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('anomali_mikros', function (Blueprint $table) {
            $table->string('nama_anomali')->nullable()->change();
        });
    }
};  