<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesByPhanQuyen;
use App\Models\CoSo;
use App\Models\NgayNghi;
use App\Models\Phong;
use App\Models\User;
use App\Models\VaiTro;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class NgayNghiController extends Controller
{
    use AuthorizesByPhanQuyen;

    private function authorizeAccess(): void
    {
        abort_unless(
            $this->hasPerm('quyen_ngay_nghi'),
            403,
            'Bạn không có quyền quản lý Ngày nghỉ.'
        );
    }

    public function index(CoSo $co_so)
    {
        $this->authorizeAccess();

        $dsNghi = $co_so->ngayNghis()
            ->with('nguoiTao')
            ->orderByDesc('tu_ngay')
            ->get();

        // Snapshot tên đối tượng (phòng / người) để hiển thị không cần n+1 truy vấn.
        $phongIds = $dsNghi->where('loai', 'phong')->pluck('doi_tuong_id')->filter()->unique();
        $userIds  = $dsNghi->whereIn('loai', ['bac_si', 'ktv'])->pluck('doi_tuong_id')->filter()->unique();
        $tenPhong = Phong::whereIn('id', $phongIds)->pluck('ten', 'id');
        $tenUser  = User::whereIn('id', $userIds)->pluck('name', 'id');

        return view('longevity.ngay-nghi.index', [
            'coSo'     => $co_so,
            'dsNghi'   => $dsNghi,
            'tenPhong' => $tenPhong,
            'tenUser'  => $tenUser,
            'phongs'   => $co_so->phongs()->orderBy('ten')->get(),
            'bacSis'   => $this->dsNguoi($co_so, ['bac_si', 'bac_si_tu_van']),
            'ktvs'     => $this->dsNguoi($co_so, ['ktv']),
        ]);
    }

    public function store(CoSo $co_so, Request $request)
    {
        $this->authorizeAccess();

        $data = $request->validate([
            'loai'         => ['required', Rule::in(array_keys(NgayNghi::LOAI))],
            'doi_tuong_id' => ['nullable', 'integer'],
            'tu_ngay'      => ['required', 'date'],
            'den_ngay'     => ['required', 'date', 'after_or_equal:tu_ngay'],
            'ca'           => ['required', Rule::in(array_keys(NgayNghi::CA))],
            'ly_do'        => ['nullable', 'string', 'max:255'],
        ], [
            'den_ngay.after_or_equal' => 'Ngày kết thúc phải bằng hoặc sau ngày bắt đầu.',
        ]);

        // Đối tượng bắt buộc & phải thuộc cơ sở (trừ loại "cơ sở" — không cần đối tượng).
        $doiTuongId = $this->validateDoiTuong($co_so, $data['loai'], $data['doi_tuong_id'] ?? null);

        NgayNghi::create([
            'co_so_id'     => $co_so->id,
            'loai'         => $data['loai'],
            'doi_tuong_id' => $doiTuongId,
            'tu_ngay'      => $data['tu_ngay'],
            'den_ngay'     => $data['den_ngay'],
            'ca'           => $data['ca'],
            'ly_do'        => $data['ly_do'] ?? null,
            'nguoi_tao_id' => auth()->id(),
        ]);

        return back()->with('ok', 'Đã thêm ngày nghỉ.');
    }

    public function destroy(CoSo $co_so, NgayNghi $ngay_nghi)
    {
        $this->authorizeAccess();
        abort_unless($ngay_nghi->co_so_id === $co_so->id, 404);

        $ngay_nghi->delete();

        return back()->with('ok', 'Đã xóa ngày nghỉ.');
    }

    /** Xác thực & chuẩn hóa doi_tuong_id theo loại; trả null cho loại cơ sở. */
    private function validateDoiTuong(CoSo $co_so, string $loai, ?int $id): ?int
    {
        if ($loai === 'co_so') {
            return null;
        }

        abort_if(! $id, 422, 'Vui lòng chọn đối tượng nghỉ.');

        if ($loai === 'phong') {
            $ok = Phong::where('co_so_id', $co_so->id)->where('id', $id)->exists();
        } else { // bac_si | ktv
            $ok = User::where('id', $id)
                ->where(fn ($q) => $q->where('co_so_id', $co_so->id)->orWhere('is_tu_van', true))
                ->exists();
        }
        abort_unless($ok, 422, 'Đối tượng không hợp lệ cho cơ sở này.');

        return $id;
    }

    /** Danh sách người (bác sĩ / KTV) của cơ sở cho dropdown. */
    private function dsNguoi(CoSo $co_so, array $vaiTroMa)
    {
        $ids = VaiTro::whereIn('ma', $vaiTroMa)->pluck('id');

        return User::whereIn('vai_tro_id', $ids)
            ->where(fn ($q) => $q->where('co_so_id', $co_so->id)->orWhere('is_tu_van', true))
            ->orderBy('name')
            ->get(['id', 'name']);
    }
}
