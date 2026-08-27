<?php

namespace Database\Seeders;

use App\Models\BacSi;
use App\Models\CoSo;
use App\Models\Phong;
use Illuminate\Database\Seeder;

/**
 * Gán BS ↔ phòng khám qua pivot phong_bac_si, áng theo keyword tên BS.
 *
 * Rule: BS chuyên khoa nào → phòng có tên khớp keyword đó (VD "Tim mạch" → phòng Nội,
 * "siêu âm" → Phòng siêu âm). Không match → gán vào phòng khám đa khoa mặc định của cơ sở.
 */
class PhongBacSiSeeder extends Seeder
{
    /** keyword trong tên BS → mảng regex khớp tên phòng khám. */
    private const RULES = [
        // Tim mạch không có phòng riêng → dồn về Phòng Nội
        'tim mạch'           => ['/khám nội|^phòng nội$/i'],
        'chuyên gia|giám đốc|phó tổng' => ['/chuyên gia/i'],
        'nội'                => ['/khám nội|^phòng nội$/i'],
        'ngoại'              => ['/ngoại/i'],
        'siêu âm'            => ['/siêu âm/i'],
        'x[-\s]?quang|chẩn đoán hình ảnh' => ['/x[\s]?quang/i'],
        'da liễu|^bác da$'   => ['/da|visia/i'],
        'yhct|y học cổ truyền' => ['/yhct/i'],
        'sản|phụ khoa'       => ['/sản|phụ/i'],
        'xét nghiệm'         => ['/xét nghiệm|lấy mẫu/i'],
    ];

    public function run(): void
    {
        foreach (CoSo::whereIn('slug', ['59ntn', '207nvt', 'lo23tdn'])->get() as $coSo) {
            $this->seedCoSo($coSo);
        }
    }

    private function seedCoSo(CoSo $coSo): void
    {
        $phongKham = Phong::where('co_so_id', $coSo->id)
            ->where('kieu_phong', 'phong_kham')->get();
        // BS của cơ sở + BS global (xuat_hien_moi_co_so=true) từ mọi cơ sở.
        $bsList = BacSi::where(function ($q) use ($coSo) {
            $q->where('co_so_id', $coSo->id)
              ->orWhere('xuat_hien_moi_co_so', true);
        })->get();

        if ($phongKham->isEmpty() || $bsList->isEmpty()) {
            $this->command?->warn("Bỏ qua {$coSo->slug} (phòng khám hoặc BS rỗng).");
            return;
        }

        // Phòng mặc định (BS không match keyword nào) — ưu tiên chuyên gia > nội > khám chung.
        $default = $phongKham->first(fn ($p) => preg_match('/chuyên gia/i', $p->ten))
            ?? $phongKham->first(fn ($p) => preg_match('/nội/i', $p->ten))
            ?? $phongKham->first(fn ($p) => preg_match('/^phòng khám$/i', $p->ten))
            ?? $phongKham->first();

        // Cross-lookup chuyên khoa từ lara-scrm.staff_members (cùng MySQL host).
        $scrmTitles = $this->loadScrmTitles();

        $totalAttached = 0;
        foreach ($bsList as $bs) {
            $blob = $bs->ten . ' ' . ($scrmTitles[$bs->ten] ?? '');
            $matched = $this->matchPhong($blob, $phongKham);
            if ($matched->isEmpty()) {
                $matched = collect([$default]);
            }
            foreach ($matched as $phong) {
                \DB::table('phong_bac_si')->updateOrInsert(
                    ['phong_id' => $phong->id, 'bac_si_id' => $bs->id],
                    []
                );
                $totalAttached++;
            }
        }

        $this->command?->info(sprintf(
            'Seeded pivot phong_bac_si %s: %d BS × trung bình %.1f phòng = %d gán',
            $coSo->slug,
            $bsList->count(),
            $totalAttached / max($bsList->count(), 1),
            $totalAttached
        ));
    }

    /** Nạp title BS từ DB lara-scrm.staff_members (cross-DB query). Trả [name => title]. */
    private function loadScrmTitles(): array
    {
        try {
            $rows = \DB::select("SELECT name, title FROM `lara-crm`.staff_members WHERE role = 'doctor'");
            $out = [];
            foreach ($rows as $r) {
                $out[$r->name] = $r->title;
            }
            return $out;
        } catch (\Throwable $e) {
            $this->command?->warn('Không đọc được lara-scrm.staff_members: ' . $e->getMessage());
            return [];
        }
    }

    /** Trả về collection phòng khớp bất kỳ rule nào theo tên BS. */
    private function matchPhong(string $tenBs, $phongKham)
    {
        $matched = collect();
        foreach (self::RULES as $bsKw => $phongRegexes) {
            if (! preg_match('/' . $bsKw . '/iu', $tenBs)) {
                continue;
            }
            foreach ($phongRegexes as $phongRegex) {
                $matched = $matched->merge($phongKham->filter(fn ($p) => preg_match($phongRegex, $p->ten)));
            }
        }
        return $matched->unique('id')->values();
    }
}
