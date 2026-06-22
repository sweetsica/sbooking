<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Phân quyền đổi từ Phòng ban → Vai trò. Xóa sạch dữ liệu cũ,
     * người dùng tick lại từ UI cho đúng vai trò.
     */
    public function up(): void
    {
        DB::table('phan_quyen')->delete();
    }

    public function down(): void
    {
        // không phục hồi
    }
};
