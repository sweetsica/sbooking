<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Đợt C.1: Bảng many-to-many `dich_vu_phong` + seed mapping HN + HCM.
// KHÔNG dùng FK cứng để idempotent + tránh vướng nếu data cũ có row lệch.
// Sale UI sẽ auto-suggest phòng theo mapping này ở đợt UI sau.
//
// Chưa xử: DV 41 DeepOxy Tổng hợp (multi-room lock), DV 40 pairing giới,
// STC no-room UI logic — dời Đợt C.2 khi thiết kế schema thêm.
return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('dich_vu_phong')) {
            Schema::create('dich_vu_phong', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('dich_vu_id');
                $t->unsignedBigInteger('phong_id');
                $t->timestamps();
                $t->unique(['dich_vu_id', 'phong_id']);
                $t->index('dich_vu_id');
                $t->index('phong_id');
            });
        }

        // Format mapping: [dich_vu_id => [phong_id, phong_id, ...]]
        // HN (co_so=1) — id phòng: 3=Nội 1, 4=Nội 2, 5=Siêu âm, 6=Thủ thuật T3,
        //   7-9=Metaboost 1/2/3, 10-12=YHCT 1/2/3, 18=X Quang, 19=Lấy mẫu,
        //   20=Da, 21=VISIA, 22=Xông, 23=Truyền. (1=Ngoại, 2=Chuyên gia — giữ nguyên seed cũ)
        // HCM (co_so=2) — id phòng: 14=Siêu âm, 15=YHCT, 24=Khám, 25=Nội,
        //   26=Xét nghiệm, 27=Cơ sở điều dưỡng, 28=X Quang.
        $mapping = [
            // ============= HN (co_so=1) =============
            // Khám lâm sàng
            2   => [1, 3, 4],           // Thăm khám tim mạch → Nội 1 + Nội 2 + Ngoại (chốt Q&A)
            4   => [5],                 // Siêu âm → Phòng siêu âm
            5   => [18],                // Chụp XQuang → Phòng X Quang
            6   => [19],                // Lấy máu → Phòng lấy mẫu
            7   => [3, 4],              // Khám Nội → Nội 1 + Nội 2
            9   => [20],                // Khám Da liễu (bác sĩ) → Phòng da
            177 => [21],                // Khám Da (Visia) → Phòng VISIA
            178 => [19],                // Thực hiện lâm sàng (lấy máu) → Phòng lấy mẫu
            179 => [5],                 // Thực hiện lâm sàng (siêu âm) → Phòng siêu âm
            180 => [18],                // Thực hiện lâm sàng (Xquang) → Phòng X Quang
            // Dịch vụ
            34  => [10, 11, 12],        // EAQ Thủy châm → YHCT 1/2/3
            181 => [10, 11, 12],        // YHPĐ 30' → YHCT 1/2/3
            182 => [10, 11, 12],        // YHPĐ 45' → YHCT 1/2/3
            183 => [10, 11, 12],        // YHPĐ 60' → YHCT 1/2/3
            35  => [6, 7, 8, 9],        // BJR (Tiêm gối) → Thủ thuật T3 + Metaboost 1/2/3
            36  => [6, 7, 8, 9],        // HA 1% (Tiêm dịch nhờn) → cả 4
            37  => [6, 7, 8, 9],        // HA 2% (Tiêm khớp gối) → cả 4
            38  => [6, 7, 8, 9],        // PRP/khớp → cả 4
            44  => [6, 7, 8, 9],        // Recells (Tiêm) → cả 4
            40  => [22],                // DeepOxy Xông → Phòng Xông
            43  => [23],                // NK (Truyền miễn dịch) → Phòng truyền
            // Skip: id 8 Khám Sản (chưa triển khai), id 42 STC (no room),
            //       id 41 DeepOxy Tổng hợp (combo Xông+YHPĐ — Đợt C.2)

            // ============= HCM (co_so=2) =============
            // Khám lâm sàng
            46  => [24, 25],            // Thăm khám tim mạch → Phòng khám + Phòng Nội (HCM tương tự HN, thu gọn)
            48  => [14],                // Siêu âm → Phòng siêu âm
            49  => [28],                // Chụp XQuang → Phòng X Quang
            50  => [26],                // Lấy máu → Phòng Xét nghiệm
            51  => [25],                // Khám Nội → Phòng Nội
            187 => [26],                // Thực hiện lâm sàng (lấy máu) → Phòng Xét nghiệm
            188 => [14],                // Thực hiện lâm sàng (siêu âm) → Phòng siêu âm
            189 => [28],                // Thực hiện lâm sàng (Xquang) → Phòng X Quang
            // Dịch vụ
            78  => [15],                // EAQ Thủy châm → Phòng YHCT
            184 => [15],                // YHPĐ 30' → Phòng YHCT
            185 => [15],                // YHPĐ 45' → Phòng YHCT
            186 => [15],                // YHPĐ 60' → Phòng YHCT
            79  => [25],                // BJR → Phòng Nội (Thủ thuật nội)
            80  => [25],                // HA 1% → Phòng Nội
            81  => [25],                // HA 2% → Phòng Nội
            82  => [25],                // PRP → Phòng Nội
            88  => [25],                // Recells → Phòng Nội
            87  => [27],                // NK (Truyền miễn dịch) → Phòng Cơ sở điều dưỡng
            // Skip: id 52 Khám Sản (chưa triển khai), id 86 STC (no room),
            //       id 45,47,53,73-77,83,84,85 (đã inactive)
        ];

        $now = now();
        foreach ($mapping as $dvId => $phongIds) {
            foreach ($phongIds as $phongId) {
                $exists = DB::table('dich_vu_phong')
                    ->where('dich_vu_id', $dvId)
                    ->where('phong_id', $phongId)
                    ->exists();
                if (! $exists) {
                    DB::table('dich_vu_phong')->insert([
                        'dich_vu_id' => $dvId,
                        'phong_id'   => $phongId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('dich_vu_phong');
    }
};
