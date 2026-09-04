<?php

namespace Database\Seeders;

use App\Models\CoSo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Sync danh sách phòng + pivot dich_vu_phong cho HN theo bảng "Phòng thực hiện"
 * PKD gửi 2026-09-04.
 *
 * 1) Rename 6 phòng cũ (giữ id → không gãy booking đang có phong_id):
 *      "Phòng siêu âm"    → "Phòng Siêu âm T3"
 *      "Phòng X Quang"    → "Phòng X Quang T1"
 *      "Phòng lấy mẫu"    → "Phòng lấy mẫu T2"
 *      "Phòng da"         → "Phòng dịch vụ da T5"
 *      "Phòng Xông"       → "Phòng Xông T4"
 *      "Phòng truyền"     → "Phòng sử dụng dịch vụ T4"
 *
 * 2) Tạo 3 phòng mới nếu chưa có:
 *      "Phòng Xét nghiệm T3" (phong_kham)
 *      "Phòng Khách - T2"    (phong_kham)
 *      "Phòng YHCT"          (phong_kham) — đơn, khác với YHCT 1/2/3 T4
 *
 * 3) Với mỗi DV liệt kê trong bảng: WIPE pivot cũ (chỉ cho DV đó) rồi INSERT theo bảng.
 *    DV không liệt kê → không đụng pivot.
 *
 * Idempotent — chạy lại an toàn.
 */
class HnPhongSyncSeeder extends Seeder
{
    public function run(): void
    {
        $csHn = CoSo::where('slug', '59ntn')->firstOrFail();
        $now = now();

        // 1) Rename (chỉ update nếu tên cũ còn khớp — không đụng nếu đã rename rồi).
        $renames = [
            'Phòng siêu âm'  => 'Phòng Siêu âm T3',
            'Phòng X Quang'  => 'Phòng X Quang T1',
            'Phòng lấy mẫu'  => 'Phòng lấy mẫu T2',
            'Phòng da'       => 'Phòng dịch vụ da T5',
            'Phòng Xông'     => 'Phòng Xông T4',
            'Phòng truyền'   => 'Phòng sử dụng dịch vụ T4',
        ];
        $renamed = 0;
        foreach ($renames as $old => $new) {
            $renamed += DB::table('phong')
                ->where('co_so_id', $csHn->id)->where('ten', $old)
                ->update(['ten' => $new, 'updated_at' => $now]);
        }

        // 2) Tạo phòng mới (nếu chưa có).
        $newPhongs = [
            ['Phòng Xét nghiệm T3', 'phong_kham',    1],
            ['Phòng Khách - T2',    'phong_kham',    1],
            ['Phòng YHCT',          'phong_kham',    1],
        ];
        $created = 0;
        foreach ($newPhongs as [$ten, $kieu, $slot]) {
            $exists = DB::table('phong')->where('co_so_id', $csHn->id)->where('ten', $ten)->exists();
            if ($exists) continue;
            DB::table('phong')->insert([
                'co_so_id'       => $csHn->id,
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

        // 3) Pivot dich_vu_phong theo bảng PKD. [dv_id => [tên phòng, ...]]
        $mapping = [
            2   => ['Phòng khám Ngoại', 'Phòng khám Nội 1', 'Phòng khám Nội 2'],
            4   => ['Phòng Siêu âm T3'],
            5   => ['Phòng X Quang T1'],
            6   => ['Phòng lấy mẫu T2'],
            7   => ['Phòng khám Nội 1', 'Phòng khám Nội 2'],
            9   => ['Phòng dịch vụ da T5'],
            11  => ['Phòng khám Ngoại', 'Phòng chuyên gia', 'Phòng khám Nội 1', 'Phòng khám Nội 2', 'Phòng YHCT'],
            12  => ['Phòng khám Ngoại', 'Phòng chuyên gia', 'Phòng khám Nội 1', 'Phòng khám Nội 2', 'Phòng YHCT'],
            34  => ['Phòng YHCT 1 T4', 'Phòng YHCT 2 T4', 'Phòng YHCT 3 T4'],
            35  => ['Phòng Thủ thuật T3', 'Phòng Metaboost 1 T4', 'Phòng Metaboost 2 T4', 'Phòng Metaboost 3 T4'],
            36  => ['Phòng Thủ thuật T3', 'Phòng Metaboost 1 T4', 'Phòng Metaboost 2 T4', 'Phòng Metaboost 3 T4'],
            37  => ['Phòng Thủ thuật T3', 'Phòng Metaboost 1 T4', 'Phòng Metaboost 2 T4', 'Phòng Metaboost 3 T4'],
            38  => ['Phòng Thủ thuật T3', 'Phòng Metaboost 1 T4', 'Phòng Metaboost 2 T4', 'Phòng Metaboost 3 T4'],
            40  => ['Phòng Xông T4'],
            41  => ['Phòng Xông T4'],
            43  => ['Phòng sử dụng dịch vụ T4'],
            44  => ['Phòng Thủ thuật T3', 'Phòng Metaboost 1 T4', 'Phòng Metaboost 2 T4', 'Phòng Metaboost 3 T4'],
            177 => ['Phòng VISIA'],
            178 => ['Phòng lấy mẫu T2'],
            179 => ['Phòng Siêu âm T3'],
            180 => ['Phòng X Quang T1'],
            181 => ['Phòng YHCT 1 T4', 'Phòng YHCT 2 T4', 'Phòng YHCT 3 T4'],
            182 => ['Phòng YHCT 1 T4', 'Phòng YHCT 2 T4', 'Phòng YHCT 3 T4'],
            183 => ['Phòng YHCT 1 T4', 'Phòng YHCT 2 T4', 'Phòng YHCT 3 T4'],
        ];

        $phongIdByTen = DB::table('phong')->where('co_so_id', $csHn->id)
            ->pluck('id', 'ten')->all();

        $pivotInserted = 0; $pivotWiped = 0; $missingDv = []; $missingPhong = [];
        foreach ($mapping as $dvId => $phongTens) {
            $dvExists = DB::table('dich_vu')->where('co_so_id', $csHn->id)->where('id', $dvId)->exists();
            if (! $dvExists) { $missingDv[] = $dvId; continue; }

            $pivotWiped += DB::table('dich_vu_phong')->where('dich_vu_id', $dvId)->delete();

            foreach ($phongTens as $ten) {
                $phongId = $phongIdByTen[$ten] ?? null;
                if (! $phongId) { $missingPhong[] = $ten; continue; }
                DB::table('dich_vu_phong')->insert([
                    'dich_vu_id' => $dvId,
                    'phong_id'   => $phongId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $pivotInserted++;
            }
        }

        $this->command?->info(sprintf(
            'HnPhongSyncSeeder: rename %d, tạo %d phòng mới, pivot xóa %d + insert %d.',
            $renamed, $created, $pivotWiped, $pivotInserted
        ));
        if ($missingDv) $this->command?->warn('DV không tồn tại (bỏ qua): ' . implode(', ', $missingDv));
        if ($missingPhong) $this->command?->warn('Phòng không tìm thấy: ' . implode(', ', array_unique($missingPhong)));

        // 2026-09-04: backfill khung_gio cho phòng mới tạo — bypass LongevitySeeder::seedPhong() (dùng insert thô).
        $this->call(EnsurePhongKhungGioSeeder::class);
    }
}
