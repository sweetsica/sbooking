<?php

namespace App\Http\Controllers;

use App\Models\CoSo;
use App\Models\User;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Xuất danh sách nhân sự toàn hệ thống Booking.
 * Mỗi cơ sở = 1 sheet + 1 sheet "Toàn hệ thống" cho user không thuộc cơ sở nào (admin, IT, MOD...).
 * Cột "Mật khẩu mặc định" điền theo quy ước: admin = 59ntn, các user còn lại = 59@ntn.
 */
class NhanSuAllController extends Controller
{
    public function export()
    {
        abort_unless(auth()->user()?->is_admin, 403);

        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);

        $index = 0;
        foreach (CoSo::orderBy('id')->get() as $coSo) {
            $this->writeSheet($spreadsheet, $index++,
                mb_substr($coSo->ten ?? ('Cơ sở ' . $coSo->id), 0, 31),
                User::with(['vaiTro', 'phongBan'])->where('co_so_id', $coSo->id)->orderBy('name')->get(),
            );
        }

        $systemUsers = User::with(['vaiTro', 'phongBan'])->whereNull('co_so_id')->orderBy('name')->get();
        if ($systemUsers->isNotEmpty()) {
            $this->writeSheet($spreadsheet, $index++, 'Toàn hệ thống', $systemUsers);
        }

        $spreadsheet->setActiveSheetIndex(0);
        $filename = 'nhan-su-booking-' . now()->format('Ymd-His') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function writeSheet(Spreadsheet $spreadsheet, int $index, string $title, $users): void
    {
        $sheet = $spreadsheet->createSheet($index);
        $sheet->setTitle($title);

        $headers = ['ID', 'Họ tên', 'Tên đăng nhập', 'Email', 'Mật khẩu mặc định',
            'Chức danh', 'Vai trò', 'Phòng ban'];
        $sheet->fromArray($headers, null, 'A1');
        $sheet->getStyle('A1:H1')->getFont()->setBold(true);
        $sheet->getStyle('A1:H1')->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('E8DDBB');

        $row = 2;
        foreach ($users as $u) {
            $sheet->fromArray([
                $u->id,
                $u->name,
                $u->username,
                $u->email,
                $u->username === 'admin' ? '59ntn' : '59@ntn',
                $u->chuc_danh,
                $u->vaiTro?->ten,
                $u->phongBan?->ten,
            ], null, 'A' . $row);
            $row++;
        }

        // Ghi chú cuối sheet
        $noteRow = $row + 1;
        $sheet->setCellValue('A' . $noteRow,
            'Tổng số nhân sự tại ' . $title . ': ' . count($users)
            . '. Cột "Mật khẩu mặc định" là mật khẩu được thiết lập lần đầu qua seeder; '
            . 'nếu người dùng đã đổi qua giao diện thì giá trị này không còn đúng.');
        $sheet->mergeCells('A' . $noteRow . ':H' . $noteRow);
        $sheet->getStyle('A' . $noteRow)->getFont()->setItalic(true)->getColor()->setRGB('7A6A3E');
        $sheet->getStyle('A' . $noteRow)->getAlignment()->setWrapText(true);

        $widths = ['A' => 6, 'B' => 26, 'C' => 18, 'D' => 30, 'E' => 20, 'F' => 22, 'G' => 22, 'H' => 22];
        foreach ($widths as $col => $w) {
            $sheet->getColumnDimension($col)->setWidth($w);
        }
        $sheet->freezePane('A2');
    }
}
