<?php

namespace App\Http\Controllers;

use App\Models\CoSo;
use App\Models\KhachHang;
use App\Models\LichHen;
use App\Models\PhanQuyen;
use App\Models\User;
use App\Models\VaiTro;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LichHenController extends Controller
{
    public function create(CoSo $co_so)
    {
        return view('longevity.lich-hen.create', $this->formData($co_so) + ['lh' => null]);
    }

    public function edit(CoSo $co_so, LichHen $lich_hen)
    {
        abort_unless($lich_hen->co_so_id === $co_so->id, 404);
        $this->authorizePerm('sua_lich_tu_van');

        $lich_hen->load('khachHang');

        return view('longevity.lich-hen.create', $this->formData($co_so) + ['lh' => $lich_hen]);
    }

    /** Dữ liệu dùng chung cho form tạo / sửa. */
    private function formData(CoSo $co_so): array
    {
        $vrBsTuVan = VaiTro::where('ma', 'bac_si_tu_van')->first();

        // Bác sĩ tư vấn: thuộc cơ sở hoặc global (is_tu_van)
        $bacSis = User::where('vai_tro_id', $vrBsTuVan?->id)
            ->where(fn ($q) => $q->where('co_so_id', $co_so->id)->orWhere('is_tu_van', true))
            ->with('caKhams')
            ->orderBy('name')->get();

        $sales = $co_so->nguoiDungs()->orderBy('name')->get();

        $caKhamMap = $bacSis->mapWithKeys(fn ($bs) => [
            $bs->id => $bs->caKhams->map(fn ($ck) => [
                'id' => $ck->id,
                'nhan' => $ck->nhan,
                'bd' => substr($ck->gio_bat_dau, 0, 5),
                'kt' => substr($ck->gio_ket_thuc, 0, 5),
            ])->values(),
        ]);

        return [
            'coSo' => $co_so,
            'bacSis' => $bacSis,
            'sales' => $sales,
            'caKhamMap' => $caKhamMap,
        ];
    }

    public function caKham(CoSo $co_so, Request $request)
    {
        $bs = User::where('id', $request->query('bac_si_id'))
            ->where(fn ($q) => $q->where('co_so_id', $co_so->id)->orWhere('is_tu_van', true))
            ->with('caKhams')->first();

        if (! $bs) {
            return response()->json(['slots' => []]);
        }

        $ngay = $request->date('ngay') ?? now();

        $except = $request->query('except');
        $counts = LichHen::where('co_so_id', $co_so->id)
            ->where('bac_si_user_id', $bs->id)
            ->whereDate('ngay_hen', $ngay)
            ->when($except, fn ($q) => $q->where('id', '!=', $except))
            ->selectRaw('ca_kham_id, COUNT(*) as c')
            ->groupBy('ca_kham_id')
            ->pluck('c', 'ca_kham_id');

        return response()->json([
            'slots' => $bs->caKhams->map(function ($ck) use ($counts) {
                $booked = (int) ($counts[$ck->id] ?? 0);

                return [
                    'id' => $ck->id,
                    'nhan' => $ck->nhan,
                    'bd' => substr($ck->gio_bat_dau, 0, 5),
                    'kt' => substr($ck->gio_ket_thuc, 0, 5),
                    'full' => $booked >= 1,
                ];
            })->values(),
        ]);
    }

    public function checkPhone(CoSo $co_so, Request $request)
    {
        $sdt = preg_replace('/\s+/', '', (string) $request->query('sdt'));
        $kh = $sdt !== '' ? KhachHang::where('co_so_id', $co_so->id)->where('so_dien_thoai', $sdt)->first() : null;

        return response()->json([
            'ton_tai' => (bool) $kh,
            'ho_ten' => $kh?->ho_ten,
        ]);
    }

    public function store(CoSo $co_so, Request $request)
    {
        $data = $request->validate([
            'ho_ten'            => ['required', 'string', 'max:255'],
            'so_dien_thoai'     => ['required', 'string', 'max:30'],
            'email'             => ['nullable', 'email', 'max:255'],
            'ngay_hen'          => ['required', 'date'],
            'bac_si_user_id'    => ['required', Rule::exists('users', 'id')],
            'ca_kham_id'        => ['required', Rule::exists('ca_kham', 'id')],
            'sale_id'           => ['required', Rule::exists('users', 'id')],
            'nguon'             => ['nullable', 'string', 'max:100'],
            'ghi_chu'           => ['nullable', 'string'],
        ], [
            'ho_ten.required'           => 'Vui lòng nhập họ tên khách hàng.',
            'so_dien_thoai.required'    => 'Vui lòng nhập số điện thoại.',
            'bac_si_user_id.required'   => 'Vui lòng chọn bác sĩ tư vấn.',
            'ca_kham_id.required'       => 'Vui lòng chọn ca khám.',
            'sale_id.required'          => 'Vui lòng chọn sale phụ trách.',
        ]);

        // Check slot availability
        $booked = LichHen::where('co_so_id', $co_so->id)
            ->where('bac_si_user_id', $data['bac_si_user_id'])
            ->where('ca_kham_id', $data['ca_kham_id'])
            ->whereDate('ngay_hen', $data['ngay_hen'])
            ->exists();

        if ($booked) {
            return back()->withInput()->withErrors([
                'ca_kham_id' => 'Ca khám này đã có người đặt cho ngày đã chọn.',
            ]);
        }

        $sdt = preg_replace('/\s+/', '', $data['so_dien_thoai']);
        $kh = KhachHang::firstOrNew(['co_so_id' => $co_so->id, 'so_dien_thoai' => $sdt]);
        $kh->ho_ten = $data['ho_ten'];
        $kh->email = $data['email'] ?? $kh->email;
        $kh->save();

        LichHen::create([
            'co_so_id'         => $co_so->id,
            'khach_hang_id'    => $kh->id,
            'bac_si_user_id'   => $data['bac_si_user_id'],
            'ca_kham_id'       => $data['ca_kham_id'],
            'sale_id'          => $data['sale_id'],
            'ngay_hen'         => $data['ngay_hen'],
            'nguon'            => $data['nguon'] ?? null,
            'ghi_chu'          => $data['ghi_chu'] ?? null,
            'trang_thai'       => 'cho_duyet',
        ]);

        return redirect("/{$co_so->slug}/ds-tu-van")
            ->with('ok', 'Đã tạo lịch tư vấn cho ' . $kh->ho_ten . '.');
    }

    public function update(CoSo $co_so, LichHen $lich_hen, Request $request)
    {
        abort_unless($lich_hen->co_so_id === $co_so->id, 404);
        $this->authorizePerm('sua_lich_tu_van');

        $data = $request->validate([
            'ho_ten'            => ['required', 'string', 'max:255'],
            'so_dien_thoai'     => ['required', 'string', 'max:30'],
            'email'             => ['nullable', 'email', 'max:255'],
            'ngay_hen'          => ['required', 'date'],
            'bac_si_user_id'    => ['required', Rule::exists('users', 'id')],
            'ca_kham_id'        => ['required', Rule::exists('ca_kham', 'id')],
            'sale_id'           => ['required', Rule::exists('users', 'id')],
            'nguon'             => ['nullable', 'string', 'max:100'],
            'ghi_chu'           => ['nullable', 'string'],
        ], [
            'ho_ten.required'           => 'Vui lòng nhập họ tên khách hàng.',
            'so_dien_thoai.required'    => 'Vui lòng nhập số điện thoại.',
            'bac_si_user_id.required'   => 'Vui lòng chọn bác sĩ tư vấn.',
            'ca_kham_id.required'       => 'Vui lòng chọn ca khám.',
            'sale_id.required'          => 'Vui lòng chọn sale phụ trách.',
        ]);

        // Ca khám đã có người đặt (trừ chính lịch đang sửa)?
        $booked = LichHen::where('co_so_id', $co_so->id)
            ->where('bac_si_user_id', $data['bac_si_user_id'])
            ->where('ca_kham_id', $data['ca_kham_id'])
            ->whereDate('ngay_hen', $data['ngay_hen'])
            ->where('id', '!=', $lich_hen->id)
            ->exists();

        if ($booked) {
            return back()->withInput()->withErrors([
                'ca_kham_id' => 'Ca khám này đã có người đặt cho ngày đã chọn.',
            ]);
        }

        $sdt = preg_replace('/\s+/', '', $data['so_dien_thoai']);
        $kh = KhachHang::firstOrNew(['co_so_id' => $co_so->id, 'so_dien_thoai' => $sdt]);
        $kh->ho_ten = $data['ho_ten'];
        $kh->email = $data['email'] ?? $kh->email;
        $kh->save();

        $lich_hen->update([
            'khach_hang_id'    => $kh->id,
            'bac_si_user_id'   => $data['bac_si_user_id'],
            'ca_kham_id'       => $data['ca_kham_id'],
            'sale_id'          => $data['sale_id'],
            'ngay_hen'         => $data['ngay_hen'],
            'nguon'            => $data['nguon'] ?? null,
            'ghi_chu'          => $data['ghi_chu'] ?? null,
        ]);

        return redirect("/{$co_so->slug}/ds-tu-van")
            ->with('ok', 'Đã cập nhật lịch tư vấn của ' . $kh->ho_ten . '.');
    }

    public function destroy(CoSo $co_so, LichHen $lich_hen)
    {
        abort_unless($lich_hen->co_so_id === $co_so->id, 404);

        $user = auth()->user();
        $ok = $user->is_admin || PhanQuyen::where(function ($q) use ($user) {
                if ($user->phong_ban_id) $q->orWhere('phong_ban_id', $user->phong_ban_id);
                if ($user->vai_tro_id) $q->orWhere('vai_tro_id', $user->vai_tro_id);
            })->where('truong', 'xoa_lich_tu_van')->exists();
        abort_unless($ok, 403, 'Bạn không có quyền xóa.');

        $ten = $lich_hen->khachHang?->ho_ten ?? 'khách';
        $lich_hen->delete();

        return redirect("/{$co_so->slug}/ds-tu-van")
            ->with('ok', 'Đã xóa lịch tư vấn của ' . $ten . '.');
    }

    /** Duyệt / bỏ duyệt lịch tư vấn (chỉ admin). */
    public function duyet(CoSo $co_so, LichHen $lich_hen)
    {
        abort_unless($lich_hen->co_so_id === $co_so->id, 404);

        $user = auth()->user();
        $ok = $user->is_admin || PhanQuyen::where(function ($q) use ($user) {
                if ($user->phong_ban_id) $q->orWhere('phong_ban_id', $user->phong_ban_id);
                if ($user->vai_tro_id) $q->orWhere('vai_tro_id', $user->vai_tro_id);
            })->where('truong', 'duyet_tu_van')->exists();
        abort_unless($ok, 403, 'Bạn không có quyền duyệt.');

        $approve = $lich_hen->trang_thai !== 'da_duyet';
        $lich_hen->trang_thai = $approve ? 'da_duyet' : 'cho_duyet';
        $lich_hen->save();

        $ten = $lich_hen->khachHang?->ho_ten ?? 'khách';

        return back()->with('ok', ($approve ? 'Đã duyệt' : 'Đã bỏ duyệt') . ' lịch tư vấn của ' . $ten . '.');
    }

    private function authorizePerm(string $field): void
    {
        $user = auth()->user();
        if ($user->is_admin) {
            return;
        }

        $ok = PhanQuyen::where(function ($q) use ($user) {
                if ($user->phong_ban_id) $q->orWhere('phong_ban_id', $user->phong_ban_id);
                if ($user->vai_tro_id) $q->orWhere('vai_tro_id', $user->vai_tro_id);
            })->where('truong', $field)->exists();

        abort_unless($ok, 403, 'Bạn không có quyền thực hiện thao tác này.');
    }

    public function manage(CoSo $co_so, Request $request)
    {
        $vrBsTuVan = VaiTro::where('ma', 'bac_si_tu_van')->first();
        $bacSis = User::where('vai_tro_id', $vrBsTuVan?->id)
            ->where(fn ($q) => $q->where('co_so_id', $co_so->id)->orWhere('is_tu_van', true))
            ->with('caKhams')
            ->orderBy('id')->get();
        $danhSachCoSo = CoSo::where('active', true)->orderBy('id')->get();
        $date = $request->date('ngay') ?? now();

        $lichHens = LichHen::where('co_so_id', $co_so->id)
            ->whereDate('ngay_hen', $date)
            ->with(['khachHang', 'sale'])
            ->orderBy('id')->get()
            ->groupBy('bac_si_user_id');

        // Một thẻ cho mỗi bác sĩ, kèm timeline ca khám theo trạng thái.
        $cards = $bacSis->map(function ($bs) use ($lichHens) {
            $byCa = ($lichHens[$bs->id] ?? collect())->keyBy('ca_kham_id');
            $slots = $bs->caKhams->sortBy('thu_tu')->values();

            $booked = 0;
            $timeline = $slots->map(function ($ck) use ($byCa, &$booked) {
                $lh = $byCa->get($ck->id);
                $tt = $lh && $lh->trang_thai !== 'tu_choi' ? $lh->trang_thai : null;
                if ($tt) {
                    $booked++;
                }

                return [
                    'ck' => $ck,
                    'lh' => $tt ? $lh : null,
                    'state' => $tt === 'da_duyet' ? 'dang_kham' : ($tt ? 'co_lich' : 'trong'),
                ];
            });

            $total = $slots->count();

            return [
                'bs' => $bs,
                'timeline' => $timeline,
                'total' => $total,
                'booked' => $booked,
                'rate' => $total > 0 ? (int) round($booked / $total * 100) : 0,
            ];
        });

        $allLich = $lichHens->flatten();

        return view('longevity.lich-hen.manage', [
            'coSo' => $co_so,
            'danhSachCoSo' => $danhSachCoSo,
            'cards' => $cards,
            'date' => $date,
            'stats' => [
                'total' => $allLich->count(),
                'approved' => $allLich->where('trang_thai', 'da_duyet')->count(),
                'pending' => $allLich->where('trang_thai', 'cho_duyet')->count(),
            ],
        ]);
    }

    public function list(CoSo $co_so, Request $request)
    {
        $query = LichHen::where('co_so_id', $co_so->id)
            ->with(['khachHang', 'bacSiTuVan', 'caKham', 'sale'])
            ->latest('id');

        if ($request->filled('ngay_tu')) {
            $query->whereDate('ngay_hen', '>=', $request->query('ngay_tu'));
        }
        if ($request->filled('ngay_den')) {
            $query->whereDate('ngay_hen', '<=', $request->query('ngay_den'));
        }
        if ($request->filled('bac_si_id')) {
            $query->where('bac_si_user_id', $request->query('bac_si_id'));
        }
        if ($request->filled('nguon')) {
            $query->where('nguon', $request->query('nguon'));
        }
        if ($request->filled('trang_thai')) {
            $query->where('trang_thai', $request->query('trang_thai'));
        }

        $lichHens = $query->paginate(20)->withQueryString();

        $vrBsTuVan = VaiTro::where('ma', 'bac_si_tu_van')->first();
        $bacSis = User::where('vai_tro_id', $vrBsTuVan?->id)
            ->where(fn ($q) => $q->where('co_so_id', $co_so->id)->orWhere('is_tu_van', true))
            ->orderBy('name')->get();

        return view('longevity.lich-hen.list', [
            'coSo' => $co_so,
            'lichHens' => $lichHens,
            'bacSis' => $bacSis,
            'nguons' => LichHen::where('co_so_id', $co_so->id)
                ->whereNotNull('nguon')->distinct()->pluck('nguon'),
            'filters' => $request->only(['ngay_tu', 'ngay_den', 'bac_si_id', 'nguon', 'trang_thai']),
        ]);
    }
}
