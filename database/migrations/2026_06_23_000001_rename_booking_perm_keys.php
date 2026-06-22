<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('phan_quyen')->where('truong', 'sua_lich_dat_phong')->update(['truong' => 'sua_booking']);
        DB::table('phan_quyen')->where('truong', 'xoa_lich_dat_phong')->update(['truong' => 'xoa_booking']);
    }

    public function down(): void
    {
        DB::table('phan_quyen')->where('truong', 'sua_booking')->update(['truong' => 'sua_lich_dat_phong']);
        DB::table('phan_quyen')->where('truong', 'xoa_booking')->update(['truong' => 'xoa_lich_dat_phong']);
    }
};
