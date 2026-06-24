<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Thêm trạng thái "tu_choi" (từ chối duyệt) + lý do từ chối.
        DB::statement("ALTER TABLE booking MODIFY COLUMN trang_thai ENUM('cho_duyet', 'da_duyet', 'da_xong', 'tu_choi') NOT NULL DEFAULT 'cho_duyet'");

        Schema::table('booking', function (Blueprint $table) {
            $table->text('ly_do_tu_choi')->nullable()->after('trang_thai');
        });
    }

    public function down(): void
    {
        // Đưa đơn bị từ chối về chờ duyệt trước khi thu hẹp enum
        DB::statement("UPDATE booking SET trang_thai = 'cho_duyet' WHERE trang_thai = 'tu_choi'");
        DB::statement("ALTER TABLE booking MODIFY COLUMN trang_thai ENUM('cho_duyet', 'da_duyet', 'da_xong') NOT NULL DEFAULT 'cho_duyet'");

        Schema::table('booking', function (Blueprint $table) {
            $table->dropColumn('ly_do_tu_choi');
        });
    }
};
