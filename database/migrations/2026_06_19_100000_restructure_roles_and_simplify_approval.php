<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Bảng vai trò
        Schema::create('vai_tro', function (Blueprint $table) {
            $table->id();
            $table->string('ten');
            $table->string('ma')->unique();
            $table->timestamps();
        });

        // 2. Users: thêm vai_tro_id + is_tu_van + consultation fields
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('vai_tro_id')->nullable()->after('phong_ban_id')->constrained('vai_tro')->nullOnDelete();
            $table->boolean('is_tu_van')->default(false)->after('is_admin');
            $table->string('chuc_danh')->nullable()->after('name');
            $table->unsignedSmallInteger('thoi_gian_kham')->nullable()->after('is_tu_van');
            $table->time('gio_bat_dau')->nullable()->after('thoi_gian_kham');
            $table->time('gio_ket_thuc')->nullable()->after('gio_bat_dau');
        });

        // 3. PhanQuyen: thêm vai_tro_id
        Schema::table('phan_quyen', function (Blueprint $table) {
            $table->foreignId('vai_tro_id')->nullable()->after('phong_ban_id')->constrained('vai_tro')->nullOnDelete();
        });

        // 4. Phong: đổi loai enum → string
        Schema::table('phong', function (Blueprint $table) {
            $table->string('loai', 30)->default('kham')->change();
        });

        // 5. Booking: bỏ 3 cấp duyệt, thêm KTV + checkboxes, đổi bac_si FK
        Schema::table('booking', function (Blueprint $table) {
            $table->dropForeign(['bac_si_id']);
            $table->dropColumn(['bac_si_id', 'xac_nhan_duyet_1', 'xac_nhan_duyet_2', 'xac_nhan_duyet_3']);
        });
        Schema::table('booking', function (Blueprint $table) {
            $table->foreignId('bac_si_user_id')->nullable()->after('dich_vu_id')->constrained('users')->nullOnDelete();
            $table->foreignId('ktv_user_id')->nullable()->after('bac_si_user_id')->constrained('users')->nullOnDelete();
            $table->boolean('co_tu_van')->default(false)->after('ket_hop_medical');
            $table->boolean('co_kham_cls')->default(false)->after('co_tu_van');
            $table->boolean('da_duyet')->default(false)->after('trang_thai');
        });

        // 5. CaKham: đổi FK từ bac_si_tu_van sang users
        Schema::table('ca_kham', function (Blueprint $table) {
            $table->dropForeign(['bac_si_tu_van_id']);
            $table->dropColumn('bac_si_tu_van_id');
        });
        Schema::table('ca_kham', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->cascadeOnDelete();
        });

        // 6. LichHen: bỏ 3 cấp duyệt, đổi bac_si_tu_van FK
        Schema::table('lich_hen', function (Blueprint $table) {
            $table->dropForeign(['bac_si_tu_van_id']);
            $table->dropColumn(['bac_si_tu_van_id', 'xac_nhan_duyet_1', 'xac_nhan_duyet_2', 'xac_nhan_duyet_3']);
        });
        Schema::table('lich_hen', function (Blueprint $table) {
            $table->foreignId('bac_si_user_id')->nullable()->after('khach_hang_id')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('lich_hen', function (Blueprint $table) {
            $table->dropConstrainedForeignId('bac_si_user_id');
            $table->foreignId('bac_si_tu_van_id')->nullable()->after('khach_hang_id')->constrained('bac_si_tu_van')->nullOnDelete();
            $table->boolean('xac_nhan_duyet_1')->default(false);
            $table->boolean('xac_nhan_duyet_2')->default(false);
            $table->boolean('xac_nhan_duyet_3')->default(false);
        });

        Schema::table('booking', function (Blueprint $table) {
            $table->dropConstrainedForeignId('bac_si_user_id');
            $table->dropConstrainedForeignId('ktv_user_id');
            $table->dropColumn(['co_tu_van', 'co_kham_cls', 'da_duyet']);
            $table->foreignId('bac_si_id')->nullable()->constrained('bac_si')->nullOnDelete();
            $table->boolean('xac_nhan_duyet_1')->default(false);
            $table->boolean('xac_nhan_duyet_2')->default(false);
            $table->boolean('xac_nhan_duyet_3')->default(false);
        });

        Schema::table('phan_quyen', function (Blueprint $table) {
            $table->dropConstrainedForeignId('vai_tro_id');
        });

        Schema::table('ca_kham', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->foreignId('bac_si_tu_van_id')->nullable()->after('id')->constrained('bac_si_tu_van')->cascadeOnDelete();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('vai_tro_id');
            $table->dropColumn(['is_tu_van', 'chuc_danh', 'thoi_gian_kham', 'gio_bat_dau', 'gio_ket_thuc']);
        });

        Schema::dropIfExists('vai_tro');
    }
};
