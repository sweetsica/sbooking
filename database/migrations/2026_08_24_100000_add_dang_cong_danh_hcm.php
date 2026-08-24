<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * 2026-08-24 — Thêm BS Đặng Công Danh (y học cổ truyền) vào cơ sở HCM (207 NVT).
 * Prod hiện chưa có → dropdown Bác sĩ rỗng khi tạo booking dịch vụ Y học Phương Đông.
 * Idempotent qua unique (co_so_id, ten).
 */
return new class extends Migration
{
    public function up(): void
    {
        $cs = DB::table('co_so')->where('slug', '207nvt')->first();
        if (! $cs) {
            if (app()->runningInConsole()) {
                echo "  → Bỏ qua: không tìm thấy cơ sở '207nvt'.\n";
            }
            return;
        }

        $now = now();
        $ten = 'Đặng Công Danh';
        $data = [
            'co_so_id'      => $cs->id,
            'ten'           => $ten,
            'chuc_danh'     => 'Bác sĩ chuyên khoa y học cổ truyền',
            'nhan_tu_van'   => 1,
            'phut_tu_van'   => 30,
            'nhan_kham_ls'  => 0,
            'phut_kham_ls'  => 5,
            'gio_bat_dau'   => '08:00',
            'gio_ket_thuc'  => '17:00',
            'active'        => 1,
            'updated_at'    => $now,
        ];

        $existing = DB::table('bac_si')->where('co_so_id', $cs->id)->where('ten', $ten)->first();
        if ($existing) {
            DB::table('bac_si')->where('id', $existing->id)->update($data);
            $msg = "cập nhật BS#{$existing->id}";
        } else {
            $id = DB::table('bac_si')->insertGetId($data + ['created_at' => $now]);
            $msg = "tạo mới BS#{$id}";
        }

        if (app()->runningInConsole()) {
            echo "  → BS Đặng Công Danh (HCM 207nvt): {$msg}\n";
        }
    }

    public function down(): void
    {
        // No-op: không xoá BS (có thể đã có booking gắn tên).
    }
};
