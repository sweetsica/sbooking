<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phản hồi sau khi sử dụng dịch vụ:
 *  - trang_thai_khach: dung_gio | muon | huy (nullable)
 *  - booking_phan_hoi: list ghi chú phản hồi (nhiều dòng, kèm tác giả + thời gian).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booking', function (Blueprint $table) {
            $table->string('trang_thai_khach', 20)->nullable()->after('trang_thai');
        });

        Schema::create('booking_phan_hoi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('booking')->cascadeOnDelete();
            $table->text('noi_dung');
            $table->foreignId('nguoi_dung_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['booking_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_phan_hoi');
        Schema::table('booking', function (Blueprint $table) {
            $table->dropColumn('trang_thai_khach');
        });
    }
};
