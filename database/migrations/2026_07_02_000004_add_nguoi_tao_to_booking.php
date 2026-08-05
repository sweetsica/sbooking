<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Thêm cột nguoi_tao_id vào booking — dùng để phân quyền "Sửa booking liên quan":
 * user chỉ được sửa booking do chính mình tạo (hoặc được gán làm BS/KTV/Sale).
 */
return new class extends Migration
{
    public function up(): void
    {
        // 2026-08-05: guard — cột có thể đã tồn tại từ migration 07_10_000002 (add cùng cột).
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
