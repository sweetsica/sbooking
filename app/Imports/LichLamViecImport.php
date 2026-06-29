<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Đọc file Excel lịch phân công theo ngày (theo THỨ TỰ sheet trong file mẫu):
 *   0 = Bác sĩ (lưới: Mã phòng | Vị trí | Ca | 1..31)
 *   1 = KTV    (lưới như trên)
 * Các sheet tra cứu (DS Bác sĩ / DS KTV) ở index 2,3 được bỏ qua.
 *
 * Kết quả thô: $this->data[loai] = mảng dòng (hàng 0 là tiêu đề chứa số ngày).
 */
class LichLamViecImport implements WithMultipleSheets
{
    /** @var array<string,array> [loai => rows] */
    public array $data = [
        'bac_si' => [],
        'ktv'    => [],
    ];

    public function sheets(): array
    {
        return [
            0 => new LichLamViecSheet($this, 'bac_si'),
            1 => new LichLamViecSheet($this, 'ktv'),
        ];
    }
}
