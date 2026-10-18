<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Anomali Flag Rasio 3 (NTB / Output)
 *   - Anomali 172 : UB (Usaha Besar)
 *   - Anomali 173 : UM (Usaha Menengah)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flag_rasio3_batches', function (Blueprint $t) {
            $t->id();
            $t->date('tanggal')->unique();
            $t->string('nama_file_ub')->nullable();
            $t->string('nama_file_um')->nullable();
            $t->string('uploaded_by')->nullable();
            $t->timestamps();
        });

        Schema::create('flag_rasio3_mikros', function (Blueprint $t) {
            $t->id();
            $t->foreignId('flag_rasio3_batch_id')->constrained('flag_rasio3_batches')->cascadeOnDelete();
            $t->char('jenis', 2);                         // UB | UM (file asal)
            $t->unsignedInteger('no')->nullable();

            $t->string('assignment_id', 64)->index();
            $t->text('link_fasih_sm')->nullable();
            $t->string('kdkab', 8)->nullable();           // level_2_code
            $t->string('kategori', 2)->nullable();
            $t->string('kode_kbli', 5)->nullable();
            $t->string('nama_usaha', 500)->nullable();

            // ---- metrik ----
            $t->unsignedInteger('tenaga_kerja')->nullable();
            $t->decimal('upah_gaji', 24, 2)->nullable();
            $t->decimal('biaya_produksi', 24, 2)->nullable();
            $t->decimal('biaya_pembelian', 24, 2)->nullable();
            $t->decimal('biaya_operasional', 24, 2)->nullable();
            $t->decimal('biaya_non_operasional', 24, 2)->nullable();
            $t->decimal('total_pengeluaran', 24, 2)->nullable();
            $t->decimal('pendapatan_barang_jasa', 24, 2)->nullable();
            $t->decimal('pendapatan_lainnya', 24, 2)->nullable();
            $t->decimal('nilai_tambah', 24, 2)->nullable();
            $t->decimal('total_aset', 24, 2)->nullable();
            $t->decimal('output', 24, 2)->nullable();
            $t->decimal('rasio_biaya_pembelian', 20, 6)->nullable(); // rasio_biaya_pembelian_barang_terhadap_omzet
            $t->string('reklasifikasi_skala_usaha', 10)->nullable();
            $t->decimal('rasio_ntb', 20, 6)->nullable();
            $t->decimal('produktivitas', 30, 2)->nullable();

            // ---- tindak lanjut ----
            $t->string('tindak_lanjut', 10)->default('belum');      // belum | sudah
            $t->string('status_penyelesaian', 30)->nullable();       // sesuai_lapangan | diperbaiki
            $t->text('catatan')->nullable();
            $t->timestamp('tindak_lanjut_at')->nullable();
            $t->string('tindak_lanjut_by')->nullable();

            $t->timestamps();

            $t->index(['flag_rasio3_batch_id', 'jenis']);
            $t->index(['flag_rasio3_batch_id', 'kategori']);
            $t->index(['flag_rasio3_batch_id', 'kode_kbli']);
            $t->index(['flag_rasio3_batch_id', 'tindak_lanjut']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flag_rasio3_mikros');
        Schema::dropIfExists('flag_rasio3_batches');
    }
};
