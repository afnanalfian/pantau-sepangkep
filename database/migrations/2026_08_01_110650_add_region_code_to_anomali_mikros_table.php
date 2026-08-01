<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('anomali_mikros', function (Blueprint $table) {
            if (!Schema::hasColumn('anomali_mikros', 'region_code')) {
                $table->string('region_code', 20)->nullable()->after('sub_sls');
                $table->index('region_code');
            }
        });
    }

    public function down(): void
    {
        Schema::table('anomali_mikros', function (Blueprint $table) {
            if (Schema::hasColumn('anomali_mikros', 'region_code')) {
                $table->dropIndex(['region_code']);
                $table->dropColumn('region_code');
            }
        });
    }
};
