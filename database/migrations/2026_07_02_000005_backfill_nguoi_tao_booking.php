<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Backfill nguoi_tao_id cho các booking cũ (tạo trước khi có cột này).
 * Tiêu chí ưu tiên: sale_id → bac_si_user_id → ktv_user_id.
 * Bản chất là "đoán tốt nhất có thể" — với booking không đủ info thì để NULL.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Cập nhật theo thứ tự ưu tiên. Mỗi UPDATE chỉ chạm rows chưa được set,
        // giảm khả năng ghi đè giữa các bước.
        DB::table('booking')->whereNull('nguoi_tao_id')->whereNotNull('sale_id')
            ->update(['nguoi_tao_id' => DB::raw('sale_id')]);

        DB::table('booking')->whereNull('nguoi_tao_id')->whereNotNull('bac_si_user_id')
            ->update(['nguoi_tao_id' => DB::raw('bac_si_user_id')]);

        DB::table('booking')->whereNull('nguoi_tao_id')->whereNotNull('ktv_user_id')
            ->update(['nguoi_tao_id' => DB::raw('ktv_user_id')]);
    }

    public function down(): void
    {
        // Không rollback data — down chỉ để migration hợp lệ. Muốn xóa hết
        // thì chạy migration cha (add_nguoi_tao_to_booking) rollback trước.
    }
};
