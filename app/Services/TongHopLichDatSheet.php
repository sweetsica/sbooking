<?php

namespace App\Services;

use App\Models\BacSi;
use App\Models\CoSo;
use App\Models\DichVu;
use App\Models\KhungGio;
use App\Models\Ktv;
use App\Models\User;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;

/**
 * Build template + parse + validate xlsx cho Tổng hợp Lịch đặt.
 *
 * Template gồm 2 sheet:
 *  - Sheet 1 "Nhập liệu": form 12 cột (5 bắt buộc), 1 dòng ví dụ.
 *  - Sheet 2 "Danh mục":   list DV / BS / KTV / Sale của cơ sở đang xem.
 *
 * Import chạy fail-fast: nếu bất kỳ dòng nào lỗi → KHÔNG insert dòng nào,
 * trả về mảng errors + file xlsx báo lỗi (bôi đỏ ô sai) để user tải về sửa.
 */
class TongHopLichDatSheet
{
    // Thứ tự cột trong sheet 1. Đổi thứ tự = đổi template.
    public const COLS = [
        'phan_loai', 'ho_ten', 'so_dien_thoai', 'ngay_thuc_hien', 'gio_bat_dau',
        'dich_vu', 'bac_si', 'ktv', 'sale_email',
        'nguon', 'ket_qua', 'ghi_chu',
    ];

    // Cột nào bắt buộc — chung cho mọi loại.
    public const REQUIRED_ALL = ['phan_loai', 'ho_ten', 'so_dien_thoai', 'ngay_thuc_hien', 'gio_bat_dau', 'sale_email'];

    /**
     * Xuất template (file .xlsx). Trả về đường dẫn tạm — controller stream về client.
     */
    public function buildTemplate(CoSo $coSo, ?string $outPath = null): string
    {
        $ss = new Spreadsheet();

        // ---------- Sheet 1: Nhập liệu ----------
        $s1 = $ss->getActiveSheet();
        $s1->setTitle('Nhập liệu');

        $headers = [
            'Phân loại (K/TV/DV) *', 'Họ tên khách *', 'SĐT *',
            'Ngày thực hiện * (YYYY-MM-DD)', 'Giờ bắt đầu * (HH:MM)',
            'Dịch vụ', 'Bác sĩ', 'KTV', 'Email sale *',
            'Nguồn', 'Kết quả (rỗng=chờ duyệt, "Đã xong", "Đã huỷ")', 'Ghi chú',
        ];
        foreach ($headers as $i => $h) {
            $col = chr(65 + $i); // A..L
            $s1->setCellValue($col.'1', $h);
        }
        $s1->getStyle('A1:L1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('1F4E78');
        $s1->getStyle('A1:L1')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $s1->getStyle('A1:L1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Dòng ví dụ (dòng 2) — italic + xám để user biết là mẫu.
        $exampleSaleEmail = User::whereHas('coSos', fn ($q) => $q->where('co_so.id', $coSo->id))
            ->orderBy('id')->value('email') ?? 'sale@longevity.com.vn';
        $exampleDv  = DichVu::where('co_so_id', $coSo->id)->orderBy('id')->value('ten') ?? '';
        $exampleBs  = BacSi::where(fn ($q) => $q->where('co_so_id', $coSo->id)->orWhere('xuat_hien_moi_co_so', true))
            ->orderBy('id')->value('ten') ?? '';
        $example = ['K', 'Nguyễn Văn A', '0912345678', now()->format('Y-m-d'), '09:00',
            $exampleDv, $exampleBs, '', $exampleSaleEmail, 'Facebook', '', 'Mẫu — xoá dòng này trước khi nhập'];
        foreach ($example as $i => $v) {
            $s1->setCellValue(chr(65 + $i).'2', $v);
        }
        $s1->getStyle('A2:L2')->getFont()->setItalic(true)->getColor()->setRGB('9CA3AF');

        // Width
        foreach (range('A', 'L') as $c) $s1->getColumnDimension($c)->setAutoSize(true);
        $s1->freezePane('A2');

        // ---------- Sheet 2: Danh mục ----------
        $s2 = $ss->createSheet();
        $s2->setTitle('Danh mục');

        $row = 1;
        $writeTable = function (string $title, array $headers, iterable $rows) use ($s2, &$row) {
            $s2->setCellValue('A'.$row, $title);
            $s2->getStyle('A'.$row)->getFont()->setBold(true)->setSize(13);
            $s2->getStyle('A'.$row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E5E7EB');
            $row++;

            foreach ($headers as $i => $h) {
                $s2->setCellValue(chr(65 + $i).$row, $h);
            }
            $lastCol = chr(65 + count($headers) - 1);
            $s2->getStyle('A'.$row.':'.$lastCol.$row)->getFont()->setBold(true);
            $s2->getStyle('A'.$row.':'.$lastCol.$row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F3F4F6');
            $row++;

            foreach ($rows as $r) {
                foreach (array_values($r) as $i => $v) {
                    $s2->setCellValue(chr(65 + $i).$row, $v);
                }
                $row++;
            }
            $row += 2; // 2 dòng trống ngăn cách
        };

        $writeTable('Dịch vụ (Tên → copy sang cột "Dịch vụ" ở Sheet 1)',
            ['Tên', 'Nhóm', 'Phút/khách', 'Là dịch vụ?'],
            DichVu::where('co_so_id', $coSo->id)->where('active', true)->orderBy('ten')->get()
                ->map(fn ($d) => [$d->ten, $d->thuoc_nhom ?? '', $d->thoi_gian_phut, $d->la_dich_vu ? 'DV' : 'Khám']));

        $writeTable('Bác sĩ (Tên → copy sang cột "Bác sĩ" ở Sheet 1)',
            ['Tên', 'Chức danh'],
            BacSi::where(fn ($q) => $q->where('co_so_id', $coSo->id)->orWhere('xuat_hien_moi_co_so', true))
                ->orderBy('ten')->get()->map(fn ($b) => [$b->ten, $b->chuc_danh ?? '']));

        $writeTable('KTV / Điều dưỡng (Tên → copy sang cột "KTV" ở Sheet 1)',
            ['Tên'],
            Ktv::where('co_so_id', $coSo->id)->where('active', true)->orderBy('ten')->get()
                ->map(fn ($k) => [$k->ten]));

        $writeTable('Sale (Email → copy sang cột "Email sale" ở Sheet 1)',
            ['Email', 'Tên'],
            $coSo->nguoiDungs()->orderBy('name')->get()->map(fn ($u) => [$u->email, $u->name]));

        foreach (range('A', 'D') as $c) $s2->getColumnDimension($c)->setAutoSize(true);

        $ss->setActiveSheetIndex(0);

        $path = $outPath ?: tempnam(sys_get_temp_dir(), 'tonghop_mau_').'.xlsx';
        (new XlsxWriter($ss))->save($path);
        return $path;
    }

    /**
     * Parse xlsx upload → mảng row (0-based) với header key.
     * Trả về [$rows, $headerMap] hoặc throw nếu file hỏng.
     */
    public function parseUpload(string $filePath): array
    {
        $ss = IOFactory::load($filePath);
        $s = $ss->getSheetByName('Nhập liệu') ?? $ss->getSheet(0);
        $data = $s->toArray(null, true, true, false);
        if (count($data) < 2) return [[], []];

        // Header ở dòng 1 — bỏ *, bỏ chú thích trong ngoặc → về key.
        $headerRow = array_map(function ($h) {
            $h = trim((string) $h);
            $h = preg_replace('/\s*\*\s*$/', '', $h);
            $h = preg_replace('/\s*\(.*?\)\s*$/', '', $h);
            return mb_strtolower(trim($h));
        }, $data[0]);
        // Map tiếng Việt → key nội bộ.
        $viToKey = [
            'phân loại' => 'phan_loai', 'họ tên khách' => 'ho_ten', 'sđt' => 'so_dien_thoai',
            'ngày thực hiện' => 'ngay_thuc_hien', 'giờ bắt đầu' => 'gio_bat_dau',
            'dịch vụ' => 'dich_vu', 'bác sĩ' => 'bac_si', 'ktv' => 'ktv', 'email sale' => 'sale_email',
            'nguồn' => 'nguon', 'kết quả' => 'ket_qua', 'ghi chú' => 'ghi_chu',
        ];
        $headerMap = [];
        foreach ($headerRow as $idx => $h) {
            if (isset($viToKey[$h])) $headerMap[$idx] = $viToKey[$h];
        }

        $rows = [];
        for ($i = 1; $i < count($data); $i++) {
            $raw = $data[$i];
            $rec = [];
            foreach ($headerMap as $idx => $key) {
                $rec[$key] = isset($raw[$idx]) ? (is_string($raw[$idx]) ? trim($raw[$idx]) : $raw[$idx]) : null;
            }
            // Bỏ dòng rỗng hoàn toàn.
            $hasAny = false;
            foreach ($rec as $v) if ($v !== null && $v !== '') { $hasAny = true; break; }
            if (! $hasAny) continue;
            // Bỏ dòng ví dụ nếu ghi chú chứa "Mẫu".
            $gc = $rec['ghi_chu'] ?? '';
            if (is_string($gc) && stripos($gc, 'Mẫu') === 0) continue;
            $rows[] = ['__row' => $i + 1, ...$rec];  // __row: số dòng thực trên xlsx (1-based)
        }
        return [$rows, $headerMap];
    }

    /**
     * Xuất lại file lỗi: giữ nguyên file gốc, bôi đỏ các ô sai + thêm cột cuối "Lỗi".
     */
    public function buildErrorFile(string $srcPath, array $errorsByRow, ?string $outPath = null): string
    {
        $ss = IOFactory::load($srcPath);
        $s = $ss->getSheetByName('Nhập liệu') ?? $ss->getSheet(0);
        $lastCol = $s->getHighestColumn();       // vd 'L'
        $errCol  = ++$lastCol;                    // 'M'
        $s->setCellValue($errCol.'1', 'Lỗi');
        $s->getStyle($errCol.'1')->getFont()->setBold(true)->getColor()->setRGB('9C0006');
        $s->getStyle($errCol.'1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFC7CE');

        $red = ['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFC7CE']]];

        // Header index → col letter (từ dòng 1)
        $headerRow = $s->rangeToArray('A1:'.$s->getHighestColumn().'1', null, false, false, false)[0] ?? [];
        $keyToCol = [];
        $viToKey = [
            'phân loại' => 'phan_loai', 'họ tên khách' => 'ho_ten', 'sđt' => 'so_dien_thoai',
            'ngày thực hiện' => 'ngay_thuc_hien', 'giờ bắt đầu' => 'gio_bat_dau',
            'dịch vụ' => 'dich_vu', 'bác sĩ' => 'bac_si', 'ktv' => 'ktv', 'email sale' => 'sale_email',
            'nguồn' => 'nguon', 'kết quả' => 'ket_qua', 'ghi chú' => 'ghi_chu',
        ];
        foreach ($headerRow as $idx => $h) {
            $h = mb_strtolower(preg_replace('/\s*\*\s*$|\s*\(.*?\)\s*$/', '', trim((string) $h)));
            if (isset($viToKey[$h])) $keyToCol[$viToKey[$h]] = chr(65 + $idx);
        }

        foreach ($errorsByRow as $rowNo => $rowErrors) {
            $msgs = [];
            foreach ($rowErrors as $field => $msg) {
                $msgs[] = $msg;
                if (isset($keyToCol[$field])) {
                    $s->getStyle($keyToCol[$field].$rowNo)->applyFromArray($red);
                }
            }
            $s->setCellValue($errCol.$rowNo, implode(' · ', array_unique($msgs)));
            $s->getStyle($errCol.$rowNo)->getFont()->getColor()->setRGB('9C0006');
        }
        $s->getColumnDimension($errCol)->setWidth(60);

        $path = $outPath ?: tempnam(sys_get_temp_dir(), 'tonghop_loi_').'.xlsx';
        (new XlsxWriter($ss))->save($path);
        return $path;
    }
}
