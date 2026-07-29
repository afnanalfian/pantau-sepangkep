<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sls_dailies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_upload_id')->constrained()->cascadeOnDelete();
            $table->date('tanggal');

            $table->string('region_code');
            $table->string('username')->nullable();
            $table->string('email_pml')->nullable();
            $table->string('nik_ppl')->nullable();
            $table->string('nik_pml')->nullable();
            $table->string('nama_sls')->nullable();
            $table->string('nama_ppl')->nullable();
            $table->string('nama_pml')->nullable();
            $table->string('pml_organik')->nullable();
            $table->string('kddesa')->nullable();
            $table->string('nmdes')->nullable();
            $table->string('kdkec')->nullable();
            $table->string('nmkec')->nullable();

            $table->integer('total_region')->default(0);
            $table->integer('approved_pengawas')->default(0);
            $table->integer('open')->default(0);
            $table->integer('draft')->default(0);
            $table->integer('submitted_pencacah')->default(0);
            $table->integer('rejected_pengawas')->default(0);
            $table->integer('edited_admin_kab')->default(0);
            $table->integer('revoked_pengawas')->default(0);
            $table->integer('submitted_respondent')->default(0);
            $table->integer('rejected_admin_kab')->default(0);
            $table->integer('completed_admin_kab')->default(0);
            $table->integer('edited_pengawas')->default(0);

            $table->decimal('capaian_ppl', 8, 2)->default(0);
            $table->decimal('capaian_pml', 8, 2)->default(0);

            $table->integer('kk_prelist_awal')->default(0);
            $table->integer('kk_ditemukan')->default(0);
            $table->integer('kk_baru')->default(0);
            $table->integer('kk_meninggal')->default(0);
            $table->integer('kk_tidak_eligible')->default(0);
            $table->integer('kk_tidak_dapat_ditemui')->default(0);
            $table->integer('kk_tidak_ditemukan')->default(0);
            $table->integer('muatan_keluarga')->default(0);

            $table->integer('usaha_prelist_awal')->default(0);
            $table->integer('usaha_ditemukan')->default(0);
            $table->integer('usaha_tutup')->default(0);
            $table->integer('usaha_ganda')->default(0);
            $table->integer('usaha_tidak_ditemukan')->default(0);
            $table->integer('usaha_baru')->default(0);
            $table->integer('muatan_usaha')->default(0);

            $table->integer('ukdk_ditemukan')->default(0);
            $table->integer('ukdk_tutup')->default(0);
            $table->integer('ukdk_ganda')->default(0);
            $table->integer('ukdk_tidak_ditemukan')->default(0);
            $table->integer('ukdk_baru')->default(0);
            $table->integer('muatan_usaha_keluarga')->default(0);

            $table->integer('total_prelist_awal')->default(0);
            $table->integer('muatan_total')->default(0);

            $table->timestamps();

            $table->index(['tanggal']);
            $table->index(['region_code']);
            $table->index(['username']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sls_dailies');
    }
};
