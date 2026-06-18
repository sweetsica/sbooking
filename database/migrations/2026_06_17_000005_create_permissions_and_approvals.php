<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 3 trường xác nhận duyệt trên booking
        Schema::table('booking', function (Blueprint $table) {
            $table->boolean('xac_nhan_duyet_1')->default(false)->after('trang_thai');
            $table->boolean('xac_nhan_duyet_2')->default(false)->after('xac_nhan_duyet_1');
            $table->boolean('xac_nhan_duyet_3')->default(false)->after('xac_nhan_duyet_2');
        });

        // Phân quyền: phòng ban X được sửa trường Y (tồn tại dòng = được phép)
        Schema::create('phan_quyen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('phong_ban_id')->constrained('phong_ban')->cascadeOnDelete();
            $table->string('truong', 60);   // khóa trường, vd: ho_ten, xac_nhan_duyet_1
            $table->timestamps();
            $table->unique(['phong_ban_id', 'truong']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('phan_quyen');
        Schema::table('booking', function (Blueprint $table) {
            $table->dropColumn(['xac_nhan_duyet_1', 'xac_nhan_duyet_2', 'xac_nhan_duyet_3']);
        });
    }
};
