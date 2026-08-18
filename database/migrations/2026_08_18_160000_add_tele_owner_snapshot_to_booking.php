<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 2026-08-18 — Snapshot Tele phụ trách phase 2 từ SCRM (lead.owner_id sau khi CM chia).
 * Hiển thị trong modal Duyệt lịch hẹn để admin cơ sở biết ai là tele trực tiếp
 * (khác creator ở nguồn MKT/BDM/BOD/Walk-in).
 *
 * Chỉ snapshot — không FK, vì user_id là của SCRM (không có bảng users tương ứng ở sbooking).
 * tele_owner_id lưu SCRM user id để trace về (nullable — booking cũ + SA/BA/MKT_BR có thể null).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booking', function (Blueprint $t) {
            $t->unsignedBigInteger('tele_owner_id')->nullable()->after('nguoi_tao_id');
            $t->string('tele_owner_name', 150)->nullable()->after('tele_owner_id');
        });
    }

    public function down(): void
    {
        Schema::table('booking', function (Blueprint $t) {
            $t->dropColumn(['tele_owner_id', 'tele_owner_name']);
        });
    }
};
