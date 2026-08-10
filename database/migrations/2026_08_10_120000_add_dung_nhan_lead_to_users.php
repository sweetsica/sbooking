<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 2026-08-10 — Cho phép sale tự tick "Dừng nhận lead" ở topbar → loại khỏi UPS bên scrm.
 * State lưu local để render UI đúng ngay lập tức + push sang scrm cùng lúc.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->boolean('dung_nhan_lead')->default(false)->after('chuc_danh');
            $t->timestamp('dung_nhan_lead_since')->nullable()->after('dung_nhan_lead');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->dropColumn(['dung_nhan_lead', 'dung_nhan_lead_since']);
        });
    }
};
