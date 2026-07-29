<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anomali_radars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anomali_batch_id')->constrained()->cascadeOnDelete();
            $table->enum('jenis', ['usaha', 'keluarga']);
            $table->string('kode');
            $table->string('kecamatan');
            $table->integer('total_assignment')->default(0);
            $table->json('data');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anomali_radars');
    }
};
