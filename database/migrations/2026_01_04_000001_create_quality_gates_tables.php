<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qg_gates', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->integer('urutan')->default(0);
            $table->timestamps();
        });

        Schema::create('qg_uks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('qg_gate_id')->constrained('qg_gates')->cascadeOnDelete();
            $table->string('nama');
            $table->integer('urutan')->default(0);
            $table->timestamps();
        });

        Schema::create('qg_aksi_preventifs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('qg_uk_id')->constrained('qg_uks')->cascadeOnDelete();
            $table->integer('urutan')->default(1);
            $table->text('deskripsi');
            $table->json('pelaksana')->nullable();
            $table->string('template_path')->nullable();
            $table->string('laporan_path')->nullable();
            $table->string('link_bukti_dukung')->nullable();
            $table->boolean('bukti_dukung_checklist')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qg_aksi_preventifs');
        Schema::dropIfExists('qg_uks');
        Schema::dropIfExists('qg_gates');
    }
};
