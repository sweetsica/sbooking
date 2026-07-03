<?php

use App\Models\PhanQuyen;
use App\Models\VaiTro;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $rows = PhanQuyen::where('truong', 'xem_booking')->get();

        foreach ($rows as $row) {
            $newTruong = 'xem_booking_co_so_toi';

            if ($row->vai_tro_id) {
                $vaiTro = VaiTro::find($row->vai_tro_id);
                if ($vaiTro) {
                    $newTruong = match ($vaiTro->ma) {
                        'admin', 'quan_tri_van_hanh' => 'xem_booking_tat_ca',
                        'ktv' => 'xem_booking_phong_toi',
                        'bac_si', 'bac_si_tu_van' => 'xem_booking_cua_toi',
                        default => 'xem_booking_co_so_toi',
                    };
                }
            }

            PhanQuyen::firstOrCreate([
                'vai_tro_id'  => $row->vai_tro_id,
                'phong_ban_id' => $row->phong_ban_id,
                'truong'      => $newTruong,
            ]);
        }

        PhanQuyen::where('truong', 'xem_booking')->delete();
    }

    public function down(): void
    {
        $newKeys = [
            'xem_booking_cua_toi',
            'xem_booking_phong_toi',
            'xem_booking_co_so_toi',
            'xem_booking_tat_ca',
        ];

        $rows = PhanQuyen::whereIn('truong', $newKeys)->get();

        foreach ($rows as $row) {
            PhanQuyen::firstOrCreate([
                'vai_tro_id'  => $row->vai_tro_id,
                'phong_ban_id' => $row->phong_ban_id,
                'truong'      => 'xem_booking',
            ]);
        }

        PhanQuyen::whereIn('truong', $newKeys)->delete();
    }
};
