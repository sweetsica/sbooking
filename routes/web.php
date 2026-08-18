<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ExcelController;
use App\Http\Controllers\LichHenController;
use App\Http\Controllers\LichLamViecController;
use App\Http\Controllers\NgayNghiController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ThongBaoController;
use App\Models\CoSo;
use Illuminate\Support\Facades\Route;

// Trang gốc -> chuyển về cơ sở của user đang đăng nhập (impersonate + login đều đúng slug).
// Guest: fallback cơ sở active đầu tiên (rồi middleware auth đá về /login).
// 2026-08-18 fix: trước hardcode CoSo::first() → impersonate admin ĐN vẫn mở /59ntn/... .
Route::get('/', function () {
    $u = auth()->user();
    $cs = $u?->coSo ?? \App\Models\CoSo::where('active', true)->first();
    abort_unless($cs, 404, 'Chưa có cơ sở nào.');

    // Vai trò khác nhau có trang chủ khác — bác sĩ về lịch tư vấn, nhân viên về tạo booking.
    $ma = $u?->vaiTro?->ma;
    if (in_array($ma, ['bac_si', 'bac_si_tu_van'], true)) {
        return redirect("/{$cs->slug}/lich-tu-van");
    }
    if ($ma === 'nhan_vien') {
        return redirect("/{$cs->slug}/tao-moi");
    }
    return redirect("/{$cs->slug}/lich-hen");
});

// Trang hướng dẫn nhanh (demo luồng vận hành) — public, không cần đăng nhập
Route::view('/demo', 'longevity.demo')->name('demo');
Route::view('/changelog', 'changelog')->name('changelog');

// Đăng nhập / đăng xuất
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Đổi mật khẩu (người dùng đã đăng nhập)
Route::middleware('auth')->group(function () {
    Route::get('/doi-mat-khau',  [AuthController::class, 'showChangePassword'])->name('password.change');
    Route::post('/doi-mat-khau', [AuthController::class, 'changePassword'])->name('password.update');

    // Dev tool 2026-08-15 — Impersonate + quick-login panel.
    Route::post('/impersonate/{user}', [\App\Http\Controllers\ImpersonateController::class, 'start'])->name('impersonate.start');
    Route::post('/impersonate-leave', [\App\Http\Controllers\ImpersonateController::class, 'leave'])->name('impersonate.leave');
    Route::get('/dev/quick-login', [\App\Http\Controllers\ImpersonateController::class, 'quickLogin'])->name('dev.quick-login');

    // B5c (2026-08-14): dropdown Sale trong modal duyệt lịch — lọc theo co_so_id của booking.
    Route::get('/api/sales-in-cosolow', function (\Illuminate\Http\Request $r) {
        $coSoId = (int) $r->query('co_so_id');
        if (! $coSoId) return response()->json(['data' => []]);
        $users = \App\Models\User::where('co_so_id', $coSoId)
            ->orderBy('name')
            ->get(['id', 'name', 'chuc_danh']);
        return response()->json(['data' => $users]);
    })->name('api.sales-in-cosolow');

    // Thông báo (in-app)
    Route::get('/thong-bao',                        [ThongBaoController::class, 'index'])->name('thongbao.index');
    Route::get('/thong-bao/summary',                [ThongBaoController::class, 'summary'])->name('thongbao.summary');
    Route::post('/thong-bao/mark-all-read',         [ThongBaoController::class, 'markAllRead'])->name('thongbao.markAllRead');
    Route::post('/thong-bao/{id}/read',             [ThongBaoController::class, 'markRead'])->name('thongbao.markRead');
    Route::delete('/thong-bao/hide-all',            [ThongBaoController::class, 'hideAll'])->name('thongbao.hideAll');
    Route::delete('/thong-bao/{id}',                [ThongBaoController::class, 'hide'])->name('thongbao.hide');

    // Xuất danh sách nhân sự toàn hệ thống (mỗi cơ sở 1 sheet). Gate admin trong controller.
    Route::get('/nhan-su-toan-he-thong/xuat', [\App\Http\Controllers\NhanSuAllController::class, 'export'])
        ->name('nhansu.all.export');

    // 2026-08-10 — Sale tự tick "Dừng nhận lead" / "Nhận lead lại" từ topbar → push scrm.
    Route::post('/dung-nhan-lead', [AuthController::class, 'toggleDungNhanLead'])->name('user.dungnhanlead');
});

Route::prefix('{co_so:slug}')->group(function () {
    // Vào thẳng slug (không có path) -> chuyển về trang chủ Lịch hẹn
    Route::get('/', fn (CoSo $co_so) => redirect("/{$co_so->slug}/lich-hen"));

    // ----- CẦN ĐĂNG NHẬP -----
    Route::middleware('auth')->group(function () {
        // Form tạo đặt phòng / lịch tư vấn — nhân viên đặt hộ khách (sale_id required).
        Route::get('/tao-moi',           [BookingController::class, 'create'])->name('booking.create');
        Route::post('/tao-moi',          [BookingController::class, 'store'])->name('booking.store');
        Route::get('/tao-moi/khung-gio', [BookingController::class, 'khungGio'])->name('booking.khunggio');
        Route::get('/tao-moi/check-sdt', [BookingController::class, 'checkPhone'])->name('booking.checksdt');
        Route::get('/tao-moi/check-ktv', [BookingController::class, 'checkKtv'])->name('booking.checkktv');
        Route::get('/tao-moi/check-bac-si', [BookingController::class, 'checkBacSi'])->name('booking.checkbs');

        // Đặt lịch dịch vụ (chỉ phòng + KTV, không có BS)
        Route::get('/dat-lich-dich-vu',  [BookingController::class, 'createDichVu'])->name('booking.dichvu.create');
        Route::post('/dat-lich-dich-vu', [BookingController::class, 'storeDichVu'])->name('booking.dichvu.store');

        Route::get('/dat-kham',            [LichHenController::class, 'create'])->name('lichhen.create');
        Route::post('/dat-kham',           [LichHenController::class, 'store'])->name('lichhen.store');
        Route::get('/dat-kham/ca-kham',    [LichHenController::class, 'caKham'])->name('lichhen.cakham');
        Route::get('/dat-kham/check-sdt',  [LichHenController::class, 'checkPhone'])->name('lichhen.checksdt');

        Route::get('/phong',     [PageController::class, 'rooms'])->name('phong');
        // 2026-08-04 (SCRM T10): /lich-hen giờ = dashboard 4 widget + list. Timeline gantt cũ dời sang /lich-hen/timeline.
        Route::get('/lich-hen',           [PageController::class, 'dashboard'])->name('dashboard');
        Route::get('/lich-hen/timeline',  [PageController::class, 'timeline'])->name('timeline');
        Route::get('/danh-sach', [PageController::class, 'bookings'])->name('bookings');
        Route::get('/duyet-lich', [PageController::class, 'approvals'])->name('approvals');

        // Trang Bác sĩ: lịch của từng bác sĩ, dữ liệu lấy từ Booking đặt phòng
        Route::get('/bac-si', [PageController::class, 'doctors'])->name('doctors');

        // Tìm kiếm lịch đặt theo tên/SĐT + xem chi tiết (chỉ đọc)
        Route::get('/tim-kiem',                [SearchController::class, 'index'])->name('search');
        Route::get('/xem-dat-phong/{booking}', [SearchController::class, 'showBooking'])->name('booking.show');

        // Sửa / Xóa / Duyệt lịch đặt phòng
        Route::get('/sua-dat-phong/{booking}',    [BookingController::class, 'edit'])->name('booking.edit');
        Route::put('/sua-dat-phong/{booking}',    [BookingController::class, 'update'])->name('booking.update');
        Route::delete('/xoa-dat-phong/{booking}', [BookingController::class, 'destroy'])->name('booking.destroy');
        Route::patch('/duyet-dat-phong/{booking}', [BookingController::class, 'duyet'])->name('booking.approve');
        Route::patch('/tu-choi-dat-phong/{booking}', [BookingController::class, 'tuChoi'])->name('booking.reject');
        Route::patch('/xong-dat-phong/{booking}',  [BookingController::class, 'xong'])->name('booking.done');
        Route::patch('/phan-hoi-dat-phong/{booking}', [BookingController::class, 'phanHoi'])->name('booking.feedback');
        // Phản hồi sau khi sử dụng dịch vụ (trạng thái khách + note nhiều dòng có tác giả) — GIỮ tên remote booking.tt-khach.
        Route::patch('/trang-thai-khach/{booking}', [BookingController::class, 'capNhatTrangThaiKhach'])->name('booking.tt-khach');
        // 2026-08-05: BỎ them-phan-hoi / xoa-phan-hoi (dead — thay bằng binh-luan bên dưới).
        // Phase 6.25 (local): tiếp đón + bình luận + GET fallback (tránh 405 khi user copy URL action).
        Route::patch('/tiep-don/{booking}', [BookingController::class, 'capNhatTiepDon'])->name('booking.tiepdon');
        // 2026-08-18: Sale tiếp đón bấm "Đã xong" — 3 field checkin + push CRM close phase 5.
        Route::patch('/checkin-done/{booking}', [BookingController::class, 'capNhatCheckinDone'])->name('booking.checkin-done');
        // 2026-08-10 — Admin (BO / cơ sở / vận hành) tick "Booking trễ".
        Route::patch('/booking-tre/{booking}', [BookingController::class, 'toggleBookingTre'])->name('booking.tre');
        Route::get('/trang-thai-khach/{booking}', function ($co_so_slug, $booking) {
            return redirect("/{$co_so_slug}/xem-dat-phong/{$booking}");
        });
        Route::post('/binh-luan/{booking}', [BookingController::class, 'themBinhLuan'])->name('booking.binhluan.them');
        Route::delete('/binh-luan/{booking}/{binh_luan}', [BookingController::class, 'xoaBinhLuan'])->name('booking.binhluan.xoa');

        Route::get('/xuat-booking',  [ExcelController::class, 'exportBooking'])->name('excel.exportBooking');
        Route::post('/nhap-booking', [ExcelController::class, 'importBooking'])->name('excel.importBooking');

        // ----- Lịch làm việc (theo tháng) + duyệt -----
        Route::get('/lich-lam-viec',                [LichLamViecController::class, 'index'])->name('llv.index');
        Route::get('/lich-lam-viec/mau',            [LichLamViecController::class, 'mau'])->name('llv.mau');
        Route::post('/lich-lam-viec/preview',       [LichLamViecController::class, 'preview'])->name('llv.preview');
        Route::post('/lich-lam-viec',               [LichLamViecController::class, 'store'])->name('llv.store');
        Route::get('/lich-lam-viec/{lich_lam_viec}', [LichLamViecController::class, 'show'])->name('llv.show');
        Route::post('/lich-lam-viec/{lich_lam_viec}/gui-duyet', [LichLamViecController::class, 'guiDuyet'])->name('llv.guiduyet');
        Route::patch('/lich-lam-viec/{lich_lam_viec}/duyet',    [LichLamViecController::class, 'duyet'])->name('llv.duyet');
        Route::patch('/lich-lam-viec/{lich_lam_viec}/tu-choi',  [LichLamViecController::class, 'tuChoi'])->name('llv.tuchoi');
        Route::delete('/lich-lam-viec/{lich_lam_viec}',         [LichLamViecController::class, 'destroy'])->name('llv.destroy');

        // ----- Ngày nghỉ (đóng cửa / nghỉ theo khoảng ngày) -----
        Route::get('/ngay-nghi',                 [NgayNghiController::class, 'index'])->name('ngaynghi.index');
        Route::post('/ngay-nghi',                [NgayNghiController::class, 'store'])->name('ngaynghi.store');
        Route::delete('/ngay-nghi/{ngay_nghi}',  [NgayNghiController::class, 'destroy'])->name('ngaynghi.destroy');

        // ----- Lịch tư vấn bác sĩ -----
        Route::get('/lich-tu-van',                    [LichHenController::class, 'manage'])->name('lichhen.manage');
        Route::get('/ds-tu-van',                      [LichHenController::class, 'list'])->name('lichhen.list');
        Route::get('/xem-tu-van/{lich_hen}',          [LichHenController::class, 'show'])->name('lichhen.show');
        Route::get('/sua-tu-van/{lich_hen}',          [LichHenController::class, 'edit'])->name('lichhen.edit');
        Route::put('/sua-tu-van/{lich_hen}',          [LichHenController::class, 'update'])->name('lichhen.update');
        Route::delete('/xoa-tu-van/{lich_hen}',       [LichHenController::class, 'destroy'])->name('lichhen.destroy');
        Route::patch('/duyet-tu-van/{lich_hen}',      [LichHenController::class, 'duyet'])->name('lichhen.approve');

        Route::get('/xuat-tu-van',  [ExcelController::class, 'exportLichHen'])->name('excel.exportLichHen');
        Route::post('/nhap-tu-van', [ExcelController::class, 'importLichHen'])->name('excel.importLichHen');

        // ----- Báo cáo (admin HOẶC quyền xem_bao_cao) -----
        Route::get('/bao-cao',      [SettingsController::class, 'baoCao'])->name('settings.baocao');
        Route::get('/bao-cao/xuat', [ExcelController::class, 'exportBaoCao'])->name('settings.baocao.xuat');

        // ----- Sơ đồ tổ chức -----
        Route::get('/so-do-to-chuc', [SettingsController::class, 'soDo'])->name('settings.sodo');

        // ----- CHỈ ADMIN: Thiết lập -----
        Route::middleware('admin')->prefix('thiet-lap')->name('settings.')->group(function () {
            Route::get('/', [SettingsController::class, 'index'])->name('index');
            // Nhật ký thông báo — admin only.
            Route::get('/nhat-ky-thong-bao', [\App\Http\Controllers\NotificationLogController::class, 'index'])->name('notification-log');
            Route::get('/{section}', [SettingsController::class, 'section'])->name('section');

            // Ghi + các mục quản trị khác: CHỈ ADMIN
            Route::middleware('admin')->group(function () {
                Route::get('/nguoi-dung/xuat', [ExcelController::class, 'exportNguoiDung'])->name('nguoidung.xuat');
                Route::get('/ket-noi/scrm', [\App\Http\Controllers\ScrmConnectionController::class, 'edit'])->name('scrm-connection.edit');
                Route::post('/ket-noi/scrm', [\App\Http\Controllers\ScrmConnectionController::class, 'update'])->name('scrm-connection.update');
                Route::post('/ket-noi/scrm/xoa-token', [\App\Http\Controllers\ScrmConnectionController::class, 'clearToken'])->name('scrm-connection.clear-token');
                Route::get('/ket-noi/scrm/xuat', [\App\Http\Controllers\ScrmConnectionController::class, 'export'])->name('scrm-connection.export');
                Route::post('/ket-noi/scrm/nhap', [\App\Http\Controllers\ScrmConnectionController::class, 'import'])->name('scrm-connection.import');
                Route::post('/{section}', [SettingsController::class, 'store'])->name('store');
                Route::put('/{section}/{id}', [SettingsController::class, 'update'])->name('update');
                Route::delete('/{section}/{id}', [SettingsController::class, 'destroy'])->name('destroy');
            });
        });
    });
});
