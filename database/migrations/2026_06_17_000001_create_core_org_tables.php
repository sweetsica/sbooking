<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Cơ sở (chi nhánh)
        Schema::create('co_so', function (Blueprint $table) {
            $table->id();
            $table->string('ten');
            $table->string('slug')->unique();      // vd: 59ntn
            $table->string('dia_chi')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // Phòng ban (Sales, KTV, Lễ tân, Quản trị...)
        Schema::create('phong_ban', function (Blueprint $table) {
            $table->id();
            $table->string('ten');
            $table->string('ma')->unique();        // vd: sales, ktv, admin
            $table->timestamps();
        });

        // Bổ sung cột cho users: thuộc cơ sở + phòng ban + admin toàn hệ thống
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('co_so_id')->nullable()->after('id')->constrained('co_so')->nullOnDelete();
            $table->foreignId('phong_ban_id')->nullable()->after('co_so_id')->constrained('phong_ban')->nullOnDelete();
            $table->boolean('is_admin')->default(false)->after('phong_ban_id'); // admin xuất hiện ở tất cả cơ sở
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('co_so_id');
            $table->dropConstrainedForeignId('phong_ban_id');
            $table->dropColumn('is_admin');
        });
        Schema::dropIfExists('phong_ban');
        Schema::dropIfExists('co_so');
    }
};
