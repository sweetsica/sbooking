<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * 2026-10-05 — Chuẩn hoá tên dịch vụ:
 *   1. "TruAge" → "TrueAge" (sai chính tả, user yêu cầu).
 *      Áp cả 3 variant: TruAge, Gene2 + Gene2 Plus + TruAge, Return TruAge.
 *   2. Thêm 2 dịch vụ "MetaBoost 150" và "MetaBoost 300" cho các cơ sở có phòng Metaboost.
 *      Thời lượng 120 phút (mặc định phòng). Link với tất cả phòng Metaboost cùng cơ sở.
 *   3. Link BS Ngà (Ngô Thị Ngà) vào các phòng Metaboost cùng cơ sở nếu chưa có —
 *      để admin thấy BS Ngà trong dropdown khi duyệt lịch MetaBoost.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1) Rename TruAge → TrueAge (giữ nguyên các từ khác trong tên).
        DB::statement("
            UPDATE dich_vu
            SET ten = REPLACE(ten, 'TruAge', 'TrueAge'),
                updated_at = NOW()
            WHERE ten LIKE '%TruAge%' AND ten NOT LIKE '%TrueAge%'
        ");

        // 2) Thêm MetaBoost 150 / 300 cho mỗi cơ sở có phòng Metaboost.
        //    (Nếu cơ sở không có phòng Metaboost → bỏ qua, không insert dv treo.)
        $coSoIds = DB::table('phong')
            ->where('ten', 'like', '%Metaboost%')
            ->distinct()->pluck('co_so_id');

        $bsNgaByCoSo = DB::table('bac_si')
            ->where('ten', 'like', '%Ngô Thị Ngà%')
            ->get(['id', 'co_so_id'])
            ->keyBy('co_so_id');

        foreach ($coSoIds as $coSoId) {
            $phongIds = DB::table('phong')
                ->where('co_so_id', $coSoId)
                ->where('ten', 'like', '%Metaboost%')
                ->pluck('id');

            foreach (['MetaBoost 150', 'MetaBoost 300'] as $ten) {
                // Idempotent: chỉ insert nếu chưa có cùng co_so + ten.
                $existingId = DB::table('dich_vu')
                    ->where('co_so_id', $coSoId)->where('ten', $ten)->value('id');

                if (! $existingId) {
                    $existingId = DB::table('dich_vu')->insertGetId([
                        'co_so_id'        => $coSoId,
                        'ten'             => $ten,
                        'thoi_gian_phut'  => 120,
                        'thuoc_nhom'      => 'khac',
                        'la_dich_vu'      => 1,
                        'khong_can_phong' => 0,
                        'active'          => 1,
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ]);
                }

                // Link dv ↔ phòng Metaboost cùng cơ sở (idempotent).
                foreach ($phongIds as $phongId) {
                    DB::table('dich_vu_phong')->updateOrInsert(
                        ['dich_vu_id' => $existingId, 'phong_id' => $phongId],
                        ['created_at' => now(), 'updated_at' => now()]
                    );
                }
            }

            // 3) Link BS Ngà vào các phòng Metaboost cùng cơ sở (idempotent).
            $bsNga = $bsNgaByCoSo[$coSoId] ?? null;
            if ($bsNga) {
                foreach ($phongIds as $phongId) {
                    DB::table('phong_bac_si')->updateOrInsert(
                        ['phong_id' => $phongId, 'bac_si_id' => $bsNga->id],
                        ['created_at' => now(), 'updated_at' => now()]
                    );
                }
            }
        }
    }

    public function down(): void
    {
        // Rollback: TrueAge → TruAge. KHÔNG xoá MetaBoost (có thể đã được book).
        DB::statement("
            UPDATE dich_vu
            SET ten = REPLACE(ten, 'TrueAge', 'TruAge'),
                updated_at = NOW()
            WHERE ten LIKE '%TrueAge%'
        ");
    }
};
