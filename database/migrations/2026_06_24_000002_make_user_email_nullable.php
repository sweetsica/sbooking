<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Đăng nhập bằng "Tài khoản" (username) nên email không còn bắt buộc.
        // Unique vẫn giữ — MySQL cho phép nhiều NULL trên cột unique.
        DB::statement('ALTER TABLE users MODIFY email VARCHAR(255) NULL');
    }

    public function down(): void
    {
        // Lấp email rỗng trước khi đặt lại NOT NULL để không vỡ ràng buộc.
        DB::table('users')->whereNull('email')->update([
            'email' => DB::raw("CONCAT('user', id, '@local.invalid')"),
        ]);
        DB::statement('ALTER TABLE users MODIFY email VARCHAR(255) NOT NULL');
    }
};
