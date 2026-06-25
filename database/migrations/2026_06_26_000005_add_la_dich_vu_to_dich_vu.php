<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dich_vu', function (Blueprint $table) {
            // false = Thăm khám (hiện ở form đặt lịch phòng khám)
            // true  = Dịch vụ  (hiện ở form đặt lịch dịch vụ)
            $table->boolean('la_dich_vu')->default(false)->after('thuoc_nhom');
        });
    }

    public function down(): void
    {
        Schema::table('dich_vu', function (Blueprint $table) {
            $table->dropColumn('la_dich_vu');
        });
    }
};
