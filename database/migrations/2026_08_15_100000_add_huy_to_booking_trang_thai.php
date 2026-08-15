<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * 2026-08-15 — Thêm 'huy' vào enum booking.trang_thai để nhận
 * auto-cancel push từ SCRM (rule 15' khách trễ) — pair với
 * BookingApiController::update chấp nhận trang_thai='huy'.
 *
 * Trước: enum('cho_duyet','da_duyet','da_xong','tu_choi').
 * Sau:  enum('cho_duyet','da_duyet','da_xong','tu_choi','huy').
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE booking MODIFY COLUMN trang_thai ENUM('cho_duyet','da_duyet','da_xong','tu_choi','huy') NOT NULL DEFAULT 'cho_duyet'");
    }

    public function down(): void
    {
        DB::statement("UPDATE booking SET trang_thai='tu_choi' WHERE trang_thai='huy'");
        DB::statement("ALTER TABLE booking MODIFY COLUMN trang_thai ENUM('cho_duyet','da_duyet','da_xong','tu_choi') NOT NULL DEFAULT 'cho_duyet'");
    }
};
