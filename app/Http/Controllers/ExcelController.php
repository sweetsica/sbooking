<?php

namespace App\Http\Controllers;

use App\Exports\BookingExport;
use App\Exports\LichHenExport;
use App\Imports\BookingImport;
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

    public function importLichHen(CoSo $co_so, Request $request)
    {
        $this->authorizeField('xuat_lich_tu_van');
        $request->validate(['file' => ['required', 'file', 'mimes:xlsx,xls,csv']]);

        $import = new LichHenImport($co_so);
        Excel::import($import, $request->file('file'));

        return back()->with('ok', "Đã nhập {$import->imported} dòng lịch tư vấn.");
    }

    private function authorizeField(string $field): void
    {
        $user = auth()->user();
        if ($user->is_admin) {
            return;
        }

        $hasPermission = $user->phong_ban_id
            && PhanQuyen::where('phong_ban_id', $user->phong_ban_id)
                ->where('truong', $field)
                ->exists();

        abort_unless($hasPermission, 403, 'Bạn không có quyền xuất/nhập Excel.');
    }
}
