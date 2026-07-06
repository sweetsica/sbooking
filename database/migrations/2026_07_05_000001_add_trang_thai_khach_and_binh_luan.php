<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Trạng thái khách (đã tới / tới trễ / hủy) — độc lập với trang_thai booking.
        Schema::table('booking', function (Blueprint $table) {
            $table->string('trang_thai_khach', 20)->nullable()->after('trang_thai');
        });

        // Bình luận nội bộ "sau dịch vụ" (nhiều người trao đổi trên 1 booking).
        Schema::create('booking_binh_luan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('booking')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('noi_dung');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_binh_luan');
        Schema::table('booking', function (Blueprint $table) {
            $table->dropColumn('trang_thai_khach');
        });
    }
};
