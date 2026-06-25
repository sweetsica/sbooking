<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ExcelController;
use App\Http\Controllers\LichHenController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SettingsController;
use App\Models\CoSo;
use Illuminate\Support\Facades\Route;

// Trang gốc -> chuyển về cơ sở mặc định (auth sẽ tự đẩy về login nếu chưa đăng nhập)
Route::get('/', function () {
    $cs = CoSo::where('active', true)->first();
    abort_unless($cs, 404, 'Chưa có cơ sở nào.');
    return redirect("/{$cs->slug}/lich-hen");
});

// Đăng nhập / đăng xuất
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Đổi mật khẩu (người dùng đã đăng nhập)
Route::middleware('auth')->group(function () {
    Route::get('/doi-mat-khau',  [AuthController::class, 'showChangePassword'])->name('password.change');
    Route::post('/doi-mat-khau', [AuthController::class, 'changePassword'])->name('password.update');
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

        Route::get('/xuat-booking',  [ExcelController::class, 'exportBooking'])->name('excel.exportBooking');
        Route::post('/nhap-booking', [ExcelController::class, 'importBooking'])->name('excel.importBooking');

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

        // ----- CHỈ ADMIN: Thiết lập -----
        Route::middleware('admin')->prefix('thiet-lap')->name('settings.')->group(function () {
            Route::get('/', [SettingsController::class, 'index'])->name('index');
            Route::get('/bao-cao/xuat', [ExcelController::class, 'exportBaoCao'])->name('baocao.xuat');
            Route::get('/{section}', [SettingsController::class, 'section'])->name('section');
            Route::post('/{section}', [SettingsController::class, 'store'])->name('store');
            Route::put('/{section}/{id}', [SettingsController::class, 'update'])->name('update');
            Route::delete('/{section}/{id}', [SettingsController::class, 'destroy'])->name('destroy');
        });
    });
});
