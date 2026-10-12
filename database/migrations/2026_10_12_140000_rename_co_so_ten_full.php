<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * 2026-10-12 — Đổi tên hiển thị 3 cơ sở theo format đầy đủ "Cơ sở N: <địa chỉ>".
 * Khớp SHORT_NAMES data app simple-booking.blade.php.
 * Idempotent.
 */
return new class extends Migration {
    public function up(): void
    {
        // Chỉ đổi DN theo yêu cầu user; 2 cơ sở kia giữ nguyên tên hiện tại
        // (tránh đoán sai đường vì slug viết tắt mơ hồ).
        DB::table('co_so')
            ->where('slug', '11-15tdn')
            ->update(['ten' => 'Cơ sở 3: 11-15 Trần Đăng Ninh', 'updated_at' => now()]);
    }

    public function down(): void
    {
        // Không rollback tên cũ — tránh nhầm lẫn, cần thì user chạy migration khác.
    }
};
