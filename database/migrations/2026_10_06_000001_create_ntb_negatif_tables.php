<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ntb_negatif_batches', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal')->unique();
            $table->string('judul')->nullable();
            $table->string('nama_file', 500)->nullable();
            $table->string('uploaded_by')->nullable();
            $table->timestamps();
        });

        Schema::create('ntb_negatif_mikros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ntb_negatif_batch_id')
                ->constrained('ntb_negatif_batches')
                ->cascadeOnDelete();

            // --- kolom dari excel (nama kolom wilayah diseragamkan dgn anomali) ---
            $table->string('kategori', 20)->nullable();                         // kategori
            $table->string('assignment_id', 100);                               // assignment_id
            $table->string('kdprov', 10)->default('');                          // kode_prov
            $table->string('nmprov', 100)->default('');                         // nama_provinsi
            $table->string('kdkab', 10)->default('');                           // kode_kab
            $table->string('nmkab', 100)->default('');                          // nama_kab_kota
            $table->string('kdkec', 10)->default('');                           // kode_kec
            $table->string('nmkec', 100)->default('');                          // nama_kecamatan
            $table->string('kddesa', 15)->default('');                          // kode_desa
            $table->string('nmdesa', 100)->default('');                         // nama_desa_kel
            $table->string('kode_sls', 10)->default('');                        // kode_sls
            $table->string('sub_sls', 10)->default('00');                       // sub_sls
            $table->string('kode_kbli', 10)->nullable();                        // kode_kbli
            $table->string('des_kbli', 500)->nullable();                        // des_kbli
            $table->string('pjk')->nullable();                                  // PJK
            $table->string('nama_usaha', 500)->nullable();                      // nama_usaha

            $table->decimal('metrik_biaya_produksi', 22, 2)->nullable();
            $table->decimal('metrik_biaya_pembelian', 22, 2)->nullable();
            $table->decimal('metrik_biaya_operasional', 22, 2)->nullable();
            $table->decimal('metrik_biaya_non_operasional', 22, 2)->nullable();
            $table->decimal('metrik_total_pengeluaran', 22, 2)->nullable();
            $table->decimal('metrik_pendapatan_barang_jasa', 22, 2)->nullable();
            $table->decimal('metrik_pendapatan_lainnya', 22, 2)->nullable();
            $table->decimal('metrik_nilai_tambah', 22, 2)->nullable();
            $table->decimal('metrik_total_aset', 22, 2)->nullable();
            $table->decimal('metrik_output', 22, 2)->nullable();

            $table->string('survey_period_id', 100)->nullable();                // survey_period_id
            $table->decimal('rasio_biaya_pembelian_omzet', 22, 6)->nullable();  // rasio_biaya_pembelian_barang_terhadap_omzet
            $table->string('reklasifikasi_skala_usaha', 50)->nullable();        // reklasifikasi_skala_usaha
            $table->decimal('rasio_ntb', 22, 6)->nullable();                    // rasio_ntb
            $table->decimal('produktivitas', 22, 6)->nullable();                // produktivitas
            $table->unsignedTinyInteger('flag_rasio_1_output_aset')->nullable();
            $table->unsignedTinyInteger('flag_rasio_2_upah_ntb')->nullable();
            $table->unsignedTinyInteger('flag_rasio_3_ntb_output')->nullable();
            $table->text('link_fasih_sm')->nullable();                          // disimpan, tombol pakai link mode edit

            // --- kunci pencarian petugas di sls_dailies ---
            $table->string('region_code', 20)->nullable()->index();

            // --- pengelolaan penyelesaian ---
            $table->string('tindak_lanjut', 10)->default('belum');              // belum | sudah
            $table->timestamp('tindak_lanjut_at')->nullable();
            $table->string('status_penyelesaian', 50)->nullable();              // diperbaiki | sesuai_lapangan
            $table->text('catatan')->nullable();
            $table->string('diselesaikan_oleh')->nullable();

            $table->timestamps();

            $table->index(['ntb_negatif_batch_id', 'tindak_lanjut'], 'ntb_mikro_batch_tl_idx');
            $table->index(['ntb_negatif_batch_id', 'nmkec', 'nmdesa'], 'ntb_mikro_batch_wil_idx');
            $table->index(['ntb_negatif_batch_id', 'kategori'], 'ntb_mikro_batch_kat_idx');
            $table->index('assignment_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ntb_negatif_mikros');
        Schema::dropIfExists('ntb_negatif_batches');
    }
};
