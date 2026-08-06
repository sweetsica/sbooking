<?php

namespace App\Imports;

use App\Models\Booking;
use App\Models\CoSo;
use App\Models\KhachHang;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class BookingImport implements ToModel, WithHeadingRow, WithValidation
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

        $ngay = $this->parseDate($row['ngay_dat'] ?? '');

        $this->imported++;

        return new Booking([
            'co_so_id' => $this->coSo->id,
            'khach_hang_id' => $kh->id,
            'ngay_dat' => $ngay ?: now()->toDateString(),
            'gio_thuc_hien' => $row['gio_thuc_hien'] ?? null,
            'gio_ket_thuc' => $row['gio_ket_thuc'] ?? null,
            'nguon' => $row['nguon'] ?? null,
            'so_luong' => $row['so_luong'] ?? $row['so_lieu_trinh'] ?? null,
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
