<?php

/**
 * Quy tắc đặt lịch tham khảo theo từng cơ sở.
 * Key cấp 1 = slug cơ sở. Cấp 2: 'phong_kham' (form đặt lịch phòng khám)
 * và 'phong_dich_vu' (form đặt lịch dịch vụ).
 */

return [
    '59ntn' => [
        'gio_hoat_dong' => '8h sáng - 18h tối',

        // Bảng tham khảo cho FORM ĐẶT LỊCH PHÒNG KHÁM
        'phong_kham' => [
            [
                'phong'   => 'Phòng khám Ngoại',
                'bac_si'  => 'BS. Nguyễn Tiến Dũng',
                'vai_tro' => 'Bác sĩ tư vấn 1',
                'uu_tien' => ['Ưu tiên 1: Tư vấn', 'Ưu tiên 2: Thăm khám lâm sàng'],
                'ghi_chu' => [
                    'Thăm khám lâm sàng: 12 khách/giờ',
                    'Tư vấn: 30 phút/khách',
                    'Sáng ưu tiên thăm khám lâm sàng',
                    'Chiều tư vấn - đọc kết quả',
                ],
            ],
            [
                'phong'   => 'Phòng chuyên gia',
                'bac_si'  => 'Lê Tuyên Hồng Dương',
                'vai_tro' => 'Bác sĩ tư vấn 2',
                'uu_tien' => ['Ưu tiên 1: Tư vấn', 'Ưu tiên 2: Thăm khám lâm sàng'],
                'ghi_chu' => [],
            ],
            [
                'phong'   => 'Phòng khám Nội 1',
                'bac_si'  => 'Trương Thị Biên',
                'vai_tro' => null,
                'uu_tien' => ['Ưu tiên 1: Thăm khám lâm sàng', 'Ưu tiên 2: Tư vấn'],
                'ghi_chu' => [],
            ],
            [
                'phong'   => 'Phòng khám Nội 2',
                'bac_si'  => 'Ngô Thị Ngà',
                'vai_tro' => null,
                'uu_tien' => ['Chỉ thăm khám lâm sàng'],
                'ghi_chu' => ['12 khách/giờ'],
            ],
            [
                'phong'   => 'Phòng khám Nội 2',
                'bac_si'  => 'Bác Biên Tim mạch',
                'vai_tro' => null,
                'uu_tien' => ['Chuyên về khám tim mạch'],
                'ghi_chu' => ['30 phút/khách'],
            ],
            [
                'phong'   => 'Phòng siêu âm',
                'bac_si'  => 'Bác Hồng',
                'vai_tro' => null,
                'uu_tien' => ['Chuyên siêu âm'],
                'ghi_chu' => ['25 phút/khách'],
            ],
        ],

        // Bảng tham khảo cho FORM ĐẶT LỊCH DỊCH VỤ
        'phong_dich_vu' => [
            [
                'phong'   => 'Phòng Xông T4',
                'dia_chi' => '59 Ngô Thì Nhậm',
                'slot'    => 'Sử dụng dịch vụ, 30 phút 1 khách',
            ],
            [
                'phong'   => 'Phòng trị liệu YHCT T4',
                'dia_chi' => '59 Ngô Thì Nhậm',
                'slot'    => 'Sử dụng dịch vụ, 1 tiếng 1 khách',
            ],
        ],
    ],

    '207nvt' => [
        'gio_hoat_dong' => '8h sáng - 18h tối',
        'phong_kham' => [
            [
                'phong'   => 'Phòng khám Nội',
                'bac_si'  => null,
                'vai_tro' => null,
                'uu_tien' => [],
                'ghi_chu' => [],
            ],
            [
                'phong'   => 'Phòng siêu âm',
                'bac_si'  => null,
                'vai_tro' => null,
                'uu_tien' => ['Chuyên siêu âm'],
                'ghi_chu' => ['25 phút/khách'],
            ],
            [
                'phong'   => 'Phòng YHCT',
                'bac_si'  => null,
                'vai_tro' => null,
                'uu_tien' => [],
                'ghi_chu' => [],
            ],
        ],
        'phong_dich_vu' => [],
    ],
];
