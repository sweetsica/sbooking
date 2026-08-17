<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 2026-08-18 — 3 field "Sale tiếp đón" khi khách checkin, cùng timestamp/actor
 * bấm nút "Đã xong" để close booking & sync về datasource CRM.
 *
 * Mapping theo bảng "Nguyên tắc chia lại số" (nhánh Tư vấn viên):
 *   tinh_trang_checkin ∈ {checkin, doi_lich, huy_lich}
 *   ket_qua_sau_checkin ∈ {tham_kham, tu_van, mua_hang, khong_mua, hoan_thanh, huy_lich_tao_moi} (nullable khi huy_lich)
 *   phan_loai ∈ {follow, booking, close} (auto-map từ Kết quả, Sale tuỳ chỉnh được)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booking', function (Blueprint $t) {
            $t->string('tinh_trang_checkin', 20)->nullable()->after('trang_thai_khach');
            $t->string('ket_qua_sau_checkin', 30)->nullable()->after('tinh_trang_checkin');
            $t->string('phan_loai', 30)->nullable()->after('ket_qua_sau_checkin');
            $t->timestamp('checkin_hoan_tat_at')->nullable()->after('tiep_don_hoan_tat');
            $t->foreignId('checkin_hoan_tat_by')->nullable()->after('checkin_hoan_tat_at')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('booking', function (Blueprint $t) {
            $t->dropConstrainedForeignId('checkin_hoan_tat_by');
            $t->dropColumn(['tinh_trang_checkin', 'ket_qua_sau_checkin', 'phan_loai', 'checkin_hoan_tat_at']);
        });
    }
};
