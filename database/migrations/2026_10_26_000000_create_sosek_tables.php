<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Anomali Sosek — semua jenis anomali (dari satu workbook multi-sheet) dalam SATU tabel data mikro.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sosek_batches', function (Blueprint $t) {
            $t->id();
            $t->date('tanggal')->unique();
            $t->json('nama_file')->nullable();     // jenis => nama file terakhir
            $t->string('uploaded_by')->nullable();
            $t->timestamps();
        });

        Schema::create('sosek_mikros', function (Blueprint $t) {
            $t->id();
            $t->foreignId('sosek_batch_id')->constrained('sosek_batches')->cascadeOnDelete();
            $t->string('jenis', 20);               // umur | profesi | kk_prov | kk_kembar | nik_prov | nik_kembar | ac_450
            $t->unsignedInteger('no')->nullable();

            $t->string('assignment_id', 64)->nullable()->index();
            $t->text('link_fasih_sm')->nullable();

            // wilayah
            $t->string('kdkec', 10)->nullable();
            $t->string('kddesa', 16)->nullable();
            $t->string('region_code', 16)->nullable();   // level_6_full_code / hasil pencarian
            $t->string('sumber_wilayah', 20)->nullable(); // file | assignment_batch | anomali | null
            $t->string('nmkec')->nullable();
            $t->string('nmdesa')->nullable();
            $t->string('nama_sls')->nullable();

            // identitas
            $t->string('nama_kk')->nullable();
            $t->string('no_kk', 20)->nullable();
            $t->string('nik', 20)->nullable();
            $t->string('nama_art')->nullable();        // nama_dtsen
            $t->string('no_urut', 10)->nullable();     // index1 / no_urut_kk

            // isi anomali (kolom penting gabungan 7 file)
            $t->smallInteger('umur')->nullable();
            $t->string('keberadaan')->nullable();
            $t->string('profesi')->nullable();
            $t->string('tgl_lahir', 40)->nullable();
            $t->string('daya_terpasang', 50)->nullable();
            $t->unsignedSmallInteger('jml_meteran')->nullable();
            $t->unsignedSmallInteger('jumlah_ac')->nullable();
            $t->string('nilai_anomali', 500)->nullable(); // ringkasan siap tampil, mis. "Umur 102 tahun"
            $t->text('daftar_remark')->nullable();
            $t->string('running_time', 30)->nullable();

            // PIC = PML organik dari sls_dailies (nama sudah diseragamkan)
            $t->string('pml_raw')->nullable();
            $t->string('pic')->nullable();

            // tindak lanjut
            $t->string('tindak_lanjut', 10)->default('belum');   // belum | sudah
            $t->string('status_penyelesaian', 30)->nullable();    // sesuai_lapangan | diperbaiki
            $t->text('catatan')->nullable();
            $t->timestamp('tindak_lanjut_at')->nullable();
            $t->string('tindak_lanjut_by')->nullable();

            $t->timestamps();

            $t->index(['sosek_batch_id', 'jenis']);
            $t->index(['sosek_batch_id', 'pic']);
            $t->index(['sosek_batch_id', 'nmkec', 'nmdesa']);
            $t->index(['sosek_batch_id', 'tindak_lanjut']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sosek_mikros');
        Schema::dropIfExists('sosek_batches');
    }
};
