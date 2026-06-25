<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dich_vu', function (Blueprint $table) {
            $table->unsignedSmallInteger('thoi_gian_phut')->default(30)->after('ten');
            $table->enum('thuoc_nhom', ['tu_van', 'kham_ls', 'khac'])->default('khac')->after('thoi_gian_phut');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('nhan_tu_van')->default(false)->after('is_tu_van');
            $table->boolean('nhan_kham_ls')->default(false)->after('nhan_tu_van');
        });
    }

    public function down(): void
    {
        Schema::table('dich_vu', function (Blueprint $table) {
            $table->dropColumn(['thoi_gian_phut', 'thuoc_nhom']);
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['nhan_tu_van', 'nhan_kham_ls']);
        });
    }
};
