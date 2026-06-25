<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('phong', function (Blueprint $table) {
            $table->enum('kieu_phong', ['phong_kham', 'phong_dich_vu'])->default('phong_kham')->after('ten');
            $table->unsignedSmallInteger('phut_moi_khach')->nullable()->after('so_slot_toi_da');
            $table->foreignId('ktv_mac_dinh_id')->nullable()->after('phut_moi_khach')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('phong', function (Blueprint $table) {
            $table->dropConstrainedForeignId('ktv_mac_dinh_id');
            $table->dropColumn(['kieu_phong', 'phut_moi_khach']);
        });
    }
};
