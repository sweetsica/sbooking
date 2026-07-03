<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        // ---- 1. Vai trò mới ----
        $newRoles = [
            ['ma' => 'sales_lead',    'ten' => 'Quản lý Sales'],
            ['ma' => 'sales_manager', 'ten' => 'Quản lý kinh doanh'],
            ['ma' => 'admin_co_so',   'ten' => 'Admin cơ sở'],
            ['ma' => 'quyen_xem',     'ten' => 'Quyền xem'],
        ];
        foreach ($newRoles as $r) {
            if (! DB::table('vai_tro')->where('ma', $r['ma'])->exists()) {
                DB::table('vai_tro')->insert(array_merge($r, [
                    'created_at' => now(), 'updated_at' => now(),
                ]));
            }
        }

        // Đổi tên vai trò tu_van_vien → Sales
        DB::table('vai_tro')->where('ma', 'tu_van_vien')->update(['ten' => 'Sales']);

        $vrIds = DB::table('vai_tro')->pluck('id', 'ma');

        // ---- 2. Phân quyền cho vai trò mới ----
        $newPerms = [
            'sales_lead'    => ['them_booking', 'xem_booking_phong_toi', 'ghi_chu_phan_hoi'],
            'sales_manager' => ['them_booking', 'xem_booking_co_so_toi', 'ghi_chu_phan_hoi'],
            'admin_co_so'   => ['duyet_booking', 'xem_booking_co_so_toi', 'duyet_tu_van'],
            'quyen_xem'     => ['xem_booking_tat_ca'],
        ];
        foreach ($newPerms as $ma => $truongs) {
            $vrId = $vrIds[$ma] ?? null;
            if (! $vrId) continue;
            foreach ($truongs as $t) {
                if (! DB::table('phan_quyen')->where('vai_tro_id', $vrId)->where('truong', $t)->exists()) {
                    DB::table('phan_quyen')->insert([
                        'vai_tro_id' => $vrId, 'truong' => $t,
                        'created_at' => now(), 'updated_at' => now(),
                    ]);
                }
            }
        }

        // Thêm ghi_chu_phan_hoi cho các vai trò cũ chưa có
        foreach (['tu_van_vien', 'ktv', 'bac_si', 'bac_si_tu_van', 'le_tan'] as $ma) {
            $vrId = $vrIds[$ma] ?? null;
            if (! $vrId) continue;
            if (! DB::table('phan_quyen')->where('vai_tro_id', $vrId)->where('truong', 'ghi_chu_phan_hoi')->exists()) {
                DB::table('phan_quyen')->insert([
                    'vai_tro_id' => $vrId, 'truong' => 'ghi_chu_phan_hoi',
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        }

        // ---- 3. Team phòng ban ----
        $cs59ntnId  = DB::table('co_so')->where('slug', '59ntn')->value('id');
        $cs207nvtId = DB::table('co_so')->where('slug', '207nvt')->value('id');

        // Không có cơ sở → seeder chưa chạy (test DB) → bỏ qua phần data migration
        if (! $cs59ntnId) return;

        $teamIds = [];
        $teams = [
            ['co_so_id' => $cs59ntnId,  'ma' => 'team_giang', 'ten' => 'Team Giang (HN)'],
            ['co_so_id' => $cs59ntnId,  'ma' => 'team_hoi',   'ten' => 'Team Hợi (HN)'],
            ['co_so_id' => $cs207nvtId, 'ma' => 'team_hcm',   'ten' => 'Team HCM'],
        ];
        foreach ($teams as $t) {
            $existing = DB::table('phong_ban')->where('co_so_id', $t['co_so_id'])->where('ma', $t['ma'])->value('id');
            if ($existing) {
                $teamIds[$t['ma']] = $existing;
            } else {
                $teamIds[$t['ma']] = DB::table('phong_ban')->insertGetId(array_merge($t, [
                    'created_at' => now(), 'updated_at' => now(),
                ]));
            }
        }

        // ---- 4. Đổi tên username HN (thêm STT) ----
        $renameHn = [
            'tttg' => ['new' => 'tttg1',  'vai_tro' => 'sales_lead',   'team' => 'team_giang'],
            'thk'  => ['new' => 'thk2',   'vai_tro' => 'tu_van_vien',  'team' => 'team_giang'],
            'nhg'  => ['new' => 'nhg3',   'vai_tro' => 'tu_van_vien',  'team' => 'team_giang'],
            'nmp'  => ['new' => 'nmp4',   'vai_tro' => 'ktv',          'team' => null],
            'nta'  => ['new' => 'nta5',   'vai_tro' => 'tu_van_vien',  'team' => 'team_giang'],
            'ntn'  => ['new' => 'ntn6',   'vai_tro' => 'tu_van_vien',  'team' => 'team_giang'],
            'ctla' => ['new' => 'ctla7',  'vai_tro' => 'tu_van_vien',  'team' => 'team_giang'],
            'tvh'  => ['new' => 'tvh8',   'vai_tro' => 'sales_lead',   'team' => 'team_hoi'],
            'ptt'  => ['new' => 'ptt9',   'vai_tro' => 'tu_van_vien',  'team' => 'team_hoi'],
            'ntt'  => ['new' => 'ntt10',  'vai_tro' => 'tu_van_vien',  'team' => 'team_hoi'],
            'nhd'  => ['new' => 'nhd11',  'vai_tro' => 'tu_van_vien',  'team' => 'team_hoi'],
            'pta'  => ['new' => 'pta12',  'vai_tro' => 'tu_van_vien',  'team' => 'team_hoi'],
            'ntm'  => ['new' => 'ntm13',  'vai_tro' => 'tu_van_vien',  'team' => 'team_hoi'],
            'nma'  => ['new' => 'nma14',  'vai_tro' => 'tu_van_vien',  'team' => 'team_hoi'],
        ];

        foreach ($renameHn as $old => $info) {
            $user = DB::table('users')->where('username', $old)->first();
            if (! $user) continue;

            $update = [
                'username'    => $info['new'],
                'email'       => $info['new'] . '@59ntn.local',
                'vai_tro_id'  => $vrIds[$info['vai_tro']] ?? $user->vai_tro_id,
                'phong_ban_id' => $info['team'] ? ($teamIds[$info['team']] ?? $user->phong_ban_id) : null,
                'updated_at'  => now(),
            ];

            // NMP → KTV Da liễu: đổi chức danh
            if ($old === 'nmp') {
                $update['chuc_danh'] = 'KTV';
            }

            DB::table('users')->where('id', $user->id)->update($update);
        }

        // ---- 5. Đổi tên username HCM (thêm STT) + đổi mật khẩu ----
        $matKhauHcm = Hash::make('207nvt');
        $renameHcm = [
            'tnkn' => ['new' => 'tnkn19', 'vai_tro' => 'sales_manager'],
            'ptkq' => ['new' => 'ptkq20', 'vai_tro' => 'tu_van_vien'],
            'ttyn' => ['new' => 'ttyn21', 'vai_tro' => 'tu_van_vien'],
            'nthn' => ['new' => 'nthn22', 'vai_tro' => 'tu_van_vien'],
            'htmm' => ['new' => 'htmm23', 'vai_tro' => 'tu_van_vien'],
            'ntth' => ['new' => 'ntth24', 'vai_tro' => 'tu_van_vien'],
            'ntkc' => ['new' => 'ntkc25', 'vai_tro' => 'tu_van_vien'],
            'ltpt' => ['new' => 'ltpt26', 'vai_tro' => 'tu_van_vien'],
            'ttbt' => ['new' => 'ttbt27', 'vai_tro' => 'tu_van_vien'],
            'ntmt' => ['new' => 'ntmt28', 'vai_tro' => 'tu_van_vien'],
            'lpd'  => ['new' => 'lpd29',  'vai_tro' => 'tu_van_vien'],
            'hbtl' => ['new' => 'hbtl30', 'vai_tro' => 'tu_van_vien'],
        ];

        foreach ($renameHcm as $old => $info) {
            $user = DB::table('users')->where('username', $old)->first();
            if (! $user) continue;

            DB::table('users')->where('id', $user->id)->update([
                'username'     => $info['new'],
                'email'        => $info['new'] . '@207nvt.local',
                'password'     => $matKhauHcm,
                'vai_tro_id'   => $vrIds[$info['vai_tro']] ?? $user->vai_tro_id,
                'phong_ban_id' => $teamIds['team_hcm'],
                'updated_at'   => now(),
            ]);
        }

        // ---- 6. Tài khoản mới ----
        $cslo23tdnId = DB::table('co_so')->where('slug', 'lo23tdn')->value('id');

        $newUsers = [
            // Lương Thị Kim Phấn (HN team Hợi)
            ['username' => 'ltkp15', 'name' => 'Lương Thị Kim Phấn', 'email' => 'ltkp15@59ntn.local',
             'chuc_danh' => 'CM', 'password' => Hash::make('59@ntn'),
             'co_so_id' => $cs59ntnId, 'phong_ban_id' => $teamIds['team_hoi'],
             'vai_tro_id' => $vrIds['tu_van_vien'], 'is_admin' => false],

            // Ashley (HCM team lead)
            ['username' => 'ashley34', 'name' => 'Ashley', 'email' => 'ashley34@207nvt.local',
             'chuc_danh' => 'CM', 'password' => $matKhauHcm,
             'co_so_id' => $cs207nvtId, 'phong_ban_id' => $teamIds['team_hcm'],
             'vai_tro_id' => $vrIds['sales_lead'], 'is_admin' => false],

            // Generic shared accounts
            ['username' => 'bsi59ntn',  'name' => 'BS chung 59 NTN',  'email' => 'bsi59ntn@local',
             'password' => Hash::make('59@ntn'), 'co_so_id' => $cs59ntnId,
             'vai_tro_id' => $vrIds['bac_si'], 'is_admin' => false],
            ['username' => 'bsi207nvt', 'name' => 'BS chung 207 NVT', 'email' => 'bsi207nvt@local',
             'password' => $matKhauHcm, 'co_so_id' => $cs207nvtId,
             'vai_tro_id' => $vrIds['bac_si'], 'is_admin' => false],
            ['username' => 'ktv59ntn',  'name' => 'KTV chung 59 NTN', 'email' => 'ktv59ntn@local',
             'password' => Hash::make('59@ntn'), 'co_so_id' => $cs59ntnId,
             'vai_tro_id' => $vrIds['ktv'], 'is_admin' => false],
            ['username' => 'ktv207nvt', 'name' => 'KTV chung 207 NVT','email' => 'ktv207nvt@local',
             'password' => $matKhauHcm, 'co_so_id' => $cs207nvtId,
             'vai_tro_id' => $vrIds['ktv'], 'is_admin' => false],
            ['username' => 'lt59ntn',   'name' => 'Lễ tân 59 NTN',    'email' => 'lt59ntn@local',
             'password' => Hash::make('59@ntn'), 'co_so_id' => $cs59ntnId,
             'vai_tro_id' => $vrIds['le_tan'], 'is_admin' => false],
            ['username' => 'lt207nvt',  'name' => 'Lễ tân 207 NVT',   'email' => 'lt207nvt@local',
             'password' => $matKhauHcm, 'co_so_id' => $cs207nvtId,
             'vai_tro_id' => $vrIds['le_tan'], 'is_admin' => false],

            // Admin cơ sở
            ['username' => 'admin59ntn',  'name' => 'Admin 59 NTN',      'email' => 'admin59ntn@local',
             'password' => Hash::make('59ntn'), 'co_so_id' => $cs59ntnId,
             'vai_tro_id' => $vrIds['admin_co_so'], 'is_admin' => false],
            ['username' => 'admin207nvt', 'name' => 'Admin 207 NVT',     'email' => 'admin207nvt@local',
             'password' => Hash::make('207nvt'), 'co_so_id' => $cs207nvtId,
             'vai_tro_id' => $vrIds['admin_co_so'], 'is_admin' => false],
            ['username' => 'adminl23tdn', 'name' => 'Admin Lô 2+3 TĐN', 'email' => 'adminl23tdn@local',
             'password' => Hash::make('l23tdn'), 'co_so_id' => $cslo23tdnId,
             'vai_tro_id' => $vrIds['admin_co_so'], 'is_admin' => false],

            // Viewers
            ['username' => 'huyently', 'name' => 'huyently', 'email' => 'huyently@sweetsica.com',
             'password' => Hash::make('123'), 'vai_tro_id' => $vrIds['quyen_xem'], 'is_admin' => false],
            ['username' => 'hangktt',  'name' => 'hangktt',  'email' => 'hangktt@sweetsica.com',
             'password' => Hash::make('123'), 'vai_tro_id' => $vrIds['quyen_xem'], 'is_admin' => false],
            ['username' => 'lyktdt',   'name' => 'lyktdt',   'email' => 'lyktdt@sweetsica.com',
             'password' => Hash::make('123'), 'vai_tro_id' => $vrIds['quyen_xem'], 'is_admin' => false],
            ['username' => 'msan',     'name' => 'msan',     'email' => 'msan@sweetsica.com',
             'password' => Hash::make('123'), 'vai_tro_id' => $vrIds['quyen_xem'], 'is_admin' => false],
            ['username' => 'mstuyet',  'name' => 'mstuyet',  'email' => 'mstuyet@sweetsica.com',
             'password' => Hash::make('123'), 'vai_tro_id' => $vrIds['quyen_xem'], 'is_admin' => false],

            // System admins
            ['username' => 'baoit', 'name' => 'Bảo IT', 'email' => 'baoit@sweetsica.com',
             'password' => Hash::make('baoit'), 'vai_tro_id' => $vrIds['admin'], 'is_admin' => true],
            ['username' => 'tumod', 'name' => 'Tú MOD', 'email' => 'tumod@sweetsica.com',
             'password' => Hash::make('tumod'), 'vai_tro_id' => $vrIds['admin'], 'is_admin' => true],
        ];

        foreach ($newUsers as $u) {
            if (DB::table('users')->where('username', $u['username'])->exists()) continue;
            DB::table('users')->insert(array_merge($u, [
                'created_at' => now(), 'updated_at' => now(),
            ]));
        }

        // ---- 7. Xóa tài khoản BS cá nhân + KTV generic cũ ----
        $deleteUsernames = ['ntd', 'lthd', 'ttb', 'ntn_bs', 'bb_tm', 'bh_sa', 'ktv1', 'ktv2', 'ktv3', 'ktv4', 'ktv5'];
        DB::table('users')->whereIn('username', $deleteUsernames)->delete();
    }

    public function down(): void
    {
        // Rollback không khả thi cho thay đổi dữ liệu lớn — chạy seeder gốc nếu cần.
    }
};
