<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ---------------------------------------------------------------
        // Batch per tanggal (sama seperti anomali_batches)
        // ---------------------------------------------------------------
        Schema::create('missing_value_batches', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal')->unique();
            $table->string('judul')->nullable();
            $table->string('nama_file', 500)->nullable();
            $table->string('uploaded_by')->nullable();
            $table->timestamps();
        });

        // ---------------------------------------------------------------
        // Data mikro: 27 kolom excel + kolom pengelolaan
        // ---------------------------------------------------------------
        Schema::create('missing_value_mikros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('missing_value_batch_id')
                ->constrained('missing_value_batches')
                ->cascadeOnDelete();

            // --- 27 kolom dari excel ---
            $table->unsignedInteger('no')->nullable();                  // No
            $table->string('nama')->nullable();                         // Nama Anggota Keluarga
            $table->string('kdprov', 10)->default('');                  // Kode Prov
            $table->string('nmprov', 100)->default('');                 // Nama Provinsi
            $table->string('kdkab', 10)->default('');                   // Kode Kab/Kota
            $table->string('nmkab', 100)->default('');                  // Nama Kab/Kota
            $table->string('kdkec', 10)->default('');                   // Kode Kec
            $table->string('nmkec', 100)->default('');                  // Nama Kecamatan
            $table->string('kddesa', 10)->default('');                  // Kode Desa
            $table->string('nmdesa', 100)->default('');                 // Nama Desa/Kel
            $table->string('kode_sls', 10)->default('');                // Kode SLS
            $table->string('sub_sls', 10)->default('00');               // Sub SLS
            $table->string('assignment_id', 100);                       // Assignment ID
            $table->text('variabel_missing')->nullable();               // Variabel Missing
            $table->string('id_petugas', 100)->nullable();              // ID Petugas
            $table->string('email_petugas')->nullable();                // Email Petugas
            $table->string('jk_se', 30)->nullable();                    // Jenis Kelamin SE
            $table->string('tgl_lahir_se', 10)->nullable();             // Tgl Lahir SE
            $table->string('bln_lahir_se', 10)->nullable();             // Bln Lahir SE
            $table->string('thn_lahir_se', 10)->nullable();             // Thn Lahir SE
            $table->string('nik_dtsen', 30)->nullable();                // NIK hasil matched DTSEN
            $table->string('nama_dtsen')->nullable();                   // Nama hasil matched DTSEN
            $table->string('jk_dtsen', 30)->nullable();                 // Jenis Kelamin hasil matched DTSEN
            $table->string('tgl_lahir_dtsen', 30)->nullable();          // Tanggal lahir hasil matched DTSEN
            $table->decimal('skor_kemiripan', 10, 4)->nullable();       // Skor Kemiripan
            $table->string('status_perbaikan')->nullable();             // Status Perbaikan
            $table->text('link_fasih')->nullable();                     // Link Fasih (disimpan, tapi TIDAK dipakai untuk tombol)

            // --- kunci pencarian petugas di sls_dailies ---
            $table->string('region_code', 20)->nullable()->index();

            // --- pengelolaan penyelesaian ---
            $table->string('tindak_lanjut', 10)->default('belum');      // belum | sudah
            $table->timestamp('tindak_lanjut_at')->nullable();
            $table->string('status_penyelesaian', 50)->nullable();      // metode (sama dengan anomali)
            $table->text('catatan')->nullable();                        // catatan opsional pegawai
            $table->string('diselesaikan_oleh')->nullable();            // role_label yang menandai selesai

            $table->timestamps();

            $table->index(['missing_value_batch_id', 'tindak_lanjut'], 'mv_mikro_batch_tl_idx');
            $table->index(['missing_value_batch_id', 'nmkec', 'nmdesa'], 'mv_mikro_batch_wil_idx');
            $table->index('assignment_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('missing_value_mikros');
        Schema::dropIfExists('missing_value_batches');
    }
};
