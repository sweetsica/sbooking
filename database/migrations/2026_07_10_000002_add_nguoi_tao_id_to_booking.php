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
