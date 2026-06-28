<?php

use App\Models\PhanQuyen;
use App\Models\VaiTro;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Seed 4 quyền thông báo mới:
     *  - nhan_tb_tag_lich      : khi được tag/gán vào lịch (lúc tạo)
     *  - nhan_tb_cap_nhat_lich : khi lịch được cập nhật / duyệt
     *  - nhan_tb_huy_lich      : khi lịch bị hủy / từ chối
     *  - nhan_tb_nhac_hen      : nhắc hẹn trước giờ thực hiện
     *
     * Mặc định:
     *  - BS / KTV / BS tư vấn  → nhận: tag, cập nhật, nhắc hẹn (không nhận huỷ — tránh phiền)
     *  - Lễ tân / Nhân viên / QT vận hành → nhận: huỷ + cập nhật (cần biết để xử lý)
     *  - Admin: full quyền qua is_admin, không cần seed
     */
    public function up(): void
    {
        $chuyenMon = ['bac_si', 'ktv', 'bac_si_tu_van'];   // người được lên lịch
        $vanHanh   = ['le_tan', 'nhan_vien', 'quan_tri_van_hanh']; // người điều phối

        $permChuyenMon = ['nhan_tb_tag_lich', 'nhan_tb_cap_nhat_lich', 'nhan_tb_nhac_hen'];
        $permVanHanh   = ['nhan_tb_cap_nhat_lich', 'nhan_tb_huy_lich'];

        foreach (VaiTro::whereIn('ma', $chuyenMon)->pluck('id') as $vaiTroId) {
            foreach ($permChuyenMon as $truong) {
                PhanQuyen::firstOrCreate(['vai_tro_id' => $vaiTroId, 'truong' => $truong]);
            }
        }

        foreach (VaiTro::whereIn('ma', $vanHanh)->pluck('id') as $vaiTroId) {
            foreach ($permVanHanh as $truong) {
                PhanQuyen::firstOrCreate(['vai_tro_id' => $vaiTroId, 'truong' => $truong]);
            }
        }
    }

    public function down(): void
    {
        PhanQuyen::whereIn('truong', [
            'nhan_tb_tag_lich',
            'nhan_tb_cap_nhat_lich',
            'nhan_tb_huy_lich',
            'nhan_tb_nhac_hen',
        ])->delete();
    }
};
