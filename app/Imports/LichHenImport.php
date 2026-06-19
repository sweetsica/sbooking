<?php

namespace App\Imports;

use App\Models\CoSo;
use App\Models\KhachHang;
use App\Models\LichHen;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class LichHenImport implements ToModel, WithHeadingRow, WithValidation
{
    public int $imported = 0;

    public function __construct(protected CoSo $coSo) {}

    public function model(array $row)
    {
        $sdt = preg_replace('/\s+/', '', $row['so_dien_thoai'] ?? '');
        if (! $sdt) {
            return null;
        }

        $kh = KhachHang::firstOrNew(['co_so_id' => $this->coSo->id, 'so_dien_thoai' => $sdt]);
        $kh->ho_ten = $row['ho_ten_kh'] ?? $kh->ho_ten ?? 'Không tên';
        $kh->email = $row['email'] ?? $kh->email;
        $kh->save();

        $ngay = $this->parseDate($row['ngay_hen'] ?? '');

        $this->imported++;

        return new LichHen([
            'co_so_id' => $this->coSo->id,
            'khach_hang_id' => $kh->id,
            'ngay_hen' => $ngay ?: now()->toDateString(),
            'nguon' => $row['nguon'] ?? null,
            'ghi_chu' => $row['ghi_chu'] ?? null,
            'trang_thai' => 'cho_duyet',
        ]);
    }

    public function rules(): array
    {
        return [
            'so_dien_thoai' => ['required'],
        ];
    }

    private function parseDate(?string $value): ?string
    {
        if (! $value) {
            return null;
        }
        if (preg_match('#(\d{1,2})/(\d{1,2})/(\d{4})#', $value, $m)) {
            return "{$m[3]}-{$m[2]}-{$m[1]}";
        }

        return date('Y-m-d', strtotime($value)) ?: null;
    }
}
