<?php

namespace App\Exports;

use App\Models\Booking;
use App\Models\CoSo;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class BookingExport implements FromQuery, WithHeadings, WithMapping, WithTitle
{
    public function __construct(protected CoSo $coSo) {}

    public function query()
    {
        return Booking::where('co_so_id', $this->coSo->id)
            ->with(['khachHang', 'phong', 'khungGio', 'dichVu', 'bacSi', 'sale'])
            ->latest('id');
    }

    public function headings(): array
    {
        return [
            'ID', 'Dấu thời gian', 'Họ tên KH', 'Số điện thoại', 'Email',
            'Ngày đặt', 'Phòng', 'Khung giờ', 'Giờ thực hiện', 'Giờ kết thúc',
            'Nguồn', 'Sale phụ trách', 'Dịch vụ', 'Số lượng',
            'Bác sĩ / Điều dưỡng', 'Ghi chú',
            // 2026-08-19: tách 2 cột — Trạng thái (approval) + Kết quả (outcome).
            'Trạng thái', 'Kết quả',
        ];
    }

    public function map($bk): array
    {
        // 2026-08-19: mirror logic dashboard/bookings blade.
        //   Trạng thái: cho_duyet / da_duyet / tu_choi (da_xong coi như đã duyệt).
        //   Kết quả: Đã xong / Trễ / Hủy / (rỗng).
        $trangThai = match ($bk->trang_thai) {
            'cho_duyet' => 'Chờ duyệt',
            'da_duyet'  => 'Đã duyệt',
            'tu_choi'   => 'Từ chối',
            'da_xong'   => 'Đã duyệt',
            default     => $bk->trang_thai ?? '',
        };
        $ketQua = match (true) {
            $bk->trang_thai === 'da_xong'    => 'Đã xong',
            $bk->trang_thai_khach === 'toi_tre' => 'Trễ',
            $bk->trang_thai_khach === 'huy'  => 'Hủy',
            default                          => '',
        };

        return [
            $bk->id,
            $bk->created_at?->format('d/m/Y H:i'),
            $bk->khachHang?->ho_ten,
            $bk->khachHang?->so_dien_thoai,
            $bk->khachHang?->email,
            $bk->ngay_dat?->format('d/m/Y'),
            $bk->phong?->ten,
            $bk->khungGio?->nhan ?? '',
            substr($bk->gio_thuc_hien ?? '', 0, 5),
            substr($bk->gio_ket_thuc ?? '', 0, 5),
            $bk->nguon,
            $bk->sale?->name,
            $bk->dichVu?->ten,
            $bk->so_luong,
            $bk->bacSi?->ten_day_du ?? '',
            $bk->ghi_chu,
            $trangThai,
            $ketQua,
        ];
    }

    public function title(): string
    {
        return 'Booking';
    }
}
