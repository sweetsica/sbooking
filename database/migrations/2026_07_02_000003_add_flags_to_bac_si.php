<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Danh mục bác sĩ (bac_si) là nguồn bác sĩ cho form đặt lịch phòng khám.
        // Bổ sung các cờ năng lực + giờ làm để form lọc theo loại dịch vụ.
        Schema::table('bac_si', function (Blueprint $table) {
            $table->boolean('xuat_hien_moi_co_so')->default(false)->after('co_so_id');
            $table->boolean('nhan_tu_van')->default(false)->after('xuat_hien_moi_co_so');
            $table->unsignedSmallInteger('phut_tu_van')->default(30)->after('nhan_tu_van');
            $table->boolean('nhan_kham_ls')->default(false)->after('phut_tu_van');
            $table->unsignedSmallInteger('phut_kham_ls')->default(5)->after('nhan_kham_ls');
            $table->string('gio_bat_dau', 5)->nullable()->after('chuc_danh');
            $table->string('gio_ket_thuc', 5)->nullable()->after('gio_bat_dau');
        });
    }

    public function down(): void
    {
        Schema::table('bac_si', function (Blueprint $table) {
            $table->dropColumn([
                'xuat_hien_moi_co_so', 'nhan_tu_van', 'phut_tu_van',
                'nhan_kham_ls', 'phut_kham_ls', 'gio_bat_dau', 'gio_ket_thuc',
            ]);
        });
    }
};
