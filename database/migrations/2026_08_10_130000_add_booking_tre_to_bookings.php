<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 2026-08-10 — Cột `booking_tre` cho phép Admin (BO / cơ sở / vận hành) đánh dấu booking quá muộn.
 * Filter mới "Chỉ booking trễ" trong danh sách; không log lịch sử.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('booking', function (Blueprint $t) {
            $t->boolean('booking_tre')->default(false)->after('trang_thai');
            $t->index(['co_so_id', 'booking_tre'], 'idx_booking_tre');
        });
    }

    public function down(): void
    {
        Schema::table('booking', function (Blueprint $t) {
            $t->dropIndex('idx_booking_tre');
            $t->dropColumn('booking_tre');
        });
    }
};
