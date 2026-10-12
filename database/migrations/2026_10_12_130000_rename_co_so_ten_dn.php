<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * 2026-10-12 — Đổi tên hiển thị cơ sở DN theo địa chỉ mới "Số 11-15 Trần Đăng Ninh".
 * Dropdown chuyển cơ sở trên topnav render từ co_so.ten → trước là "Lô 2+3 TĐN"
 * (đã lệch sau rename slug). Chuẩn hoá: "11-15 TĐN" khớp 1-1 với slug.
 * Idempotent.
 */
return new class extends Migration {
    public function up(): void
    {
        DB::table('co_so')
            ->where('slug', '11-15tdn')
            ->update(['ten' => '11-15 TĐN', 'updated_at' => now()]);
    }

    public function down(): void
    {
        DB::table('co_so')
            ->where('slug', '11-15tdn')
            ->update(['ten' => 'Lô 2+3 TĐN', 'updated_at' => now()]);
    }
};
