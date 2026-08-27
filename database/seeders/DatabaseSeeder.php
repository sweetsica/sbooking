<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Chỉ seed dữ liệu THẬT: cơ sở, phòng ban, users, phòng, bác sĩ, dịch vụ, lịch nghỉ.
        $this->call(LongevitySeeder::class);

        // 3 seeder nhân sự theo cơ sở (tạo sale/tele/tuvan/page01 gốc, viết tắt username).
        $this->call(HanoiStaffSeeder::class);
        $this->call(HcmStaffSeeder::class);
        $this->call(DaNangStaffSeeder::class);

        // Đồng bộ username sbooking với format vị trí bên CRM (<cơ_sở>.<chức_vụ>NN) — match qua name.
        $this->call(SyncUsernamesFromCrmSeeder::class);

        // Đồng bộ email theo username (@longevity.com.vn).
        $this->call(SyncBookingEmailsSeeder::class);

        // Đồng bộ dich_vu HN theo sheet (deactive gạch, sửa duration, rename).
        $this->call(SyncDichVuFromSheetSeeder::class);

        // Backfill khung_gio cho phòng mới thiếu (migration 2026_08_24_150000 quên tạo).
        $this->call(EnsurePhongKhungGioSeeder::class);

        // Gán BS ↔ Phòng khám theo chuyên khoa (áng từ tên BS + scrm.staff_members.title).
        $this->call(PhongBacSiSeeder::class);

        // Lịch làm việc mẫu (3 cơ sở, tháng hiện tại + tháng sau, mọi BS/KTV trực S+C).
        $this->call(LichLamViecMauSeeder::class);

        // Các seeder DEMO booking — đã TẮT (bật khi cần demo data đặt lịch):
        // $this->call(LichDatMauSeeder::class);
        // $this->call(LichThang6Seeder::class);
        // $this->call(LichTuVanThang6Seeder::class);
    }
}
