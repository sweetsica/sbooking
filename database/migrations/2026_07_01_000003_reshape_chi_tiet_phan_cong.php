<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Đổi lich_lam_viec_chi_tiet sang mô hình PHÂN CÔNG THEO NGÀY:
 * mỗi dòng = 1 ô trong lưới (phòng × ca Sáng/Chiều × ngày) → 1 bác sĩ / KTV.
 * (Bảng chưa có dữ liệu thật nên có thể đổi cột thoải mái.)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lich_lam_viec_chi_tiet', function (Blueprint $table) {
            $table->dropColumn(['gio_bat_dau', 'gio_ket_thuc', 'so_giuong', 'thoi_gian_phut']);
        });

        Schema::table('lich_lam_viec_chi_tiet', function (Blueprint $table) {
            $table->unsignedBigInteger('phong_id')->nullable()->after('doi_tuong_id');
            $table->date('ngay')->nullable()->after('phong_id');
            $table->string('ca', 10)->nullable()->after('ngay'); // sang | chieu
            $table->index(['phong_id', 'ngay', 'ca']);
        });
    }

    public function down(): void
    {
        Schema::table('lich_lam_viec_chi_tiet', function (Blueprint $table) {
            $table->dropIndex(['phong_id', 'ngay', 'ca']);
            $table->dropColumn(['phong_id', 'ngay', 'ca']);
        });

        Schema::table('lich_lam_viec_chi_tiet', function (Blueprint $table) {
            $table->time('gio_bat_dau')->nullable();
            $table->time('gio_ket_thuc')->nullable();
            $table->unsignedSmallInteger('so_giuong')->nullable();
            $table->unsignedSmallInteger('thoi_gian_phut')->nullable();
        });
    }
};
