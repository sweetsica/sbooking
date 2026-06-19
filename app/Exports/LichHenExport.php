<?php

namespace App\Exports;

use App\Models\CoSo;
use App\Models\LichHen;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class LichHenExport implements FromQuery, WithHeadings, WithMapping, WithTitle
{
    public function __construct(protected CoSo $coSo) {}

    public function query()
    {
        return LichHen::where('co_so_id', $this->coSo->id)
            ->with(['khachHang', 'bacSiTuVan', 'caKham', 'sale'])
            ->latest('id');
    }

    public function headings(): array
    {
        return [
            'ID', 'Dấu thời gian', 'Họ tên KH', 'Số điện thoại', 'Email',
            'Ngày hẹn', 'Bác sĩ tư vấn', 'Ca khám', 'Nguồn',
            'Sale phụ trách', 'Ghi chú', 'Trạng thái',
        ];
    }

    public function map($lh): array
    {
        return [
            $lh->id,
            $lh->created_at?->format('d/m/Y H:i'),
            $lh->khachHang?->ho_ten,
            $lh->khachHang?->so_dien_thoai,
            $lh->khachHang?->email,
            $lh->ngay_hen?->format('d/m/Y'),
            $lh->bacSiTuVan?->ten_day_du,
            $lh->caKham?->nhan,
            $lh->nguon,
            $lh->sale?->name,
            $lh->ghi_chu,
            $lh->trang_thai === 'da_duyet' ? 'Đã duyệt' : ($lh->trang_thai === 'tu_choi' ? 'Từ chối' : 'Chờ duyệt'),
        ];
    }

    public function title(): string
    {
        return 'Lịch tư vấn';
    }
}
