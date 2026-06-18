<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Phòng trị liệu
        Schema::create('phong', function (Blueprint $table) {
            $table->id();
            $table->foreignId('co_so_id')->constrained('co_so')->cascadeOnDelete();
            $table->string('ten');
            $table->enum('loai', ['vip', 'cong_dong'])->default('cong_dong');
            $table->unsignedSmallInteger('so_slot_toi_da')->default(1); // số giường/slot tối đa
            $table->enum('trang_thai', ['hoat_dong', 'bao_tri'])->default('hoat_dong');
            $table->timestamps();
        });

        // Khung giờ phục vụ của phòng (mỗi khung 1 tiếng) — link tới form tạo booking
        Schema::create('khung_gio', function (Blueprint $table) {
            $table->id();
            $table->foreignId('phong_id')->constrained('phong')->cascadeOnDelete();
            $table->time('gio_bat_dau');   // vd 08:00
            $table->time('gio_ket_thuc');  // vd 09:00
            $table->unsignedSmallInteger('thu_tu')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('khung_gio');
        Schema::dropIfExists('phong');
    }
};
