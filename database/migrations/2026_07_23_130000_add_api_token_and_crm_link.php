<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 6.21 — Bridge Booking → CRM lara-scrm:
 *  - users.api_token: shared secret theo user, dùng khi push tới CRM.
 *  - booking.crm_khach_ma: mã KH bên CRM (VD KH-033-REF) — capture từ URL create form.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('api_token', 80)->nullable()->unique()->after('password');
        });
        Schema::table('booking', function (Blueprint $table) {
            $table->string('crm_khach_ma', 40)->nullable()->index()->after('nguon');
        });
    }

    public function down(): void
    {
        Schema::table('booking', function (Blueprint $table) {
            $table->dropColumn('crm_khach_ma');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('api_token');
        });
    }
};
