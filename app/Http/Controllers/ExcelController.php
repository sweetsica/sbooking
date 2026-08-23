<?php

namespace App\Http\Controllers;

use App\Exports\BaoCaoExport;
use App\Exports\BookingExport;
use App\Exports\CauHinhExport;
use App\Exports\LichHenExport;
use App\Exports\NguoiDungExport;
use App\Http\Controllers\SettingsController;
use App\Imports\BookingImport;
use App\Imports\CauHinhImport;
use App\Imports\LichHenImport;
use App\Models\CoSo;
use App\Models\PhanQuyen;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExcelController extends Controller
{
    public function exportBooking(CoSo $co_so)
    {
        $this->authorizeField('xuat_lich_dat_phong');

        $name = 'booking-' . $co_so->slug . '-' . now()->format('Ymd-His') . '.xlsx';

        return Excel::download(new BookingExport($co_so), $name);
    }

    public function exportLichHen(CoSo $co_so)
    {
        $this->authorizeField('xuat_lich_tu_van');

        $name = 'tu-van-' . $co_so->slug . '-' . now()->format('Ymd-His') . '.xlsx';

        return Excel::download(new LichHenExport($co_so), $name);
    }

    public function importBooking(CoSo $co_so, Request $request)
    {
        $this->authorizeField('xuat_lich_dat_phong');
        $request->validate(['file' => ['required', 'file', 'mimes:xlsx,xls,csv']]);

        $import = new BookingImport($co_so);
        Excel::import($import, $request->file('file'));

        return back()->with('ok', "Đã nhập {$import->imported} dòng booking.");
    }

    public function exportBaoCao(CoSo $co_so, Request $request, SettingsController $settings)
    {
        // Route không còn admin-only: admin luôn qua, non-admin cần quyền xem_bao_cao.
        $this->authorizeField('xem_bao_cao');
        $data = $settings->buildBaoCao($co_so, $request);
        $name = 'bao-cao-' . $co_so->slug . '-' . now()->format('Ymd-His') . '.xlsx';
        return Excel::download(new BaoCaoExport($data), $name);
    }

    public function exportNguoiDung(CoSo $co_so)
    {
        // Route đã được gate qua middleware 'admin' (chỉ admin hệ thống) nên không cần check thêm.
        $name = 'nguoi-dung-' . $co_so->slug . '-' . now()->format('Ymd-His') . '.xlsx';
        return Excel::download(new NguoiDungExport($co_so), $name);
    }

    public function importLichHen(CoSo $co_so, Request $request)
    {
        $this->authorizeField('xuat_lich_tu_van');
        $request->validate(['file' => ['required', 'file', 'mimes:xlsx,xls,csv']]);

        $import = new LichHenImport($co_so);
        Excel::import($import, $request->file('file'));

        return back()->with('ok', "Đã nhập {$import->imported} dòng lịch tư vấn.");
    }

    public function exportCauHinh(CoSo $co_so)
    {
        // Admin only. Xuất toàn bộ (không lọc theo cơ sở) — cột co_so_id trong file phân biệt.
        $name = 'cau-hinh-toan-bo-' . now()->format('Ymd-His') . '.xlsx';
        return Excel::download(new CauHinhExport(), $name);
    }

    public function importCauHinh(CoSo $co_so, Request $request)
    {
        $request->validate(['file' => ['required', 'file', 'mimes:xlsx,xls']]);

        $import = new CauHinhImport();
        Excel::import($import, $request->file('file'));

        $s = $import->stats;
        $msg = "Phòng: +{$s['phong']['insert']} / ✏{$s['phong']['update']} / ⚠{$s['phong']['skip']}. "
             . "Dịch vụ: +{$s['dich_vu']['insert']} / ✏{$s['dich_vu']['update']} / ⚠{$s['dich_vu']['skip']}. "
             . "Bác sĩ: +{$s['bac_si']['insert']} / ✏{$s['bac_si']['update']} / ⚠{$s['bac_si']['skip']}.";

        return back()->with('ok', $msg)->with('import_errors', $import->errors);
    }

    private function authorizeField(string $field): void
    {
        $user = auth()->user();
        if ($user->is_admin) {
            return;
        }

        $hasPermission = PhanQuyen::where(function ($q) use ($user) {
                if ($user->phong_ban_id) $q->orWhere('phong_ban_id', $user->phong_ban_id);
                if ($user->vai_tro_id) $q->orWhere('vai_tro_id', $user->vai_tro_id);
            })->where('truong', $field)->exists();

        abort_unless($hasPermission, 403, 'Bạn không có quyền xuất/nhập Excel.');
    }
}
