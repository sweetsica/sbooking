<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Đợt B1:
// 1. HN + HCM: deactivate DV "Y học Phương Đông" gộp, tách thành 3 DV riêng 30'/45'/60'.
// 2. HCM 207 (co_so=2): insert Phòng X Quang + 4 DV lâm sàng mới, active/deactivate
//    theo mapping phòng đã chốt (Q&A 2026-08-24).
//
// DV 40 (DeepOxy Xông pairing giới) + DV 41 (DeepOxy Tổng hợp = combo Xông + YHPĐ, lock 2 phòng)
// vẫn dời Đợt C (cần schema multi-room booking).
return new class extends Migration {
    public function up(): void
    {
        // ============================================================
        // 1. YHPĐ — cả HN (co_so=1) và HCM (co_so=2)
        // ============================================================
        // Deactivate id 39 (HN) + id 83 (HCM) — "Y học Phương Đông" gộp cũ.
        DB::table('dich_vu')
            ->whereIn('id', [39, 83])
            ->update(['active' => 0, 'updated_at' => now()]);

        // Insert 3 DV YHPĐ mới cho cả HN + HCM (2 cơ sở × 3 = 6 rows nếu chưa có)
        $yhpdVariants = [
            ['ten' => 'Y học Phương Đông 30\'', 'phut' => 30],
            ['ten' => 'Y học Phương Đông 45\'', 'phut' => 45],
            ['ten' => 'Y học Phương Đông 60\'', 'phut' => 60],
        ];
        foreach ([1, 2] as $csId) {
            foreach ($yhpdVariants as $dv) {
                $exists = DB::table('dich_vu')
                    ->where('co_so_id', $csId)
                    ->where('ten', $dv['ten'])
                    ->exists();
                if (! $exists) {
                    DB::table('dich_vu')->insert([
                        'co_so_id'       => $csId,
                        'ten'            => $dv['ten'],
                        'thoi_gian_phut' => $dv['phut'],
                        'thuoc_nhom'     => 'khac',
                        'la_dich_vu'     => 1,
                        'active'         => 1,
                        'created_at'     => now(),
                        'updated_at'     => now(),
                    ]);
                }
            }
        }

        // ============================================================
        // 2. HCM 207 (co_so=2) — Insert Phòng X Quang
        // ============================================================
        $exists = DB::table('phong')
            ->where('co_so_id', 2)
            ->where('ten', 'Phòng X Quang')
            ->exists();
        if (! $exists) {
            DB::table('phong')->insert([
                'co_so_id'          => 2,
                'ten'               => 'Phòng X Quang',
                'kieu_phong'        => 'phong_kham',
                'so_slot_toi_da'    => 1,
                'phut_moi_khach'    => 15,
                'duoc_dat_tu_van'   => 0,
                'trang_thai'        => 'hoat_dong',
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
        }

        // ============================================================
        // 3. HCM (co_so=2) — Insert 3 DV "Thực hiện lâm sàng" mới (như HN)
        // ============================================================
        $newLamSangHcm = [
            ['ten' => 'Thực hiện lâm sàng (lấy máu)',   'phut' => 5],
            ['ten' => 'Thực hiện lâm sàng (siêu âm)',   'phut' => 25],
            ['ten' => 'Thực hiện lâm sàng (Xquang)',    'phut' => 15],
        ];
        foreach ($newLamSangHcm as $dv) {
            $exists = DB::table('dich_vu')
                ->where('co_so_id', 2)
                ->where('ten', $dv['ten'])
                ->exists();
            if (! $exists) {
                DB::table('dich_vu')->insert([
                    'co_so_id'       => 2,
                    'ten'            => $dv['ten'],
                    'thoi_gian_phut' => $dv['phut'],
                    'thuoc_nhom'     => 'kham_ls',
                    'la_dich_vu'     => 0,
                    'active'         => 1,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);
            }
        }

        // ============================================================
        // 4. HCM — Deactivate DV không có phòng phù hợp
        // ============================================================
        // id 45 (Thăm khám lâm sàng cũ), 47 (Thực hiện lâm sàng cũ),
        // 53 (Khám Da liễu — HCM chưa có Phòng da),
        // 73-77 (Gene/TruAge), 79-82 (BJR/HA/PRP — HCM không có Phòng Thủ thuật),
        // 84 (DeepOxy Xông — không có Phòng Xông), 85 (DeepOxy Tổng hợp — combo Xông+YHPĐ),
        // 88 (Recells — không có Phòng Thủ thuật).
        // id 86 STC Japan: giữ active (user chốt "disable chọn phòng" — DV làm ở nước ngoài,
        //     UI booking sẽ skip phần chọn phòng cho DV này ở đợt UI sau).
        DB::table('dich_vu')
            ->whereIn('id', [45, 47, 53, 73, 74, 75, 76, 77, 79, 80, 81, 82, 84, 85, 88])
            ->update(['active' => 0, 'updated_at' => now()]);

        // ============================================================
        // 5. HCM — Set thoi_gian_phut theo sheet cho DV vẫn active
        // ============================================================
        $hcmDurations = [
            78 => 60,  // EAQ (1 vùng)
            86 => 15,  // STC Japan
            // id 46,48,49,50,51,52,87 đã đúng thời lượng từ trước, không đụng.
        ];
        foreach ($hcmDurations as $id => $phut) {
            DB::table('dich_vu')
                ->where('co_so_id', 2)
                ->where('id', $id)
                ->update(['thoi_gian_phut' => $phut, 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
        // Không rollback — restore từ backup DB nếu cần.
    }
};
