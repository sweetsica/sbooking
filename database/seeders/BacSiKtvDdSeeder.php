<?php

namespace Database\Seeders;

use App\Models\BacSi;
use App\Models\CoSo;
use Illuminate\Database\Seeder;

/**
 * Seed/cập nhật danh sách nhân sự phòng khám (BS + KTV + Điều dưỡng)
 * vào bảng `bac_si` theo dữ liệu chốt ngày 2026-09-03.
 *
 * Idempotent theo (co_so_id, ten). KHÔNG xóa dòng khác — chỉ upsert.
 * Cơ sở Đà Nẵng không đụng (user tự quản 1 BS bên đó).
 *
 * Chức danh: viết đầy đủ ("Kỹ thuật viên", "Điều dưỡng") thay vì viết tắt (KTV/DD).
 */
class BacSiKtvDdSeeder extends Seeder
{
    public function run(): void
    {
        $csHn  = CoSo::where('slug', '59ntn')->firstOrFail();
        $csHcm = CoSo::where('slug', '207nvt')->firstOrFail();

        // Preset field cho từng nhóm.
        $bsThuong = [
            'chuc_danh' => 'BS.',
            'nhan_tu_van' => true,  'phut_tu_van' => 30,
            'nhan_kham_ls' => true, 'phut_kham_ls' => 5,
            'gio_bat_dau' => '08:00', 'gio_ket_thuc' => '17:00',
            'xuat_hien_moi_co_so' => false,
            'active' => true,
        ];

        $ktv = [
            'chuc_danh' => 'Kỹ thuật viên',
            'nhan_tu_van' => false, 'phut_tu_van' => 30,
            'nhan_kham_ls' => false, 'phut_kham_ls' => 5,
            'gio_bat_dau' => null, 'gio_ket_thuc' => null,
            'xuat_hien_moi_co_so' => false,
            'active' => true,
        ];

        $dd = [
            'chuc_danh' => 'Điều dưỡng',
            'nhan_tu_van' => false, 'phut_tu_van' => 30,
            'nhan_kham_ls' => false, 'phut_kham_ls' => 5,
            'gio_bat_dau' => '08:00', 'gio_ket_thuc' => '17:00',
            'xuat_hien_moi_co_so' => false,
            'active' => true,
        ];

        // (co_so_id, ten, [overrides]) — override chỉ ghi đè field khác preset.
        $rows = [
            // ── Hà Nội (BS) ───────────────────────────────────────────────
            [$csHn->id,  'Nguyễn Tiến Dũng',        $bsThuong, []],
            [$csHn->id,  'Lê Tuyên Hồng Dương',     $bsThuong, []],
            [$csHn->id,  'Trương Thị Biên',         $bsThuong, []],
            [$csHn->id,  'Ngô Thị Ngà',             $bsThuong, []],
            [$csHn->id,  'Bác Biên Tim mạch',       $bsThuong, ['nhan_tu_van' => false, 'phut_kham_ls' => 30]],
            [$csHn->id,  'Bác Hồng',                $bsThuong, ['nhan_tu_van' => false, 'phut_kham_ls' => 15]],
            [$csHn->id,  'Bác Bình',                $bsThuong, ['nhan_tu_van' => false, 'phut_kham_ls' => 25]],

            // ── HCM (BS) ──────────────────────────────────────────────────
            [$csHcm->id, 'Bác sĩ Hoàng Văn Đông',   $bsThuong, ['xuat_hien_moi_co_so' => true]],
            [$csHcm->id, 'Bác sĩ Lê Huy Thư',       $bsThuong, []],
            [$csHcm->id, 'Bác sĩ Đặng Công Danh',   $bsThuong, []],
            [$csHcm->id, 'Bác sĩ Duy Anh',          $bsThuong, []],
            [$csHcm->id, 'Bác sĩ Đức',              $bsThuong, []],
            [$csHcm->id, 'Bác sĩ Danh',             $bsThuong, []],
            [$csHcm->id, 'Bác sĩ Quỳnh',            $bsThuong, []],
            [$csHcm->id, 'Y sĩ Thuận',              $bsThuong, ['chuc_danh' => 'Y sĩ']],

            // ── HCM (KTV) ─────────────────────────────────────────────────
            [$csHcm->id, 'KTV Thúy Kiều',           $ktv, []],
            [$csHcm->id, 'KTV Thùy',                $ktv, []],
            [$csHcm->id, 'KTV Huyền',               $ktv, []],
            [$csHcm->id, 'KTV Chính',               $ktv, []],

            // ── HCM (Điều dưỡng) ─────────────────────────────────────────
            [$csHcm->id, 'DD Thu Loan',             $dd, []],
            [$csHcm->id, 'DD Tam Tuấn',             $dd, []],
            [$csHcm->id, 'DD Hồng Gấm',             $dd, []],
        ];

        $created = 0; $updated = 0;
        foreach ($rows as [$coSoId, $ten, $preset, $override]) {
            $attrs = array_merge($preset, $override);
            $bs = BacSi::where(['co_so_id' => $coSoId, 'ten' => $ten])->first();
            if ($bs) { $bs->update($attrs); $updated++; }
            else     { BacSi::create(array_merge(['co_so_id' => $coSoId, 'ten' => $ten], $attrs)); $created++; }
        }

        $this->command?->info("BacSiKtvDdSeeder: tạo mới {$created}, cập nhật {$updated}, tổng " . count($rows) . '.');
    }
}
