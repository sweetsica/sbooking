<?php

use App\Models\CoSo;
use App\Models\DichVu;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $newServices = [
            'Thủy châm (1 vùng)',
            'BJR (1 vùng)',
            'Metaboost',
            'MesoF',
            'Thải độc (ILR)',
            'Miễn dịch (MAT)',
            'Y học Phương Đông (Ghế YHCT tầng 4)',
        ];

        foreach (CoSo::pluck('id') as $coSoId) {
            foreach ($newServices as $ten) {
                DichVu::firstOrCreate(
                    ['co_so_id' => $coSoId, 'ten' => $ten],
                    ['thuoc_nhom' => 'khac', 'la_dich_vu' => true, 'active' => true],
                );
            }
        }
    }

    public function down(): void
    {
        $names = [
            'Thủy châm (1 vùng)',
            'BJR (1 vùng)',
            'Metaboost',
            'MesoF',
            'Thải độc (ILR)',
            'Miễn dịch (MAT)',
            'Y học Phương Đông (Ghế YHCT tầng 4)',
        ];

        DichVu::whereIn('ten', $names)->whereIn('co_so_id', [1, 2])->delete();
    }
};
