<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booking', function (Blueprint $table) {
            $table->timestamp('nhac_hen_luc')->nullable()->after('phan_hoi_khach');
        });

        Schema::table('lich_hen', function (Blueprint $table) {
            $table->timestamp('nhac_hen_luc')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('booking', function (Blueprint $table) {
            $table->dropColumn('nhac_hen_luc');
        });

        Schema::table('lich_hen', function (Blueprint $table) {
            $table->dropColumn('nhac_hen_luc');
        });
    }
};
