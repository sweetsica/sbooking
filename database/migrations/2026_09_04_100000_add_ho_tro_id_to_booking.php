<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 2026-09-04 — Thêm cột booking.ho_tro_id (KTV/DD hỗ trợ ca).
 *
 * Design v3 (chốt 2026-09-04): một số dv cần 1 nhân sự chính (BS hoặc KTV) + 1 nhân sự hỗ trợ.
 * Hỗ trợ nullable (dv không cần thì để trống). Cả 2 cột FK cùng bảng `bac_si`.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('booking', function (Blueprint $t) {
            $t->foreignId('ho_tro_id')->nullable()->after('bac_si_id')
                ->constrained('bac_si')->nullOnDelete()
                ->comment('KTV/DD hỗ trợ ca (nullable, dv không cần thì để trống)');
        });
    }

    public function down(): void
    {
        Schema::table('booking', function (Blueprint $t) {
            $t->dropConstrainedForeignId('ho_tro_id');
        });
    }
};
