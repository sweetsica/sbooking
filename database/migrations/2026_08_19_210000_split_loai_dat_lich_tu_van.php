<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * 2026-08-19 — Phase B: tách booking.loai_dat_lich từ 2 giá trị
 *   ('phong_kham','dich_vu') thành 3 ('kham_ls','tu_van','dich_vu').
 *
 * Trước đây kham_ls + tu_van gộp = 'phong_kham', chỉ phân biệt qua
 *   dich_vu.thuoc_nhom. Bên SCRM đã tách 3 loại — sbooking bắt kịp.
 *
 * Bước:
 *   1. Nới enum tạm sang 4 giá trị (thêm kham_ls, tu_van).
 *   2. Backfill: UPDATE booking loai_dat_lich=kham_ls|tu_van tuỳ dich_vu.thuoc_nhom.
 *      Booking không map được DV (dich_vu_id NULL) → default kham_ls.
 *   3. Siết enum về đúng 3 giá trị.
 *
 * Rollback: gộp kham_ls+tu_van về phong_kham. Best-effort.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Bước 1: mở rộng enum lên 4 giá trị.
        DB::statement("ALTER TABLE booking MODIFY COLUMN loai_dat_lich ENUM('phong_kham','dich_vu','kham_ls','tu_van') NOT NULL DEFAULT 'kham_ls'");

        // Bước 2: backfill từ phong_kham → kham_ls hoặc tu_van theo thuoc_nhom.
        //   dich_vu.thuoc_nhom = 'tu_van' → tu_van
        //   còn lại → kham_ls
        $tuVanCnt = DB::update("
            UPDATE booking b
            JOIN dich_vu dv ON dv.id = b.dich_vu_id
            SET b.loai_dat_lich = 'tu_van'
            WHERE b.loai_dat_lich = 'phong_kham' AND dv.thuoc_nhom = 'tu_van'
        ");
        $khamLsCnt = DB::update("
            UPDATE booking
            SET loai_dat_lich = 'kham_ls'
            WHERE loai_dat_lich = 'phong_kham'
        ");

        if (app()->runningInConsole()) {
            echo "  → backfill: tu_van={$tuVanCnt}, kham_ls={$khamLsCnt}\n";
        }

        // Bước 3: siết enum còn đúng 3 giá trị.
        DB::statement("ALTER TABLE booking MODIFY COLUMN loai_dat_lich ENUM('kham_ls','tu_van','dich_vu') NOT NULL DEFAULT 'kham_ls'");
    }

    public function down(): void
    {
        // Best-effort: mở enum, gộp lại, siết về cũ.
        DB::statement("ALTER TABLE booking MODIFY COLUMN loai_dat_lich ENUM('phong_kham','dich_vu','kham_ls','tu_van') NOT NULL DEFAULT 'phong_kham'");
        DB::update("UPDATE booking SET loai_dat_lich='phong_kham' WHERE loai_dat_lich IN ('kham_ls','tu_van')");
        DB::statement("ALTER TABLE booking MODIFY COLUMN loai_dat_lich ENUM('phong_kham','dich_vu') NOT NULL DEFAULT 'phong_kham'");
    }
};
