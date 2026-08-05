<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booking', function (Blueprint $table) {
            // 2026-08-05: hasColumn guard — cột có thể đã tồn tại từ nhánh khác merge trước.
            if (! Schema::hasColumn('booking', 'lan_dau')) {
                $table->boolean('lan_dau')->default(false)->after('ket_hop_medical');
            }
            if (! Schema::hasColumn('booking', 'khach_tang')) {
                $table->string('khach_tang', 20)->default('khong')->after('lan_dau');
            }
            if (! Schema::hasColumn('booking', 'khach_tang_ghi_chu')) {
                $table->string('khach_tang_ghi_chu')->nullable()->after('khach_tang');
            }
        });
    }

    public function down(): void
    {
        Schema::table('booking', function (Blueprint $table) {
            $table->dropColumn(['lan_dau', 'khach_tang', 'khach_tang_ghi_chu']);
        });
    }
};
