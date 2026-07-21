<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SyncBookingEmailsSeeder extends Seeder
{
    public function run(): void
    {
        // Chỉ update cột email theo username. Không đụng name/role/vai_tro/co_so/gì khác.
        // Mục tiêu: đồng bộ email với bên CRM (@longevity.com.vn).

        $map = [
            // Nhóm A — trùng người với CRM, dùng slug email theo CRM
            'baoit' => 'baoit@longevity.com.vn',
            'tumod' => 'tumod@longevity.com.vn',
            'tttg'  => 'ttg@longevity.com.vn',
            'thk'   => 'thk@longevity.com.vn',
            'nhg'   => 'nhg@longevity.com.vn',
            'nmp'   => 'nmp@longevity.com.vn',
            'nta'   => 'nta@longevity.com.vn',
            'ntn'   => 'ntn@longevity.com.vn',
            'ctla'  => 'cla@longevity.com.vn',
            'tvh'   => 'tvh@longevity.com.vn',
            'ptt'   => 'ptt@longevity.com.vn',
            'ntt'   => 'ntt@longevity.com.vn',
            'nhd'   => 'nhd@longevity.com.vn',
            'pta'   => 'pta@longevity.com.vn',
            'ntm'   => 'ntm@longevity.com.vn',
            'nma'   => 'nma@longevity.com.vn',
            'tnkn'  => 'tnkn@longevity.com.vn',
            'ptkq'  => 'ptkq@longevity.com.vn',
            'ttyn'  => 'tyn@longevity.com.vn',
            'nthn'  => 'nhn@longevity.com.vn',
            'htmm'  => 'hmm@longevity.com.vn',
            'ntth'  => 'ntt2@longevity.com.vn',
            'ntkc'  => 'nkc@longevity.com.vn',
            'ltpt'  => 'lpt@longevity.com.vn',
            'ttbt'  => 'tbt@longevity.com.vn',
            'ntmt'  => 'nmt@longevity.com.vn',
            'lpd'   => 'lpd@longevity.com.vn',
            'hbtl'  => 'hbtl@longevity.com.vn',

            // Nhóm B — booking-only, đổi domain qua @longevity.com.vn
            'admin'      => 'admin@longevity.com.vn',
            'adminvh'    => 'adminvh@longevity.com.vn',
            'ktv_viet'   => 'ktv_viet@longevity.com.vn',
            'ktv_tu'     => 'ktv_tu@longevity.com.vn',
            'ktv_hoa'    => 'ktv_hoa@longevity.com.vn',
            'ktv_dong'   => 'ktv_dong@longevity.com.vn',
            'ddt_trang'  => 'ddt_trang@longevity.com.vn',
            'dd_thao'    => 'dd_thao@longevity.com.vn',
            'dd_quynh'   => 'dd_quynh@longevity.com.vn',
            'ddt_nhan'   => 'ddt_nhan@longevity.com.vn',
            'dd_mi'      => 'dd_mi@longevity.com.vn',
            'ktv_tthao'  => 'ktv_tthao@longevity.com.vn',
            'ktv_huong'  => 'ktv_huong@longevity.com.vn',
            'ktv_phuong' => 'ktv_phuong@longevity.com.vn',
            'ktv_bach'   => 'ktv_bach@longevity.com.vn',
            'ktv_vi'     => 'ktv_vi@longevity.com.vn',
            'bsi59ntn'   => 'bsi59ntn@longevity.com.vn',
            'bsi207nvt'  => 'bsi207nvt@longevity.com.vn',
            'ktv_kieu'   => 'ktv_kieu@longevity.com.vn',
            'ktv_gam'    => 'ktv_gam@longevity.com.vn',
            'ktv_huyen'  => 'ktv_huyen@longevity.com.vn',
            'ktv_thuan'  => 'ktv_thuan@longevity.com.vn',
            'ddt_loan'   => 'ddt_loan@longevity.com.vn',
            'dd_tuan'    => 'dd_tuan@longevity.com.vn',
            'dd_tien'    => 'dd_tien@longevity.com.vn',
            'ktv_tan'    => 'ktv_tan@longevity.com.vn',
            'dd_thanh'   => 'dd_thanh@longevity.com.vn',
        ];

        $updated = 0;
        $missing = [];
        $now = now();

        foreach ($map as $username => $newEmail) {
            $affected = DB::table('users')
                ->where('username', $username)
                ->update(['email' => $newEmail, 'updated_at' => $now]);

            if ($affected > 0) {
                $updated++;
            } else {
                $missing[] = $username;
            }
        }

        $this->command->info("Đã cập nhật email cho {$updated}/" . count($map) . " tài khoản.");
        if ($missing) {
            $this->command->warn('Không tìm thấy username: ' . implode(', ', $missing));
        }
    }
}
