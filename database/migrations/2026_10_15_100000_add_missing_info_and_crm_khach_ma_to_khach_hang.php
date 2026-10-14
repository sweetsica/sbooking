<?php

/**
 * Phase 6.27 (CRM): lead "thiếu info" — khách SA/BA/WI đến tận cơ sở nhưng chưa khai SĐT.
 * CRM push booking kèm so_dien_thoai=null + crm_khach_ma (vd KH-035-SA).
 *
 * Thay đổi:
 *  - khach_hang.so_dien_thoai: nullable (giữ index).
 *  - khach_hang.missing_info: flag khách thiếu SĐT (icon ⚠ bên UI).
 *  - khach_hang.crm_khach_ma: mã KH bên CRM, dùng làm key gộp sau (6.27.f).
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('khach_hang', function (Blueprint $table) {
            $table->string('so_dien_thoai')->nullable()->change();
            $table->boolean('missing_info')->default(false)->after('email');
            $table->string('crm_khach_ma', 40)->nullable()->after('missing_info')->index();
        });
    }

    public function down(): void
    {
        Schema::table('khach_hang', function (Blueprint $table) {
            $table->dropIndex(['crm_khach_ma']);
            $table->dropColumn(['missing_info', 'crm_khach_ma']);
            $table->string('so_dien_thoai')->nullable(false)->change();
        });
    }
};
