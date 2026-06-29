<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bảng "ngày nghỉ" — đóng cửa / nghỉ theo khoảng ngày, cho 4 cấp đối tượng:
     *  - co_so  : đóng cả cơ sở (doi_tuong_id = null)
     *  - phong  : đóng 1 phòng (khám hoặc dịch vụ) (doi_tuong_id = phong.id)
     *  - bac_si : bác sĩ nghỉ (doi_tuong_id = users.id)
     *  - ktv    : KTV nghỉ    (doi_tuong_id = users.id)
     *
     * Ca: ca_ngay (cả ngày) | sang | chieu — khớp 2 ca cố định của lịch làm việc.
     * Hành vi (chốt với user): co_so/phong = CHẶN CỨNG khi đặt; bac_si/ktv = CẢNH BÁO MỀM.
     */
    public function up(): void
    {
        Schema::create('ngay_nghi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('co_so_id')->constrained('co_so')->cascadeOnDelete();
            $table->string('loai'); // co_so | phong | bac_si | ktv
            $table->unsignedBigInteger('doi_tuong_id')->nullable(); // phong.id hoặc users.id; null khi loai=co_so
            $table->date('tu_ngay');
            $table->date('den_ngay');
            $table->string('ca')->default('ca_ngay'); // ca_ngay | sang | chieu
            $table->string('ly_do')->nullable();
            $table->foreignId('nguoi_tao_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['co_so_id', 'loai', 'tu_ngay', 'den_ngay']);
            $table->index(['co_so_id', 'loai', 'doi_tuong_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ngay_nghi');
    }
};
