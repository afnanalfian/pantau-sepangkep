<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Matching ASN Pangkep
 * Pendataan ASN Sensus Ekonomi 2026 Kabupaten Pangkajene dan Kepulauan
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matching_asn_batches', function (Blueprint $t) {
            $t->id();
            $t->date('tanggal')->unique();
            $t->string('nama_file')->nullable();
            $t->string('uploaded_by')->nullable();
            $t->timestamps();
        });

        Schema::create('matching_asn_mikros', function (Blueprint $t) {
            $t->id();
            $t->foreignId('matching_asn_batch_id')->constrained('matching_asn_batches')->cascadeOnDelete();
            $t->unsignedInteger('no')->nullable();

            $t->string('assignment_id', 64)->index();
            $t->string('code_identity', 100)->nullable();

            // wilayah
            $t->string('kdkab', 4)->nullable();
            $t->string('kdkec', 3)->nullable();
            $t->string('kddesa', 3)->nullable();
            $t->string('nmkab')->nullable();
            $t->string('nmkec')->nullable();
            $t->string('nmdesa')->nullable();

            // identitas
            $t->string('nomor_kk', 20)->nullable();
            $t->string('nik', 20)->nullable();
            $t->string('sumber_nik', 50)->nullable();
            $t->unsignedInteger('jumlah_kandidat_kk')->nullable();
            $t->string('nama_prelist', 500)->nullable();
            $t->string('alamat_prelist', 500)->nullable();
            $t->string('jenis_prelist', 100)->nullable();
            $t->string('assignment_status', 50)->nullable();   // assignment_status_alias
            $t->string('petugas')->nullable();
            $t->string('tanggal_modifikasi', 30)->nullable();

            // hasil matching ASN
            $t->string('instansi_kerja')->nullable();          // null = Instansi Tidak Ditemukan (#N/A)
            $t->string('instansi_lokasi')->nullable();
            $t->boolean('is_duplikat')->default(false);        // kolom DUPLIKAT = "HAPUS"
            $t->string('duplikat_label', 20)->nullable();      // nilai asli: HAPUS / AMAN

            // tindak lanjut
            $t->string('tindak_lanjut', 10)->default('belum');   // belum | sudah
            $t->string('status_penyelesaian', 20)->nullable();    // didata | dihapus
            $t->text('catatan')->nullable();
            $t->timestamp('tindak_lanjut_at')->nullable();
            $t->string('tindak_lanjut_by')->nullable();

            $t->timestamps();

            $t->index(['matching_asn_batch_id', 'nmkec', 'nmdesa']);
            $t->index(['matching_asn_batch_id', 'instansi_kerja']);
            $t->index(['matching_asn_batch_id', 'is_duplikat']);
            $t->index(['matching_asn_batch_id', 'tindak_lanjut']);
            $t->index('nik');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matching_asn_mikros');
        Schema::dropIfExists('matching_asn_batches');
    }
};
