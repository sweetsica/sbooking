<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Thêm trường "Phản hồi từ khách" — ghi nhận sau khi lịch hẹn Đã xong.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booking', function (Blueprint $table) {
            $table->text('phan_hoi_khach')->nullable()->after('ly_do_tu_choi');
        });
    }

    public function down(): void
    {
        Schema::table('booking', function (Blueprint $table) {
            $table->dropColumn('phan_hoi_khach');
        });
    }
};
