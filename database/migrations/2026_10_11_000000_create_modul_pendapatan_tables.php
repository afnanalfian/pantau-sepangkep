<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Kolom identitas & wilayah yang sama di kedua modul. */
    private function kolomUmum(Blueprint $t): void
    {
        $t->unsignedInteger('no')->nullable();
        $t->string('assignment_id', 64)->index();
        $t->string('no_usaha', 16)->nullable();
        $t->string('nama_usaha', 500)->nullable();
        $t->string('kdprov', 4)->nullable();
        $t->string('nmprov')->nullable();
        $t->string('kdkab', 8)->nullable();
        $t->string('nmkab')->nullable();
        $t->string('kdkec', 10)->nullable();
        $t->string('nmkec')->nullable();
        $t->string('kddesa', 16)->nullable();
        $t->string('nmdesa')->nullable();
        $t->string('kode_sls', 8)->nullable();
        $t->string('nama_sls', 500)->nullable();
        $t->string('sub_sls', 4)->nullable();
        $t->string('jalur_pelaporan', 50)->nullable();
        $t->decimal('pendapatan_utama', 18, 2)->nullable();
        $t->decimal('pendapatan_lain', 18, 2)->nullable();
        $t->decimal('total_pengeluaran', 18, 2)->nullable();
    }

    private function kolomTindakLanjut(Blueprint $t): void
    {
        $t->string('tindak_lanjut', 10)->default('belum'); // belum | sudah
        $t->timestamp('tindak_lanjut_at')->nullable();
        $t->string('tindak_lanjut_by')->nullable();
        $t->timestamps();
    }

    public function up(): void
    {
        // ================= PEMINDAHAN PENDAPATAN =================
        Schema::create('pemindahan_batches', function (Blueprint $t) {
            $t->id();
            $t->date('tanggal')->unique();
            $t->string('nama_file')->nullable();
            $t->string('uploaded_by')->nullable();
            $t->timestamps();
        });

        Schema::create('pemindahan_mikros', function (Blueprint $t) {
            $t->id();
            $t->foreignId('pemindahan_batch_id')->constrained('pemindahan_batches')->cascadeOnDelete();
            $this->kolomUmum($t);
            $this->kolomTindakLanjut($t);

            $t->index(['pemindahan_batch_id', 'nmkec', 'nmdesa']);
            $t->index(['pemindahan_batch_id', 'tindak_lanjut']);
        });

        // ================= PENDAPATAN 1 JUTA KE BAWAH =================
        Schema::create('sejuta_batches', function (Blueprint $t) {
            $t->id();
            $t->date('tanggal')->unique();
            $t->string('nama_file')->nullable();
            $t->string('uploaded_by')->nullable();
            $t->timestamps();
        });

        Schema::create('sejuta_mikros', function (Blueprint $t) {
            $t->id();
            $t->foreignId('sejuta_batch_id')->constrained('sejuta_batches')->cascadeOnDelete();
            $this->kolomUmum($t);
            $t->decimal('total_pendapatan', 18, 2)->nullable();
            $t->unsignedInteger('jml_tenaga_kerja')->nullable();
            $t->string('keberadaan', 100)->nullable();
            $t->text('catatan')->nullable();
            $this->kolomTindakLanjut($t);

            $t->index(['sejuta_batch_id', 'nmkec', 'nmdesa']);
            $t->index(['sejuta_batch_id', 'tindak_lanjut']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pemindahan_mikros');
        Schema::dropIfExists('pemindahan_batches');
        Schema::dropIfExists('sejuta_mikros');
        Schema::dropIfExists('sejuta_batches');
    }
};
