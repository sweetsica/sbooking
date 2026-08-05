<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Backfill nguoi_tao_id cho các booking cũ (tạo trước khi có cột này).
 * 2026-08-05 fix: dùng Schema::hasColumn guard vì bac_si_user_id / ktv_user_id là tên cột
 * từ nhánh cũ — schema hiện tại chỉ có bac_si_id (FK sang bảng bac_si, không phải user) → skip.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('booking', 'sale_id')) {
            DB::table('booking')->whereNull('nguoi_tao_id')->whereNotNull('sale_id')
                ->update(['nguoi_tao_id' => DB::raw('sale_id')]);
        }
        if (Schema::hasColumn('booking', 'bac_si_user_id')) {
            DB::table('booking')->whereNull('nguoi_tao_id')->whereNotNull('bac_si_user_id')
                ->update(['nguoi_tao_id' => DB::raw('bac_si_user_id')]);
        }
        if (Schema::hasColumn('booking', 'ktv_user_id')) {
            DB::table('booking')->whereNull('nguoi_tao_id')->whereNotNull('ktv_user_id')
                ->update(['nguoi_tao_id' => DB::raw('ktv_user_id')]);
        }
    }

    public function down(): void
    {
        // Không rollback data — down chỉ để migration hợp lệ. Muốn xóa hết
        // thì chạy migration cha (add_nguoi_tao_to_booking) rollback trước.
    }
};
