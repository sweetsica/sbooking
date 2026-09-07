<?php

namespace App\Http\Controllers;

use App\Models\CoSo;
use App\Models\DichVu;

/**
 * Page tra cứu danh mục dịch vụ toàn hệ thống (cross cơ sở).
 * Chỉ đọc — dùng để admin check nhanh sau khi thêm/sửa: dịch vụ nào ở
 * cơ sở nào, gắn với phòng nào, ai thực hiện.
 *
 * Cột: ID · Cơ sở · Tên · Thời gian · Nhóm · Là DV · Phòng thực hiện ·
 *      Nhân sự thực hiện (BS + KTV pivot dich_vu_bac_si).
 */
class DanhMucDichVuController extends Controller
{
    public function index(CoSo $co_so)
    {
        // Eager-load để tránh N+1: co_so, phongs (pivot dich_vu_phong), bacSis (pivot dich_vu_bac_si).
        $rows = DichVu::with(['coSo:id,ten,slug', 'phongs:id,ten', 'bacSis:id,ten,chuc_danh'])
            ->orderBy('co_so_id')
            ->orderBy('ten')
            ->get();

        return view('longevity.settings.danh-muc-dich-vu', [
            'coSo' => $co_so,   // context cơ sở đang xem (dùng cho breadcrumb + link về Thiết lập)
            'rows' => $rows,
        ]);
    }
}
