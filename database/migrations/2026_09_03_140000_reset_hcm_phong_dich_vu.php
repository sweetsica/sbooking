<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Reset toàn bộ phòng + dịch vụ HCM (co_so_id=2) theo bản chốt PKD 2026-09-03.
 * Kèm tạo pivot `dich_vu_bac_si` (map dv ↔ nhân sự thực hiện).
 *
 * Đã CHỐT với user: được xóa booking HCM cũ (cả done lẫn chưa done), seed lại từ scratch.
 * Dữ liệu HN (co_so_id=1) và ĐN không đụng.
 *
 * Sau khi chạy migration này → chạy 4 seeder (theo thứ tự):
 *   BacSiKtvDdSeeder → HcmPhongResetSeeder → HcmDichVuResetSeeder → HcmDichVuBacSiSeeder
 */
return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('dich_vu_bac_si')) {
            Schema::create('dich_vu_bac_si', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('dich_vu_id');
                $t->unsignedBigInteger('bac_si_id');
                $t->timestamps();
                $t->unique(['dich_vu_id', 'bac_si_id']);
                $t->index('dich_vu_id');
                $t->index('bac_si_id');
            });
        }

        $hcmPhongIds = DB::table('phong')->where('co_so_id', 2)->pluck('id')->all();
        $hcmDvIds    = DB::table('dich_vu')->where('co_so_id', 2)->pluck('id')->all();

        Schema::disableForeignKeyConstraints();

        try {
            // 1. Xóa booking HCM (cả done). booking → cascade xoá booking_status, ...
            DB::table('booking')->where('co_so_id', 2)->delete();

            // 2. Xóa pivot dv_phong & phong_bac_si của HCM
            if (! empty($hcmPhongIds)) {
                DB::table('phong_bac_si')->whereIn('phong_id', $hcmPhongIds)->delete();
                DB::table('dich_vu_phong')->whereIn('phong_id', $hcmPhongIds)->delete();
            }
            if (! empty($hcmDvIds)) {
                DB::table('dich_vu_phong')->whereIn('dich_vu_id', $hcmDvIds)->delete();
            }

            // 3. Xóa lịch hẹn / phân công / ngày nghỉ liên quan phòng HCM (nếu bảng tồn tại)
            if (Schema::hasTable('chi_tiet_phan_cong') && ! empty($hcmPhongIds)) {
                DB::table('chi_tiet_phan_cong')->whereIn('phong_id', $hcmPhongIds)->delete();
            }
            if (Schema::hasTable('ngay_nghi') && ! empty($hcmPhongIds)) {
                DB::table('ngay_nghi')->where('loai', 'phong')->whereIn('doi_tuong_id', $hcmPhongIds)->delete();
            }

            // 4. Xóa phòng + dịch vụ HCM
            DB::table('phong')->where('co_so_id', 2)->delete();
            DB::table('dich_vu')->where('co_so_id', 2)->delete();

            // 5. Reset AUTO_INCREMENT không cần thiết — id mới nối tiếp là OK.
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }

    public function down(): void
    {
        // KHÔNG rollback data — restore từ backup nếu cần.
        Schema::dropIfExists('dich_vu_bac_si');
    }
};
