<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Satu batch = satu kali upload Cek KBLI untuk satu tanggal
        Schema::create('kbli_batches', function (Blueprint $t) {
            $t->id();
            $t->date('tanggal')->unique();
            $t->string('judul')->nullable();
            $t->string('nama_file')->nullable();
            $t->string('uploaded_by')->nullable();
            $t->timestamps();
        });

        // Data mikro (1 baris excel = 1 baris di sini)
        Schema::create('kbli_mikros', function (Blueprint $t) {
            $t->id();
            $t->foreignId('kbli_batch_id')->constrained('kbli_batches')->cascadeOnDelete();
            $t->unsignedInteger('no')->nullable();

            // ---- 11 kolom dari excel ----
            $t->string('assignment_id', 64)->index();
            $t->string('index1', 32)->nullable();
            $t->string('kdprov', 16)->nullable();   // level_1_full_code
            $t->string('kdkab', 16)->nullable();    // level_2_full_code
            $t->string('kdkec', 16)->nullable();    // level_3_full_code (kecamatan)
            $t->string('kddesa', 16)->nullable();   // level_4_full_code (desa/kelurahan)
            $t->string('kbli_akhir', 5)->nullable();
            $t->string('nama_usaha', 500)->nullable();
            $t->text('keg_utama')->nullable();
            $t->text('produk')->nullable();
            $t->text('link_fasih_sm')->nullable();

            // ---- turunan saat import ----
            $t->string('nmkec')->nullable();
            $t->string('nmdesa')->nullable();
            $t->char('kbli_kategori', 1)->nullable();

            // ---- tindak lanjut ----
            $t->string('tindak_lanjut', 10)->default('belum');      // belum | sudah
            $t->string('status_penyelesaian', 30)->nullable();       // sesuai_lapangan | diperbaiki
            $t->text('catatan')->nullable();
            $t->timestamp('tindak_lanjut_at')->nullable();
            $t->string('tindak_lanjut_by')->nullable();

            $t->timestamps();

            $t->index(['kbli_batch_id', 'nmkec', 'nmdesa']);
            $t->index(['kbli_batch_id', 'kbli_akhir']);
            $t->index(['kbli_batch_id', 'tindak_lanjut']);
        });

        // Master judul KBLI (diisi lewat: php artisan kbli:import-master file.xlsx)
        Schema::create('kbli_masters', function (Blueprint $t) {
            $t->string('kode', 5)->primary();
            $t->string('judul', 500);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kbli_mikros');
        Schema::dropIfExists('kbli_batches');
        Schema::dropIfExists('kbli_masters');
    }
};
