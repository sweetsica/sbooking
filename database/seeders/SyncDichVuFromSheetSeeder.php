<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Đồng bộ dich_vu HN theo sheet "DS NHÂN SỰ LMS" (bản mày gửi 2026-08-28):
 * - Deactive các DV bị gạch bỏ trong sheet.
 * - Update thoi_gian_phut cho các DV sai lệch.
 * - Rename các DV "sắp triển khai" → tên rõ ràng khi sheet đã chốt.
 *
 * Idempotent: chỉ update, không insert/delete → chạy lại an toàn.
 */
class SyncDichVuFromSheetSeeder extends Seeder
{
    /** ID các DV bị gạch bỏ trong sheet → active=false. */
    private const DEACTIVATE_IDS = [
        1,   // Thăm khám lâm sàng (trừ tim mạch) — đã tách thành 178-180
        3,   // Thực hiện lâm sàng — đã tách thành 178-180
        29,  // Gene2 me Plus
        30,  // Gene2 me
        31,  // TruAge
        32,  // Gene2 + Gene2 Plus + TruAge
        33,  // Return TruAge
        39,  // Y học Phương Đông (gốc) — đã tách thành 181-183 (30/45/60)
    ];

    /** ID => phút mới (từ sheet). Chỉ liệt kê các DV cần sửa. */
    private const DURATION_UPDATES = [
        34 => 60,  // EAQ (1 vùng)
        35 => 10,  // BJR (1 khớp)
        36 => 10,  // HA 1%/khớp
        37 => 10,  // HA 2%/khớp
        38 => 10,  // PRP/khớp
        40 => 15,  // DeepOxy & DetoxCell (xông)
        41 => 90,  // DeepOxy & DetoxCell (tổng hợp) — lock 2 phòng (chưa impl)
        42 => 15,  // STC Japan
        44 => 15,  // Recells
    ];

    /** ID => tên mới (rename khi sheet đã chốt). */
    private const RENAMES = [
        9 => 'Khám Da liễu (bác sĩ)',
    ];

    public function run(): void
    {
        $csHn = DB::table('co_so')->where('slug', '59ntn')->first();
        if (! $csHn) {
            $this->command?->warn('Không thấy cơ sở 59ntn — bỏ qua.');
            return;
        }
        $csId = $csHn->id;

        $deact = DB::table('dich_vu')
            ->where('co_so_id', $csId)
            ->whereIn('id', self::DEACTIVATE_IDS)
            ->update(['active' => false, 'updated_at' => now()]);

        $updDur = 0;
        foreach (self::DURATION_UPDATES as $id => $phut) {
            $updDur += DB::table('dich_vu')
                ->where('co_so_id', $csId)
                ->where('id', $id)
                ->update(['thoi_gian_phut' => $phut, 'updated_at' => now()]);
        }

        $updName = 0;
        foreach (self::RENAMES as $id => $tenMoi) {
            $updName += DB::table('dich_vu')
                ->where('co_so_id', $csId)
                ->where('id', $id)
                ->update(['ten' => $tenMoi, 'updated_at' => now()]);
        }

        $this->command?->info(sprintf(
            'Sync DV HN: deactive %d, sửa duration %d, rename %d',
            $deact,
            $updDur,
            $updName
        ));
    }
}
