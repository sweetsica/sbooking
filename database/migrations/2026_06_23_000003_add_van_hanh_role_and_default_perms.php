<?php

use App\Models\PhanQuyen;
use App\Models\VaiTro;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Thêm vai trò "Quản trị vận hành" (xem + duyệt) và seed quyền mặc định:
     *  - Nhân viên       : thêm + xem booking (xem danh sách chỉ đọc).
     *  - QT vận hành      : xem + duyệt (đặt phòng & tư vấn).
     *  - QT hệ thống (admin): is_admin = full quyền (đổi tên cho rõ).
     */
    public function up(): void
    {
        // Đổi tên vai trò admin cho rõ vai trò "Quản trị hệ thống"
        VaiTro::where('ma', 'admin')->update(['ten' => 'Quản trị hệ thống']);

        $vrNhanVien = VaiTro::firstOrCreate(['ma' => 'nhan_vien'], ['ten' => 'Nhân viên']);
        $vrVanHanh  = VaiTro::firstOrCreate(['ma' => 'quan_tri_van_hanh'], ['ten' => 'Quản trị vận hành']);

        $perms = [
            $vrNhanVien->id => ['them_booking', 'xem_booking'],
            $vrVanHanh->id  => ['xem_booking', 'duyet_booking', 'duyet_tu_van'],
        ];

        foreach ($perms as $vaiTroId => $truongs) {
            foreach ($truongs as $truong) {
                PhanQuyen::firstOrCreate(['vai_tro_id' => $vaiTroId, 'truong' => $truong]);
            }
        }
    }

    public function down(): void
    {
        $vrVanHanh = VaiTro::where('ma', 'quan_tri_van_hanh')->first();
        if ($vrVanHanh) {
            PhanQuyen::where('vai_tro_id', $vrVanHanh->id)->delete();
            $vrVanHanh->delete();
        }

        VaiTro::where('ma', 'admin')->update(['ten' => 'Quản trị viên']);
    }
};
