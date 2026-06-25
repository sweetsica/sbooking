<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;

class BaoCaoExport implements WithMultipleSheets
{
    public function __construct(protected array $data) {}

    public function sheets(): array
    {
        $sheets = [
            new BaoCaoSummarySheet($this->data),
        ];
        $loai = $this->data['filters']['loai'] ?? 'all';
        if ($loai !== 'tu_van') {
            $sheets[] = new BaoCaoBookingSheet($this->data['bookings']);
        }
        if ($loai !== 'booking') {
            $sheets[] = new BaoCaoLichHenSheet($this->data['lichHens']);
        }
        return $sheets;
    }
}

class BaoCaoSummarySheet implements FromCollection, WithHeadings, WithTitle
{
    use Exportable;
    public function __construct(protected array $data) {}

    public function title(): string { return 'Tổng hợp'; }
    public function headings(): array { return ['Chỉ tiêu', 'Giá trị']; }

    public function collection()
    {
        $f = $this->data['filters'];
        $c = $this->data['counter'];
        $rows = [
            ['Loại lọc', match ($f['loai'] ?? 'all') { 'booking' => 'Chỉ đặt phòng', 'tu_van' => 'Chỉ tư vấn', default => 'Tất cả' }],
            ['Từ ngày', $f['tu'] ?: '—'],
            ['Đến ngày', $f['den'] ?: '—'],
            ['Bác sĩ', $f['bacSiId'] ?: '—'],
            ['Sale', $f['saleId'] ?: '—'],
            ['KTV', $f['ktvId'] ?: '—'],
            ['---', '---'],
            ['ĐẶT PHÒNG — Tổng', $c['booking']['total']],
            ['ĐẶT PHÒNG — Đã duyệt', $c['booking']['da_duyet']],
            ['ĐẶT PHÒNG — Chờ duyệt', $c['booking']['cho_duyet']],
            ['ĐẶT PHÒNG — Từ chối', $c['booking']['tu_choi']],
            ['ĐẶT PHÒNG — Đã xong', $c['booking']['da_xong']],
            ['---', '---'],
            ['TƯ VẤN — Tổng', $c['tu_van']['total']],
            ['TƯ VẤN — Đã duyệt', $c['tu_van']['da_duyet']],
            ['TƯ VẤN — Chờ duyệt', $c['tu_van']['cho_duyet']],
            ['TƯ VẤN — Từ chối', $c['tu_van']['tu_choi']],
            ['---', '---'],
            ['TỔNG CỘNG đơn', $c['tong']['total']],
            ['TỔNG CỘNG đã duyệt', $c['tong']['da_duyet']],
            ['TỔNG CỘNG từ chối', $c['tong']['tu_choi']],
        ];
        return new Collection($rows);
    }
}

class BaoCaoBookingSheet implements FromCollection, WithHeadings, WithTitle
{
    use Exportable;
    public function __construct(protected $bookings) {}

    public function title(): string { return 'Đặt phòng'; }

    public function headings(): array
    {
        return ['ID', 'Ngày', 'Khách', 'SĐT', 'Phòng', 'Khung giờ', 'Giờ thực hiện', 'Giờ kết thúc',
                'Bác sĩ', 'KTV', 'Sale', 'Dịch vụ', 'Nguồn', 'Ghi chú', 'Trạng thái'];
    }

    public function collection()
    {
        return $this->bookings->map(fn ($bk) => [
            $bk->id,
            $bk->ngay_dat?->format('d/m/Y'),
            $bk->khachHang?->ho_ten,
            $bk->khachHang?->so_dien_thoai,
            $bk->phong?->ten,
            $bk->khungGio?->nhan,
            substr($bk->gio_thuc_hien ?? '', 0, 5),
            substr($bk->gio_ket_thuc ?? '', 0, 5),
            $bk->bacSi?->ten_day_du,
            $bk->ktv?->name,
            $bk->sale?->name,
            $bk->dichVu?->ten,
            $bk->nguon,
            $bk->ghi_chu,
            match ($bk->trang_thai) {
                'da_duyet' => 'Đã duyệt',
                'tu_choi'  => 'Từ chối',
                'da_xong'  => 'Đã xong',
                default    => 'Chờ duyệt',
            },
        ]);
    }
}

class BaoCaoLichHenSheet implements FromCollection, WithHeadings, WithTitle
{
    use Exportable;
    public function __construct(protected $lichHens) {}

    public function title(): string { return 'Tư vấn'; }

    public function headings(): array
    {
        return ['ID', 'Ngày', 'Khách', 'SĐT', 'Bác sĩ tư vấn', 'Ca khám', 'Sale', 'Nguồn', 'Ghi chú', 'Trạng thái'];
    }

    public function collection()
    {
        return $this->lichHens->map(fn ($lh) => [
            $lh->id,
            $lh->ngay_hen?->format('d/m/Y'),
            $lh->khachHang?->ho_ten,
            $lh->khachHang?->so_dien_thoai,
            $lh->bacSiTuVan?->name,
            $lh->caKham?->nhan,
            $lh->sale?->name,
            $lh->nguon,
            $lh->ghi_chu,
            match ($lh->trang_thai) {
                'da_duyet' => 'Đã duyệt',
                'tu_choi'  => 'Từ chối',
                default    => 'Chờ duyệt',
            },
        ]);
    }
}
