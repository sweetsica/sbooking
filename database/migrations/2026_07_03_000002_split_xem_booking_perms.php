<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $vaiTroMap = DB::table('vai_tro')
            ->whereIn('ma', ['admin', 'quan_tri_van_hanh', 'ktv', 'bac_si', 'bac_si_tu_van', 'tu_van_vien', 'le_tan', 'nhan_vien'])
            ->pluck('ma', 'id');

        $rows = DB::table('phan_quyen')->where('truong', 'xem_booking')->get();

        foreach ($rows as $row) {
            $ma = $vaiTroMap[$row->vai_tro_id] ?? null;

            $newKey = match ($ma) {
                'admin', 'quan_tri_van_hanh' => 'xem_booking_tat_ca',
                'ktv'                        => 'xem_booking_phong_toi',
                'bac_si', 'bac_si_tu_van'    => 'xem_booking_cua_toi',
                default                      => 'xem_booking_co_so_toi',
            };

            DB::table('phan_quyen')->where('id', $row->id)->update(['truong' => $newKey]);
        }
    }

    public function down(): void
    {
        DB::table('phan_quyen')
            ->whereIn('truong', ['xem_booking_cua_toi', 'xem_booking_phong_toi', 'xem_booking_co_so_toi', 'xem_booking_tat_ca'])
            ->update(['truong' => 'xem_booking']);
    }
};
