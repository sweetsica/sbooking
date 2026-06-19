<?php

namespace App\Http\Controllers;

use App\Models\BacSiTuVan;
use App\Models\CoSo;
use App\Models\KhachHang;
use App\Models\LichHen;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LichHenController extends Controller
{
    public function create(CoSo $co_so)
    {
        $bacSis = $co_so->bacSiTuVans()
            ->where('active', true)
            ->with('caKhams')
            ->get();

        $sales = $co_so->nguoiDungs()
            ->whereHas('phongBan', fn ($q) => $q->where('ma', 'sales'))
            ->orderBy('name')->get();

        $caKhamMap = $bacSis->mapWithKeys(fn ($bs) => [
            $bs->id => $bs->caKhams->map(fn ($ck) => [
                'id' => $ck->id,
                'nhan' => $ck->nhan,
                'bd' => substr($ck->gio_bat_dau, 0, 5),
                'kt' => substr($ck->gio_ket_thuc, 0, 5),
            ])->values(),
        ]);

        return view('yhct.lich-hen.create', [
            'coSo' => $co_so,
            'bacSis' => $bacSis,
            'sales' => $sales,
            'caKhamMap' => $caKhamMap,
        ]);
    }

    public function caKham(CoSo $co_so, Request $request)
    {
        $bs = BacSiTuVan::where('co_so_id', $co_so->id)
            ->where('id', $request->query('bac_si_id'))
            ->with('caKhams')->first();

        if (! $bs) {
            return response()->json(['slots' => []]);
        }

        $ngay = $request->date('ngay') ?? now();

        $counts = LichHen::where('co_so_id', $co_so->id)
            ->where('bac_si_tu_van_id', $bs->id)
            ->whereDate('ngay_hen', $ngay)
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
            'bac_si_tu_van_id'  => ['required', Rule::exists('bac_si_tu_van', 'id')->where('co_so_id', $co_so->id)],
            'ca_kham_id'        => ['required', Rule::exists('ca_kham', 'id')],
            'sale_id'           => ['required', Rule::exists('users', 'id')],
            'nguon'             => ['nullable', 'string', 'max:100'],
            'ghi_chu'           => ['nullable', 'string'],
        ], [
            'ho_ten.required'           => 'Vui lòng nhập họ tên khách hàng.',
            'so_dien_thoai.required'    => 'Vui lòng nhập số điện thoại.',
            'bac_si_tu_van_id.required' => 'Vui lòng chọn bác sĩ tư vấn.',
            'ca_kham_id.required'       => 'Vui lòng chọn ca khám.',
            'sale_id.required'          => 'Vui lòng chọn sale phụ trách.',
        ]);

        // Check slot availability
        $booked = LichHen::where('co_so_id', $co_so->id)
            ->where('bac_si_tu_van_id', $data['bac_si_tu_van_id'])
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
            'bac_si_tu_van_id' => $data['bac_si_tu_van_id'],
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

    public function manage(CoSo $co_so, Request $request)
    {
        $bacSis = $co_so->bacSiTuVans()->where('active', true)->with('caKhams')->get();
        $danhSachCoSo = CoSo::where('active', true)->orderBy('id')->get();
        $date = $request->date('ngay') ?? now();

        $bs = $bacSis->firstWhere('id', (int) $request->query('bac_si_id'))
            ?? $bacSis->first();

        $slots = $bs ? $bs->caKhams()->orderBy('thu_tu')->get() : collect();

        $lichHens = collect();
        if ($bs) {
            $lichHens = LichHen::where('co_so_id', $co_so->id)
                ->where('bac_si_tu_van_id', $bs->id)
                ->whereDate('ngay_hen', $date)
                ->with(['khachHang', 'caKham', 'sale'])
                ->orderBy('id')->get();
        }

        $byCa = $lichHens->keyBy('ca_kham_id');
        $grid = $slots->map(fn ($ck) => [
            'slot' => $ck,
            'lichHen' => $byCa->get($ck->id),
        ]);

        return view('yhct.lich-hen.manage', [
            'coSo' => $co_so,
            'danhSachCoSo' => $danhSachCoSo,
            'bacSis' => $bacSis,
            'bs' => $bs,
            'date' => $date,
            'grid' => $grid,
            'stats' => [
                'total' => $lichHens->count(),
                'approved' => $lichHens->where('trang_thai', 'da_duyet')->count(),
                'pending' => $lichHens->where('trang_thai', 'cho_duyet')->count(),
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
            $query->where('bac_si_tu_van_id', $request->query('bac_si_id'));
        }
        if ($request->filled('nguon')) {
            $query->where('nguon', $request->query('nguon'));
        }

        $lichHens = $query->paginate(20)->withQueryString();
        $bacSis = $co_so->bacSiTuVans()->where('active', true)->get();

        return view('yhct.lich-hen.list', [
            'coSo' => $co_so,
            'lichHens' => $lichHens,
            'bacSis' => $bacSis,
            'nguons' => LichHen::where('co_so_id', $co_so->id)
                ->whereNotNull('nguon')->distinct()->pluck('nguon'),
            'filters' => $request->only(['ngay_tu', 'ngay_den', 'bac_si_id', 'nguon']),
        ]);
    }
}
