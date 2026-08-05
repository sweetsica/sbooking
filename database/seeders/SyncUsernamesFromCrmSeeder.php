<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Đồng bộ username sbooking với format vị trí bên CRM (<cơ_sở>.<chức_vụ>NN).
 * Match qua NAME (họ tên đầy đủ) — nếu trùng thì đổi username sang giá trị CRM.
 * Idempotent: user đã có username khớp thì skip.
 *
 * Chỉ áp cho user cùng người bên CRM (staff kinh doanh).
 * KTV/ĐD/BS/adminvh giữ nguyên username cũ.
 */
class SyncUsernamesFromCrmSeeder extends Seeder
{
    /** Map name → username CRM (snapshot 2026-07-28). Cập nhật khi CRM có user mới. */
    private const NAME_TO_USERNAME = [
        // Đà Nẵng
        'Lương Thị Kim Phấn'        => 'dn.cms01',
        'Tài khoản Trực Page cơ sở ĐN' => 'dn.page01',
        'Nguyễn Thị Ánh Nhung'      => 'dn.sale01',
        'Lê Thị Hoàng Uyên'         => 'dn.sale02',
        'Lương Thị Kim Hiếu'        => 'dn.sale03',
        'Sử Trung Kiên'             => 'dn.sale04',
        'Lương Thị Tường Vy'        => 'dn.sale05',
        'Trần Ngọc An Hoà'          => 'dn.sale06',
        'Nguyễn Thị Mỹ Hạnh'        => 'dn.sale07',
        'Nguyễn Thị Bông'           => 'dn.tl01',

        // HCM
        'CM Booking Team Ashley'    => 'hcm.cmb01',
        'Trần Thị Bích Trâm'        => 'hcm.cms01',
        'Nguyễn Thị Minh Thư'       => 'hcm.cms02',
        'Huỳnh Bùi Thanh Lan'       => 'hcm.cms03',
        'Trần Nguyễn Kim Ngân'      => 'hcm.dm01',
        'Tài khoản Trực Page cơ sở HCM' => 'hcm.page01',
        'Trương Thị Yến Nhi'        => 'hcm.sale01',
        'Nguyễn Thị Hoài Như'       => 'hcm.sale02',
        'Huỳnh Thị My My'           => 'hcm.sale03',
        'Nguyễn Thị Thanh'          => 'hcm.sale04',
        'Nguyễn Thị Kim Chi'        => 'hcm.sale05',
        'Lê Phát Đạt'               => 'hcm.sale06',
        'Phan Trần Khánh Quỳn'      => 'hcm.tl01',

        // Hà Nội
        // 2026-08-05: rename "Nguyễn Booking 1/Trần Booking 2" → "Tài khoản Booking 1/2" (đồng bộ scrm).
        'Tài khoản Booking 1'       => 'hn.book01',
        'Tài khoản Booking 2'       => 'hn.book02',
        'CM Booking'                => 'hn.cmb01',
        'CM Booking Team Giang'     => 'hn.cmb02',
        'Trần Thị Thu Giang'        => 'hn.cms01',
        'Tạ Văn Hợi'                => 'hn.cms02',
        'CM Sale'                   => 'hn.cms03',
        'CM Sale Team Giang'        => 'hn.cms04',
        'Tài khoản Trực Page cơ sở HN' => 'hn.page01',
        'NV Kinh Doanh'             => 'hn.sale01',
        'NV Marketing'              => 'hn.sale02',
        'Trần Huy Kiên'             => 'hn.sale03',
        'Nguyễn Hương Giang'        => 'hn.sale04',
        'Nguyễn Minh Phương'        => 'hn.sale05',
        'Nguyễn Thị Anh'            => 'hn.sale06',
        'Nguyễn Thị Nga'            => 'hn.sale07',
        'Cao Thị Lan Anh'           => 'hn.sale08',
        'Phạm Thanh Trúc'           => 'hn.sale09',
        'Nguyễn Thị Thúy'           => 'hn.sale10',
        'Phạm Tú Anh'               => 'hn.sale11',
        'Nguyễn Trà My'             => 'hn.sale12',
        'Nguyễn Mai Anh'            => 'hn.sale13',
        'Nguyễn Hoành Đức'          => 'hn.tl01',
        'Lê Thị Phương Tự'          => 'hn.tlkd01',

        // Vận hành & Giám sát (Observer)
        'Huyền' => 'vh.obs01',
        'Hằng'  => 'vh.obs02',
        'Ly'    => 'vh.obs03',
        'An'    => 'vh.obs04',
        'Tuyết' => 'vh.obs05',
    ];

    public function run(): void
    {
        $updated = 0;
        foreach (self::NAME_TO_USERNAME as $name => $username) {
            $user = User::firstWhere('name', $name);
            if (! $user) continue;
            if ($user->username === $username) continue;

            // Check không trùng username với user khác
            $conflict = User::where('username', $username)->where('id', '!=', $user->id)->exists();
            if ($conflict) {
                $this->command?->warn("SyncUsernamesFromCrm: username '$username' đã bị user khác chiếm, bỏ qua '$name'.");
                continue;
            }
            $user->forceFill(['username' => $username])->save();
            $updated++;
        }

        $this->command?->info("SyncUsernamesFromCrm: đã đổi $updated username khớp CRM.");
    }
}
