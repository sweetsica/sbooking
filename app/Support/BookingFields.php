<?php

namespace App\Support;

class BookingFields
{
    /**
     * Danh sách các trường của booking có thể phân quyền sửa.
     * 16 trường gốc (đúng các cột trong trang Danh sách) + 3 trường Xác nhận duyệt.
     *
     * @return array<string,string> [khóa => nhãn]
     */
    public static function all(): array
    {
        return [
            'dau_thoi_gian'   => 'Dấu thời gian',
            'ho_ten'          => 'Họ tên KH',
            'so_dien_thoai'   => 'Số điện thoại',
            'email'           => 'Địa chỉ Email',
            'ngay_dat'        => 'Ngày đặt',
            'phong_id'        => 'Phòng chức năng',
            'khung_gio_id'    => 'Khung giờ',
            'gio_thuc_hien'   => 'Thực hiện DV',
            'gio_ket_thuc'    => 'Dự kiến kết thúc',
            'nguon'           => 'Nguồn',
            'sale_id'         => 'Sale phụ trách',
            'dich_vu_id'      => 'Liệu pháp / Dịch vụ',
            'so_lieu_trinh'   => 'Số liệu trình',
            'ket_hop_medical' => 'Kết hợp Medical',
            'bac_si_user_id'  => 'Bác sĩ',
            'ktv_user_id'     => 'Kỹ thuật viên',
            'ghi_chu'         => 'Ghi chú',
            'duyet_booking'      => 'Duyệt lịch đặt phòng',
            'sua_lich_dat_phong' => 'Sửa lịch đặt phòng',
            'xuat_lich_dat_phong' => 'Xuất lịch đặt phòng (Excel)',
            'xoa_lich_dat_phong' => 'Xóa lịch đặt phòng',
        ];
    }

    public static function keys(): array
    {
        return array_keys(self::all());
    }
}
