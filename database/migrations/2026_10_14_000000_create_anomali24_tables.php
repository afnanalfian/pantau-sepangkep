<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Anomali 24 = Anomali KBLI Tidak Sesuai 01122.
 * Strukturnya sama dengan Cek KBLI + kolom PIC.
 * Judul KBLI memakai tabel kbli_masters milik modul Cek KBLI (tidak dibuat ulang).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anomali24_batches', function (Blueprint $t) {
            $t->id();
            $t->date('tanggal')->unique();
            $t->string('judul')->nullable();
            $t->string('nama_file')->nullable();
            $t->string('uploaded_by')->nullable();
            $t->timestamps();
        });

        Schema::create('anomali24_mikros', function (Blueprint $t) {
            $t->id();
            $t->foreignId('anomali24_batch_id')->constrained('anomali24_batches')->cascadeOnDelete();
            $t->unsignedInteger('no')->nullable();

            // ---- 12 kolom dari excel ----
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
            $t->string('pic')->nullable();

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

            $t->index(['anomali24_batch_id', 'nmkec', 'nmdesa']);
            $t->index(['anomali24_batch_id', 'kbli_akhir']);
            $t->index(['anomali24_batch_id', 'pic']);
            $t->index(['anomali24_batch_id', 'tindak_lanjut']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anomali24_mikros');
        Schema::dropIfExists('anomali24_batches');
    }
};
