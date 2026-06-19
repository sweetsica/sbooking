<?php

namespace App\Http\Controllers;

use App\Models\BacSi;
use App\Models\BacSiTuVan;
use App\Models\CoSo;
use App\Models\DichVu;
use App\Models\Menu;
use App\Models\PhanQuyen;
use App\Models\Phong;
use App\Models\PhongBan;
use App\Models\User;
use App\Support\BookingFields;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class SettingsController extends Controller
{
    // 8 mục thiết lập
    public const SECTIONS = [
        'phong'      => ['Phòng chức năng', 'meeting_room', 'Phòng trị liệu: số slot tối đa, khung giờ phục vụ (liên kết với form tạo booking).'],
        'phong-ban'  => ['Phòng ban', 'corporate_fare', 'Bộ phận: Kinh doanh (Sales), Quản trị... — dùng cho phân quyền & gán người dùng.'],
        'bac-si'     => ['Bác sĩ', 'medical_services', 'Thêm/sửa/xóa bác sĩ để booking lấy thông tin.'],
        'co-so'      => ['Cơ sở', 'store', 'Mỗi cơ sở (chi nhánh) có nhân sự, bác sĩ, phòng/giường riêng.'],
        'quyen'      => ['Quyền', 'admin_panel_settings', 'Phòng ban nào được Xem / Sửa (tất cả - từng trường) / Xóa.'],
        'nguoi-dung' => ['Người dùng', 'group', 'Thêm/sửa/xóa người dùng.'],
        'dich-vu'    => ['Dịch vụ', 'spa', 'CRUD tên dịch vụ — đưa vào form tạo mới.'],
        'dieu-duong' => ['Điều dưỡng / Bác sĩ', 'health_and_safety', 'CRUD tên Điều dưỡng/Bác sĩ — đưa vào form tạo mới.'],
        'menu'       => ['Menu', 'restaurant_menu', 'CRUD tên Menu — đưa vào form tạo mới (dạng ô tick).'],
        'bac-si-tu-van' => ['Bác sĩ tư vấn', 'stethoscope', 'Thêm/sửa bác sĩ tư vấn + thời gian khám, giờ làm — hệ thống tự chia ca khám.'],
    ];

    // Cấu hình các mục có CRUD
    private function editableConfig(): array
    {
        $catalog = fn ($model, $fields) => ['model' => $model, 'kind' => 'catalog', 'fields' => $fields];
        $phongBanOptions = PhongBan::orderBy('ten')->pluck('ten', 'id')->all();

        return [
            'dich-vu' => $catalog(DichVu::class, [
                'ten'    => ['label' => 'Tên dịch vụ', 'type' => 'text', 'rules' => ['required', 'string', 'max:255']],
                'active' => ['label' => 'Kích hoạt', 'type' => 'toggle', 'rules' => ['nullable', 'boolean']],
            ]),
            'menu' => $catalog(Menu::class, [
                'ten'    => ['label' => 'Tên menu', 'type' => 'text', 'rules' => ['required', 'string', 'max:255']],
                'active' => ['label' => 'Kích hoạt', 'type' => 'toggle', 'rules' => ['nullable', 'boolean']],
            ]),
            'bac-si' => $catalog(BacSi::class, [
                'chuc_danh' => ['label' => 'Chức danh', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:50'], 'placeholder' => 'BS. / KTV. / Điều dưỡng'],
                'ten'       => ['label' => 'Họ tên', 'type' => 'text', 'rules' => ['required', 'string', 'max:255']],
                'active'    => ['label' => 'Đang làm', 'type' => 'toggle', 'rules' => ['nullable', 'boolean']],
            ]),
            'phong' => $catalog(Phong::class, [
                'ten'            => ['label' => 'Tên phòng', 'type' => 'text', 'rules' => ['required', 'string', 'max:255']],
                'loai'          => ['label' => 'Loại', 'type' => 'select', 'options' => ['vip' => 'VIP', 'cong_dong' => 'Cộng đồng'], 'rules' => ['required', Rule::in(['vip', 'cong_dong'])]],
                'so_slot_toi_da' => ['label' => 'Số slot tối đa', 'type' => 'number', 'rules' => ['required', 'integer', 'min:1', 'max:99']],
                'trang_thai'    => ['label' => 'Trạng thái', 'type' => 'select', 'options' => ['hoat_dong' => 'Hoạt động', 'bao_tri' => 'Bảo trì'], 'rules' => ['required', Rule::in(['hoat_dong', 'bao_tri'])]],
                'gio_mo'        => ['label' => 'Giờ mở cửa', 'type' => 'hour', 'rules' => ['required', 'regex:/^\d{2}:00$/'], 'virtual' => true],
                'gio_dong'      => ['label' => 'Giờ đóng cửa', 'type' => 'hour', 'rules' => ['required', 'regex:/^\d{2}:00$/'], 'virtual' => true],
            ]),
            'bac-si-tu-van' => $catalog(BacSiTuVan::class, [
                'chuc_danh'      => ['label' => 'Chức danh', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:50'], 'placeholder' => 'BS. / ThS.'],
                'ten'            => ['label' => 'Họ tên', 'type' => 'text', 'rules' => ['required', 'string', 'max:255']],
                'thoi_gian_kham' => ['label' => 'Thời gian khám (phút)', 'type' => 'number', 'rules' => ['required', 'integer', 'min:5', 'max:120']],
                'gio_bat_dau'    => ['label' => 'Giờ bắt đầu', 'type' => 'hour', 'rules' => ['required', 'regex:/^\d{2}:\d{2}$/']],
                'gio_ket_thuc'   => ['label' => 'Giờ kết thúc', 'type' => 'hour', 'rules' => ['required', 'regex:/^\d{2}:\d{2}$/']],
                'active'         => ['label' => 'Đang làm', 'type' => 'toggle', 'rules' => ['nullable', 'boolean']],
            ]),
            'nguoi-dung' => [
                'model' => User::class, 'kind' => 'user',
                'fields' => [
                    'name'         => ['label' => 'Họ tên', 'type' => 'text', 'rules' => ['required', 'string', 'max:255']],
                    'username'     => ['label' => 'Tài khoản', 'type' => 'text', 'rules' => [], 'placeholder' => 'vd: tttg'],
                    'email'        => ['label' => 'Email', 'type' => 'text', 'rules' => []],
                    'phong_ban_id' => ['label' => 'Phòng ban', 'type' => 'select', 'options' => ['' => '— Không —'] + $phongBanOptions, 'rules' => ['nullable', Rule::exists('phong_ban', 'id')]],
                    'is_admin'     => ['label' => 'Quản trị (mọi cơ sở)', 'type' => 'toggle', 'rules' => ['nullable', 'boolean']],
                    'password'     => ['label' => 'Mật khẩu', 'type' => 'password', 'rules' => [], 'virtual' => true, 'placeholder' => 'Tối thiểu 6 ký tự'],
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
        return $section === 'dieu-duong' ? 'bac-si' : $section;
    }

    public function index(CoSo $co_so)
    {
        return view('longevity.settings.index', ['coSo' => $co_so, 'sections' => self::SECTIONS]);
    }

    public function section(CoSo $co_so, string $section)
    {
        abort_unless(isset(self::SECTIONS[$section]), 404);
        $config = $this->editableConfig()[$this->resolveSection($section)] ?? null;

        $rows = match ($section) {
            'phong'      => $co_so->phongs()->with('khungGios')->get(),
            'dich-vu'    => $co_so->dichVus()->get(),
            'menu'       => $co_so->menus()->get(),
            'bac-si', 'dieu-duong' => $co_so->bacSis()->get(),
            'bac-si-tu-van' => $co_so->bacSiTuVans()->with('caKhams')->get(),
            'nguoi-dung' => $co_so->nguoiDungs()->with('phongBan')->get(),
            'co-so'      => CoSo::orderBy('id')->get(),
            'phong-ban'  => PhongBan::orderBy('id')->get(),
            default      => collect(),
        };

        // Dữ liệu cho ma trận phân quyền sửa trường
        $quyen = null;
        if ($section === 'quyen') {
            $allowed = PhanQuyen::all()
                ->groupBy('phong_ban_id')
                ->map(fn ($g) => $g->pluck('truong')->all());
            $quyen = [
                'phongBans' => PhongBan::orderBy('id')->get(),
                'fields' => BookingFields::all(),
                'allowed' => $allowed,   // [phong_ban_id => [truong,...]]
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
        ]);
    }

    public function store(CoSo $co_so, Request $request, string $section)
    {
        if ($section === 'quyen') {
            return $this->saveQuyen($request);
        }

        [$model, $config] = $this->mustEditable($section);

        return match ($config['kind']) {
            'user' => $this->saveUser($co_so, $request, null),
            'coso' => $this->saveCoSo($request, null),
            'phongban' => $this->savePhongBan($request, null),
            default => $this->saveCatalog($co_so, $request, $config, $model, $section, null),
        };
    }

    public function update(CoSo $co_so, Request $request, string $section, int $id)
    {
        [$model, $config] = $this->mustEditable($section);

        return match ($config['kind']) {
            'user' => $this->saveUser($co_so, $request, $co_so->nguoiDungs()->findOrFail($id)),
            'coso' => $this->saveCoSo($request, CoSo::findOrFail($id)),
            'phongban' => $this->savePhongBan($request, PhongBan::findOrFail($id)),
            default => $this->saveCatalog($co_so, $request, $config, $model, $section, $model::where('co_so_id', $co_so->id)->findOrFail($id)),
        };
    }

    public function destroy(CoSo $co_so, string $section, int $id)
    {
        [$model, $config] = $this->mustEditable($section);

        if ($config['kind'] === 'coso') {
            $cs = CoSo::findOrFail($id);
            if ($cs->id === $co_so->id) {
                return back()->with('err', 'Không thể xóa cơ sở đang xem.');
            }
            $cs->delete();
        } elseif ($config['kind'] === 'phongban') {
            $pb = PhongBan::withCount('nguoiDungs')->findOrFail($id);
            if ($pb->nguoi_dungs_count > 0) {
                return back()->with('err', 'Không thể xóa: phòng ban đang có người dùng. Hãy chuyển họ sang phòng ban khác trước.');
            }
            $pb->delete(); // phân quyền của phòng ban này tự xóa theo (cascade)
        } elseif ($config['kind'] === 'user') {
            $co_so->nguoiDungs()->findOrFail($id)->delete();
        } else {
            $model::where('co_so_id', $co_so->id)->findOrFail($id)->delete();
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
            $attrs['co_so_id'] = $co_so->id;
            $record = $model::create($attrs);
        }

        if ($section === 'phong') {
            $this->regenSlots($record, $data['gio_mo'], $data['gio_dong']);
        }

        if ($section === 'bac-si-tu-van') {
            $record->taoCaKham();
        }

        return back()->with('ok', $record->wasRecentlyCreated ? 'Đã thêm mới.' : 'Đã cập nhật.');
    }

    private function saveUser(CoSo $co_so, Request $request, ?User $user)
    {
        $data = $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'username'     => ['nullable', 'string', 'max:50', 'regex:/^[a-z0-9._-]+$/', Rule::unique('users', 'username')->ignore($user?->id)],
            'email'        => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->id)],
            'phong_ban_id' => ['nullable', Rule::exists('phong_ban', 'id')],
            'is_admin'     => ['nullable', 'boolean'],
            'password'     => [$user ? 'nullable' : 'required', 'string', 'min:6'],
        ], [
            'username.regex' => 'Tài khoản chỉ gồm chữ thường, số, dấu chấm, gạch dưới hoặc gạch ngang.',
        ]);

        $isAdmin = $request->boolean('is_admin');
        $attrs = [
            'name'         => $data['name'],
            'username'     => ($data['username'] ?? null) ?: null,
            'email'        => $data['email'],
            'phong_ban_id' => ($data['phong_ban_id'] ?? null) ?: null,
            'is_admin'     => $isAdmin,
            'co_so_id'     => $isAdmin ? null : $co_so->id,
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

    // Lưu ma trận phân quyền sửa trường (phòng ban × trường)
    private function saveQuyen(Request $request)
    {
        $validKeys = BookingFields::keys();
        $allow = (array) $request->input('allow', []); // [phong_ban_id => [truong,...]]

        DB::transaction(function () use ($allow, $validKeys) {
            foreach (PhongBan::pluck('id') as $pbId) {
                $truongs = array_values(array_intersect((array) ($allow[$pbId] ?? []), $validKeys));
                PhanQuyen::where('phong_ban_id', $pbId)->delete();
                foreach ($truongs as $t) {
                    PhanQuyen::create(['phong_ban_id' => $pbId, 'truong' => $t]);
                }
            }
        });

        return back()->with('ok', 'Đã lưu phân quyền sửa trường.');
    }

    private function savePhongBan(Request $request, ?PhongBan $pb)
    {
        $data = $request->validate([
            'ten' => ['required', 'string', 'max:255'],
            'ma'  => ['required', 'string', 'max:50', 'regex:/^[a-z0-9_-]+$/', Rule::unique('phong_ban', 'ma')->ignore($pb?->id)],
        ], [
            'ma.regex' => 'Mã chỉ gồm chữ thường, số, gạch dưới hoặc gạch ngang.',
            'ma.unique' => 'Mã phòng ban đã tồn tại.',
        ]);

        $pb ? $pb->update($data) : PhongBan::create($data);

        return back()->with('ok', $pb ? 'Đã cập nhật phòng ban.' : 'Đã thêm phòng ban.');
    }

    // ----- helpers -----

    private function mustEditable(string $section): array
    {
        abort_unless(isset(self::SECTIONS[$section]), 404);
        $config = $this->editableConfig()[$this->resolveSection($section)] ?? null;
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
