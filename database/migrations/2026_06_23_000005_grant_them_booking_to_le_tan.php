<?php

use App\Models\PhanQuyen;
use App\Models\VaiTro;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /** Lễ tân: được THÊM booking (ngoài quyền xem đã có). */
    public function up(): void
    {
        if ($vr = VaiTro::where('ma', 'le_tan')->first()) {
            PhanQuyen::firstOrCreate(['vai_tro_id' => $vr->id, 'truong' => 'them_booking']);
        }
    }

    public function down(): void
    {
        if ($vr = VaiTro::where('ma', 'le_tan')->first()) {
            PhanQuyen::where('vai_tro_id', $vr->id)->where('truong', 'them_booking')->delete();
        }
    }
};
