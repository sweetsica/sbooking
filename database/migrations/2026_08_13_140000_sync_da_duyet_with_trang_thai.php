<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * 2026-08-13 — Sync cột `da_duyet` theo `trang_thai` cho toàn bộ booking cũ.
 * Trước đây CRM push tạo booking `phong_kham` với trang_thai='da_duyet' + da_duyet=true.
 * Sau fix BookingApiController luôn trả cho_duyet, nhưng data cũ có 2 cột lệch:
 * trang_thai='cho_duyet' nhưng da_duyet=true (hoặc ngược lại).
 *
 * Nguyên tắc: trang_thai là source of truth (nhất quán với badge UI + button label).
 */
return new class extends Migration {
    public function up(): void
    {
        $fixed = DB::table('booking')
            ->where('trang_thai', 'da_duyet')
            ->where('da_duyet', false)
            ->update(['da_duyet' => true]);

        $fixed += DB::table('booking')
            ->where('trang_thai', '!=', 'da_duyet')
            ->where('da_duyet', true)
            ->update(['da_duyet' => false]);

        if (function_exists('logger')) logger()->info("Backfill da_duyet ↔ trang_thai: sync {$fixed} rows.");
    }

    public function down(): void
    {
        // no-op
    }
};
