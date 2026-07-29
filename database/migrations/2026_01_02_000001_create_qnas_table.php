<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qnas', function (Blueprint $table) {
            $table->id();
            $table->string('nama')->nullable();
            $table->text('pertanyaan');
            $table->text('jawaban')->nullable();
            $table->string('dijawab_oleh')->nullable();
            $table->timestamp('dijawab_at')->nullable();
            $table->enum('status', ['menunggu', 'dijawab'])->default('menunggu');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qnas');
    }
};
