<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booking', function (Blueprint $table) {
            $table->boolean('lan_dau')->default(false)->after('ket_hop_medical');
            $table->string('khach_tang', 20)->default('khong')->after('lan_dau');
            $table->string('khach_tang_ghi_chu')->nullable()->after('khach_tang');
        });
    }

    public function down(): void
    {
        Schema::table('booking', function (Blueprint $table) {
            $table->dropColumn(['lan_dau', 'khach_tang', 'khach_tang_ghi_chu']);
        });
    }
};
