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
        $this->call(LongevitySeeder::class);
        $this->call(LichDatMauSeeder::class);
        $this->call(LichThang6Seeder::class);
        $this->call(LichTuVanThang6Seeder::class);
        $this->call(LichLamViecMauSeeder::class);
    }
}
