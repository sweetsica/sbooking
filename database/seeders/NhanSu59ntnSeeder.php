<?php

namespace Database\Seeders;

use App\Models\CoSo;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class NhanSu59ntnSeeder extends Seeder
{
    /**
     * Nhân sự cơ sở 59ntn.
     * Tài khoản (username) = chữ cái đầu mỗi từ trong tên (bỏ dấu, viết thường).
     * Mật khẩu chung: 59@ntn
     */
    public function run(): void
    {
        $coSo = CoSo::where('slug', '59ntn')->first();
        if (! $coSo) {
            $this->command?->warn('Chưa có cơ sở 59ntn — bỏ qua.');

            return;
        }

        // [Họ tên => tài khoản]
        $nhanSu = [
            'Trần Thị Thu Giang'  => 'tttg',
            'Nguyễn Hồng Nhung'   => 'nhn',
            'Nguyễn Thị Hiền'     => 'nth',
            'Nguyễn Minh Phương'  => 'nmp',
            'Nguyễn Hương Giang'  => 'nhg',
            'Đỗ Thu Hương'        => 'dth',
            'Nguyễn Thị Nga'      => 'ntn',
            'Phạm Tú Anh'         => 'pta',
            'Tạ Văn Hợi'          => 'tvh',
            'Nguyễn Văn Nam'      => 'nvn',
            'Phạm Thanh Trúc'     => 'ptt',
            'Nguyễn Hoành Đức'    => 'nhd',
            'Nguyễn Trà My'       => 'ntm',
            'Diệu Hạnh'           => 'dh',
            'Cao Thị Lan Anh'     => 'ctla',
            'Nguyễn Mai Anh'      => 'nma',
            'Trần Huy Kiên'       => 'thk',
            'Nguyễn Thị Anh'      => 'nta',
        ];

        $matKhau = Hash::make('59@ntn');
        $count = 0;

        foreach ($nhanSu as $hoTen => $taiKhoan) {
            User::updateOrCreate(
                ['username' => $taiKhoan],
                [
                    'name'         => $hoTen,
                    'email'        => $taiKhoan . '@59ntn.local',
                    'password'     => $matKhau,
                    'co_so_id'     => $coSo->id,
                    'phong_ban_id' => null,
                    'is_admin'     => false,
                ]
            );
            $count++;
        }

        $this->command?->info("Đã thêm/cập nhật {$count} nhân sự cho cơ sở 59ntn (mật khẩu: 59@ntn).");
    }
}
