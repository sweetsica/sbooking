<?php

use App\Models\PhanQuyen;
use App\Models\VaiTro;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Seed quyền "quyen_ngay_nghi" cho vai trò "Quản trị vận hành" làm mặc định
     * (admin luôn vượt qua qua is_admin). Admin có thể chỉnh lại trong Thiết lập → Quyền.
     */
    public function up(): void
    {
        $vaiTroId = VaiTro::where('ma', 'quan_tri_van_hanh')->value('id');
        if (! $vaiTroId) {
            return;
        }

        PhanQuyen::firstOrCreate(['vai_tro_id' => $vaiTroId, 'truong' => 'quyen_ngay_nghi']);
    }

    public function down(): void
    {
        PhanQuyen::where('truong', 'quyen_ngay_nghi')->delete();
    }
};
