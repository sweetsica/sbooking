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
            // CRUD cấp chính
            'xem_booking'     => 'Xem booking',
            'them_booking'    => 'Thêm booking',
            'sua_booking'     => 'Sửa booking (cho phép sửa)',
            'xoa_booking'     => 'Xóa booking',
            // Trường con của "Sửa booking"
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
            // Nhập / xuất
            'xuat_lich_dat_phong' => 'Xuất lịch đặt phòng (Excel)',
            // Duyệt
            'duyet_booking'   => 'Duyệt lịch đặt phòng',
        ];
    }

    /** Các trường con thuộc quyền "Sửa booking" (chỉ áp dụng khi sua_booking bật). */
    public static function suaSubFields(): array
    {
        $all = self::all();
        $keys = [
            'dau_thoi_gian', 'ho_ten', 'so_dien_thoai', 'email',
            'ngay_dat', 'phong_id', 'khung_gio_id', 'gio_thuc_hien', 'gio_ket_thuc',
            'nguon', 'sale_id', 'dich_vu_id', 'so_lieu_trinh', 'ket_hop_medical',
            'bac_si_user_id', 'ktv_user_id', 'ghi_chu',
        ];
        return array_intersect_key($all, array_flip($keys));
    }

    public static function keys(): array
    {
        return array_keys(self::all());
    }

    /**
     * Gom các trường thành 3 nhóm để hiển thị ma trận phân quyền.
     *
     * @return array<string,array{icon:string,fields:array<string,string>}>
     */
    public static function groups(): array
    {
        $all = self::all();
        $pick = fn (array $keys) => array_intersect_key($all, array_flip($keys));

        return [
            'Quyền booking' => [
                'icon'   => 'edit_calendar',
                'fields' => $pick(['xem_booking', 'them_booking', 'sua_booking', 'xoa_booking']),
                'sub'    => [
                    // Trường con xuất hiện ngay dưới quyền cha 'sua_booking'
                    'sua_booking' => self::suaSubFields(),
                ],
            ],
            'Quyền nhập / xuất dữ liệu' => [
                'icon'   => 'import_export',
                'fields' => $pick(['xuat_lich_dat_phong']),
                'sub'    => [],
            ],
            'Quyền duyệt' => [
                'icon'   => 'verified',
                'fields' => $pick(['duyet_booking']),
                'sub'    => [],
            ],
        ];
    }
}
