<?php

namespace Database\Seeders;

use App\Models\CoSo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seed phòng + dịch vụ + pivot (dv_phong, dv_bac_si, phong_bac_si) cho cơ sở
 * Đà Nẵng (lo23tdn) theo bảng PKD 2026-09-04 (mã tham chiếu spreadsheet 217-267).
 *
 * Phụ thuộc: LongevitySeeder (cơ sở DN), BacSiKtvDdSeeder (đủ 8 nhân sự DN).
 *
 * Thao tác:
 *  1) Rename 4 phòng DN cũ theo naming mới ("DN suffix" → "Tx suffix"):
 *       "Phòng Xét nghiệm - T2" → "Phòng Xét nghiệm T2"
 *       "Phòng Thủ thuật DN"    → "Phòng Thủ thuật T2"
 *       "Phòng Metaboost DN"    → "Phòng Metaboost T3"
 *       "Phòng YHCT DN"         → "Phòng YHCT T3"
 *  2) Tạo 7 phòng mới nếu chưa có (khám lâm sàng + Phòng Khách gene).
 *  3) Upsert 29 DV theo (co_so, ten). Với DV liệt kê:
 *       - WIPE pivot dv_phong + dv_bac_si (chỉ của DV đó) rồi INSERT theo bảng.
 *       - Đồng bộ pivot phong_bac_si (mỗi phòng gắn tất cả nhân sự có mặt trong DV ở phòng đó).
 *
 * Idempotent — chạy lại an toàn.
 *
 * LƯU Ý dedupe: bảng PKD có row 229/230 trùng tên 219/220 ("Tư vấn - đọc kết quả"
 * và "Tư vấn"). DB dùng (co_so, ten) làm khóa logic → chỉ giữ 1 DV mỗi tên.
 * Row 227 "Khám Da liễu" 30' và 261 "Khám Da liễu" 15' trùng tên → 261 rename
 * thành "Khám Da liễu (Visia)" để không đè.
 */
class DnDichVuSeeder extends Seeder
{
    public function run(): void
    {
        $csDn = CoSo::where('slug', 'lo23tdn')->firstOrFail();
        $now = now();

        // 1) Rename phòng cũ (chỉ update nếu còn tên cũ).
        $renames = [
            'Phòng Xét nghiệm - T2' => 'Phòng Xét nghiệm T2',
            'Phòng Thủ thuật DN'    => 'Phòng Thủ thuật T2',
            'Phòng Metaboost DN'    => 'Phòng Metaboost T3',
            'Phòng YHCT DN'         => 'Phòng YHCT T3',
        ];
        $renamed = 0;
        foreach ($renames as $old => $new) {
            $renamed += DB::table('phong')
                ->where('co_so_id', $csDn->id)->where('ten', $old)
                ->update(['ten' => $new, 'updated_at' => $now]);
        }

        // 2) Tạo phòng mới (find-or-create).
        // [ten, kieu_phong, so_slot]
        $newPhongs = [
            ['Phòng Xét nghiệm T2',      'phong_kham',     1],
            ['Phòng Khám Nội T2',        'phong_kham',     1],
            ['Phòng Siêu âm T2',         'phong_kham',     1],
            ['Phòng X Quang T1',         'phong_kham',     1],
            ['Phòng lấy mẫu T2',         'phong_kham',     1],
            ['Phòng Khám Sản T2',        'phong_kham',     1],
            ['Phòng khám da liễu T2',    'phong_kham',     1],
            ['Phòng Khách - T4',         'phong_dich_vu',  1],
            ['Phòng YHCT T3',            'phong_dich_vu',  2],
            ['Phòng Thủ thuật T2',       'phong_dich_vu',  1],
            ['Phòng Metaboost T3',       'phong_dich_vu',  3],
        ];
        $created = 0;
        foreach ($newPhongs as [$ten, $kieu, $slot]) {
            $exists = DB::table('phong')->where('co_so_id', $csDn->id)->where('ten', $ten)->exists();
            if ($exists) continue;
            DB::table('phong')->insert([
                'co_so_id'       => $csDn->id,
                'ten'            => $ten,
                'kieu_phong'     => $kieu,
                'so_slot_toi_da' => $slot,
                'trang_thai'     => 'hoat_dong',
                'loai'           => 'cong_dong',
                'created_at'     => $now,
                'updated_at'     => $now,
            ]);
            $created++;
        }

        // 3) Danh sách DV: [ten, phut, thuoc_nhom, la_dich_vu, khong_can_phong, [phong tens], [bac_si tens]].
        $ddNhom = ['DD Hoàng Trinh', 'DD Ngọc Bích', 'DD Ánh Tuyết', 'DD Đỗ Hoa'];
        $bsChinh = ['Mai Tấn Mẫn', 'Bác sĩ Nguyễn Văn Đăng'];
        $metaThuThuat = ['Phòng Thủ thuật T2', 'Phòng Metaboost T3'];

        $rows = [
            // 217
            ['Thăm khám lâm sàng (trừ tim mạch)', 5,  'kham_ls', 0, 0, ['Phòng Khám Nội T2'], $bsChinh],
            // 218
            ['Thăm khám tim mạch',                30, 'kham_ls', 0, 0, ['Phòng Khám Nội T2'], []],
            // 219 (= 229 dedup)
            ['Tư vấn - đọc kết quả',              30, 'tu_van',  0, 0, ['Phòng Khám Nội T2'], $bsChinh],
            // 220 (= 230 dedup)
            ['Tư vấn',                            30, 'tu_van',  0, 0, ['Phòng Khám Nội T2'], $bsChinh],
            // 221
            ['Thực hiện lâm sàng',                5,  'kham_ls', 0, 0, ['Phòng Xét nghiệm T2'], ['Nguyễn Thị Phượng']],
            // 222
            ['Siêu âm',                           25, 'kham_ls', 0, 0, ['Phòng Siêu âm T2'], $ddNhom],
            // 223
            ['Chụp XQuang',                       15, 'kham_ls', 0, 0, ['Phòng X Quang T1'], ['KTV Lê Nữ Như Ngọc']],
            // 224
            ['Lấy máu',                           10, 'kham_ls', 0, 0, ['Phòng lấy mẫu T2'], $ddNhom],
            // 225
            ['Khám Nội',                          30, 'kham_ls', 0, 0, ['Phòng Khám Nội T2'], $bsChinh],
            // 226
            ['Khám Sản',                          30, 'kham_ls', 0, 0, ['Phòng Khám Sản T2'], []],
            // 227
            ['Khám Da liễu',                      30, 'kham_ls', 0, 0, ['Phòng khám da liễu T2'], []],
            // 228 — Đọc kết quả Gene: không phòng, không nhân sự (khong_can_phong=1).
            ['Đọc kết quả Gene',                  30, 'tu_van',  0, 1, [], []],
            // 247/248 — Gene (Phòng Khách - T4).
            ['Gene2 me Plus',                     30, 'khac',    1, 0, ['Phòng Khách - T4'], []],
            ['Gene2 me',                          30, 'khac',    1, 0, ['Phòng Khách - T4'], []],
            // 249/250/251 — TruAge (Phòng Xét nghiệm T2).
            ['TruAge',                            30, 'khac',    1, 0, ['Phòng Xét nghiệm T2'], []],
            ['Gene2 + Gene2 Plus + TruAge',       30, 'khac',    1, 0, ['Phòng Xét nghiệm T2'], []],
            ['Return TruAge',                     30, 'khac',    1, 0, ['Phòng Xét nghiệm T2'], []],
            // 252
            ['EAQ (1 vùng)',                      30, 'khac',    1, 0, ['Phòng YHCT T3'], $ddNhom],
            // 253-256 — Thủ thuật + Metaboost.
            ['BJR (1 khớp)',                      30, 'khac',    1, 0, $metaThuThuat, $ddNhom],
            ['HA 1%/khớp',                        30, 'khac',    1, 0, $metaThuThuat, $ddNhom],
            ['HA 2%/khớp',                        30, 'khac',    1, 0, $metaThuThuat, $ddNhom],
            ['PRP/khớp',                          30, 'khac',    1, 0, $metaThuThuat, $ddNhom],
            // 257
            ['Y học Phương Đông',                 30, 'khac',    1, 0, ['Phòng YHCT T3'], []],
            // 258 — STC Japan: không phòng (khong_can_phong=1).
            ['STC Japan',                         30, 'khac',    1, 1, [], []],
            // 259 — NK: không phòng (sheet để trống → khong_can_phong=1).
            ['NK',                                30, 'khac',    1, 1, [], []],
            // 260
            ['Recells',                           30, 'khac',    1, 0, $metaThuThuat, []],
            // 261 — trùng tên 227 với 15' — rename thành "(Visia)" để phân biệt.
            ['Khám Da liễu (Visia)',              15, 'kham_ls', 0, 0, ['Phòng khám da liễu T2'], []],
            // 262/263/264
            ['Thực hiện lâm sàng (lấy máu)',      5,  'kham_ls', 0, 0, ['Phòng lấy mẫu T2'], $ddNhom],
            ['Thực hiện lâm sàng (siêu âm)',      25, 'kham_ls', 0, 0, ['Phòng Siêu âm T2'], $ddNhom],
            // 264: sheet ghi "Phòng YHCT T3" cho Xquang — có vẻ typo nhưng follow sheet.
            ['Thực hiện lâm sàng (Xquang)',       15, 'kham_ls', 0, 0, ['Phòng YHCT T3'], ['KTV Lê Nữ Như Ngọc']],
            // 265/266/267 — Y học Phương Đông 30'/45'/60'.
            ["Y học Phương Đông 30'",             30, 'khac',    1, 0, ['Phòng YHCT T3'], []],
            ["Y học Phương Đông 45'",             45, 'khac',    1, 0, ['Phòng YHCT T3'], []],
            ["Y học Phương Đông 60'",             60, 'khac',    1, 0, ['Phòng YHCT T3'], []],
        ];

        // Map tên → id (nạp 1 lần sau khi tạo phòng mới ở bước 2).
        $phongIdByTen = DB::table('phong')->where('co_so_id', $csDn->id)->pluck('id', 'ten')->all();
        $bsIdByTen   = DB::table('bac_si')->where('co_so_id', $csDn->id)->pluck('id', 'ten')->all();

        // Track pivot phong_bac_si cần insert (dedupe).
        $phongBacSiPairs = [];

        $dvCreated = 0; $dvUpdated = 0; $pivotP = 0; $pivotBs = 0;
        $missingP = []; $missingBs = [];

        foreach ($rows as [$ten, $phut, $nhom, $laDv, $khongCanPhong, $phongTens, $bsTens]) {
            $dvId = DB::table('dich_vu')->where('co_so_id', $csDn->id)->where('ten', $ten)->value('id');
            $attrs = [
                'thoi_gian_phut'  => $phut,
                'thuoc_nhom'      => $nhom,
                'la_dich_vu'      => $laDv,
                'khong_can_phong' => $khongCanPhong,
                'active'          => 1,
                'updated_at'      => $now,
            ];
            if ($dvId) {
                DB::table('dich_vu')->where('id', $dvId)->update($attrs);
                $dvUpdated++;
            } else {
                $dvId = DB::table('dich_vu')->insertGetId(array_merge($attrs, [
                    'co_so_id'   => $csDn->id,
                    'ten'        => $ten,
                    'created_at' => $now,
                ]));
                $dvCreated++;
            }

            // WIPE + INSERT pivot dv_phong.
            DB::table('dich_vu_phong')->where('dich_vu_id', $dvId)->delete();
            foreach ($phongTens as $pTen) {
                $pId = $phongIdByTen[$pTen] ?? null;
                if (! $pId) { $missingP[] = $pTen; continue; }
                DB::table('dich_vu_phong')->insert([
                    'dich_vu_id' => $dvId, 'phong_id' => $pId,
                    'created_at' => $now, 'updated_at' => $now,
                ]);
                $pivotP++;
            }

            // WIPE + INSERT pivot dv_bac_si.
            DB::table('dich_vu_bac_si')->where('dich_vu_id', $dvId)->delete();
            foreach ($bsTens as $bsTen) {
                $bsId = $bsIdByTen[$bsTen] ?? null;
                if (! $bsId) { $missingBs[] = $bsTen; continue; }
                DB::table('dich_vu_bac_si')->insert([
                    'dich_vu_id' => $dvId, 'bac_si_id' => $bsId,
                    'created_at' => $now, 'updated_at' => $now,
                ]);
                $pivotBs++;
            }

            // Chuẩn bị pivot phong_bac_si: mọi (phòng, bs) trong DV này gán chéo nhau.
            foreach ($phongTens as $pTen) {
                $pId = $phongIdByTen[$pTen] ?? null;
                if (! $pId) continue;
                foreach ($bsTens as $bsTen) {
                    $bsId = $bsIdByTen[$bsTen] ?? null;
                    if (! $bsId) continue;
                    $phongBacSiPairs["{$pId}-{$bsId}"] = [$pId, $bsId];
                }
            }
        }

        // Đồng bộ phong_bac_si (chỉ INSERT, không WIPE — tránh gãy config phòng-bs khác).
        $pbsCreated = 0;
        foreach ($phongBacSiPairs as [$pId, $bsId]) {
            $exists = DB::table('phong_bac_si')
                ->where('phong_id', $pId)->where('bac_si_id', $bsId)->exists();
            if ($exists) continue;
            DB::table('phong_bac_si')->insert([
                'phong_id' => $pId, 'bac_si_id' => $bsId,
                'created_at' => $now, 'updated_at' => $now,
            ]);
            $pbsCreated++;
        }

        $this->command?->info(sprintf(
            'DnDichVuSeeder: rename %d, tạo %d phòng, DV tạo %d + update %d, pivot dv_phong %d + dv_bac_si %d, phong_bac_si tạo %d.',
            $renamed, $created, $dvCreated, $dvUpdated, $pivotP, $pivotBs, $pbsCreated
        ));
        if ($missingP)  $this->command?->warn('Phòng không có: ' . implode(', ', array_unique($missingP)));
        if ($missingBs) $this->command?->warn('Nhân sự không có (chạy BacSiKtvDdSeeder trước?): ' . implode(', ', array_unique($missingBs)));

        // 2026-09-04: backfill khung_gio cho phòng mới tạo — bypass LongevitySeeder::seedPhong() (dùng insert thô).
        // Không có bước này → API /khung-gio trả empty → dropdown khung giờ bên data trống.
        $this->call(EnsurePhongKhungGioSeeder::class);
    }
}
