<?php

namespace App\Exports;

use App\Models\CoSo;
use App\Models\User;
use App\Models\VaiTro;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Xuất file Excel MẪU lịch phân công theo ngày cho 1 cơ sở.
 *
 * 4 sheet THEO ĐÚNG THỨ TỰ:
 *   0 = Bác sĩ  (lưới: Mã phòng | Vị trí | Ca | 1..31) — phòng khám
 *   1 = KTV     (lưới như trên) — phòng dịch vụ
 *   2 = DS Bác sĩ (tra cứu username)
 *   3 = DS KTV    (tra cứu username)
 *
 * Người dùng điền USERNAME bác sĩ/KTV vào ô ngày tương ứng (ca Sáng/Chiều).
 * Ô trống = phòng đó đóng ca/ngày đó.
 */
class LichLamViecMauExport implements WithMultipleSheets
{
    public function __construct(protected CoSo $coSo) {}

    public function sheets(): array
    {
        return [
            $this->gridSheet('Bác sĩ', 'phong_kham'),
            $this->gridSheet('KTV', 'phong_dich_vu'),
            $this->dsBacSiSheet('DS Bác sĩ'),
            $this->dsSheet('DS KTV', ['ktv']),
        ];
    }

    private function gridSheet(string $title, string $kieuPhong): LichLamViecSheetExport
    {
        $headings = array_merge(['Mã phòng', 'Vị trí', 'Ca'], range(1, 31));

        $rows = [];
        $phongs = $this->coSo->phongs()->where('kieu_phong', $kieuPhong)->orderBy('ten')->get(['id', 'ten']);
        foreach ($phongs as $p) {
            foreach (['Sáng', 'Chiều'] as $ca) {
                $rows[] = array_merge([$p->id, $p->ten, $ca], array_fill(0, 31, ''));
            }
        }

        return new LichLamViecSheetExport($title, $headings, $rows);
    }

    /** DS Bác sĩ = danh mục bac_si (điền theo HỌ TÊN vào lưới). */
    private function dsBacSiSheet(string $title): LichLamViecSheetExport
    {
        $rows = \App\Models\BacSi::where('active', true)
            ->where(fn ($q) => $q->where('co_so_id', $this->coSo->id)->orWhere('xuat_hien_moi_co_so', true))
            ->orderBy('ten')
            ->get()
            ->map(fn ($b) => [$b->ten_day_du])
            ->all();

        return new LichLamViecSheetExport($title, ['Họ tên'], $rows);
    }

    private function dsSheet(string $title, array $vaiTroMa): LichLamViecSheetExport
    {
        $vaiTroIds = VaiTro::whereIn('ma', $vaiTroMa)->pluck('id');

        $rows = User::whereIn('vai_tro_id', $vaiTroIds)
            ->where(fn ($q) => $q->where('co_so_id', $this->coSo->id)->orWhere('is_tu_van', true))
            ->orderBy('name')
            ->get(['username', 'name'])
            ->map(fn ($u) => [$u->username, $u->name])
            ->all();

        return new LichLamViecSheetExport($title, ['Username', 'Họ tên'], $rows);
    }
}
