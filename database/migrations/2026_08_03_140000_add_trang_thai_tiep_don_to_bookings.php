<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 6.25.C-sbooking (2026-08-03) — Nút "Đang tiếp đón / Hoàn tất" cho sale.
 *
 * trang_thai_tiep_don: null | 'dang_tiep_don' | 'hoan_tat'
 * tiep_don_user_id: sale được chia (auto từ scrm UPS) — controls quyền tick nút.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booking', function (Blueprint $t) {
            $t->string('trang_thai_tiep_don', 20)->nullable()->after('trang_thai_khach');
            $t->foreignId('tiep_don_user_id')->nullable()->after('trang_thai_tiep_don')
                ->constrained('users')->nullOnDelete();
            $t->dateTime('tiep_don_bat_dau')->nullable()->after('tiep_don_user_id');
            $t->dateTime('tiep_don_hoan_tat')->nullable()->after('tiep_don_bat_dau');
        });
    }

    public function down(): void
    {
        Schema::table('booking', function (Blueprint $t) {
            $t->dropForeign(['tiep_don_user_id']);
            $t->dropColumn(['trang_thai_tiep_don', 'tiep_don_user_id', 'tiep_don_bat_dau', 'tiep_don_hoan_tat']);
        });
    }
};
