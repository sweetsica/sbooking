<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE booking MODIFY COLUMN trang_thai ENUM('cho_duyet', 'da_duyet', 'da_xong') NOT NULL DEFAULT 'cho_duyet'");
    }

    public function down(): void
    {
        // Đưa các bản ghi 'da_xong' về 'da_duyet' trước khi thu hẹp enum
        DB::statement("UPDATE booking SET trang_thai = 'da_duyet' WHERE trang_thai = 'da_xong'");
        DB::statement("ALTER TABLE booking MODIFY COLUMN trang_thai ENUM('cho_duyet', 'da_duyet') NOT NULL DEFAULT 'cho_duyet'");
    }
};
