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

        // Đồng bộ username sbooking với format vị trí bên CRM (<cơ_sở>.<chức_vụ>NN) — match qua name.
        $this->call(SyncUsernamesFromCrmSeeder::class);

        // Các seeder lịch DEMO (booking mẫu, lịch làm việc mẫu) — đã TẮT.
        // Bật lại khi cần demo:
        // $this->call(LichDatMauSeeder::class);
        // $this->call(LichThang6Seeder::class);
        // $this->call(LichTuVanThang6Seeder::class);
        // $this->call(LichLamViecMauSeeder::class);
    }
}
