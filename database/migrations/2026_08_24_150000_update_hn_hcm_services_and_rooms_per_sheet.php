<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Cập nhật dịch vụ + phòng HN (co_so=1) và HCM 207 NVT (co_so=2) theo sheet
// "Dịch vụ HN → Phòng thực hiện" do PKD gửi 2026-08-24.
// Idempotent: check trước khi insert/update, không throw nếu chạy lại.
return new class extends Migration {
    public function up(): void
    {
        // ============================================================
        // A1. Deactivate DV không dùng nữa ở HN (co_so=1)
        // ============================================================
        // id 1 (Thăm khám lâm sàng trừ tim mạch), id 3 (Thực hiện lâm sàng)
        // id 29-33 (Gene2 me Plus, Gene2 me, TruAge, Gene2+Plus+TruAge, Return TruAge)
        // Chỉ set active=0, KHÔNG xoá — id 1,3 có booking cũ.
        DB::table('dich_vu')
            ->where('co_so_id', 1)
            ->whereIn('id', [1, 3, 29, 30, 31, 32, 33])
            ->update(['active' => 0, 'updated_at' => now()]);

        // ============================================================
        // A2. Set thoi_gian_phut cho DV HN theo sheet
        // ============================================================
        $durations = [
            2  => 30,  // Thăm khám tim mạch
            4  => 25,  // Siêu âm
            5  => 15,  // Chụp XQuang
            6  => 10,  // Lấy máu
            7  => 30,  // Khám Nội
            8  => 30,  // Khám Sản
            9  => 30,  // Khám Da liễu
            34 => 60,  // EAQ (1 vùng) — Thủy châm YHCT
            35 => 10,  // BJR (1 khớp) — Tiêm gối
            36 => 10,  // HA 1%/khớp — Tiêm dịch nhờn
            37 => 10,  // HA 2%/khớp — Tiêm khớp gối
            38 => 10,  // PRP/khớp
            39 => 60,  // Y học Phương Đông (default 60; linh hoạt 30/45/60 sẽ làm Đợt B)
            40 => 15,  // DeepOxy Xông
            41 => 90,  // DeepOxy Tổng hợp (lock 2 phòng — Đợt B)
            42 => 15,  // STC Japan
            43 => 30,  // NK — Truyền miễn dịch
            44 => 15,  // Recells
        ];
        foreach ($durations as $id => $phut) {
            DB::table('dich_vu')
                ->where('co_so_id', 1)
                ->where('id', $id)
                ->update(['thoi_gian_phut' => $phut, 'updated_at' => now()]);
        }

        // ============================================================
        // A3. Insert 4 DV mới HN (khám lâm sàng)
        // ============================================================
        $newDvs = [
            ['ten' => 'Khám Da (Visia)',                'phut' => 15],
            ['ten' => 'Thực hiện lâm sàng (lấy máu)',   'phut' => 5],
            ['ten' => 'Thực hiện lâm sàng (siêu âm)',   'phut' => 25],
            ['ten' => 'Thực hiện lâm sàng (Xquang)',    'phut' => 15],
        ];
        foreach ($newDvs as $dv) {
            $exists = DB::table('dich_vu')
                ->where('co_so_id', 1)
                ->where('ten', $dv['ten'])
                ->exists();
            if (! $exists) {
                DB::table('dich_vu')->insert([
                    'co_so_id'       => 1,
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
        // A4. Insert 6 phòng thiếu ở HN (co_so=1) — giữ nguyên 12 phòng cũ
        // ============================================================
        $newPhongsHn = [
            ['ten' => 'Phòng X Quang',     'kieu' => 'phong_kham',     'slot' => 1, 'phut' => 15],
            ['ten' => 'Phòng lấy mẫu',     'kieu' => 'phong_kham',     'slot' => 2, 'phut' => 10],
            ['ten' => 'Phòng da',          'kieu' => 'phong_kham',     'slot' => 1, 'phut' => 30],
            ['ten' => 'Phòng VISIA',       'kieu' => 'phong_kham',     'slot' => 1, 'phut' => 15],
            ['ten' => 'Phòng Xông',        'kieu' => 'phong_dich_vu',  'slot' => 2, 'phut' => 60], // pairing giới — Đợt B
            ['ten' => 'Phòng truyền',      'kieu' => 'phong_dich_vu',  'slot' => 1, 'phut' => 30],
        ];
        foreach ($newPhongsHn as $p) {
            $exists = DB::table('phong')
                ->where('co_so_id', 1)
                ->where('ten', $p['ten'])
                ->exists();
            if (! $exists) {
                DB::table('phong')->insert([
                    'co_so_id'          => 1,
                    'ten'               => $p['ten'],
                    'kieu_phong'        => $p['kieu'],
                    'so_slot_toi_da'    => $p['slot'],
                    'phut_moi_khach'    => $p['phut'],
                    'duoc_dat_tu_van'   => 0,
                    'trang_thai'        => 'hoat_dong',
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);
            }
        }

        // ============================================================
        // A5. HCM 207 NVT (co_so=2) — giữ id 14 (siêu âm) + id 15 (YHCT),
        //     xoá id 13 (Phòng Tư vấn, 0 booking), thêm 4 phòng mới.
        //     Đủ 6 phòng theo sheet: Khám / Nội / Siêu Âm / Xét nghiệm / YHCT / Cơ sở điều dưỡng.
        // ============================================================
        // Xoá id 13 nếu vẫn còn + không có booking
        $tuVanHasBooking = DB::table('booking')->where('phong_id', 13)->exists();
        if (! $tuVanHasBooking) {
            DB::table('phong')->where('id', 13)->where('co_so_id', 2)->delete();
        }

        // Fix id 15 YHCT HCM: phong_kham → phong_dich_vu, slot 2, phut 60 (đồng bộ HN YHCT)
        DB::table('phong')
            ->where('id', 15)
            ->where('co_so_id', 2)
            ->update([
                'kieu_phong'     => 'phong_dich_vu',
                'so_slot_toi_da' => 2,
                'phut_moi_khach' => 60,
                'updated_at'     => now(),
            ]);

        $newPhongsHcm = [
            ['ten' => 'Phòng khám',                'kieu' => 'phong_kham',    'slot' => 2, 'phut' => 30],
            ['ten' => 'Phòng Nội',                 'kieu' => 'phong_kham',    'slot' => 2, 'phut' => 30],
            ['ten' => 'Phòng Xét nghiệm',          'kieu' => 'phong_kham',    'slot' => 2, 'phut' => 10],
            ['ten' => 'Phòng Cơ sở điều dưỡng',    'kieu' => 'phong_dich_vu', 'slot' => 1, 'phut' => 30],
        ];
        foreach ($newPhongsHcm as $p) {
            $exists = DB::table('phong')
                ->where('co_so_id', 2)
                ->where('ten', $p['ten'])
                ->exists();
            if (! $exists) {
                DB::table('phong')->insert([
                    'co_so_id'          => 2,
                    'ten'               => $p['ten'],
                    'kieu_phong'        => $p['kieu'],
                    'so_slot_toi_da'    => $p['slot'],
                    'phut_moi_khach'    => $p['phut'],
                    'duoc_dat_tu_van'   => 0,
                    'trang_thai'        => 'hoat_dong',
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        // Không rollback data — nếu cần rollback thì restore từ backup DB.
    }
};
