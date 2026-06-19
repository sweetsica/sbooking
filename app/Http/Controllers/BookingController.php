<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\CoSo;
use App\Models\KhachHang;
use App\Models\PhanQuyen;
use App\Models\Phong;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BookingController extends Controller
{
    public function create(CoSo $co_so)
    {
        return view('longevity.create', $this->formData($co_so) + ['bk' => null]);
    }

    public function edit(CoSo $co_so, Booking $booking)
    {
        abort_unless($booking->co_so_id === $co_so->id, 404);

        $booking->load(['khachHang', 'menus']);

        return view('longevity.create', $this->formData($co_so) + ['bk' => $booking]);
    }

    /** Dữ liệu dùng chung cho form tạo / sửa. */
    private function formData(CoSo $co_so): array
    {
        $co_so->load([
            'phongs' => fn ($q) => $q->where('trang_thai', 'hoat_dong')->with('khungGios'),
            'dichVus' => fn ($q) => $q->where('active', true),
            'bacSis' => fn ($q) => $q->where('active', true),
            'menus' => fn ($q) => $q->where('active', true),
        ]);

        // Nhân viên Sales của cơ sở (+ admin toàn hệ thống)
        $sales = $co_so->nguoiDungs()
            ->whereHas('phongBan', fn ($q) => $q->where('ma', 'sales'))
            ->orderBy('name')->get();

        // Khung giờ theo từng phòng -> JSON cho dropdown động
        $slots = $co_so->phongs->mapWithKeys(fn ($p) => [
            $p->id => $p->khungGios->map(fn ($k) => [
                'id' => $k->id,
                'nhan' => $k->nhan,
                'bd' => substr($k->gio_bat_dau, 0, 5),
                'kt' => substr($k->gio_ket_thuc, 0, 5),
            ])->values(),
        ]);

        return [
            'coSo' => $co_so,
            'phongs' => $co_so->phongs,
            'dichVus' => $co_so->dichVus,
            'bacSis' => $co_so->bacSis,
            'menus' => $co_so->menus,
            'sales' => $sales,
            'slots' => $slots,
        ];
    }

    // Trả về khung giờ (1 tiếng) của 1 phòng + tình trạng đã đầy theo ngày
    public function khungGio(CoSo $co_so, Request $request)
    {
        $phong = Phong::where('co_so_id', $co_so->id)
            ->where('id', $request->query('phong_id'))
            ->with('khungGios')->first();

        if (! $phong) {
            return response()->json(['capacity' => 0, 'slots' => []]);
        }

        $ngay = $request->date('ngay') ?? now();
        $capacity = max(1, (int) $phong->so_slot_toi_da);

        // Số booking theo từng khung giờ cho phòng + ngày đó
        $except = $request->query('except');
        $counts = Booking::where('co_so_id', $co_so->id)
            ->where('phong_id', $phong->id)
            ->whereDate('ngay_dat', $ngay)
            ->when($except, fn ($q) => $q->where('id', '!=', $except))
            ->selectRaw('khung_gio_id, COUNT(*) as c')
            ->groupBy('khung_gio_id')
            ->pluck('c', 'khung_gio_id');

        return response()->json([
            'capacity' => $capacity,
            'slots' => $phong->khungGios->map(function ($k) use ($counts, $capacity) {
                $booked = (int) ($counts[$k->id] ?? 0);

                return [
                    'id' => $k->id,
                    'nhan' => $k->nhan,
                    'bd' => substr($k->gio_bat_dau, 0, 5),
                    'kt' => substr($k->gio_ket_thuc, 0, 5),
                    'gio' => (int) substr($k->gio_bat_dau, 0, 2),
                    'booked' => $booked,
                    'capacity' => $capacity,
                    'full' => $booked >= $capacity,
                ];
            })->values(),
        ]);
    }

    // Khung giờ đã kín chỗ (đủ số slot của phòng) cho phòng + ngày?
    private function khungGioDayCho(CoSo $co_so, int $phongId, int $khungGioId, string $ngay, ?int $exceptId = null): bool
    {
        $capacity = max(1, (int) optional(Phong::find($phongId))->so_slot_toi_da);
        $booked = Booking::where('co_so_id', $co_so->id)
            ->where('phong_id', $phongId)
            ->where('khung_gio_id', $khungGioId)
            ->whereDate('ngay_dat', $ngay)
            ->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))
            ->count();

        return $booked >= $capacity;
    }

    // Kiểm tra trùng số điện thoại trong cơ sở
    public function checkPhone(CoSo $co_so, Request $request)
    {
        $sdt = preg_replace('/\s+/', '', (string) $request->query('sdt'));
        $kh = null;
        if ($sdt !== '') {
            $kh = KhachHang::where('co_so_id', $co_so->id)
                ->where('so_dien_thoai', $sdt)->first();
        }

        return response()->json([
            'ton_tai' => (bool) $kh,
            'ho_ten' => $kh?->ho_ten,
        ]);
    }

    public function store(CoSo $co_so, Request $request)
    {
        $data = $request->validate([
            'ho_ten'        => ['required', 'string', 'max:255'],
            'so_dien_thoai' => ['required', 'string', 'max:30'],
            'email'         => ['nullable', 'email', 'max:255'],
            'ngay_dat'      => ['required', 'date'],
            'phong_id'      => ['required', Rule::exists('phong', 'id')->where('co_so_id', $co_so->id)],
            'khung_gio_id'  => ['required', Rule::exists('khung_gio', 'id')],
            'gio_thuc_hien' => ['nullable', 'regex:/^\d{2}:(00|30)$/'], // phút fix 00 / 30
            'gio_ket_thuc'  => ['nullable', 'regex:/^\d{2}:(00|30)$/'],
            'dich_vu_id'    => ['required', Rule::exists('dich_vu', 'id')],
            'sale_id'       => ['required', Rule::exists('users', 'id')],
            'bac_si_id'     => ['nullable', Rule::exists('bac_si', 'id')],
            'so_lieu_trinh' => ['nullable', 'string', 'max:50'],
            'nguon'         => ['nullable', 'string', 'max:100'],
            'ket_hop_medical' => ['nullable', 'boolean'],
            'ghi_chu'       => ['nullable', 'string'],
            'menu_ids'      => ['nullable', 'array'],
            'menu_ids.*'    => [Rule::exists('menu', 'id')],
        ], [
            'ho_ten.required'        => 'Vui lòng nhập họ tên khách hàng.',
            'so_dien_thoai.required' => 'Vui lòng nhập số điện thoại.',
            'phong_id.required'      => 'Vui lòng chọn phòng.',
            'khung_gio_id.required'  => 'Vui lòng chọn khung giờ.',
            'dich_vu_id.required'    => 'Vui lòng chọn liệu pháp/dịch vụ.',
            'sale_id.required'       => 'Vui lòng chọn sale phụ trách.',
            'gio_thuc_hien.regex'    => 'Phút thực hiện chỉ được là 00 hoặc 30.',
        ]);

        // Chặn đặt trùng: khung giờ đã kín chỗ thì không cho đặt nữa
        if ($this->khungGioDayCho($co_so, (int) $data['phong_id'], (int) $data['khung_gio_id'], $data['ngay_dat'])) {
            return back()->withInput()->withErrors([
                'khung_gio_id' => 'Khung giờ này đã được đặt kín cho ngày đã chọn. Vui lòng chọn khung giờ khác.',
            ]);
        }

        $sdt = preg_replace('/\s+/', '', $data['so_dien_thoai']);

        // Tìm/khởi tạo khách theo SĐT trong cơ sở
        $kh = KhachHang::firstOrNew([
            'co_so_id' => $co_so->id,
            'so_dien_thoai' => $sdt,
        ]);
        $kh->ho_ten = $data['ho_ten'];
        $kh->email = $data['email'] ?? $kh->email;
        $kh->save();

        $gioBatDau = ! empty($data['gio_thuc_hien']) ? $data['gio_thuc_hien'] . ':00' : null;
        $gioKetThuc = ! empty($data['gio_ket_thuc']) ? $data['gio_ket_thuc'] . ':00' : null;

        $booking = Booking::create([
            'co_so_id'      => $co_so->id,
            'khach_hang_id' => $kh->id,
            'phong_id'      => $data['phong_id'],
            'khung_gio_id'  => $data['khung_gio_id'],
            'dich_vu_id'    => $data['dich_vu_id'],
            'bac_si_id'     => $data['bac_si_id'] ?? null,
            'sale_id'       => $data['sale_id'],
            'ngay_dat'      => $data['ngay_dat'],
            'gio_thuc_hien' => $gioBatDau,
            'gio_ket_thuc'  => $gioKetThuc,
            'so_lieu_trinh' => $data['so_lieu_trinh'] ?? null,
            'nguon'         => $data['nguon'] ?? null,
            'ket_hop_medical' => $request->boolean('ket_hop_medical'),
            'ghi_chu'       => $data['ghi_chu'] ?? null,
            'trang_thai'    => 'cho_duyet',
        ]);

        if (! empty($data['menu_ids'])) {
            $booking->menus()->sync($data['menu_ids']);
        }

        return redirect("/{$co_so->slug}/danh-sach")
            ->with('ok', 'Đã tạo lịch hẹn cho ' . $kh->ho_ten . '.');
    }

    public function update(CoSo $co_so, Booking $booking, Request $request)
    {
        abort_unless($booking->co_so_id === $co_so->id, 404);

        $data = $request->validate([
            'ho_ten'        => ['required', 'string', 'max:255'],
            'so_dien_thoai' => ['required', 'string', 'max:30'],
            'email'         => ['nullable', 'email', 'max:255'],
            'ngay_dat'      => ['required', 'date'],
            'phong_id'      => ['required', Rule::exists('phong', 'id')->where('co_so_id', $co_so->id)],
            'khung_gio_id'  => ['required', Rule::exists('khung_gio', 'id')],
            'gio_thuc_hien' => ['nullable', 'regex:/^\d{2}:(00|30)$/'],
            'gio_ket_thuc'  => ['nullable', 'regex:/^\d{2}:(00|30)$/'],
            'dich_vu_id'    => ['required', Rule::exists('dich_vu', 'id')],
            'sale_id'       => ['required', Rule::exists('users', 'id')],
            'bac_si_id'     => ['nullable', Rule::exists('bac_si', 'id')],
            'so_lieu_trinh' => ['nullable', 'string', 'max:50'],
            'nguon'         => ['nullable', 'string', 'max:100'],
            'ket_hop_medical' => ['nullable', 'boolean'],
            'ghi_chu'       => ['nullable', 'string'],
            'menu_ids'      => ['nullable', 'array'],
            'menu_ids.*'    => [Rule::exists('menu', 'id')],
        ], [
            'ho_ten.required'        => 'Vui lòng nhập họ tên khách hàng.',
            'so_dien_thoai.required' => 'Vui lòng nhập số điện thoại.',
            'phong_id.required'      => 'Vui lòng chọn phòng.',
            'khung_gio_id.required'  => 'Vui lòng chọn khung giờ.',
            'dich_vu_id.required'    => 'Vui lòng chọn liệu pháp/dịch vụ.',
            'sale_id.required'       => 'Vui lòng chọn sale phụ trách.',
            'gio_thuc_hien.regex'    => 'Phút thực hiện chỉ được là 00 hoặc 30.',
        ]);

        // Chặn trùng khung giờ, nhưng bỏ qua chính booking đang sửa
        if ($this->khungGioDayCho($co_so, (int) $data['phong_id'], (int) $data['khung_gio_id'], $data['ngay_dat'], $booking->id)) {
            return back()->withInput()->withErrors([
                'khung_gio_id' => 'Khung giờ này đã được đặt kín cho ngày đã chọn. Vui lòng chọn khung giờ khác.',
            ]);
        }

        $sdt = preg_replace('/\s+/', '', $data['so_dien_thoai']);
        $kh = KhachHang::firstOrNew(['co_so_id' => $co_so->id, 'so_dien_thoai' => $sdt]);
        $kh->ho_ten = $data['ho_ten'];
        $kh->email = $data['email'] ?? $kh->email;
        $kh->save();

        $booking->update([
            'khach_hang_id' => $kh->id,
            'phong_id'      => $data['phong_id'],
            'khung_gio_id'  => $data['khung_gio_id'],
            'dich_vu_id'    => $data['dich_vu_id'],
            'bac_si_id'     => $data['bac_si_id'] ?? null,
            'sale_id'       => $data['sale_id'],
            'ngay_dat'      => $data['ngay_dat'],
            'gio_thuc_hien' => ! empty($data['gio_thuc_hien']) ? $data['gio_thuc_hien'] . ':00' : null,
            'gio_ket_thuc'  => ! empty($data['gio_ket_thuc']) ? $data['gio_ket_thuc'] . ':00' : null,
            'so_lieu_trinh' => $data['so_lieu_trinh'] ?? null,
            'nguon'         => $data['nguon'] ?? null,
            'ket_hop_medical' => $request->boolean('ket_hop_medical'),
            'ghi_chu'       => $data['ghi_chu'] ?? null,
        ]);

        $booking->menus()->sync($data['menu_ids'] ?? []);

        return redirect("/{$co_so->slug}/danh-sach")
            ->with('ok', 'Đã cập nhật lịch hẹn của ' . $kh->ho_ten . '.');
    }

    public function destroy(CoSo $co_so, Booking $booking)
    {
        abort_unless($booking->co_so_id === $co_so->id, 404);
        $this->authorizePerm('xoa_lich_dat_phong');

        $ten = $booking->khachHang?->ho_ten ?? 'khách';
        $booking->menus()->detach();
        $booking->delete();

        return redirect("/{$co_so->slug}/danh-sach")
            ->with('ok', 'Đã xóa lịch hẹn của ' . $ten . '.');
    }

    /** Duyệt / bỏ duyệt 1 cấp (1..3) của lịch đặt phòng. Duyệt tuần tự. */
    public function duyet(CoSo $co_so, Booking $booking, Request $request)
    {
        abort_unless($booking->co_so_id === $co_so->id, 404);

        $level = (int) $request->input('level');
        abort_unless(in_array($level, [1, 2, 3], true), 422, 'Cấp duyệt không hợp lệ.');
        $this->authorizePerm('xac_nhan_duyet_' . $level);

        $field = 'xac_nhan_duyet_' . $level;
        $approve = ! $booking->{$field};

        // Duyệt tuần tự: cấp dưới phải xong trước; bỏ duyệt thì cấp trên phải chưa duyệt.
        if ($approve) {
            for ($i = 1; $i < $level; $i++) {
                abort_if(! $booking->{'xac_nhan_duyet_' . $i}, 422, 'Phải duyệt cấp ' . $i . ' trước.');
            }
        } else {
            for ($i = $level + 1; $i <= 3; $i++) {
                abort_if((bool) $booking->{'xac_nhan_duyet_' . $i}, 422, 'Phải bỏ duyệt cấp ' . $i . ' trước.');
            }
        }

        $booking->{$field} = $approve;
        $booking->trang_thai = ($booking->xac_nhan_duyet_1 && $booking->xac_nhan_duyet_2 && $booking->xac_nhan_duyet_3)
            ? 'da_duyet' : 'cho_duyet';
        $booking->save();

        return back()->with('ok', ($approve ? 'Đã duyệt cấp ' . $level : 'Đã bỏ duyệt cấp ' . $level)
            . ' cho lịch hẹn của ' . ($booking->khachHang?->ho_ten ?? 'khách') . '.');
    }

    private function authorizePerm(string $field): void
    {
        $user = auth()->user();
        if ($user->is_admin) {
            return;
        }

        $ok = $user->phong_ban_id
            && PhanQuyen::where('phong_ban_id', $user->phong_ban_id)
                ->where('truong', $field)->exists();

        abort_unless($ok, 403, 'Bạn không có quyền xóa.');
    }
}
