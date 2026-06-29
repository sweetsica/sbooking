<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Giờ làm việc + thời gian ca đặt mặc định của cơ sở (phòng khám)
        Schema::table('co_so', function (Blueprint $table) {
            $table->time('gio_mo_cua')->nullable()->after('dia_chi');
            $table->time('gio_dong_cua')->nullable()->after('gio_mo_cua');
            $table->unsignedSmallInteger('thoi_gian_ca_phut')->nullable()->after('gio_dong_cua');
        });

        // Bản lịch làm việc theo tháng (mỗi cơ sở × mỗi tháng)
        Schema::create('lich_lam_viec', function (Blueprint $table) {
            $table->id();
            $table->foreignId('co_so_id')->constrained('co_so')->cascadeOnDelete();
            $table->date('thang'); // ngày đầu tháng, vd 2026-07-01
            $table->enum('trang_thai', ['nhap', 'cho_duyet', 'da_duyet', 'tu_choi'])->default('nhap');
            $table->foreignId('nguoi_tao_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('nguoi_duyet_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('ly_do_tu_choi')->nullable();
            $table->string('file_goc')->nullable(); // đường dẫn file Excel gốc
            $table->text('ghi_chu')->nullable();
            $table->timestamp('applied_at')->nullable();
            $table->timestamps();

            $table->unique(['co_so_id', 'thang']);
        });

        // Chi tiết snapshot từng dòng của 1 bản lịch
        Schema::create('lich_lam_viec_chi_tiet', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lich_lam_viec_id')->constrained('lich_lam_viec')->cascadeOnDelete();
            // gio_phong_kham | gio_bac_si | gio_ktv | giuong | ca_dat
            $table->string('loai', 30);
            $table->unsignedBigInteger('doi_tuong_id')->nullable(); // user_id (BS/KTV) hoặc phong_id
            $table->string('ten')->nullable();                      // nhãn snapshot để hiển thị
            $table->time('gio_bat_dau')->nullable();
            $table->time('gio_ket_thuc')->nullable();
            $table->unsignedSmallInteger('so_giuong')->nullable();
            $table->unsignedSmallInteger('thoi_gian_phut')->nullable();
            $table->timestamps();

            $table->index(['lich_lam_viec_id', 'loai']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lich_lam_viec_chi_tiet');
        Schema::dropIfExists('lich_lam_viec');
        Schema::table('co_so', function (Blueprint $table) {
            $table->dropColumn(['gio_mo_cua', 'gio_dong_cua', 'thoi_gian_ca_phut']);
        });
    }
};
