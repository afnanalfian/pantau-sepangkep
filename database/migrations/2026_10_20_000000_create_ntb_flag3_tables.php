<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Anomali NTB Flag 3 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ntb_flag3_batches', function (Blueprint $t) {
            $t->id();
            $t->date('tanggal')->unique();
            $t->string('nama_file')->nullable();
            $t->string('uploaded_by')->nullable();
            $t->timestamps();
        });

        Schema::create('ntb_flag3_mikros', function (Blueprint $t) {
            $t->id();
            $t->foreignId('ntb_flag3_batch_id')->constrained('ntb_flag3_batches')->cascadeOnDelete();
            $t->unsignedInteger('no')->nullable();

            $t->string('assignment_id', 64)->index();
            $t->string('survey_period_id', 64)->nullable();
            $t->string('kdprov', 4)->nullable();    // level_1_full_code
            $t->string('nmprov')->nullable();       // level_1_name
            $t->string('kdkab', 8)->nullable();     // level_2_full_code
            $t->string('nmkab')->nullable();        // level_2_name
            $t->string('kdkec', 10)->nullable();    // level_3_full_code
            $t->string('kddesa', 16)->nullable();   // level_4_full_code
            $t->string('nama_usaha', 500)->nullable();

            $t->decimal('total_pengeluaran', 24, 2)->nullable();
            $t->decimal('pendapatan_barang_jasa', 24, 2)->nullable();
            $t->decimal('pendapatan_lainnya', 24, 2)->nullable();
            $t->decimal('nilai_tambah', 24, 2)->nullable();
            $t->decimal('total_aset', 24, 2)->nullable();
            $t->decimal('rasio_ntb', 20, 6)->nullable();
            $t->string('pic')->nullable();

            // turunan saat import (dari data harian sls_dailies)
            $t->string('nmkec')->nullable();
            $t->string('nmdesa')->nullable();

            // tindak lanjut
            $t->string('tindak_lanjut', 10)->default('belum');    // belum | sudah
            $t->string('status_penyelesaian', 30)->nullable();     // sesuai_lapangan | diperbaiki
            $t->text('catatan')->nullable();
            $t->timestamp('tindak_lanjut_at')->nullable();
            $t->string('tindak_lanjut_by')->nullable();

            $t->timestamps();

            $t->index(['ntb_flag3_batch_id', 'nmkec', 'nmdesa']);
            $t->index(['ntb_flag3_batch_id', 'pic']);
            $t->index(['ntb_flag3_batch_id', 'tindak_lanjut']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ntb_flag3_mikros');
        Schema::dropIfExists('ntb_flag3_batches');
    }
};
