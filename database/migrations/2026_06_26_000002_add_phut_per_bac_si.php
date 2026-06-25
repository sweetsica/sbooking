<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedSmallInteger('phut_tu_van')->default(30)->after('nhan_tu_van');
            $table->unsignedSmallInteger('phut_kham_ls')->default(5)->after('nhan_kham_ls');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phut_tu_van', 'phut_kham_ls']);
        });
    }
};
