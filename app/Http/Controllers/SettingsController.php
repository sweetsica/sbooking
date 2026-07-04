<?php

namespace App\Http\Controllers;

use App\Models\BacSi;
use App\Models\Booking;
use App\Models\CoSo;
use App\Models\DichVu;
use App\Models\Ktv;
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

class SettingsController extends Controller
{
    public const SECTIONS = [
        'phong'      => ['Phòng chức năng', 'meeting_room', 'Phòng khám: số slot tối đa, khung giờ phục vụ.'],
        'bac-si'     => ['Bác sĩ', 'stethoscope', 'Quản lý danh sách bác sĩ — chức danh, giờ làm, thời gian khám.'],
        'ktv'        => ['Kỹ thuật viên', 'spa', 'Quản lý danh sách KTV — giờ làm việc theo cơ sở.'],
        'phong-ban'  => ['Phòng ban', 'corporate_fare', 'Bộ phận: Kinh doanh (Sales), Quản trị... — dùng cho phân quyền & gán người dùng.'],
        'vai-tro'    => ['Vai trò', 'badge', 'Vai trò: Nhân viên, KTV, Bác sĩ, Bác sĩ tư vấn, Lễ tân...'],
        'co-so'      => ['Cơ sở', 'store', 'Mỗi cơ sở (chi nhánh) có nhân sự, bác sĩ, phòng riêng.'],
        'quyen'      => ['Quyền', 'admin_panel_settings', 'Vai trò nào được Xem / Thêm / Sửa / Xóa booking.'],
        'nguoi-dung' => ['Người dùng', 'group', 'Thêm/sửa/xóa người dùng (bao gồm KTV, Bác sĩ, Lễ tân...).'],
        'dich-vu'    => ['Liệu pháp', 'healing', 'Liệu pháp / Dịch vụ — đưa vào form đặt lịch.'],
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
        $vaiTroOptions = VaiTro::orderBy('ten')->pluck('ten', 'id')->all();

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
                'ktv_mac_dinh_id' => ['label' => 'KTV mặc định', 'type' => 'select', 'options' => ['' => '— Không —'] + Ktv::where('co_so_id', $co_so->id)->where('active', true)->orderBy('ten')->pluck('ten', 'id')->all(), 'rules' => ['nullable', Rule::exists('ktv', 'id')], 'hint' => 'Auto chọn khi khách đặt phòng dịch vụ'],
                'trang_thai'    => ['label' => 'Trạng thái', 'type' => 'select', 'options' => ['hoat_dong' => 'Hoạt động', 'bao_tri' => 'Bảo trì'], 'rules' => ['required', Rule::in(['hoat_dong', 'bao_tri'])]],
                'gio_mo'        => ['label' => 'Giờ mở cửa', 'type' => 'hour', 'rules' => ['required', 'regex:/^\d{2}:00$/'], 'virtual' => true],
                'gio_dong'      => ['label' => 'Giờ đóng cửa', 'type' => 'hour', 'rules' => ['required', 'regex:/^\d{2}:00$/'], 'virtual' => true],
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
                    // Ẩn toggle "Quản trị (mọi cơ sở)": muốn cấp quyền admin thì chọn vai trò "Quản trị hệ thống" (tự bật is_admin).
                    'is_tu_van'      => ['label' => 'Tư vấn (xuất hiện mọi cơ sở)', 'type' => 'toggle', 'rules' => ['nullable', 'boolean']],
                    'nhan_tu_van'    => ['label' => 'Tư vấn / Đọc kết quả', 'type' => 'toggle', 'rules' => ['nullable', 'boolean']],
                    'phut_tu_van'    => ['label' => 'Số phút thực hiện (tư vấn)', 'type' => 'number', 'rules' => ['nullable', 'integer', 'min:1', 'max:240'], 'min' => 1, 'max' => 240, 'placeholder' => 'vd: 30'],
                    'nhan_kham_ls'   => ['label' => 'Thăm khám lâm sàng', 'type' => 'toggle', 'rules' => ['nullable', 'boolean']],
                    'phut_kham_ls'   => ['label' => 'Số phút thực hiện (khám LS)', 'type' => 'number', 'rules' => ['nullable', 'integer', 'min:1', 'max:240'], 'min' => 1, 'max' => 240, 'placeholder' => 'vd: 5'],
                    'gio_bat_dau'    => ['label' => 'Giờ bắt đầu', 'type' => 'hour', 'rules' => ['nullable', 'string', 'max:5'], 'virtual' => true],
                    'gio_ket_thuc'   => ['label' => 'Giờ kết thúc', 'type' => 'hour', 'rules' => ['nullable', 'string', 'max:5'], 'virtual' => true],
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
            'bac-si' => $catalog(BacSi::class, [
                'ten'            => ['label' => 'Họ tên', 'type' => 'text', 'rules' => ['required', 'string', 'max:255'], 'required' => true],
                'chuc_danh'      => ['label' => 'Chức danh', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:50'], 'placeholder' => 'BS. / PGS.TS.BS.'],
                'xuat_hien_moi_co_so' => ['label' => 'Xuất hiện mọi cơ sở', 'type' => 'toggle', 'rules' => ['nullable', 'boolean']],
                'nhan_tu_van'    => ['label' => 'Nhận tư vấn', 'type' => 'toggle', 'rules' => ['nullable', 'boolean']],
                'phut_tu_van'    => ['label' => 'Phút tư vấn', 'type' => 'number', 'rules' => ['nullable', 'integer', 'min:1', 'max:240'], 'min' => 1, 'max' => 240, 'placeholder' => '30'],
                'nhan_kham_ls'   => ['label' => 'Nhận khám lâm sàng', 'type' => 'toggle', 'rules' => ['nullable', 'boolean']],
                'phut_kham_ls'   => ['label' => 'Phút khám LS', 'type' => 'number', 'rules' => ['nullable', 'integer', 'min:1', 'max:240'], 'min' => 1, 'max' => 240, 'placeholder' => '5'],
                'gio_bat_dau'    => ['label' => 'Giờ bắt đầu', 'type' => 'hour', 'rules' => ['nullable', 'string', 'max:5']],
                'gio_ket_thuc'   => ['label' => 'Giờ kết thúc', 'type' => 'hour', 'rules' => ['nullable', 'string', 'max:5']],
                'active'         => ['label' => 'Hoạt động', 'type' => 'toggle', 'rules' => ['nullable', 'boolean']],
            ]),
            'ktv' => $catalog(Ktv::class, [
                'ten'            => ['label' => 'Họ tên', 'type' => 'text', 'rules' => ['required', 'string', 'max:255'], 'required' => true],
                'gio_bat_dau'    => ['label' => 'Giờ bắt đầu', 'type' => 'hour', 'rules' => ['nullable', 'string', 'max:5']],
                'gio_ket_thuc'   => ['label' => 'Giờ kết thúc', 'type' => 'hour', 'rules' => ['nullable', 'string', 'max:5']],
                'active'         => ['label' => 'Hoạt động', 'type' => 'toggle', 'rules' => ['nullable', 'boolean']],
            ]),
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

    public function index(CoSo $co_so)
    {
        return view('longevity.settings.index', ['coSo' => $co_so, 'sections' => self::SECTIONS]);
    }

    public function section(CoSo $co_so, string $section, Request $request)
    {
        abort_unless(isset(self::SECTIONS[$section]), 404);
        $config = $this->editableConfig($co_so)[$this->resolveSection($section)] ?? null;

        $rows = match ($section) {
            'phong'      => $co_so->phongs()->with('khungGios')->get(),
            'bac-si'     => BacSi::where(fn ($q) => $q->where('co_so_id', $co_so->id)->orWhere('xuat_hien_moi_co_so', true))
                ->orderBy('ten')->get(),
            'ktv'        => Ktv::where('co_so_id', $co_so->id)->orderBy('ten')->get(),
            'dich-vu'    => \App\Models\DichVu::where('co_so_id', $co_so->id)->orderBy('ten')->get(),
            'menu'       => \App\Models\Menu::where('co_so_id', $co_so->id)->orderBy('ten')->get(),
            'nguoi-dung' => User::with(['phongBan', 'vaiTro'])
                ->where(fn ($q) => $q->where('co_so_id', $co_so->id)->orWhereNull('co_so_id'))
                ->when($request->filled('q'), fn ($q) => $q->where('name', 'like', '%'.$request->query('q').'%'))
                ->when($request->filled('vai_tro_id'), fn ($q) => $q->where('vai_tro_id', $request->query('vai_tro_id')))
                ->when($request->filled('chuc_danh'), fn ($q) => $q->where('chuc_danh', $request->query('chuc_danh')))
                ->when($request->filled('is_tu_van'), fn ($q) => $q->where('is_tu_van', $request->query('is_tu_van') === '1'))
                ->orderByDesc('is_admin')->orderBy('name')->get(),
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
                'current' => $request->only(['q', 'vai_tro_id', 'chuc_danh', 'is_tu_van']),
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
                ->when($bacSiId, fn ($q) => $q->where('bac_si_user_id', $bacSiId))
                ->when($saleId, fn ($q) => $q->where('sale_id', $saleId))
                ->when($ktvId, fn ($q) => $q->where('ktv_user_id', $ktvId))
                ->orderByDesc('ngay_dat')->orderBy('id');
            $bookings = $bq->get();
        }

        // ----- LICH HEN query -----
        $lichHens = collect();
        if ($loai !== 'booking') {
            // Lưu ý: lich_hen không có ktv_user_id → nếu lọc theo KTV thì lich_hen không match → trả về rỗng.
            $lq = LichHen::where('co_so_id', $co_so->id)
                ->with(['khachHang', 'bacSiTuVan', 'caKham', 'sale'])
                ->when($tu, fn ($q) => $q->whereDate('ngay_hen', '>=', $tu))
                ->when($den, fn ($q) => $q->whereDate('ngay_hen', '<=', $den))
                ->when($bacSiId, fn ($q) => $q->where('bac_si_user_id', $bacSiId))
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
            // Trạng thái khách sau khi sử dụng dịch vụ (chỉ áp cho booking).
            'kh_dung_gio' => $coll->where('trang_thai_khach', 'dung_gio')->count(),
            'kh_muon'     => $coll->where('trang_thai_khach', 'muon')->count(),
            'kh_huy'      => $coll->where('trang_thai_khach', 'huy')->count(),
        ];

        $counter = [
            'booking' => $countByStatus($bookings),
            'tu_van'  => $countByStatus($lichHens),
        ];
        $counter['tong'] = [
            'total'    => $counter['booking']['total'] + $counter['tu_van']['total'],
            'da_duyet' => $counter['booking']['da_duyet'] + $counter['tu_van']['da_duyet'],
            'cho_duyet'=> $counter['booking']['cho_duyet'] + $counter['tu_van']['cho_duyet'],
            'tu_choi'  => $counter['booking']['tu_choi'] + $counter['tu_van']['tu_choi'],
            'da_xong'  => $counter['booking']['da_xong'],
            // Trạng thái khách chỉ có ở booking → total = số của booking.
            'kh_dung_gio' => $counter['booking']['kh_dung_gio'],
            'kh_muon'     => $counter['booking']['kh_muon'],
            'kh_huy'      => $counter['booking']['kh_huy'],
        ];

        // ----- Options cho dropdown filter -----
        $bacSiVaiTroIds = VaiTro::whereIn('ma', ['bac_si', 'bac_si_tu_van'])->pluck('id');
        $bacSis = User::whereIn('vai_tro_id', $bacSiVaiTroIds)
            ->where(fn ($q) => $q->where('co_so_id', $co_so->id)->orWhere('is_tu_van', true))
            ->orderBy('name')->get(['id', 'name', 'chuc_danh']);

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
            'is_admin'       => ['nullable', 'boolean'],
            'is_tu_van'      => ['nullable', 'boolean'],
            'nhan_tu_van'    => ['nullable', 'boolean'],
            'nhan_kham_ls'   => ['nullable', 'boolean'],
            'phut_tu_van'    => ['nullable', 'integer', 'min:1', 'max:240'],
            'phut_kham_ls'   => ['nullable', 'integer', 'min:1', 'max:240'],
            'gio_bat_dau'    => ['nullable', 'string', 'max:5'],
            'gio_ket_thuc'   => ['nullable', 'string', 'max:5'],
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

        $attrs = [
            'name'           => $data['name'],
            'chuc_danh'      => ($data['chuc_danh'] ?? null) ?: null,
            'username'       => ($data['username'] ?? null) ?: null,
            'email'          => ($data['email'] ?? null) ?: null,
            'phong_ban_id'   => ($data['phong_ban_id'] ?? null) ?: null,
            'vai_tro_id'     => $vaiTroId,
            'is_admin'       => $isAdmin,
            'is_tu_van'      => $request->boolean('is_tu_van'),
            'nhan_tu_van'    => $request->boolean('nhan_tu_van'),
            'nhan_kham_ls'   => $request->boolean('nhan_kham_ls'),
            'phut_tu_van'    => $request->integer('phut_tu_van') ?: 30,
            'phut_kham_ls'   => $request->integer('phut_kham_ls') ?: 5,
            'gio_bat_dau'    => ($data['gio_bat_dau'] ?? null) ?: null,
            'gio_ket_thuc'   => ($data['gio_ket_thuc'] ?? null) ?: null,
            'co_so_id'       => $isAdmin ? null : $co_so->id,
        ];
        if (! empty($data['password'])) {
            $attrs['password'] = Hash::make($data['password']);
        }

        $user ? $user->update($attrs) : User::create($attrs);

        return back()->with('ok', $user ? 'Đã cập nhật người dùng.' : 'Đã thêm người dùng.');
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
                // array_unique để dedupe: 1 trường có thể được tick ở nhiều nhóm mirror
                // (sub của sua_booking / sua_lien_quan / sua_dich_vu_cua_toi) — chỉ lưu 1 row.
                $truongs = array_values(array_unique(array_intersect((array) ($allow[$vtId] ?? []), $validKeys)));
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
