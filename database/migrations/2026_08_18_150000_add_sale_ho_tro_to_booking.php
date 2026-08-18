<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 2026-08-18 — Sale hỗ trợ tiếp đón: khi khách của sale A tới nhưng A bận,
 * admin gán thêm sale B hỗ trợ. A giữ giá trị sở hữu (owner), B chỉ hỗ trợ đón.
 *
 * Với 3 nguồn SA/BA/MKT_BR: tiep_don_user_id FIX CỨNG = creator (không admin sửa),
 * admin chỉ được thêm tiep_don_ho_tro_id. Nguồn khác: admin sửa cả 2 slot bình thường.
 *
 * Report cá nhân: tính 0.5 cho các case có sale hỗ trợ (0.5 + 0.5 chia đôi).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booking', function (Blueprint $t) {
            $t->foreignId('tiep_don_ho_tro_id')->nullable()->after('tiep_don_user_id')->constrained('users')->nullOnDelete();
            $t->foreignId('tiep_don_ho_tro_by')->nullable()->after('tiep_don_ho_tro_id')->constrained('users')->nullOnDelete();
            $t->timestamp('tiep_don_ho_tro_at')->nullable()->after('tiep_don_ho_tro_by');
        });
    }

    public function down(): void
    {
        Schema::table('booking', function (Blueprint $t) {
            $t->dropConstrainedForeignId('tiep_don_ho_tro_id');
            $t->dropConstrainedForeignId('tiep_don_ho_tro_by');
            $t->dropColumn('tiep_don_ho_tro_at');
        });
    }
};
