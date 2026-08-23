<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;

/**
 * Xuất toàn bộ cấu hình Phòng / Dịch vụ / Bác sĩ của MỌI cơ sở vào 1 file 3 sheet.
 * Cột co_so_id ở mỗi sheet để phân biệt.
 */
class CauHinhExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new PhongSheet(),
            new DichVuSheet(),
            new BacSiSheet(),
        ];
    }
}

class PhongSheet implements FromCollection, WithHeadings, WithTitle
{
    public function title(): string { return 'Phong'; }

    public function headings(): array
    {
        return ['id', 'co_so_id', 'ten', 'kieu_phong', 'duoc_dat_tu_van', 'loai', 'so_slot_toi_da', 'phut_moi_khach', 'trang_thai'];
    }

    public function collection()
    {
        return DB::table('phong')
            ->orderBy('co_so_id')->orderBy('id')
            ->get(['id', 'co_so_id', 'ten', 'kieu_phong', 'duoc_dat_tu_van', 'loai', 'so_slot_toi_da', 'phut_moi_khach', 'trang_thai'])
            ->map(fn ($r) => (array) $r);
    }
}

class DichVuSheet implements FromCollection, WithHeadings, WithTitle
{
    public function title(): string { return 'DichVu'; }

    public function headings(): array
    {
        return ['id', 'co_so_id', 'ten', 'thoi_gian_phut', 'thuoc_nhom', 'la_dich_vu', 'active'];
    }

    public function collection()
    {
        return DB::table('dich_vu')
            ->orderBy('co_so_id')->orderBy('id')
            ->get(['id', 'co_so_id', 'ten', 'thoi_gian_phut', 'thuoc_nhom', 'la_dich_vu', 'active'])
            ->map(fn ($r) => (array) $r);
    }
}

class BacSiSheet implements FromCollection, WithHeadings, WithTitle
{
    public function title(): string { return 'BacSi'; }

    public function headings(): array
    {
        return ['id', 'co_so_id', 'ten', 'chuc_danh', 'nhan_tu_van', 'phut_tu_van', 'nhan_kham_ls', 'phut_kham_ls', 'gio_bat_dau', 'gio_ket_thuc', 'xuat_hien_moi_co_so', 'active'];
    }

    public function collection()
    {
        return DB::table('bac_si')
            ->orderBy('co_so_id')->orderBy('id')
            ->get(['id', 'co_so_id', 'ten', 'chuc_danh', 'nhan_tu_van', 'phut_tu_van', 'nhan_kham_ls', 'phut_kham_ls', 'gio_bat_dau', 'gio_ket_thuc', 'xuat_hien_moi_co_so', 'active'])
            ->map(fn ($r) => (array) $r);
    }
}
