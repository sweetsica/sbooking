<?php

use App\Models\PhanQuyen;
use App\Models\VaiTro;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Lễ tân, KTV, Bác sĩ, Bác sĩ tư vấn: được XEM booking (chỉ đọc),
     * không có quyền sửa/xóa/duyệt. Admin hệ thống đã full quyền qua is_admin.
     */
    public function up(): void
    {
        $maList = ['ktv', 'bac_si', 'bac_si_tu_van', 'le_tan'];
        foreach (VaiTro::whereIn('ma', $maList)->pluck('id') as $vaiTroId) {
            PhanQuyen::firstOrCreate(['vai_tro_id' => $vaiTroId, 'truong' => 'xem_booking']);
        }
    }

    public function down(): void
    {
        $maList = ['ktv', 'bac_si', 'bac_si_tu_van', 'le_tan'];
        $ids = VaiTro::whereIn('ma', $maList)->pluck('id');
        PhanQuyen::whereIn('vai_tro_id', $ids)->where('truong', 'xem_booking')->delete();
    }
};
