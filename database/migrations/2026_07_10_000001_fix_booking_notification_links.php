<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Thông báo booking trước đây trỏ vào trang SỬA (/sua-dat-phong/{id}) — đòi quyền
 * 'sua_booking' và không hiện lý do từ chối. Đổi sang trang CHI TIẾT chỉ đọc
 * (/xem-dat-phong/{id}) cho các thông báo đã lưu để chuông cũ cũng mở được.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Dùng token không có dấu '/' vì JSON lưu escape '/' -> '\/' (LIKE '%/sua-dat-phong/%'
        // sẽ không khớp). 'sua-dat-phong' chỉ xuất hiện trong URL link nên thay an toàn.
        DB::table('notifications')
            ->where('data', 'like', '%sua-dat-phong%')
            ->update([
                'data' => DB::raw("REPLACE(data, 'sua-dat-phong', 'xem-dat-phong')"),
            ]);
    }

    public function down(): void
    {
        DB::table('notifications')
            ->where('data', 'like', '%xem-dat-phong%')
            ->update([
                'data' => DB::raw("REPLACE(data, 'xem-dat-phong', 'sua-dat-phong')"),
            ]);
    }
};
