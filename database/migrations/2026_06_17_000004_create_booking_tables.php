<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Khách hàng
        Schema::create('khach_hang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('co_so_id')->nullable()->constrained('co_so')->nullOnDelete();
            $table->string('ho_ten');
            $table->string('so_dien_thoai')->index();   // dùng kiểm tra trùng
            $table->string('email')->nullable();
            $table->timestamps();
        });

        // Booking / Lịch hẹn
        Schema::create('booking', function (Blueprint $table) {
            $table->id();
            $table->foreignId('co_so_id')->constrained('co_so')->cascadeOnDelete();
            $table->foreignId('khach_hang_id')->constrained('khach_hang')->cascadeOnDelete();
            $table->foreignId('phong_id')->nullable()->constrained('phong')->nullOnDelete();
            $table->foreignId('khung_gio_id')->nullable()->constrained('khung_gio')->nullOnDelete();
            $table->foreignId('dich_vu_id')->nullable()->constrained('dich_vu')->nullOnDelete();
            $table->foreignId('bac_si_id')->nullable()->constrained('bac_si')->nullOnDelete();
            $table->foreignId('sale_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('ngay_dat');
            $table->time('gio_thuc_hien')->nullable();    // phút fix 00 / 30
            $table->time('gio_ket_thuc')->nullable();
            $table->string('so_lieu_trinh')->nullable();  // vd 1/10
            $table->string('nguon')->nullable();          // Fanpage, Hotline...
            $table->boolean('ket_hop_medical')->default(false);
            $table->text('ghi_chu')->nullable();          // mục Ghi chú tách riêng
            $table->enum('trang_thai', ['cho_duyet', 'da_duyet'])->default('cho_duyet');
            $table->timestamps();
        });

        // Pivot booking <-> menu (chọn nhiều bằng ô tick)
        Schema::create('booking_menu', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('booking')->cascadeOnDelete();
            $table->foreignId('menu_id')->constrained('menu')->cascadeOnDelete();
            $table->unique(['booking_id', 'menu_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_menu');
        Schema::dropIfExists('booking');
        Schema::dropIfExists('khach_hang');
    }
};
