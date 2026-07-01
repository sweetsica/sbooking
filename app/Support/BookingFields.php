<?php

namespace App\Support;

class BookingFields
{
    /**
     * Danh sách tất cả các trường có thể phân quyền (gắn theo vai trò).
     * Gồm: quyền đặt phòng (booking) + quyền đặt lịch bác sĩ (tư vấn)
     * + nhập/xuất + duyệt.
     *
     * @return array<string,string> [khóa => nhãn]
     */
    public static function all(): array
    {
        return [
            // ----- Đặt phòng (booking) -----
            'xem_booking'     => 'Xem booking',
            'them_booking'    => 'Thêm booking',
            'sua_booking'     => 'Sửa booking — tất cả (mọi lịch)',
            'sua_booking_lien_quan' => 'Sửa booking — chỉ lịch liên quan (mình tạo / BS / KTV / Sale)',
            'sua_booking_dich_vu_cua_toi' => 'Sửa booking phòng dịch vụ — chỉ của tôi (dịch vụ + mình là BS/KTV/Sale/người tạo)',
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

            // ----- Đặt lịch bác sĩ (tư vấn) -----
            'sua_lich_tu_van' => 'Sửa lịch tư vấn',
            'xoa_lich_tu_van' => 'Xóa lịch tư vấn',

            // ----- Nhập / xuất -----
            'xuat_lich_dat_phong' => 'Xuất / Nhập lịch đặt phòng (Excel)',
            'xuat_lich_tu_van'    => 'Xuất / Nhập lịch tư vấn (Excel)',

            // ----- Duyệt -----
            'duyet_booking'   => 'Duyệt lịch đặt phòng',
            'duyet_tu_van'    => 'Duyệt lịch tư vấn',

            // ----- Phản hồi khách -----
            'ghi_chu_phan_hoi' => 'Ghi chú phản hồi khách (trạng thái + note)',

            // ----- Lịch làm việc (theo tháng) -----
            'quyen_lich_lam_viec' => 'Tạo / upload lịch làm việc',
            'duyet_lich_lam_viec' => 'Duyệt & áp dụng lịch làm việc',

            // ----- Ngày nghỉ (đóng cửa / nghỉ) -----
            'quyen_ngay_nghi' => 'Quản lý ngày nghỉ (đóng cửa / nghỉ)',

            // ----- Thông báo (in-app + email) -----
            'nhan_tb_tag_lich'      => 'Nhận TB khi được tag vào lịch',
            'nhan_tb_cap_nhat_lich' => 'Nhận TB khi lịch được cập nhật/duyệt',
            'nhan_tb_huy_lich'      => 'Nhận TB khi lịch bị hủy/từ chối',
            'nhan_tb_nhac_hen'      => 'Nhắc hẹn trước giờ thực hiện',
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
     * Gom các trường thành nhóm để hiển thị ma trận phân quyền.
     *
     * @return array<string,array{icon:string,fields:array<string,string>}>
     */
    public static function groups(): array
    {
        $all = self::all();
        $pick = fn (array $keys) => array_intersect_key($all, array_flip($keys));

        return [
            'Quyền đặt phòng' => [
                'icon'   => 'edit_calendar',
                'fields' => $pick(['xem_booking', 'them_booking', 'sua_booking', 'sua_booking_lien_quan', 'sua_booking_dich_vu_cua_toi', 'xoa_booking']),
                'sub'    => [
                    // Sub-fields hiển thị dưới từng loại "Sửa booking" để admin dễ đối chiếu.
                    // Backend: 3 loại quyền chia sẻ cùng danh sách trường (không tạo key mới)
                    // → tick 1 trường ở bất kỳ nhóm nào cũng cấp cùng field-level cho vai trò.
                    'sua_booking' => self::suaSubFields(),
                    'sua_booking_lien_quan' => self::suaSubFields(),
                    'sua_booking_dich_vu_cua_toi' => self::suaSubFields(),
                ],
            ],
            'Quyền đặt lịch bác sĩ' => [
                'icon'   => 'medical_services',
                'fields' => $pick(['sua_lich_tu_van', 'xoa_lich_tu_van']),
                'sub'    => [],
            ],
            'Quyền nhập / xuất dữ liệu' => [
                'icon'   => 'import_export',
                'fields' => $pick(['xuat_lich_dat_phong', 'xuat_lich_tu_van']),
                'sub'    => [],
            ],
            'Quyền duyệt' => [
                'icon'   => 'verified',
                'fields' => $pick(['duyet_booking', 'duyet_tu_van']),
                'sub'    => [],
            ],
            'Quyền phản hồi khách' => [
                'icon'   => 'rate_review',
                'fields' => $pick(['ghi_chu_phan_hoi']),
                'sub'    => [],
            ],
            'Quyền lịch làm việc' => [
                'icon'   => 'event_available',
                'fields' => $pick(['quyen_lich_lam_viec', 'duyet_lich_lam_viec']),
                'sub'    => [],
            ],
            'Quyền ngày nghỉ' => [
                'icon'   => 'event_busy',
                'fields' => $pick(['quyen_ngay_nghi']),
                'sub'    => [],
            ],
            'Thông báo' => [
                'icon'   => 'notifications_active',
                'fields' => $pick([
                    'nhan_tb_tag_lich',
                    'nhan_tb_cap_nhat_lich',
                    'nhan_tb_huy_lich',
                    'nhan_tb_nhac_hen',
                ]),
                'sub'    => [],
            ],
        ];
    }
}
