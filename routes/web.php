<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ExcelController;
use App\Http\Controllers\LichHenController;
use App\Http\Controllers\PageController;
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

Route::prefix('{co_so:slug}')->group(function () {
    // ----- CÔNG KHAI: form tạo lịch hẹn (không cần đăng nhập) -----
    Route::get('/tao-moi',           [BookingController::class, 'create'])->name('booking.create');
    Route::post('/tao-moi',          [BookingController::class, 'store'])->name('booking.store');
    Route::get('/tao-moi/khung-gio', [BookingController::class, 'khungGio'])->name('booking.khunggio');
    Route::get('/tao-moi/check-sdt', [BookingController::class, 'checkPhone'])->name('booking.checksdt');

    // ----- CÔNG KHAI: form đặt lịch tư vấn -----
    Route::get('/dat-kham',           [LichHenController::class, 'create'])->name('lichhen.create');
    Route::post('/dat-kham',          [LichHenController::class, 'store'])->name('lichhen.store');
    Route::get('/dat-kham/ca-kham',   [LichHenController::class, 'caKham'])->name('lichhen.cakham');
    Route::get('/dat-kham/check-sdt', [LichHenController::class, 'checkPhone'])->name('lichhen.checksdt');

    // ----- CẦN ĐĂNG NHẬP -----
    Route::middleware('auth')->group(function () {
        Route::get('/phong',     [PageController::class, 'rooms'])->name('phong');
        Route::get('/lich-hen',  [PageController::class, 'timeline'])->name('timeline');
        Route::get('/danh-sach', [PageController::class, 'bookings'])->name('bookings');

        Route::get('/lich-tu-van', [LichHenController::class, 'manage'])->name('lichhen.manage');
        Route::get('/ds-tu-van',   [LichHenController::class, 'list'])->name('lichhen.list');

        Route::get('/xuat-booking',  [ExcelController::class, 'exportBooking'])->name('excel.exportBooking');
        Route::get('/xuat-tu-van',   [ExcelController::class, 'exportLichHen'])->name('excel.exportLichHen');
        Route::post('/nhap-booking', [ExcelController::class, 'importBooking'])->name('excel.importBooking');
        Route::post('/nhap-tu-van',  [ExcelController::class, 'importLichHen'])->name('excel.importLichHen');

        // ----- CHỈ ADMIN: Thiết lập -----
        Route::middleware('admin')->prefix('thiet-lap')->name('settings.')->group(function () {
            Route::get('/', [SettingsController::class, 'index'])->name('index');
            Route::get('/{section}', [SettingsController::class, 'section'])->name('section');
            Route::post('/{section}', [SettingsController::class, 'store'])->name('store');
            Route::put('/{section}/{id}', [SettingsController::class, 'update'])->name('update');
            Route::delete('/{section}/{id}', [SettingsController::class, 'destroy'])->name('destroy');
        });
    });
});
