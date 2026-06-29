<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;

/**
 * Thu thập toàn bộ dòng (kể cả hàng tiêu đề) của 1 sheet vào mảng dùng chung
 * trên LichLamViecImport. Đọc theo VỊ TRÍ CỘT — không dùng heading row để
 * tránh lệ thuộc cách Maatwebsite slug hoá tiêu đề tiếng Việt.
 */
class LichLamViecSheet implements ToArray
{
    public function __construct(protected LichLamViecImport $parent, protected string $loai) {}

    public function array(array $rows): void
    {
        $this->parent->data[$this->loai] = $rows;
    }
}
