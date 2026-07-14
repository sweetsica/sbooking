<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booking', function (Blueprint $table) {
            $table->unsignedTinyInteger('so_luong_lo')->nullable()->after('so_lieu_trinh');
            $table->string('dung_tich_lo', 10)->nullable()->after('so_luong_lo');
        });
    }

    public function down(): void
    {
        Schema::table('booking', function (Blueprint $table) {
            $table->dropColumn(['so_luong_lo', 'dung_tich_lo']);
        });
    }
};
