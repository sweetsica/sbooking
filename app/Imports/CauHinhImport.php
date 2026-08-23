<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;

/**
 * Import cấu hình Phòng + Dịch vụ + Bác sĩ TOÀN BỘ (mọi cơ sở).
 * - Row có `id` → update theo id (bất kể co_so_id trên row).
 * - Row không id → insert; PHẢI có `co_so_id` hợp lệ + `ten`.
 * - Không xóa.
 */
class CauHinhImport implements WithMultipleSheets
{
    public array $stats = [
        'phong' => ['insert' => 0, 'update' => 0, 'skip' => 0],
        'dich_vu' => ['insert' => 0, 'update' => 0, 'skip' => 0],
        'bac_si' => ['insert' => 0, 'update' => 0, 'skip' => 0],
    ];
    public array $errors = [];
    public array $validCoSoIds;

    public function __construct()
    {
        $this->validCoSoIds = DB::table('co_so')->pluck('id')->all();
    }

    public function sheets(): array
    {
        return [
            'Phong' => new PhongImportSheet($this),
            'DichVu' => new DichVuImportSheet($this),
            'BacSi' => new BacSiImportSheet($this),
        ];
    }
}

abstract class BaseSheet implements ToCollection, WithHeadingRow, WithTitle
{
    public function __construct(protected CauHinhImport $parent) {}

    protected function toBool($v): int
    {
        if (is_bool($v)) return $v ? 1 : 0;
        $s = strtolower(trim((string) $v));
        return in_array($s, ['1', 'true', 'yes', 'y', 'x'], true) ? 1 : 0;
    }

    protected function toIntOrNull($v): ?int
    {
        if ($v === null || $v === '') return null;
        return (int) $v;
    }

    protected function toStringOrNull($v): ?string
    {
        if ($v === null || $v === '') return null;
        return (string) $v;
    }

    protected function logError(string $sheet, int $rowIdx, string $msg): void
    {
        $this->parent->errors[] = "[{$sheet}] Dòng {$rowIdx}: {$msg}";
        $this->parent->stats[$sheet]['skip']++;
    }
}

class PhongImportSheet extends BaseSheet
{
    public function title(): string { return 'Phong'; }

    public function collection(Collection $rows): void
    {
        $validKieu = ['phong_kham', 'phong_dich_vu'];
        $validTrang = ['hoat_dong', 'tam_dung'];

        foreach ($rows as $i => $row) {
            $rowIdx = $i + 2;

            $ten = $this->toStringOrNull($row['ten'] ?? null);
            if (!$ten) { $this->logError('phong', $rowIdx, 'Thiếu tên'); continue; }

            $coSoId = $this->toIntOrNull($row['co_so_id'] ?? null);
            if (!$coSoId || !in_array($coSoId, $this->parent->validCoSoIds, true)) {
                $this->logError('phong', $rowIdx, "co_so_id không hợp lệ: " . ($coSoId ?? 'null'));
                continue;
            }

            $kieu = $this->toStringOrNull($row['kieu_phong'] ?? null) ?? 'phong_kham';
            if (!in_array($kieu, $validKieu, true)) { $this->logError('phong', $rowIdx, "kieu_phong không hợp lệ: {$kieu}"); continue; }

            $trangThai = $this->toStringOrNull($row['trang_thai'] ?? null) ?? 'hoat_dong';
            if (!in_array($trangThai, $validTrang, true)) { $this->logError('phong', $rowIdx, "trang_thai không hợp lệ: {$trangThai}"); continue; }

            $data = [
                'co_so_id' => $coSoId,
                'ten' => $ten,
                'kieu_phong' => $kieu,
                'duoc_dat_tu_van' => $this->toBool($row['duoc_dat_tu_van'] ?? 0),
                'loai' => $this->toStringOrNull($row['loai'] ?? null) ?? 'kham',
                'so_slot_toi_da' => $this->toIntOrNull($row['so_slot_toi_da'] ?? null),
                'phut_moi_khach' => $this->toIntOrNull($row['phut_moi_khach'] ?? null),
                'trang_thai' => $trangThai,
                'updated_at' => now(),
            ];

            $id = $this->toIntOrNull($row['id'] ?? null);
            if ($id) {
                if (!DB::table('phong')->where('id', $id)->exists()) { $this->logError('phong', $rowIdx, "id={$id} không tồn tại"); continue; }
                DB::table('phong')->where('id', $id)->update($data);
                $this->parent->stats['phong']['update']++;
            } else {
                $data['created_at'] = now();
                DB::table('phong')->insert($data);
                $this->parent->stats['phong']['insert']++;
            }
        }
    }
}

class DichVuImportSheet extends BaseSheet
{
    public function title(): string { return 'DichVu'; }

    public function collection(Collection $rows): void
    {
        $validNhom = ['kham_ls', 'tu_van', 'khac'];

        foreach ($rows as $i => $row) {
            $rowIdx = $i + 2;

            $ten = $this->toStringOrNull($row['ten'] ?? null);
            if (!$ten) { $this->logError('dich_vu', $rowIdx, 'Thiếu tên'); continue; }

            $coSoId = $this->toIntOrNull($row['co_so_id'] ?? null);
            if (!$coSoId || !in_array($coSoId, $this->parent->validCoSoIds, true)) {
                $this->logError('dich_vu', $rowIdx, "co_so_id không hợp lệ: " . ($coSoId ?? 'null'));
                continue;
            }

            $nhom = $this->toStringOrNull($row['thuoc_nhom'] ?? null) ?? 'khac';
            if (!in_array($nhom, $validNhom, true)) { $this->logError('dich_vu', $rowIdx, "thuoc_nhom không hợp lệ: {$nhom}"); continue; }

            $phut = $this->toIntOrNull($row['thoi_gian_phut'] ?? null);
            if ($phut === null || $phut < 1) { $this->logError('dich_vu', $rowIdx, "thoi_gian_phut phải >= 1"); continue; }

            $data = [
                'co_so_id' => $coSoId,
                'ten' => $ten,
                'thoi_gian_phut' => $phut,
                'thuoc_nhom' => $nhom,
                'la_dich_vu' => $this->toBool($row['la_dich_vu'] ?? 0),
                'active' => $this->toBool($row['active'] ?? 1),
                'updated_at' => now(),
            ];

            $id = $this->toIntOrNull($row['id'] ?? null);
            if ($id) {
                if (!DB::table('dich_vu')->where('id', $id)->exists()) { $this->logError('dich_vu', $rowIdx, "id={$id} không tồn tại"); continue; }
                DB::table('dich_vu')->where('id', $id)->update($data);
                $this->parent->stats['dich_vu']['update']++;
            } else {
                $data['created_at'] = now();
                DB::table('dich_vu')->insert($data);
                $this->parent->stats['dich_vu']['insert']++;
            }
        }
    }
}

class BacSiImportSheet extends BaseSheet
{
    public function title(): string { return 'BacSi'; }

    public function collection(Collection $rows): void
    {
        foreach ($rows as $i => $row) {
            $rowIdx = $i + 2;

            $ten = $this->toStringOrNull($row['ten'] ?? null);
            if (!$ten) { $this->logError('bac_si', $rowIdx, 'Thiếu tên'); continue; }

            $coSoId = $this->toIntOrNull($row['co_so_id'] ?? null);
            if (!$coSoId || !in_array($coSoId, $this->parent->validCoSoIds, true)) {
                $this->logError('bac_si', $rowIdx, "co_so_id không hợp lệ: " . ($coSoId ?? 'null'));
                continue;
            }

            $data = [
                'co_so_id' => $coSoId,
                'ten' => $ten,
                'chuc_danh' => $this->toStringOrNull($row['chuc_danh'] ?? null) ?? 'BS.',
                'nhan_tu_van' => $this->toBool($row['nhan_tu_van'] ?? 0),
                'phut_tu_van' => $this->toIntOrNull($row['phut_tu_van'] ?? null) ?? 30,
                'nhan_kham_ls' => $this->toBool($row['nhan_kham_ls'] ?? 0),
                'phut_kham_ls' => $this->toIntOrNull($row['phut_kham_ls'] ?? null) ?? 5,
                'gio_bat_dau' => $this->toStringOrNull($row['gio_bat_dau'] ?? null) ?? '08:00',
                'gio_ket_thuc' => $this->toStringOrNull($row['gio_ket_thuc'] ?? null) ?? '17:00',
                'xuat_hien_moi_co_so' => $this->toBool($row['xuat_hien_moi_co_so'] ?? 0),
                'active' => $this->toBool($row['active'] ?? 1),
                'updated_at' => now(),
            ];

            $id = $this->toIntOrNull($row['id'] ?? null);
            if ($id) {
                if (!DB::table('bac_si')->where('id', $id)->exists()) { $this->logError('bac_si', $rowIdx, "id={$id} không tồn tại"); continue; }
                DB::table('bac_si')->where('id', $id)->update($data);
                $this->parent->stats['bac_si']['update']++;
            } else {
                $data['created_at'] = now();
                DB::table('bac_si')->insert($data);
                $this->parent->stats['bac_si']['insert']++;
            }
        }
    }
}
