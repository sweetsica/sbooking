<?php

use App\Models\PhanQuyen;
use App\Models\VaiTro;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Seed 2 quyền lịch làm việc cho vai trò "Quản trị vận hành" làm mặc định
     * (admin luôn vượt qua qua is_admin). Admin có thể chỉnh lại trong Thiết lập → Quyền.
     *  - quyen_lich_lam_viec : tạo / upload lịch làm việc
     *  - duyet_lich_lam_viec : duyệt & áp dụng lịch làm việc
     */
    public function up(): void
    {
        $vaiTroId = VaiTro::where('ma', 'quan_tri_van_hanh')->value('id');
        if (! $vaiTroId) {
            return;
        }

        foreach (['quyen_lich_lam_viec', 'duyet_lich_lam_viec'] as $truong) {
            PhanQuyen::firstOrCreate(['vai_tro_id' => $vaiTroId, 'truong' => $truong]);
        }
    }

    public function down(): void
    {
        PhanQuyen::whereIn('truong', ['quyen_lich_lam_viec', 'duyet_lich_lam_viec'])->delete();
    }
};
