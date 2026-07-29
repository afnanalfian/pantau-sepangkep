<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anomali_mikros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anomali_batch_id')->constrained()->cascadeOnDelete();
            $table->enum('jenis', ['usaha', 'keluarga']);
            $table->integer('no')->nullable();
            $table->string('nama')->nullable();
            $table->string('kdprov')->nullable();
            $table->string('nmprov')->nullable();
            $table->string('kdkab')->nullable();
            $table->string('nmkab')->nullable();
            $table->string('kdkec')->nullable();
            $table->string('nmkec')->nullable();
            $table->string('kddesa')->nullable();
            $table->string('nmdesa')->nullable();
            $table->string('kode_sls')->nullable();
            $table->string('sub_sls')->nullable();
            $table->string('assignment_id')->nullable();
            $table->string('nama_anomali')->nullable();
            $table->enum('tindak_lanjut', ['belum', 'sudah'])->default('belum');
            $table->timestamp('tindak_lanjut_at')->nullable();
            $table->string('id_petugas')->nullable();
            $table->string('email_petugas')->nullable();
            $table->string('link_fasih')->nullable();
            $table->timestamps();

            $table->index(['jenis', 'kdkec', 'tindak_lanjut']);
            $table->index(['email_petugas']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anomali_mikros');
    }
};
