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

// Trang gốc -> chuyển về cơ sở mặc định (auth sẽ tự đẩy về login nếu chưa đăng nhập)
Route::get('/', function () {
    $cs = CoSo::where('active', true)->first();
    abort_unless($cs, 404, 'Chưa có cơ sở nào.');
    return redirect("/{$cs->slug}/lich-hen");
});

// Trang hướng dẫn nhanh (demo luồng vận hành) — public, không cần đăng nhập
Route::view('/demo', 'longevity.demo')->name('demo');

// Đăng nhập / đăng xuất
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Đổi mật khẩu (người dùng đã đăng nhập)
Route::middleware('auth')->group(function () {
    Route::get('/doi-mat-khau',  [AuthController::class, 'showChangePassword'])->name('password.change');
    Route::post('/doi-mat-khau', [AuthController::class, 'changePassword'])->name('password.update');

    // Thông báo (in-app)
    Route::get('/thong-bao',                        [ThongBaoController::class, 'index'])->name('thongbao.index');
    Route::get('/thong-bao/summary',                [ThongBaoController::class, 'summary'])->name('thongbao.summary');
    Route::post('/thong-bao/mark-all-read',         [ThongBaoController::class, 'markAllRead'])->name('thongbao.markAllRead');
    Route::post('/thong-bao/{id}/read',             [ThongBaoController::class, 'markRead'])->name('thongbao.markRead');
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
        Route::get('/lich-hen',  [PageController::class, 'timeline'])->name('timeline');
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
        Route::patch('/trang-thai-khach/{booking}', [BookingController::class, 'capNhatTrangThaiKhach'])->name('booking.trangthaikhach');
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

        // ----- Thiết lập -----
        // Admin xem/sửa mọi mục. Người có quyền "xem_bao_cao" chỉ vào được mục Báo cáo
        // (SettingsController tự chặn các mục khác) — nên các route ĐỌC không gắn middleware admin.
        Route::prefix('thiet-lap')->name('settings.')->group(function () {
            // Đọc: admin (mọi mục) hoặc người có quyền xem_bao_cao (chỉ Báo cáo)
            Route::get('/', [SettingsController::class, 'index'])->name('index');
            Route::get('/bao-cao/xuat', [ExcelController::class, 'exportBaoCao'])->name('baocao.xuat');
            Route::get('/{section}', [SettingsController::class, 'section'])->name('section');

            // Ghi + các mục quản trị khác: CHỈ ADMIN
            Route::middleware('admin')->group(function () {
                Route::get('/nguoi-dung/xuat', [ExcelController::class, 'exportNguoiDung'])->name('nguoidung.xuat');
                Route::get('/ket-noi/scrm', [\App\Http\Controllers\ScrmConnectionController::class, 'edit'])->name('scrm-connection.edit');
                Route::post('/ket-noi/scrm', [\App\Http\Controllers\ScrmConnectionController::class, 'update'])->name('scrm-connection.update');
                Route::post('/{section}', [SettingsController::class, 'store'])->name('store');
                Route::put('/{section}/{id}', [SettingsController::class, 'update'])->name('update');
                Route::delete('/{section}/{id}', [SettingsController::class, 'destroy'])->name('destroy');
            });
        });
    });
});
