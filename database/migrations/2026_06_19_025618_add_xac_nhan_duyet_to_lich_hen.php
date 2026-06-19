<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('lich_hen', function (Blueprint $table) {
            $table->boolean('xac_nhan_duyet_1')->default(false)->after('trang_thai');
            $table->boolean('xac_nhan_duyet_2')->default(false)->after('xac_nhan_duyet_1');
            $table->boolean('xac_nhan_duyet_3')->default(false)->after('xac_nhan_duyet_2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lich_hen', function (Blueprint $table) {
            $table->dropColumn(['xac_nhan_duyet_1', 'xac_nhan_duyet_2', 'xac_nhan_duyet_3']);
        });
    }
};
