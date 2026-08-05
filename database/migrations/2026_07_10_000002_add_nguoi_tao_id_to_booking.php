<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Thêm cột người tạo booking để phân quyền xem:
 * - Có quyền 'xem_booking' → xem tất cả.
 * - Không có → chỉ xem booking do chính mình tạo (nguoi_tao_id = id user).
 * Booking cũ (trước migration) nguoi_tao_id = NULL → không thuộc về ai.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 2026-08-05: guard — cột có thể đã tồn tại từ migration 07_02_000004 (duplicate).
        if (Schema::hasColumn('booking', 'nguoi_tao_id')) return;

        Schema::table('booking', function (Blueprint $table) {
            $table->foreignId('nguoi_tao_id')->nullable()->after('sale_id')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('booking', function (Blueprint $table) {
            $table->dropConstrainedForeignId('nguoi_tao_id');
        });
    }
};
