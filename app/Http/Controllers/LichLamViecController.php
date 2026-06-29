<?php

namespace App\Http\Controllers;

use App\Exports\LichLamViecMauExport;
use App\Http\Controllers\Concerns\AuthorizesByPhanQuyen;
use App\Imports\LichLamViecImport;
use App\Models\CoSo;
use App\Models\LichLamViec;
use App\Models\Phong;
use App\Models\User;
use App\Models\VaiTro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class LichLamViecController extends Controller
{
    use AuthorizesByPhanQuyen;

    private function authorizeAccess(): void
    {
        abort_unless(
            $this->hasPerm('quyen_lich_lam_viec') || $this->hasPerm('duyet_lich_lam_viec'),
            403,
            'Bạn không có quyền truy cập Lịch làm việc.'
        );
    }

    public function index(CoSo $co_so)
    {
        $this->authorizeAccess();

        $dsLich = $co_so->lichLamViecs()
            ->with(['nguoiTao', 'nguoiDuyet'])
            ->orderByDesc('thang')
            ->get();

        return view('longevity.lich-lam-viec.index', [
            'coSo'      => $co_so,
            'dsLich'    => $dsLich,
            'canUpload' => $this->hasPerm('quyen_lich_lam_viec'),
            'canDuyet'  => $this->hasPerm('duyet_lich_lam_viec'),
        ]);
    }

    public function show(CoSo $co_so, LichLamViec $lich_lam_viec)
    {
        $this->authorizeAccess();
        abort_unless($lich_lam_viec->co_so_id === $co_so->id, 404);

        $lich_lam_viec->load(['chiTiets', 'nguoiTao', 'nguoiDuyet']);
        $grid = $this->gridFromChiTiet($lich_lam_viec);

        return view('longevity.lich-lam-viec.show', [
            'coSo'      => $co_so,
            'lich'      => $lich_lam_viec,
            'grid'      => $grid,
            'canUpload' => $this->hasPerm('quyen_lich_lam_viec'),
            'canDuyet'  => $this->hasPerm('duyet_lich_lam_viec'),
        ]);
    }

    /** Tải file Excel mẫu (lưới điền sẵn phòng + sheet tra cứu username). */
    public function mau(CoSo $co_so)
    {
        $this->authorizeAccess();

        $name = 'lich-lam-viec-mau-' . $co_so->slug . '-' . now()->format('Ymd') . '.xlsx';

        return Excel::download(new LichLamViecMauExport($co_so), $name);
    }

    /** B1: Upload → đọc lưới → màn XEM TRƯỚC (kèm khớp lại username chưa nhận diện). */
    public function preview(CoSo $co_so, Request $request)
    {
        $this->authorizePerm('quyen_lich_lam_viec');

        $data = $request->validate([
            'thang'   => ['required', 'date_format:Y-m'],
            'file'    => ['required', 'file', 'mimes:xlsx,xls'],
            'ghi_chu' => ['nullable', 'string', 'max:1000'],
        ], [
            'thang.required'    => 'Vui lòng chọn tháng áp dụng.',
            'thang.date_format' => 'Tháng không hợp lệ.',
            'file.required'     => 'Vui lòng chọn file Excel.',
            'file.mimes'        => 'File phải là Excel (.xlsx, .xls).',
        ]);

        $thang = $data['thang'] . '-01';

        $existing = LichLamViec::where('co_so_id', $co_so->id)->whereDate('thang', $thang)->first();
        if ($existing && $existing->trang_thai === 'da_duyet') {
            return back()->with('err', 'Tháng này đã được duyệt & áp dụng. Liên hệ quản trị nếu cần điều chỉnh.');
        }

        $import = new LichLamViecImport;
        Excel::import($import, $request->file('file'));

        $parsed = $this->parseSchedule($co_so, $import->data, $thang);

        if (empty($parsed['assignments']) && empty($parsed['unmatched'])) {
            return back()->with('err', 'Không đọc được dữ liệu khớp với cơ sở từ file. Hãy dùng đúng file mẫu (Tải mẫu).');
        }

        $path = $request->file('file')->store('lich-lam-viec');

        return view('longevity.lich-lam-viec.preview', [
            'coSo'    => $co_so,
            'thang'   => $thang,
            'thangYm' => $data['thang'],
            'ghiChu'  => $data['ghi_chu'] ?? null,
            'filePath'=> $path,
            'parsed'  => $parsed,
            'dsNguoi' => $this->dsNguoi($co_so),
            'daCo'    => (bool) $existing,
        ]);
    }

    /** B2: Xác nhận → đọc lại file + áp map khớp lại → lưu bản nháp. */
    public function store(CoSo $co_so, Request $request)
    {
        $this->authorizePerm('quyen_lich_lam_viec');

        $request->validate([
            'thang'    => ['required', 'date_format:Y-m'],
            'ghi_chu'  => ['nullable', 'string', 'max:1000'],
            'file_goc' => ['required', 'string', 'max:255'],
        ], [
            'thang.required'    => 'Thiếu tháng áp dụng.',
            'thang.date_format' => 'Tháng không hợp lệ.',
            'file_goc.required' => 'Thiếu file nguồn — vui lòng tải lên lại.',
        ]);

        $thang = $request->input('thang') . '-01';

        $existing = LichLamViec::where('co_so_id', $co_so->id)->whereDate('thang', $thang)->first();
        if ($existing && $existing->trang_thai === 'da_duyet') {
            return back()->with('err', 'Tháng này đã được duyệt & áp dụng. Liên hệ quản trị nếu cần điều chỉnh.');
        }

        $filePath = $request->input('file_goc');
        if (! str_starts_with($filePath, 'lich-lam-viec/') || ! Storage::disk('local')->exists($filePath)) {
            return back()->with('err', 'File nguồn không hợp lệ — vui lòng tải lên lại.');
        }

        // Map khớp lại: [usernameRaw => user_id]
        $overrides = collect((array) $request->input('map', []))
            ->filter(fn ($v) => $v !== null && $v !== '')
            ->map(fn ($v) => (int) $v)
            ->all();

        $import = new LichLamViecImport;
        Excel::import($import, Storage::disk('local')->path($filePath));
        $parsed = $this->parseSchedule($co_so, $import->data, $thang, $overrides);

        $assignments = $parsed['assignments'];
        if (empty($assignments)) {
            return back()->with('err', 'Chưa có ô nào khớp được bác sĩ/KTV để lưu.');
        }

        DB::transaction(function () use ($existing, $co_so, $thang, $request, $filePath, $assignments) {
            $lich = $existing ?: new LichLamViec(['co_so_id' => $co_so->id, 'thang' => $thang]);
            $lich->fill([
                'trang_thai'    => 'nhap',
                'nguoi_tao_id'  => auth()->id(),
                'file_goc'      => $filePath,
                'ghi_chu'       => $request->input('ghi_chu'),
                'ly_do_tu_choi' => null,
            ]);
            $lich->save();

            $lich->chiTiets()->delete();
            $lich->chiTiets()->createMany(array_map(fn ($a) => [
                'loai'         => $a['loai'],
                'doi_tuong_id' => $a['uid'],
                'phong_id'     => $a['phong_id'],
                'ngay'         => $a['ngay'],
                'ca'           => $a['ca'],
                'ten'          => $a['ten'],
            ], $assignments));
        });

        return redirect("/{$co_so->slug}/lich-lam-viec")
            ->with('ok', 'Đã lưu lịch làm việc tháng ' . date('m/Y', strtotime($thang)) . ' (' . count($assignments) . ' ca trực). Hãy kiểm tra rồi gửi duyệt.');
    }

    public function guiDuyet(CoSo $co_so, LichLamViec $lich_lam_viec)
    {
        $this->authorizePerm('quyen_lich_lam_viec');
        abort_unless($lich_lam_viec->co_so_id === $co_so->id, 404);

        if (! in_array($lich_lam_viec->trang_thai, ['nhap', 'tu_choi'], true)) {
            return back()->with('err', 'Chỉ gửi duyệt được bản nháp hoặc bản bị từ chối.');
        }

        $lich_lam_viec->update(['trang_thai' => 'cho_duyet', 'ly_do_tu_choi' => null]);

        return back()->with('ok', 'Đã gửi duyệt lịch làm việc.');
    }

    /** Duyệt → bản này thành lịch ĐANG HIỆU LỰC (form đặt lịch sẽ đọc theo). */
    public function duyet(CoSo $co_so, LichLamViec $lich_lam_viec)
    {
        $this->authorizePerm('duyet_lich_lam_viec');
        abort_unless($lich_lam_viec->co_so_id === $co_so->id, 404);

        if ($lich_lam_viec->trang_thai !== 'cho_duyet') {
            return back()->with('err', 'Chỉ duyệt được bản đang chờ duyệt.');
        }

        $lich_lam_viec->update([
            'trang_thai'     => 'da_duyet',
            'nguoi_duyet_id' => auth()->id(),
            'applied_at'     => now(),
            'ly_do_tu_choi'  => null,
        ]);

        return back()->with('ok', 'Đã duyệt & áp dụng lịch làm việc. Lịch trực bác sĩ/KTV nay có hiệu lực.');
    }

    public function tuChoi(CoSo $co_so, LichLamViec $lich_lam_viec, Request $request)
    {
        $this->authorizePerm('duyet_lich_lam_viec');
        abort_unless($lich_lam_viec->co_so_id === $co_so->id, 404);

        $request->validate(
            ['ly_do_tu_choi' => ['required', 'string', 'max:1000']],
            ['ly_do_tu_choi.required' => 'Vui lòng nhập lý do từ chối.']
        );

        if ($lich_lam_viec->trang_thai !== 'cho_duyet') {
            return back()->with('err', 'Chỉ từ chối được bản đang chờ duyệt.');
        }

        $lich_lam_viec->update([
            'trang_thai'     => 'tu_choi',
            'nguoi_duyet_id' => auth()->id(),
            'ly_do_tu_choi'  => $request->input('ly_do_tu_choi'),
        ]);

        return back()->with('ok', 'Đã từ chối lịch làm việc.');
    }

    public function destroy(CoSo $co_so, LichLamViec $lich_lam_viec)
    {
        $this->authorizePerm('quyen_lich_lam_viec');
        abort_unless($lich_lam_viec->co_so_id === $co_so->id, 404);

        if ($lich_lam_viec->trang_thai === 'da_duyet') {
            return back()->with('err', 'Không thể xóa bản đã duyệt & áp dụng.');
        }

        $lich_lam_viec->delete();

        return back()->with('ok', 'Đã xóa lịch làm việc.');
    }

    // ----- parse & helpers -----

    /**
     * Đọc 2 lưới (bac_si, ktv) → cấu trúc:
     *  - days: số ngày có cột
     *  - sheets[loai]: danh sách phòng [phong_id, ten, sang[day=>cell], chieu[day=>cell]]
     *    cell = ['raw'=>, 'uid'=>, 'name'=>] (uid null nếu chưa khớp)
     *  - unmatched: [usernameRaw => số lần] (chưa khớp được người)
     *  - assignments: các ca đã khớp → lưu chi tiết
     *
     * @param array<string,int> $overrides usernameRaw => user_id (khớp lại thủ công)
     */
    private function parseSchedule(CoSo $co_so, array $data, string $thang, array $overrides = []): array
    {
        $daysInMonth = (int) date('t', strtotime($thang));
        $ym = substr($thang, 0, 7);

        // Người hợp lệ trong cơ sở (gồm tư vấn toàn hệ thống)
        $users = User::where(fn ($q) => $q->where('co_so_id', $co_so->id)->orWhere('is_tu_van', true))
            ->get(['id', 'name', 'username']);
        $byUsername = [];
        $byName = [];
        $byId = [];
        foreach ($users as $u) {
            $byId[$u->id] = $u->name;
            if ($u->username) $byUsername[mb_strtolower($u->username)] = [$u->id, $u->name];
            $byName[mb_strtolower(trim($u->name))] = [$u->id, $u->name];
        }
        $phongs = Phong::where('co_so_id', $co_so->id)->pluck('ten', 'id');

        $matchUser = function (string $raw) use ($overrides, $byUsername, $byName, $byId) {
            $raw = trim($raw);
            if (isset($overrides[$raw]) && isset($byId[$overrides[$raw]])) {
                return [$overrides[$raw], $byId[$overrides[$raw]]];
            }
            $key = mb_strtolower($raw);
            if (isset($byUsername[$key])) return $byUsername[$key];
            if (isset($byName[$key])) return $byName[$key];
            return [null, null];
        };

        $sheets = ['bac_si' => [], 'ktv' => []];
        $unmatched = [];
        $assignments = [];
        $daysSet = [];

        foreach (['bac_si', 'ktv'] as $loai) {
            $rows = $data[$loai] ?? [];
            if (empty($rows)) continue;

            // Cột ngày: từ index 3 trở đi, header là số 1..31
            $header = $rows[0] ?? [];
            $dayCols = [];
            foreach ($header as $col => $val) {
                if ($col >= 3 && is_numeric($val)) {
                    $d = (int) $val;
                    if ($d >= 1 && $d <= $daysInMonth) {
                        $dayCols[$col] = $d;
                        $daysSet[$d] = true;
                    }
                }
            }

            $byPhong = [];
            foreach (array_slice($rows, 1) as $r) {
                $phongId = $this->parseInt($r[0] ?? null);
                $ca = $this->normalizeCa((string) ($r[2] ?? ''));
                if (! $phongId || ! $phongs->has($phongId) || ! $ca) {
                    continue;
                }
                if (! isset($byPhong[$phongId])) {
                    $byPhong[$phongId] = ['phong_id' => $phongId, 'ten' => $phongs[$phongId], 'sang' => [], 'chieu' => []];
                }
                foreach ($dayCols as $col => $dayNum) {
                    $raw = trim((string) ($r[$col] ?? ''));
                    if ($raw === '') continue;
                    [$uid, $name] = $matchUser($raw);
                    $byPhong[$phongId][$ca][$dayNum] = ['raw' => $raw, 'uid' => $uid, 'name' => $name];
                    if ($uid) {
                        $assignments[] = [
                            'loai' => $loai, 'phong_id' => $phongId,
                            'ngay' => sprintf('%s-%02d', $ym, $dayNum), 'ca' => $ca,
                            'uid' => $uid, 'ten' => $name,
                        ];
                    } else {
                        $unmatched[$raw] = ($unmatched[$raw] ?? 0) + 1;
                    }
                }
            }
            $sheets[$loai] = array_values($byPhong);
        }

        ksort($daysSet);

        return [
            'days'        => array_keys($daysSet),
            'sheets'      => $sheets,
            'unmatched'   => $unmatched,
            'assignments' => $assignments,
        ];
    }

    /** Dựng lưới hiển thị từ chi tiết đã lưu (cho trang show). */
    private function gridFromChiTiet(LichLamViec $lich): array
    {
        $sheets = ['bac_si' => [], 'ktv' => []];
        $daysSet = [];
        $byKey = ['bac_si' => [], 'ktv' => []];

        foreach ($lich->chiTiets as $ct) {
            if (! isset($sheets[$ct->loai])) continue;
            $d = (int) $ct->ngay?->format('j');
            $daysSet[$d] = true;
            $pid = $ct->phong_id;
            if (! isset($byKey[$ct->loai][$pid])) {
                $byKey[$ct->loai][$pid] = [
                    'phong_id' => $pid,
                    'ten' => $ct->phong?->ten ?? ('Phòng #' . $pid),
                    'sang' => [], 'chieu' => [],
                ];
            }
            $byKey[$ct->loai][$pid][$ct->ca][$d] = ['raw' => $ct->ten, 'uid' => $ct->doi_tuong_id, 'name' => $ct->ten];
        }

        ksort($daysSet);
        foreach ($byKey as $loai => $list) {
            $sheets[$loai] = array_values($list);
        }

        return ['days' => array_keys($daysSet), 'sheets' => $sheets];
    }

    /** Danh sách bác sĩ + KTV của cơ sở cho dropdown khớp lại. */
    private function dsNguoi(CoSo $co_so): array
    {
        $bsIds = VaiTro::whereIn('ma', ['bac_si', 'bac_si_tu_van'])->pluck('id');
        $ktvIds = VaiTro::where('ma', 'ktv')->pluck('id');

        $fmt = fn ($u) => $u->name . ($u->username ? " ({$u->username})" : '');

        return [
            'bac_si' => User::whereIn('vai_tro_id', $bsIds)
                ->where(fn ($q) => $q->where('co_so_id', $co_so->id)->orWhere('is_tu_van', true))
                ->orderBy('name')->get()->mapWithKeys(fn ($u) => [$u->id => $fmt($u)])->all(),
            'ktv' => User::whereIn('vai_tro_id', $ktvIds)
                ->where('co_so_id', $co_so->id)
                ->orderBy('name')->get()->mapWithKeys(fn ($u) => [$u->id => $fmt($u)])->all(),
        ];
    }

    private function normalizeCa(string $raw): ?string
    {
        $s = mb_strtolower(Str::ascii(trim($raw)));
        if (str_contains($s, 'sang')) return 'sang';
        if (str_contains($s, 'chieu')) return 'chieu';
        return null;
    }

    private function parseInt($value): ?int
    {
        if ($value === null || $value === '') return null;
        $n = (int) preg_replace('/[^0-9]/', '', (string) $value);

        return $n > 0 ? $n : null;
    }
}
