<?php

namespace App\Exports;

use App\Models\CoSo;
use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * Xuất danh sách người dùng (chỉ admin hệ thống dùng — route đã gate bằng middleware 'admin').
 *
 * Phạm vi = đúng như danh sách trên màn Thiết lập → Người dùng: user THUỘC cơ sở hiện tại
 * cộng các tài khoản TOÀN HỆ THỐNG (co_so_id null). KHÔNG kèm bác sĩ / admin của cơ sở khác.
 *
 * Cột "Mật khẩu" luôn để TRỐNG: mật khẩu lưu dạng băm (bcrypt, một chiều) nên
 * không thể — và không nên — hoàn nguyên ra bản thô.
 */
class NguoiDungExport implements FromCollection, WithHeadings, WithMapping
{
    use Exportable;

    public function __construct(protected CoSo $coSo) {}

    public function headings(): array
    {
        return ['Họ tên', 'Tài khoản', 'Mật khẩu', 'Chức danh', 'Vai trò', 'Phòng ban'];
    }

    public function collection(): Collection
    {
        return User::with(['vaiTro', 'phongBan'])
            ->where(fn ($q) => $q->where('co_so_id', $this->coSo->id)->orWhereNull('co_so_id'))
            ->orderByRaw('co_so_id IS NULL')
            ->orderBy('name')
            ->get();
    }

    public function map($user): array
    {
        return [
            $user->name,
            $user->username,
            '', // Mật khẩu để trống (đã băm, không hoàn nguyên)
            $user->chuc_danh,
            $user->vaiTro?->ten,
            $user->phongBan?->ten,
        ];
    }
}
