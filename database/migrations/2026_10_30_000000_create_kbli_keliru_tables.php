<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** KBLI Keliru — KBLI/kategori yang tidak sesuai dengan rule rincian 13b / jenis usaha. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kbli_keliru_batches', function (Blueprint $t) {
            $t->id();
            $t->date('tanggal')->unique();
            $t->string('nama_file')->nullable();
            $t->string('uploaded_by')->nullable();
            $t->timestamps();
        });

        Schema::create('kbli_keliru_mikros', function (Blueprint $t) {
            $t->id();
            $t->foreignId('kbli_keliru_batch_id')->constrained('kbli_keliru_batches')->cascadeOnDelete();
            $t->unsignedInteger('no')->nullable();

            $t->string('kdkab', 8)->nullable();                      // level_2_full_code
            $t->string('assignment_id', 64)->index();
            $t->string('index1', 32)->nullable();

            // rincian 13b (b1-b4) & jenis usaha
            $t->string('produk_sendiri', 10)->nullable();            // produk_sendiri_value
            $t->string('layanan_mamin', 10)->nullable();             // layanan_mamin_value
            $t->string('keg_penjualan', 10)->nullable();             // keg_penjualan_value
            $t->string('keg_jasa', 10)->nullable();                  // keg_jasa_value
            $t->string('jenis_usaha', 10)->nullable();               // jenis_usaha_value
            $t->text('keg_utama')->nullable();

            // KBLI saat ini
            $t->string('kategori', 2)->nullable();
            $t->string('kbli_genai', 10)->nullable();                // kbli_genai_value
            $t->string('kbli_akhir', 10)->nullable();
            $t->string('label_sumber_kbli', 100)->nullable();

            // rule & validasi
            $t->string('kategori_rule', 100)->nullable();
            $t->string('kategori_rule_desc')->nullable();
            $t->string('rule_13b', 100)->nullable();
            $t->string('rule_ju', 100)->nullable();
            $t->string('kat_val_kbli', 50)->nullable();
            $t->string('dasar_rule')->nullable();
            $t->string('alasan_tidak_sesuai')->nullable();

            // rekomendasi
            $t->string('kategori_rekomendasi', 100)->nullable();
            $t->string('kategori_rekomendasi_desc')->nullable();
            $t->text('keterangan_rekomendasi')->nullable();

            $t->text('link')->nullable();
            $t->string('pic_raw')->nullable();
            $t->string('pic')->nullable();                            // nama PIC yang sudah diseragamkan

            // tindak lanjut
            $t->string('tindak_lanjut', 10)->default('belum');      // belum | sudah
            $t->string('status_penyelesaian', 30)->nullable();       // sudah_sesuai | diperbaiki
            $t->text('catatan')->nullable();
            $t->timestamp('tindak_lanjut_at')->nullable();
            $t->string('tindak_lanjut_by')->nullable();

            $t->timestamps();

            $t->index(['kbli_keliru_batch_id', 'pic']);
            $t->index(['kbli_keliru_batch_id', 'kategori']);
            $t->index(['kbli_keliru_batch_id', 'tindak_lanjut']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kbli_keliru_mikros');
        Schema::dropIfExists('kbli_keliru_batches');
    }
};
