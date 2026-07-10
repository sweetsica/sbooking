<?php

namespace App\Http\Controllers;

use App\Models\BacSi;
use App\Models\Booking;
use App\Models\CoSo;
use App\Models\DichVu;
use App\Models\LichHen;
use App\Models\Menu;
use App\Models\PhanQuyen;
use App\Models\Phong;
use App\Models\PhongBan;
use App\Models\User;
use App\Models\VaiTro;
use App\Support\BookingFields;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SettingsController extends Controller
{
    // 8 mục thiết lập
    public const SECTIONS = [
        'phong'      => ['Phòng chức năng', 'meeting_room', 'Phòng khám: số slot tối đa, khung giờ phục vụ.'],
        'phong-ban'  => ['Phòng ban', 'corporate_fare', 'Bộ phận: Kinh doanh (Sales), Quản trị... — dùng cho phân quyền & gán người dùng.'],
        'vai-tro'    => ['Vai trò', 'badge', 'Vai trò: Nhân viên, KTV, Bác sĩ, Bác sĩ tư vấn, Lễ tân...'],
        'co-so'      => ['Cơ sở', 'store', 'Mỗi cơ sở (chi nhánh) có nhân sự, bác sĩ, phòng riêng.'],
        'quyen'      => ['Quyền', 'admin_panel_settings', 'Vai trò nào được Xem / Thêm / Sửa / Xóa booking.'],
        'nguoi-dung' => ['Người dùng', 'group', 'Thêm/sửa/xóa người dùng (bao gồm KTV, Bác sĩ, Lễ tân...).'],
        'dich-vu'    => ['Liệu pháp', 'spa', 'Liệu pháp / Dịch vụ — đưa vào form đặt lịch.'],
        'menu'       => ['Menu', 'restaurant_menu', 'CRUD tên Menu — đưa vào form tạo mới (dạng ô tick).'],
        'bao-cao'    => ['Báo cáo', 'analytics', 'Tổng hợp lịch đặt phòng + lịch tư vấn theo bộ lọc, xuất Excel.'],
    ];

    // Cấu hình các mục có CRUD
    private function editableConfig(?CoSo $co_so = null): array
    {
        $catalog = fn ($model, $fields) => ['model' => $model, 'kind' => 'catalog', 'fields' => $fields];
        // Phòng ban giờ RIÊNG từng cơ sở → dropdown chỉ lấy phòng ban của cơ sở đang xem.
        $phongBanOptions = PhongBan::when($co_so, fn ($q) => $q->where('co_so_id', $co_so->id))
            ->orderBy('ten')->pluck('ten', 'id')->all();
        // super-admin = is_admin + không thuộc cơ sở nào (toàn hệ thống).
        $superAdmin = ($u = auth()->user()) && $u->is_admin && is_null($u->co_so_id);
        // Vai trò "Quản trị hệ thống" (ma='admin') → tự bật is_admin = full quyền.
        // CHỈ super-admin mới được cấp vai trò này; người khác không thấy option
        // để không tự tạo ra super-admin mới.
        $vaiTroOptions = VaiTro::orderBy('ten')
            ->when(! $superAdmin, fn ($q) => $q->where('ma', '!=', 'admin'))
            ->pluck('ten', 'id')->all();
        $coSoOptions = CoSo::orderBy('id')->pluck('ten', 'id')->all();
        // "Toàn hệ thống" (co_so_id null) CHỈ super-admin mới được chọn — tránh admin
        // cơ sở tự nâng tài khoản thành toàn hệ thống.
        $coSoBlank = $superAdmin ? ['' => '— Toàn hệ thống —'] : [];

        return [
            'dich-vu' => $catalog(DichVu::class, [
                'ten'    => ['label' => 'Tên liệu pháp', 'type' => 'text', 'rules' => ['required', 'string', 'max:255']],
                'thoi_gian_phut' => ['label' => 'Thời gian (phút/khách)', 'type' => 'number', 'rules' => ['required', 'integer', 'min:1', 'max:240'], 'min' => 1, 'max' => 240, 'placeholder' => 'vd: 30'],
                'thuoc_nhom' => ['label' => 'Thuộc nhóm', 'type' => 'select', 'options' => ['khac' => 'Khác', 'tu_van' => 'Tư vấn', 'kham_ls' => 'Thăm khám lâm sàng'], 'rules' => ['required', 'in:tu_van,kham_ls,khac']],
                'la_dich_vu' => ['label' => 'Là dịch vụ (chỉ hiện ở đặt lịch dịch vụ)', 'type' => 'toggle', 'rules' => ['nullable', 'boolean'], 'hint' => 'Tắt = Thăm khám (hiện ở form đặt lịch phòng khám). Bật = Dịch vụ (vd Xông hơi, YHCT).'],
                'active' => ['label' => 'Kích hoạt', 'type' => 'toggle', 'rules' => ['nullable', 'boolean']],
            ]),
            'menu' => $catalog(Menu::class, [
                'ten'    => ['label' => 'Tên menu', 'type' => 'text', 'rules' => ['required', 'string', 'max:255']],
                'active' => ['label' => 'Kích hoạt', 'type' => 'toggle', 'rules' => ['nullable', 'boolean']],
            ]),
            'phong' => $catalog(Phong::class, [
                'ten'            => ['label' => 'Tên phòng', 'type' => 'text', 'rules' => ['required', 'string', 'max:255']],
                'kieu_phong'    => ['label' => 'Kiểu phòng', 'type' => 'select', 'options' => ['phong_kham' => 'Phòng khám', 'phong_dich_vu' => 'Phòng dịch vụ'], 'rules' => ['required', Rule::in(['phong_kham', 'phong_dich_vu'])]],
                'loai'          => ['label' => 'Loại', 'type' => 'select', 'options' => ['kham' => 'Khám', 'vip' => 'VIP', 'cong_dong' => 'Cộng đồng'], 'rules' => ['required', 'string', 'max:30']],
                'so_slot_toi_da' => ['label' => 'Số slot tối đa', 'type' => 'number', 'rules' => ['required', 'integer', 'min:1', 'max:99']],
                'phut_moi_khach' => ['label' => 'Phút/khách (phòng dịch vụ)', 'type' => 'number', 'rules' => ['nullable', 'integer', 'min:1', 'max:240'], 'min' => 1, 'max' => 240, 'placeholder' => 'vd: 30', 'hint' => 'Chỉ dùng cho phòng dịch vụ'],
                'ktv_mac_dinh_id' => ['label' => 'KTV mặc định', 'type' => 'select', 'options' => ['' => '— Không —'] + \App\Models\User::whereHas('vaiTro', fn ($q) => $q->where('ma', 'ktv'))->orderBy('name')->pluck('name', 'id')->all(), 'rules' => ['nullable', Rule::exists('users', 'id')], 'hint' => 'Auto chọn khi khách đặt phòng dịch vụ'],
                'trang_thai'    => ['label' => 'Trạng thái', 'type' => 'select', 'options' => ['hoat_dong' => 'Hoạt động', 'bao_tri' => 'Bảo trì'], 'rules' => ['required', Rule::in(['hoat_dong', 'bao_tri'])]],
                'gio_mo'        => ['label' => 'Giờ mở cửa', 'type' => 'hour', 'rules' => ['required', 'regex:/^\d{2}:00$/'], 'virtual' => true],
                'gio_dong'      => ['label' => 'Giờ đóng cửa', 'type' => 'hour', 'rules' => ['required', 'regex:/^\d{2}:00$/'], 'virtual' => true],
                'bac_si_ids'    => ['label' => 'Bác sĩ của phòng', 'type' => 'multiselect', 'virtual' => true,
                    'options' => BacSi::where('active', true)
                        ->where(fn ($q) => $q->where('co_so_id', $co_so?->id)->orWhere('xuat_hien_moi_co_so', true))
                        ->orderBy('ten')->get()->mapWithKeys(fn ($b) => [$b->id => $b->ten_day_du])->all(),
                    'rules' => ['nullable', 'array'], 'hint' => 'Bác sĩ được chọn sẽ hiện ở form đặt lịch phòng khám của phòng này'],
            ]),
            'vai-tro' => $catalog(VaiTro::class, [
                'ten' => ['label' => 'Tên vai trò', 'type' => 'text', 'rules' => ['required', 'string', 'max:255']],
                'ma'  => ['label' => 'Mã', 'type' => 'text', 'rules' => ['required', 'string', 'max:50']],
            ]),
            'nguoi-dung' => [
                'model' => User::class, 'kind' => 'user',
                'fields' => [
                    // 3 trường bắt buộc gom liền nhau cho dễ nhập.
                    'name'           => ['label' => 'Họ tên', 'type' => 'text', 'rules' => ['required', 'string', 'max:255'], 'required' => true],
                    'username'       => ['label' => 'Tài khoản', 'type' => 'text', 'rules' => [], 'required' => true, 'placeholder' => 'vd: tttg'],
                    'password'       => ['label' => 'Mật khẩu', 'type' => 'password', 'rules' => [], 'required' => true, 'virtual' => true, 'placeholder' => 'Tối thiểu 6 ký tự', 'hint' => 'Để trống nếu giữ nguyên (khi sửa)'],
                    'chuc_danh'      => ['label' => 'Chức danh', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:50'], 'placeholder' => 'BS. / KTV. / PGS.TS.BS.'],
                    'email'          => ['label' => 'Email', 'type' => 'text', 'rules' => [], 'placeholder' => 'Không bắt buộc'],
                    'phong_ban_id'   => ['label' => 'Phòng ban', 'type' => 'select', 'options' => ['' => '— Không —'] + $phongBanOptions, 'rules' => ['nullable', Rule::exists('phong_ban', 'id')]],
                    'vai_tro_id'     => ['label' => 'Vai trò', 'type' => 'select', 'options' => ['' => '— Không —'] + $vaiTroOptions, 'rules' => ['nullable', Rule::exists('vai_tro', 'id')]],
                    // Cơ sở của tài khoản: để "Toàn hệ thống" (trống) CHỈ dành cho super-admin,
                    // các tài khoản còn lại gán đúng cơ sở → chỉ hiện ở cơ sở đó.
                    'co_so_id'       => ['label' => 'Cơ sở', 'type' => 'select', 'options' => $coSoBlank + $coSoOptions, 'rules' => ['nullable', Rule::exists('co_so', 'id')]],
                    // Các trường lịch tư vấn/khám (nhan_tu_van, phut_tu_van, nhan_kham_ls,
                    // phut_kham_ls, gio_bat_dau/ket_thuc, is_tu_van) đã chuyển sang DANH MỤC
                    // Bác sĩ (bảng bac_si) → ẩn khỏi form người dùng cho đỡ nhầm.
                ],
            ],
            'co-so' => [
                'model' => CoSo::class, 'kind' => 'coso',
                'fields' => [
                    'ten'     => ['label' => 'Tên cơ sở', 'type' => 'text', 'rules' => ['required', 'string', 'max:255']],
                    'slug'    => ['label' => 'Slug (đường dẫn)', 'type' => 'text', 'rules' => [], 'placeholder' => 'vd: 59ntn'],
                    'dia_chi' => ['label' => 'Địa chỉ', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:255']],
                    'active'  => ['label' => 'Hoạt động', 'type' => 'toggle', 'rules' => ['nullable', 'boolean']],
                ],
            ],
            'phong-ban' => [
                'model' => PhongBan::class, 'kind' => 'phongban',
                'fields' => [
                    'ten' => ['label' => 'Tên phòng ban', 'type' => 'text', 'rules' => ['required', 'string', 'max:255'], 'placeholder' => 'vd: Kinh doanh (Sales)'],
                    'ma'  => ['label' => 'Mã', 'type' => 'text', 'rules' => [], 'placeholder' => 'vd: sales'],
                ],
            ],
        ];
    }

    private function resolveSection(string $section): string
    {
        return $section;
    }

    /**
     * Các mục Thiết lập mà user hiện tại được ĐỌC.
     * - Admin: toàn bộ.
     * - Không phải admin: chỉ những mục gắn với quyền được cấp (hiện: Báo cáo ↔ xem_bao_cao).
     *
     * @return array<int,string> danh sách key của SECTIONS
     */
    private function allowedSections(): array
    {
        $user = auth()->user();
        if ($user && $user->is_admin) {
            return array_keys(self::SECTIONS);
        }

        // Mục Thiết lập ↔ quyền cần có (dành cho non-admin).
        $sectionPerm = ['bao-cao' => 'xem_bao_cao'];

        $allowed = [];
        foreach ($sectionPerm as $sectionKey => $perm) {
            if ($this->userHasPerm($perm)) {
                $allowed[] = $sectionKey;
            }
        }

        return $allowed;
    }

    /** Non-admin có quyền $truong (theo vai trò / phòng ban) hay không. */
    private function userHasPerm(string $truong): bool
    {
        $user = auth()->user();
        if (! $user || (! $user->vai_tro_id && ! $user->phong_ban_id)) {
            return false;
        }

        return PhanQuyen::where(function ($q) use ($user) {
            if ($user->phong_ban_id) $q->orWhere('phong_ban_id', $user->phong_ban_id);
            if ($user->vai_tro_id) $q->orWhere('vai_tro_id', $user->vai_tro_id);
        })->where('truong', $truong)->exists();
    }

    public function index(CoSo $co_so)
    {
        $allowed = $this->allowedSections();
        abort_if(empty($allowed), 403, 'Bạn không có quyền truy cập Thiết lập.');

        $sections = array_intersect_key(self::SECTIONS, array_flip($allowed));

        return view('longevity.settings.index', ['coSo' => $co_so, 'sections' => $sections]);
    }

    public function section(CoSo $co_so, string $section, Request $request)
    {
        abort_unless(isset(self::SECTIONS[$section]), 404);
        abort_unless(in_array($section, $this->allowedSections(), true), 403, 'Bạn không có quyền truy cập mục này.');
        $config = $this->editableConfig($co_so)[$this->resolveSection($section)] ?? null;

        $rows = match ($section) {
            'phong'      => $co_so->phongs()->with(['khungGios', 'bacSis'])->get(),
            'dich-vu'    => \App\Models\DichVu::where('co_so_id', $co_so->id)->orderBy('ten')->get(),
            'menu'       => \App\Models\Menu::where('co_so_id', $co_so->id)->orderBy('ten')->get(),
            'nguoi-dung' => User::with(['phongBan', 'vaiTro'])
                ->where(fn ($q) => $q->where('co_so_id', $co_so->id)->orWhereNull('co_so_id'))
                ->when($request->filled('q'), fn ($q) => $q->where('name', 'like', '%'.$request->query('q').'%'))
                ->when($request->filled('vai_tro_id'), fn ($q) => $q->where('vai_tro_id', $request->query('vai_tro_id')))
                ->when($request->filled('chuc_danh'), fn ($q) => $q->where('chuc_danh', $request->query('chuc_danh')))
                ->when($request->filled('phong_ban_id'), fn ($q) => $q->where('phong_ban_id', $request->query('phong_ban_id')))
                // User thuộc cơ sở hiện tại trước, tài khoản hệ thống (co_so_id null) xuống nhóm cuối.
                ->orderByRaw('co_so_id IS NULL')->orderBy('name')->get(),
            'co-so'      => CoSo::orderBy('id')->get(),
            'phong-ban'  => PhongBan::where('co_so_id', $co_so->id)->orderBy('id')->get(),
            'vai-tro'    => VaiTro::orderBy('id')->get(),
            default      => collect(),
        };

        // Dữ liệu báo cáo (filter + counter)
        $baoCao = null;
        if ($section === 'bao-cao') {
            $baoCao = $this->buildBaoCao($co_so, $request);
        }

        // Dữ liệu cho form lọc người dùng
        $userFilters = null;
        if ($section === 'nguoi-dung') {
            $userFilters = [
                'vaiTros' => VaiTro::orderBy('id')->get(),
                'chucDanhs' => User::whereNotNull('chuc_danh')->where('chuc_danh', '!=', '')
                    ->distinct()->orderBy('chuc_danh')->pluck('chuc_danh'),
                'phongBans' => PhongBan::where('co_so_id', $co_so->id)->orderBy('ten')->get(),
                'current' => $request->only(['q', 'vai_tro_id', 'chuc_danh', 'phong_ban_id']),
            ];
        }

        // Dữ liệu cho ma trận phân quyền sửa trường (theo Vai trò)
        $quyen = null;
        if ($section === 'quyen') {
            $allowed = PhanQuyen::whereNotNull('vai_tro_id')
                ->get()
                ->groupBy('vai_tro_id')
                ->map(fn ($g) => $g->pluck('truong')->all());
            $quyen = [
                'vaiTros' => VaiTro::orderBy('id')->get(),
                'fields' => BookingFields::all(),
                'groups' => BookingFields::groups(),
                'allowed' => $allowed,   // [vai_tro_id => [truong,...]]
            ];
        }

        return view('longevity.settings.section', [
            'coSo' => $co_so,
            'sections' => self::SECTIONS,
            'key' => $section,
            'meta' => self::SECTIONS[$section],
            'rows' => $rows,
            'config' => $config,
            'quyen' => $quyen,
            'userFilters' => $userFilters,
            'baoCao' => $baoCao,
        ]);
    }

    /**
     * Build dữ liệu báo cáo theo filter.
     * Trả về: ['bookings' => Collection, 'lichHens' => Collection, 'counter' => array, 'filters' => array, 'options' => array]
     */
    public function buildBaoCao(CoSo $co_so, Request $request): array
    {
        $loai = $request->query('loai', 'all'); // all | booking | tu_van
        $tu = $request->query('tu');             // ngày từ
        $den = $request->query('den');           // ngày đến
        $bacSiId = $request->query('bac_si_id');
        $saleId = $request->query('sale_id');
        $ktvId = $request->query('ktv_id');

        // ----- BOOKING query -----
        $bookings = collect();
        if ($loai !== 'tu_van') {
            $bq = Booking::where('co_so_id', $co_so->id)
                ->with(['khachHang', 'phong', 'khungGio', 'dichVu', 'bacSi', 'ktv', 'sale'])
                ->when($tu, fn ($q) => $q->whereDate('ngay_dat', '>=', $tu))
                ->when($den, fn ($q) => $q->whereDate('ngay_dat', '<=', $den))
                ->when($bacSiId, fn ($q) => $q->where('bac_si_id', $bacSiId))
                ->when($saleId, fn ($q) => $q->where('sale_id', $saleId))
                ->when($ktvId, fn ($q) => $q->where('ktv_user_id', $ktvId))
                ->orderByDesc('ngay_dat')->orderBy('id');
            $bookings = $bq->get();
        }

        // ----- LICH HEN query -----
        $lichHens = collect();
        if ($loai !== 'booking') {
            // Lưu ý: lich_hen không có ktv_user_id → nếu lọc theo KTV thì lich_hen không match → trả về rỗng.
            // Bộ lọc bác sĩ (danh mục) chỉ áp cho booking; lịch tư vấn dùng bác sĩ = tài khoản user (entity khác).
            $lq = LichHen::where('co_so_id', $co_so->id)
                ->with(['khachHang', 'bacSiTuVan', 'caKham', 'sale'])
                ->when($tu, fn ($q) => $q->whereDate('ngay_hen', '>=', $tu))
                ->when($den, fn ($q) => $q->whereDate('ngay_hen', '<=', $den))
                ->when($saleId, fn ($q) => $q->where('sale_id', $saleId))
                ->orderByDesc('ngay_hen')->orderBy('id');
            $lichHens = $ktvId ? collect() : $lq->get();
        }

        // ----- Counter -----
        $countByStatus = fn ($coll, $field = 'trang_thai') => [
            'total'    => $coll->count(),
            'da_duyet' => $coll->where($field, 'da_duyet')->count(),
            'cho_duyet'=> $coll->where($field, 'cho_duyet')->count(),
            'tu_choi'  => $coll->where($field, 'tu_choi')->count(),
            'da_xong'  => $coll->where($field, 'da_xong')->count(),
        ];

        $counter = [
            'booking' => $countByStatus($bookings),
            'tu_van'  => $countByStatus($lichHens),
        ];
        // Thống kê trạng thái khách (chỉ áp dụng cho booking đặt phòng).
        $counter['booking']['dung_gio'] = $bookings->where('trang_thai_khach', 'da_toi')->count();
        $counter['booking']['tre']      = $bookings->where('trang_thai_khach', 'toi_tre')->count();
        $counter['booking']['huy']      = $bookings->where('trang_thai_khach', 'huy')->count();
        $counter['tong'] = [
            'total'    => $counter['booking']['total'] + $counter['tu_van']['total'],
            'da_duyet' => $counter['booking']['da_duyet'] + $counter['tu_van']['da_duyet'],
            'cho_duyet'=> $counter['booking']['cho_duyet'] + $counter['tu_van']['cho_duyet'],
            'tu_choi'  => $counter['booking']['tu_choi'] + $counter['tu_van']['tu_choi'],
            'da_xong'  => $counter['booking']['da_xong'],
        ];

        // ----- Options cho dropdown filter -----
        // Bác sĩ = DANH MỤC bac_si (lọc booking theo bác sĩ đã gán vào phòng).
        $bacSis = BacSi::where('active', true)
            ->where(fn ($q) => $q->where('co_so_id', $co_so->id)->orWhere('xuat_hien_moi_co_so', true))
            ->orderBy('ten')->get(['id', 'ten', 'chuc_danh']);

        $vrKtv = VaiTro::where('ma', 'ktv')->first();
        $ktvs = $vrKtv ? User::where('vai_tro_id', $vrKtv->id)->where('co_so_id', $co_so->id)
            ->orderBy('name')->get(['id', 'name']) : collect();

        $sales = $co_so->nguoiDungs()->orderBy('name')->get(['users.id', 'users.name']);

        return [
            'bookings' => $bookings,
            'lichHens' => $lichHens,
            'counter'  => $counter,
            'filters'  => compact('loai', 'tu', 'den', 'bacSiId', 'saleId', 'ktvId'),
            'options'  => compact('bacSis', 'ktvs', 'sales'),
        ];
    }

    public function store(CoSo $co_so, Request $request, string $section)
    {
        if ($section === 'quyen') {
            return $this->saveQuyen($request);
        }

        [$model, $config] = $this->mustEditable($section, $co_so);

        return match ($config['kind']) {
            'user' => $this->saveUser($co_so, $request, null),
            'coso' => $this->saveCoSo($request, null),
            'phongban' => $this->savePhongBan($co_so, $request, null),
            default => $this->saveCatalog($co_so, $request, $config, $model, $section, null),
        };
    }

    public function update(CoSo $co_so, Request $request, string $section, int $id)
    {
        [$model, $config] = $this->mustEditable($section, $co_so);

        return match ($config['kind']) {
            'user' => $this->saveUser($co_so, $request, $this->findManageableUser($co_so, $id)),
            'coso' => $this->saveCoSo($request, CoSo::findOrFail($id)),
            'phongban' => $this->savePhongBan($co_so, $request, PhongBan::where('co_so_id', $co_so->id)->findOrFail($id)),
            default => $this->saveCatalog($co_so, $request, $config, $model, $section,
                $this->findCatalogRecord($model, $co_so, $id)
            ),
        };
    }

    // Người dùng thao tác được trong 1 cơ sở = user của cơ sở đó HOẶC quản trị toàn hệ thống (co_so_id NULL).
    private function findManageableUser(CoSo $co_so, int $id): User
    {
        return User::where('id', $id)
            ->where(fn ($q) => $q->where('co_so_id', $co_so->id)->orWhereNull('co_so_id'))
            ->firstOrFail();
    }

    // Bản ghi catalog (Liệu pháp, Menu, Phòng...) chỉ thuộc cơ sở đang xem.
    // Khớp đúng phạm vi với section() — không cho sửa/xóa bản ghi của cơ sở khác.
    private function findCatalogRecord(string $model, CoSo $co_so, int $id)
    {
        if (in_array('co_so_id', (new $model)->getFillable())) {
            return $model::where('co_so_id', $co_so->id)->findOrFail($id);
        }

        return $model::findOrFail($id);
    }

    public function destroy(CoSo $co_so, string $section, int $id)
    {
        [$model, $config] = $this->mustEditable($section, $co_so);

        if ($config['kind'] === 'coso') {
            $cs = CoSo::findOrFail($id);
            if ($cs->id === $co_so->id) {
                return back()->with('err', 'Không thể xóa cơ sở đang xem.');
            }
            $cs->delete();
        } elseif ($config['kind'] === 'phongban') {
            $pb = PhongBan::withCount('nguoiDungs')->where('co_so_id', $co_so->id)->findOrFail($id);
            if ($pb->nguoi_dungs_count > 0) {
                return back()->with('err', 'Không thể xóa: phòng ban đang có người dùng. Hãy chuyển họ sang phòng ban khác trước.');
            }
            $pb->delete(); // phân quyền của phòng ban này tự xóa theo (cascade)
        } elseif ($config['kind'] === 'user') {
            $this->findManageableUser($co_so, $id)->delete();
        } else {
            $record = $this->findCatalogRecord($model, $co_so, $id);
            $record->delete();
        }

        return back()->with('ok', 'Đã xóa.');
    }

    // ----- handlers -----

    private function saveCatalog(CoSo $co_so, Request $request, array $config, string $model, string $section, $record)
    {
        $data = $request->validate($this->rulesFrom($config));
        $attrs = $this->modelAttrs($data, $config);

        if ($record) {
            $record->update($attrs);
        } else {
            if (in_array('co_so_id', (new $model)->getFillable())) {
                $attrs['co_so_id'] = $co_so->id;
            }
            $record = $model::create($attrs);
        }

        if ($section === 'phong') {
            $this->regenSlots($record, $data['gio_mo'], $data['gio_dong']);
            // Gán bác sĩ (danh mục) vào phòng — hiện ở form đặt lịch phòng khám.
            $record->bacSis()->sync($data['bac_si_ids'] ?? []);
        }

        return back()->with('ok', $record->wasRecentlyCreated ? 'Đã thêm mới.' : 'Đã cập nhật.');
    }

    private function saveUser(CoSo $co_so, Request $request, ?User $user)
    {
        $data = $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'chuc_danh'      => ['nullable', 'string', 'max:50'],
            'username'       => ['required', 'string', 'max:50', 'regex:/^[a-z0-9._-]+$/', Rule::unique('users', 'username')->ignore($user?->id)],
            'email'          => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->id)],
            'phong_ban_id'   => ['nullable', Rule::exists('phong_ban', 'id')],
            'vai_tro_id'     => ['nullable', Rule::exists('vai_tro', 'id')],
            'co_so_id'       => ['nullable', Rule::exists('co_so', 'id')],
            'is_admin'       => ['nullable', 'boolean'],
            'password'       => [$user ? 'nullable' : 'required', 'string', 'min:6'],
        ], [
            'username.required' => 'Vui lòng nhập tài khoản đăng nhập.',
            'username.regex'    => 'Tài khoản chỉ gồm chữ thường, số, dấu chấm, gạch dưới hoặc gạch ngang.',
            'username.unique'   => 'Tài khoản này đã có người dùng. Vui lòng chọn tài khoản khác.',
            'name.required'     => 'Vui lòng nhập họ tên.',
            'email.email'       => 'Email không hợp lệ.',
            'email.unique'      => 'Email này đã được dùng cho tài khoản khác.',
            'password.required' => 'Vui lòng nhập mật khẩu cho người dùng mới (tối thiểu 6 ký tự).',
            'password.min'      => 'Mật khẩu phải có ít nhất 6 ký tự.',
        ]);

        // Vai trò 'admin' → tự động bật is_admin (tránh trường hợp quên tick toggle)
        $vaiTroId = ($data['vai_tro_id'] ?? null) ?: null;
        $isAdminByRole = $vaiTroId && VaiTro::where('id', $vaiTroId)->where('ma', 'admin')->exists();
        $isAdmin = $request->boolean('is_admin') || $isAdminByRole;

        // Chốt chặn: CHỈ super-admin (is_admin + toàn hệ thống) mới được đụng tới quyền
        // Quản trị hệ thống. Người khác:
        //  - Tạo mới / nâng lên admin  → chặn (tránh tự tạo super-admin).
        //  - Sửa tài khoản admin sẵn có → giữ nguyên is_admin, không cho hạ nhầm.
        $actor = $request->user();
        $actorSuperAdmin = $actor && $actor->is_admin && is_null($actor->co_so_id);
        if (! $actorSuperAdmin) {
            if ($isAdmin && ! ($user && $user->is_admin)) {
                throw ValidationException::withMessages([
                    'vai_tro_id' => 'Chỉ Quản trị hệ thống (toàn hệ thống) mới được cấp quyền Quản trị hệ thống cho người dùng.',
                ]);
            }
            // Không phải super-admin → không đổi được trạng thái quản trị: giữ nguyên giá trị cũ.
            $isAdmin = (bool) ($user?->is_admin ?? false);
        }

        $attrs = [
            'name'           => $data['name'],
            'chuc_danh'      => ($data['chuc_danh'] ?? null) ?: null,
            'username'       => ($data['username'] ?? null) ?: null,
            'email'          => ($data['email'] ?? null) ?: null,
            'phong_ban_id'   => ($data['phong_ban_id'] ?? null) ?: null,
            'vai_tro_id'     => $vaiTroId,
            'is_admin'       => $isAdmin,
            // Cơ sở lấy theo ô chọn; is_admin KHÔNG còn tự ép về toàn hệ thống nữa.
            'co_so_id'       => $this->resolveCoSoId($request, $co_so, $user, $data['co_so_id'] ?? null),
        ];
        if (! empty($data['password'])) {
            $attrs['password'] = Hash::make($data['password']);
        }

        $user ? $user->update($attrs) : User::create($attrs);

        return back()->with('ok', $user ? 'Đã cập nhật người dùng.' : 'Đã thêm người dùng.');
    }

    /**
     * Quyết định co_so_id cho user, có chốt chặn quyền:
     * - "Toàn hệ thống" (null) CHỈ super-admin (is_admin + co_so_id null) mới được đặt.
     * - Admin cơ sở / người khác: không thể tạo tài khoản toàn hệ thống, cũng không thể
     *   đổi cơ sở của một tài khoản toàn hệ thống sẵn có (giữ nguyên).
     */
    private function resolveCoSoId(Request $request, CoSo $co_so, ?User $user, $submitted): ?int
    {
        $submitted = ($submitted ?? null) ?: null; // '' -> null (toàn hệ thống)
        $actor = $request->user();
        $superAdmin = $actor && $actor->is_admin && is_null($actor->co_so_id);

        if ($superAdmin) {
            return $submitted; // super-admin toàn quyền, kể cả đặt null
        }

        // Không phải super-admin:
        if ($user && is_null($user->co_so_id)) {
            return null; // tài khoản toàn hệ thống sẵn có -> giữ nguyên, không cho cướp
        }

        // Không được để null; ép về cơ sở đã chọn hoặc cơ sở hiện tại.
        return $submitted ?: ($user?->co_so_id ?? $co_so->id);
    }

    private function saveCoSo(Request $request, ?CoSo $cs)
    {
        $data = $request->validate([
            'ten'     => ['required', 'string', 'max:255'],
            'slug'    => ['required', 'string', 'max:60', 'regex:/^[a-z0-9-]+$/', Rule::unique('co_so', 'slug')->ignore($cs?->id)],
            'dia_chi' => ['nullable', 'string', 'max:255'],
            'active'  => ['nullable', 'boolean'],
        ], [
            'slug.regex' => 'Slug chỉ gồm chữ thường, số và dấu gạch ngang.',
        ]);
        $data['active'] = $request->boolean('active');

        $cs ? $cs->update($data) : CoSo::create($data);

        return back()->with('ok', $cs ? 'Đã cập nhật cơ sở.' : 'Đã thêm cơ sở.');
    }

    // Lưu ma trận phân quyền (vai trò × trường)
    private function saveQuyen(Request $request)
    {
        $validKeys = BookingFields::keys();
        $allow = (array) $request->input('allow', []); // [vai_tro_id => [truong,...]]

        DB::transaction(function () use ($allow, $validKeys) {
            foreach (VaiTro::pluck('id') as $vtId) {
                $truongs = array_values(array_intersect((array) ($allow[$vtId] ?? []), $validKeys));
                PhanQuyen::where('vai_tro_id', $vtId)->delete();
                foreach ($truongs as $t) {
                    PhanQuyen::create(['vai_tro_id' => $vtId, 'truong' => $t]);
                }
            }
        });

        return back()->with('ok', 'Đã lưu phân quyền theo vai trò.');
    }

    private function savePhongBan(CoSo $co_so, Request $request, ?PhongBan $pb)
    {
        $data = $request->validate([
            'ten' => ['required', 'string', 'max:255'],
            // Mã duy nhất TRONG cơ sở (mỗi cơ sở có bộ phòng ban riêng).
            'ma'  => ['required', 'string', 'max:50', 'regex:/^[a-z0-9_-]+$/',
                Rule::unique('phong_ban', 'ma')->where('co_so_id', $co_so->id)->ignore($pb?->id)],
        ], [
            'ma.regex' => 'Mã chỉ gồm chữ thường, số, gạch dưới hoặc gạch ngang.',
            'ma.unique' => 'Mã phòng ban đã tồn tại trong cơ sở này.',
        ]);

        if ($pb) {
            $pb->update($data);
        } else {
            PhongBan::create($data + ['co_so_id' => $co_so->id]);
        }

        return back()->with('ok', $pb ? 'Đã cập nhật phòng ban.' : 'Đã thêm phòng ban.');
    }

    // ----- helpers -----

    private function mustEditable(string $section, ?CoSo $co_so = null): array
    {
        abort_unless(isset(self::SECTIONS[$section]), 404);
        $config = $this->editableConfig($co_so)[$this->resolveSection($section)] ?? null;
        abort_unless($config, 403, 'Mục này chưa hỗ trợ chỉnh sửa.');

        return [$config['model'], $config];
    }

    private function rulesFrom(array $config): array
    {
        $rules = [];
        foreach ($config['fields'] as $name => $f) {
            $rules[$name] = $f['rules'];
        }

        return $rules;
    }

    private function modelAttrs(array $data, array $config): array
    {
        $attrs = [];
        foreach ($config['fields'] as $name => $f) {
            if (! empty($f['virtual'])) {
                continue;
            }
            $attrs[$name] = ($f['type'] ?? null) === 'toggle' ? (bool) ($data[$name] ?? false) : ($data[$name] ?? null);
        }

        return $attrs;
    }

    private function regenSlots(Phong $phong, string $open, string $close): void
    {
        $phong->khungGios()->delete();
        $h = (int) substr($open, 0, 2);
        $end = (int) substr($close, 0, 2);
        $i = 0;
        for ($cur = $h; $cur < $end; $cur++, $i++) {
            $phong->khungGios()->create([
                'gio_bat_dau' => sprintf('%02d:00:00', $cur),
                'gio_ket_thuc' => sprintf('%02d:00:00', $cur + 1),
                'thu_tu' => $i,
            ]);
        }
    }
}
