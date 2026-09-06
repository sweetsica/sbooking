<?php

namespace App\Services;

use App\Models\BacSi;
use App\Models\Booking;
use App\Models\CaKham;
use App\Models\CoSo;
use App\Models\DichVu;
use App\Models\KhachHang;
use App\Models\KhungGio;
use App\Models\Ktv;
use App\Models\LichHen;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Validate + insert booking bulk từ file xlsx (fail-fast).
 *
 * Bypass abort(410) trong BookingController vì đây là "admin bulk import",
 * scope hẹp — chỉ chạy khi admin upload từ trang Tổng hợp lịch đặt.
 * Log rõ nguoi_tao_id = admin user để audit.
 */
class TongHopLichDatImporter
{
    /**
     * Validate hết. Không insert.
     * Trả về ['errors' => [rowNo => [field => msg]], 'valid' => [...normalizedRows]].
     */
    public function validate(CoSo $coSo, array $rows): array
    {
        $errors = [];
        $valid  = [];

        // Preload lookup tables (theo cơ sở).
        $dvByName = DichVu::where('co_so_id', $coSo->id)->where('active', true)
            ->get()->keyBy(fn ($d) => mb_strtolower(trim($d->ten)));
        $bsByName = BacSi::where(fn ($q) => $q->where('co_so_id', $coSo->id)->orWhere('xuat_hien_moi_co_so', true))
            ->get()->keyBy(fn ($b) => mb_strtolower(trim($b->ten)));
        $ktvByName = Ktv::where('co_so_id', $coSo->id)->where('active', true)
            ->get()->keyBy(fn ($k) => mb_strtolower(trim($k->ten)));
        $saleByEmail = $coSo->nguoiDungs()->get(['users.id', 'users.email', 'users.name'])
            ->keyBy(fn ($u) => mb_strtolower(trim($u->email)));
        $khungGioByPhongTime = KhungGio::whereHas('phong', fn ($q) => $q->where('co_so_id', $coSo->id))
            ->get()->groupBy('phong_id');
        $caKhamByBsTime = CaKham::all()->groupBy('bac_si_tu_van_id');

        foreach ($rows as $rec) {
            $rowNo = $rec['__row'];
            $err = [];

            // ---- Field bắt buộc chung ----
            foreach (TongHopLichDatSheet::REQUIRED_ALL as $f) {
                if (empty($rec[$f])) $err[$f] = "Thiếu {$f}";
            }

            // Phân loại
            $pl = strtoupper(trim((string) ($rec['phan_loai'] ?? '')));
            if (! in_array($pl, ['K', 'TV', 'DV'], true)) {
                $err['phan_loai'] = 'Phân loại phải là K / TV / DV';
            }

            // SĐT
            $sdt = preg_replace('/\D+/', '', (string) ($rec['so_dien_thoai'] ?? ''));
            if ($sdt === '' || ! preg_match('/^0[2-9]\d{8,9}$/', $sdt)) {
                $err['so_dien_thoai'] = 'SĐT không hợp lệ (VN 10-11 số, đầu 0)';
            }

            // Ngày
            $ngay = null;
            try {
                $v = $rec['ngay_thuc_hien'];
                if ($v instanceof \DateTimeInterface) $ngay = Carbon::instance($v);
                elseif (is_numeric($v)) $ngay = Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $v));
                elseif (! empty($v)) $ngay = Carbon::parse((string) $v);
            } catch (\Throwable $e) { $ngay = null; }
            if (! $ngay) $err['ngay_thuc_hien'] = 'Ngày không parse được (dùng YYYY-MM-DD)';

            // Giờ
            $gio = null;
            $gRaw = $rec['gio_bat_dau'] ?? null;
            if ($gRaw instanceof \DateTimeInterface) $gio = $gRaw->format('H:i');
            elseif (is_numeric($gRaw)) {
                $secs = (int) round(((float) $gRaw) * 86400);
                $gio = gmdate('H:i', $secs);
            } elseif (is_string($gRaw) && preg_match('/^(\d{1,2}):(\d{2})/', $gRaw, $m)) {
                $gio = sprintf('%02d:%02d', (int) $m[1], $m[2]);
            }
            if (! $gio) $err['gio_bat_dau'] = 'Giờ không hợp lệ (dùng HH:MM)';

            // Sale
            $saleEmail = mb_strtolower(trim((string) ($rec['sale_email'] ?? '')));
            $sale = $saleByEmail->get($saleEmail);
            if (! $sale) $err['sale_email'] = "Email sale không thuộc cơ sở này ({$saleEmail})";

            // Kết quả
            $ketQua = trim((string) ($rec['ket_qua'] ?? ''));
            if ($ketQua !== '' && ! in_array($ketQua, ['Đã xong', 'Đã huỷ', 'Đã hủy'], true)) {
                $err['ket_qua'] = 'Kết quả chỉ nhận: rỗng / Đã xong / Đã huỷ';
            }

            // ---- Theo phân loại ----
            $dvId = $bsId = $ktvId = $caKhamId = null;

            if ($pl === 'K' || $pl === 'DV') {
                $dvName = mb_strtolower(trim((string) ($rec['dich_vu'] ?? '')));
                if ($dvName === '') $err['dich_vu'] = "Cần Dịch vụ cho phân loại {$pl}";
                elseif (! $dvByName->has($dvName)) $err['dich_vu'] = "Dịch vụ không có trong danh mục cơ sở này";
                else $dvId = $dvByName->get($dvName)->id;

                $bsName = mb_strtolower(trim((string) ($rec['bac_si'] ?? '')));
                if ($bsName !== '' && ! $bsByName->has($bsName)) $err['bac_si'] = 'Bác sĩ không có trong danh mục';
                elseif ($bsName !== '') $bsId = $bsByName->get($bsName)->id;

                $ktvName = mb_strtolower(trim((string) ($rec['ktv'] ?? '')));
                if ($ktvName !== '' && ! $ktvByName->has($ktvName)) $err['ktv'] = 'KTV không có trong danh mục';
                elseif ($ktvName !== '') $ktvId = $ktvByName->get($ktvName)->id;
            } elseif ($pl === 'TV') {
                // TV bắt buộc bác sĩ tư vấn.
                $bsName = mb_strtolower(trim((string) ($rec['bac_si'] ?? '')));
                if ($bsName === '') $err['bac_si'] = 'Cần Bác sĩ tư vấn cho phân loại TV';
                elseif (! $bsByName->has($bsName)) $err['bac_si'] = 'Bác sĩ không có trong danh mục';
                else {
                    $bsId = $bsByName->get($bsName)->id;
                    // Lookup ca_kham theo giờ. bac_si dùng chung bảng bac_si, nhưng ca_kham FK bac_si_tu_van_id.
                    // Skip nghiêm ngặt v1: nếu không có ca_kham match giờ → dùng ca_kham đầu tiên của BS.
                    $caList = $caKhamByBsTime->get($bsId, collect());
                    $matched = $caList->first(fn ($c) => substr($c->gio_bat_dau, 0, 5) === $gio);
                    $caKhamId = $matched?->id ?? $caList->first()?->id;
                    if (! $caKhamId) $err['gio_bat_dau'] = "BS chưa có ca khám nào — không thể tạo TV";
                }
            }

            if ($err) {
                $errors[$rowNo] = $err;
                continue;
            }

            $valid[] = [
                '__row'  => $rowNo,
                'phan_loai' => $pl,
                'ho_ten' => trim((string) $rec['ho_ten']),
                'sdt'    => $sdt,
                'ngay'   => $ngay->toDateString(),
                'gio'    => $gio,
                'dich_vu_id' => $dvId,
                'bac_si_id'  => $bsId,
                'ktv_user_id' => $ktvId,
                'ca_kham_id' => $caKhamId,
                'sale_id'    => $sale->id,
                'nguon'      => trim((string) ($rec['nguon'] ?? '')),
                'ket_qua'    => $ketQua,
                'ghi_chu'    => trim((string) ($rec['ghi_chu'] ?? '')),
            ];
        }

        return ['errors' => $errors, 'valid' => $valid];
    }

    /**
     * Insert tất cả (transaction). Chỉ gọi sau khi validate() trả 0 lỗi.
     * @return int  số dòng đã insert.
     */
    public function commit(CoSo $coSo, array $validRows, int $adminUserId): int
    {
        $count = 0;
        DB::transaction(function () use ($coSo, $validRows, $adminUserId, &$count) {
            foreach ($validRows as $r) {
                // Upsert khách theo (co_so, sdt).
                $kh = KhachHang::firstOrCreate(
                    ['co_so_id' => $coSo->id, 'so_dien_thoai' => $r['sdt']],
                    ['ho_ten' => $r['ho_ten']]
                );
                if ($kh->ho_ten !== $r['ho_ten']) {
                    $kh->update(['ho_ten' => $r['ho_ten']]);
                }

                $ttMap = [
                    ''         => 'cho_duyet',
                    'Đã xong'  => 'da_xong',
                    'Đã huỷ'   => 'da_duyet',   // no "da_huy" enum → giữ da_duyet + trang_thai_khach='huy'
                    'Đã hủy'   => 'da_duyet',
                ];
                $trangThai = $ttMap[$r['ket_qua']] ?? 'cho_duyet';
                $trangThaiKhach = in_array($r['ket_qua'], ['Đã huỷ', 'Đã hủy'], true) ? 'huy' : null;

                if ($r['phan_loai'] === 'TV') {
                    LichHen::create([
                        'co_so_id'        => $coSo->id,
                        'khach_hang_id'   => $kh->id,
                        'bac_si_tu_van_id'=> $r['bac_si_id'],
                        'ca_kham_id'      => $r['ca_kham_id'],
                        'sale_id'         => $r['sale_id'],
                        'ngay_hen'        => $r['ngay'],
                        'nguon'           => $r['nguon'] ?: null,
                        'ghi_chu'         => $r['ghi_chu'] ?: null,
                        'trang_thai'      => $trangThai === 'da_xong' ? 'da_duyet' : $trangThai,
                    ]);
                } else {
                    // K hoặc DV → Booking (BYPASS abort(410) — admin import scope).
                    Booking::create([
                        'co_so_id'      => $coSo->id,
                        'loai_dat_lich' => $r['phan_loai'] === 'DV' ? 'dich_vu' : 'phong_kham',
                        'khach_hang_id' => $kh->id,
                        'dich_vu_id'    => $r['dich_vu_id'],
                        'bac_si_id'     => $r['bac_si_id'],
                        'ktv_user_id'   => $r['ktv_user_id'],
                        'sale_id'       => $r['sale_id'],
                        'nguoi_tao_id'  => $adminUserId,       // audit: admin bulk import
                        'ngay_dat'      => $r['ngay'],
                        'gio_thuc_hien' => $r['gio'],
                        'nguon'         => $r['nguon'] ?: null,
                        'ghi_chu'       => $r['ghi_chu'] ?: null,
                        'trang_thai'    => $trangThai,
                        'trang_thai_khach' => $trangThaiKhach,
                    ]);
                }
                $count++;
            }
        });
        return $count;
    }
}
