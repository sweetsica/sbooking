<?php

use App\Models\BacSi;
use App\Models\CoSo;
use App\Models\DichVu;
use App\Models\Phong;
use App\Models\User;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

/**
 * 2026-10-05 — Quick Sheets (sbooking side).
 * Admin only. 4 tab:
 *   - dich_vu  : Dịch vụ
 *   - bac_si   : Bác sĩ / KTV / Điều dưỡng
 *   - phong    : Phòng
 *   - users    : Nhân sự (sbooking users — sale/admin)
 *
 * Full CRUD inline + Export CSV + Import CSV. Filter theo cơ sở của route (coSo).
 * Nút "Sync" pull sale list từ SCRM /api/ups/sales-today upsert vào users local
 * (match email, update name/chuc_danh). Mục đích: SCRM là master của sale name.
 */
new class extends Component
{
    use WithPagination, WithFileUploads;

    /** 2026-10-05: Livewire 3 không serialize Eloquent prop ngon → giữ 3 scalar public:
     *   coSoId / coSoTen / coSoSlug. Template dùng $coSoTen trực tiếp, không cần method call. */
    public int $coSoId;
    public string $coSoTen = '';
    public string $coSoSlug = '';
    public string $tab = 'dich_vu';
    public string $search = '';
    public array $draft = [];
    public array $editing = [];
    public $importFile = null;

    protected function queryString(): array
    {
        return ['tab' => ['except' => 'dich_vu']];
    }

    public function mount(int $coSoId): void
    {
        abort_unless(auth()->user()?->is_admin, 403);
        $this->coSoId = $coSoId;
        $co = CoSo::findOrFail($coSoId);
        $this->coSoTen = $co->ten;
        $this->coSoSlug = $co->slug;
        $this->resetDraft();
    }

    /** Resolve CoSo Eloquent khi backend thực cần (export filename, etc.). */
    protected function getCoSo(): CoSo
    {
        return CoSo::findOrFail($this->coSoId);
    }

    public function updatedTab(): void
    {
        $this->resetPage();
        $this->resetDraft();
        $this->editing = [];
        $this->search = '';
        $this->resetErrorBag();
    }

    public function updatedSearch(): void { $this->resetPage(); }

    protected function resetDraft(): void
    {
        $this->draft = match ($this->tab) {
            'dich_vu' => ['ten'=>'', 'thoi_gian_phut'=>30, 'thuoc_nhom'=>'khac', 'la_dich_vu'=>true, 'khong_can_phong'=>false, 'active'=>true],
            'bac_si'  => ['ten'=>'', 'chuc_danh'=>'BS.', 'nhan_tu_van'=>true, 'phut_tu_van'=>30, 'nhan_kham_ls'=>true, 'phut_kham_ls'=>5, 'gio_bat_dau'=>'08:00', 'gio_ket_thuc'=>'17:00', 'active'=>true],
            'phong'   => ['ten'=>'', 'kieu_phong'=>'phong_dich_vu', 'duoc_dat_tu_van'=>false, 'loai'=>'kham', 'so_slot_toi_da'=>1, 'phut_moi_khach'=>30, 'trang_thai'=>'hoat_dong'],
            'users'   => ['name'=>'', 'email'=>'', 'username'=>'', 'chuc_danh'=>'', 'is_tu_van'=>false, 'is_admin'=>false],
            default   => [],
        };
    }

    public function addRow(): void
    {
        try {
            match ($this->tab) {
                'dich_vu' => DichVu::create(array_merge($this->validatedDraft(['ten']), ['co_so_id' => $this->coSoId])),
                'bac_si'  => BacSi::create(array_merge($this->validatedDraft(['ten']), ['co_so_id' => $this->coSoId, 'xuat_hien_moi_co_so' => false])),
                'phong'   => Phong::create(array_merge($this->validatedDraft(['ten']), ['co_so_id' => $this->coSoId])),
                'users'   => $this->addUser(),
                default   => null,
            };
        } catch (\Throwable $e) {
            $this->addError('draft', $e->getMessage());
            return;
        }
        $this->resetDraft();
    }

    protected function validatedDraft(array $required): array
    {
        $data = $this->draft;
        foreach ($required as $f) {
            if (! isset($data[$f]) || $data[$f] === '') throw new \Exception("Thiếu trường {$f}");
        }
        return $data;
    }

    protected function addUser(): void
    {
        $data = $this->validatedDraft(['name', 'email']);
        if (User::where('email', $data['email'])->exists()) throw new \Exception('Email đã tồn tại.');
        User::create([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'username'  => $data['username'] ?: strtolower(str_replace(' ', '', $data['name'])),
            'chuc_danh' => $data['chuc_danh'] ?: null,
            'co_so_id'  => $this->coSoId,
            'is_tu_van' => (bool) $data['is_tu_van'],
            'is_admin'  => (bool) $data['is_admin'],
            'password'  => bcrypt(\Illuminate\Support\Str::random(20)),
        ]);
    }

    public function startEdit(int $id): void
    {
        $row = $this->findRow($id);
        if (! $row) return;
        $this->editing[$id] = $row->only(array_keys($this->draft));
    }

    public function cancelEdit(int $id): void { unset($this->editing[$id]); }

    public function saveEdit(int $id): void
    {
        $row = $this->findRow($id);
        if (! $row || ! isset($this->editing[$id])) return;
        try {
            $data = $this->editing[$id];
            // Giữ nguyên khoá bắt buộc (ten/name/email) nếu edit bỏ trống.
            $row->update(array_filter($data, fn ($v) => $v !== null && $v !== ''));
        } catch (\Throwable $e) {
            $this->addError('row_' . $id, $e->getMessage());
            return;
        }
        unset($this->editing[$id]);
    }

    public function deleteRow(int $id): void
    {
        $row = $this->findRow($id);
        if (! $row) return;
        try {
            $row->delete();
        } catch (\Throwable $e) {
            $this->addError('row_' . $id, $e->getMessage());
        }
        unset($this->editing[$id]);
    }

    protected function findRow(int $id)
    {
        return match ($this->tab) {
            'dich_vu' => DichVu::where('co_so_id', $this->coSoId)->find($id),
            'bac_si'  => BacSi::where('co_so_id', $this->coSoId)->find($id),
            'phong'   => Phong::where('co_so_id', $this->coSoId)->find($id),
            'users'   => User::where('co_so_id', $this->coSoId)->find($id),
            default   => null,
        };
    }

    /* ============== EXPORT / IMPORT CSV ============== */

    public function exportCsv()
    {
        $rows = $this->query()->get();
        $cols = $this->columns();
        $csv = implode(',', $cols) . "\n";
        foreach ($rows as $r) {
            $csv .= implode(',', array_map(fn ($c) => '"' . str_replace('"', '""', (string) $r->$c) . '"', $cols)) . "\n";
        }
        $filename = "sbooking-{$this->tab}-{$this->getCoSo()->slug}-" . now()->format('Ymd-His') . '.csv';
        return response()->streamDownload(fn () => print($csv), $filename, ['Content-Type' => 'text/csv; charset=utf-8']);
    }

    public function importCsv(): void
    {
        if (! $this->importFile) { $this->addError('importFile', 'Chọn file CSV trước.'); return; }
        $path = $this->importFile->getRealPath();
        $fh = fopen($path, 'r');
        if (! $fh) { $this->addError('importFile', 'Không đọc được file.'); return; }

        $header = fgetcsv($fh);
        if (! $header) { $this->addError('importFile', 'File rỗng.'); fclose($fh); return; }

        $created = 0; $updated = 0; $errors = 0;
        $modelClass = $this->modelClass();
        while (($row = fgetcsv($fh)) !== false) {
            $data = array_combine($header, $row);
            if (! $data) { $errors++; continue; }
            try {
                $data['co_so_id'] = $this->coSoId;
                $id = (int) ($data['id'] ?? 0);
                unset($data['id']);
                if ($id && $existing = $modelClass::where('co_so_id', $this->coSoId)->find($id)) {
                    $existing->update($data);
                    $updated++;
                } else {
                    $modelClass::create($data);
                    $created++;
                }
            } catch (\Throwable $e) {
                $errors++;
            }
        }
        fclose($fh);
        $this->importFile = null;
        session()->flash('sheet_ok', "Import: tạo {$created}, cập nhật {$updated}, lỗi {$errors}.");
    }

    protected function modelClass(): string
    {
        return match ($this->tab) {
            'dich_vu' => DichVu::class,
            'bac_si'  => BacSi::class,
            'phong'   => Phong::class,
            'users'   => User::class,
        };
    }

    protected function columns(): array
    {
        return match ($this->tab) {
            'dich_vu' => ['id', 'ten', 'thoi_gian_phut', 'thuoc_nhom', 'la_dich_vu', 'khong_can_phong', 'active'],
            'bac_si'  => ['id', 'ten', 'chuc_danh', 'nhan_tu_van', 'phut_tu_van', 'nhan_kham_ls', 'phut_kham_ls', 'gio_bat_dau', 'gio_ket_thuc', 'active'],
            'phong'   => ['id', 'ten', 'kieu_phong', 'loai', 'duoc_dat_tu_van', 'so_slot_toi_da', 'phut_moi_khach', 'trang_thai'],
            'users'   => ['id', 'name', 'email', 'username', 'chuc_danh', 'is_tu_van', 'is_admin'],
        };
    }

    protected function query()
    {
        $q = $this->modelClass()::query()->where('co_so_id', $this->coSoId);
        if ($this->search !== '') {
            $s = trim($this->search);
            $tenField = $this->tab === 'users' ? 'name' : 'ten';
            $q->where($tenField, 'like', "%$s%");
        }
        return $q->orderBy('id', 'desc');
    }

    /* ============== SYNC từ SCRM ============== */

    public function syncFromScrm(): void
    {
        abort_unless(auth()->user()?->is_admin, 403);
        $scrmUrl = rtrim((string) \App\Models\AppSetting::get('scrm_url'), '/');
        if (! $scrmUrl) {
            session()->flash('sync_err', 'Chưa cấu hình scrm_url trong AppSetting.');
            return;
        }
        try {
            $resp = \Illuminate\Support\Facades\Http::timeout(10)
                ->get("{$scrmUrl}/api/ups/sales-today", ['sbooking_co_so_id' => $this->coSoId]);
            if (! $resp->ok()) throw new \Exception("HTTP {$resp->status()}");
            $data = $resp->json('data') ?? [];
            $matched = 0; $notFound = 0;
            foreach ($data as $u) {
                $email = $u['email'] ?? null;
                if (! $email) { $notFound++; continue; }
                $local = User::where('email', $email)->first();
                if ($local) {
                    $local->update(array_filter([
                        'name'      => $u['name'] ?? null,
                        'chuc_danh' => $u['chuc_danh'] ?? null,
                    ]));
                    $matched++;
                } else {
                    $notFound++;
                }
            }
            session()->flash('sync_ok', "Sync từ SCRM: match {$matched} sale, {$notFound} chưa có local user.");
        } catch (\Throwable $e) {
            session()->flash('sync_err', 'Sync lỗi: ' . $e->getMessage());
        }
    }

    public function with(): array
    {
        return [
            'rows'       => $this->query()->paginate(30),
            'coSo'       => $this->getCoSo(), // 2026-10-05: expose cho template dùng $coSo trực tiếp.
            'coSoList'   => CoSo::orderBy('id')->get(['id', 'ten', 'slug']),
            'nhomOpts'   => ['tu_van' => 'Tư vấn', 'kham_ls' => 'Khám LS', 'khac' => 'Khác'],
            'kieuOpts'   => ['phong_kham' => 'Phòng khám', 'phong_dich_vu' => 'Phòng dịch vụ'],
            'loaiOpts'   => ['kham' => 'Khám', 'dich_vu' => 'Dịch vụ'],
            'ttrOpts'    => ['hoat_dong' => 'Hoạt động', 'ngung' => 'Ngừng'],
        ];
    }
};

?>

<div class="-mx-container-margin -mt-24 pt-24 bg-[#f8f9fa] min-h-screen flex flex-col"
     style="font-family: Arial, Roboto, 'Helvetica Neue', sans-serif;">

    @php
        $tabs = [
            'dich_vu' => '💆 Dịch vụ',
            'bac_si'  => '👨‍⚕️ Bác sĩ/KTV/ĐD',
            'phong'   => '🚪 Phòng',
            'users'   => '🧑‍💼 Nhân sự',
        ];
        $thCls  = 'border border-gray-300 px-2 py-1 font-semibold text-left whitespace-nowrap';
        $tdCls  = 'border border-gray-300 px-2 py-0.5 whitespace-nowrap';
        $inpCls = 'w-full px-1 py-0 border-0 bg-transparent focus:outline-none focus:ring-1 focus:ring-blue-500 focus:bg-white';
    @endphp

    {{-- Top toolbar --}}
    <div class="flex items-center justify-between gap-3 px-3 py-1.5 border-b border-gray-300 bg-white sticky top-16 z-20">
        <div class="flex items-center gap-2">
            <span class="text-sm font-semibold text-gray-800">⚡ Quick Sheets</span>
            <span class="text-[11px] text-gray-500">Cơ sở <b>{{ $coSoTen }}</b> · admin only</span>
        </div>
        <div class="flex items-center gap-3 text-[12px]">
            <input type="search" wire:model.live.debounce.300ms="search" placeholder="🔍 Tìm trong tab"
                   class="border border-gray-300 rounded px-2 py-0.5 w-56">
            <button wire:click="exportCsv" class="text-[12px] font-semibold text-sky-700 border border-sky-300 hover:bg-sky-50 px-2 py-0.5 rounded">⬇ Export CSV</button>
            <label class="text-[12px] font-semibold text-amber-700 border border-amber-300 hover:bg-amber-50 px-2 py-0.5 rounded cursor-pointer">
                ⬆ Import CSV
                <input type="file" wire:model="importFile" accept=".csv" class="hidden">
            </label>
            @if ($importFile)
                <button wire:click="importCsv" class="text-[12px] font-semibold text-white bg-amber-600 hover:bg-amber-700 px-2 py-0.5 rounded">Chạy import</button>
            @endif
            <button wire:click="syncFromScrm" wire:loading.attr="disabled" wire:target="syncFromScrm"
                    class="text-[12px] font-semibold text-white bg-emerald-600 hover:bg-emerald-700 disabled:opacity-60 px-2 py-0.5 rounded"
                    title="Pull sale list từ SCRM (ups/sales-today) → upsert local users theo email">
                <span wire:loading.remove wire:target="syncFromScrm">⚡ Sync từ SCRM</span>
                <span wire:loading wire:target="syncFromScrm">⏳</span>
            </button>
            <a href="/{{ $coSoSlug }}/thiet-lap" class="text-gray-600 hover:text-gray-900 underline">← Thiết lập</a>
        </div>
    </div>
    @if (session('sync_ok'))<div class="bg-emerald-50 border-b border-emerald-200 text-emerald-800 text-[11px] px-3 py-1">✓ {{ session('sync_ok') }}</div>@endif
    @if (session('sync_err'))<div class="bg-red-50 border-b border-red-200 text-red-800 text-[11px] px-3 py-1">⚠ {{ session('sync_err') }}</div>@endif
    @if (session('sheet_ok'))<div class="bg-sky-50 border-b border-sky-200 text-sky-800 text-[11px] px-3 py-1">✓ {{ session('sheet_ok') }}</div>@endif
    @if ($errors->any())<div class="bg-red-50 border-b border-red-200 text-red-800 text-[11px] px-3 py-1">⚠ {{ $errors->first() }}</div>@endif

    {{-- Sheet --}}
    <div class="flex-1 overflow-auto bg-white">
        <table class="w-full border-collapse" style="font-size: 12px;">
            @switch($tab)

            @case('dich_vu')
                <thead class="sticky top-0 z-10 bg-[#f1f3f4]"><tr>
                    <th class="{{ $thCls }} w-14 text-center">ID</th>
                    <th class="{{ $thCls }}">Tên dịch vụ</th>
                    <th class="{{ $thCls }} w-24 text-center">Phút</th>
                    <th class="{{ $thCls }} w-28">Nhóm</th>
                    <th class="{{ $thCls }} w-20 text-center">Là DV</th>
                    <th class="{{ $thCls }} w-24 text-center">Không cần phòng</th>
                    <th class="{{ $thCls }} w-20 text-center">Active</th>
                    <th class="{{ $thCls }} w-28 text-center">Thao tác</th>
                </tr></thead>
                <tbody>
                    <tr class="bg-[#e8f0fe]">
                        <td class="{{ $tdCls }} text-center text-blue-600">+</td>
                        <td class="{{ $tdCls }}"><input wire:model="draft.ten" placeholder="Tên *" class="{{ $inpCls }}"></td>
                        <td class="{{ $tdCls }}"><input type="number" wire:model="draft.thoi_gian_phut" class="{{ $inpCls }} text-center"></td>
                        <td class="{{ $tdCls }}">
                            <select wire:model="draft.thuoc_nhom" class="{{ $inpCls }}">
                                @foreach ($nhomOpts as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
                            </select>
                        </td>
                        <td class="{{ $tdCls }} text-center"><input type="checkbox" wire:model="draft.la_dich_vu"></td>
                        <td class="{{ $tdCls }} text-center"><input type="checkbox" wire:model="draft.khong_can_phong"></td>
                        <td class="{{ $tdCls }} text-center"><input type="checkbox" wire:model="draft.active"></td>
                        <td class="{{ $tdCls }} text-center"><button wire:click="addRow" class="text-[11px] font-semibold text-white bg-blue-600 hover:bg-blue-700 px-2 py-0.5 rounded">+ Thêm</button></td>
                    </tr>
                    @forelse ($rows as $r)
                        @php $e = isset($editing[$r->id]); @endphp
                        <tr class="{{ $r->active ? '' : 'bg-gray-50 text-gray-500' }} hover:bg-[#f8f9fa]">
                            <td class="{{ $tdCls }} text-center text-gray-500">{{ $r->id }}</td>
                            @if ($e)
                                <td class="{{ $tdCls }}"><input wire:model="editing.{{ $r->id }}.ten" class="{{ $inpCls }}"></td>
                                <td class="{{ $tdCls }}"><input type="number" wire:model="editing.{{ $r->id }}.thoi_gian_phut" class="{{ $inpCls }} text-center"></td>
                                <td class="{{ $tdCls }}">
                                    <select wire:model="editing.{{ $r->id }}.thuoc_nhom" class="{{ $inpCls }}">
                                        @foreach ($nhomOpts as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
                                    </select>
                                </td>
                                <td class="{{ $tdCls }} text-center"><input type="checkbox" wire:model="editing.{{ $r->id }}.la_dich_vu"></td>
                                <td class="{{ $tdCls }} text-center"><input type="checkbox" wire:model="editing.{{ $r->id }}.khong_can_phong"></td>
                                <td class="{{ $tdCls }} text-center"><input type="checkbox" wire:model="editing.{{ $r->id }}.active"></td>
                                <td class="{{ $tdCls }} text-center">
                                    <button wire:click="saveEdit({{ $r->id }})" class="text-[11px] text-green-700 hover:underline">💾</button>
                                    <button wire:click="cancelEdit({{ $r->id }})" class="text-[11px] text-gray-600 hover:underline ml-1">✕</button>
                                </td>
                            @else
                                <td class="{{ $tdCls }}">{{ $r->ten }}</td>
                                <td class="{{ $tdCls }} text-center">{{ $r->thoi_gian_phut }}'</td>
                                <td class="{{ $tdCls }} text-[11px] uppercase">{{ $r->thuoc_nhom }}</td>
                                <td class="{{ $tdCls }} text-center">{{ $r->la_dich_vu ? '✓' : '—' }}</td>
                                <td class="{{ $tdCls }} text-center">{{ $r->khong_can_phong ? '✓' : '—' }}</td>
                                <td class="{{ $tdCls }} text-center">{{ $r->active ? '✓' : '—' }}</td>
                                <td class="{{ $tdCls }} text-center">
                                    <button wire:click="startEdit({{ $r->id }})" class="text-[11px] text-blue-700 hover:underline">Sửa</button>
                                    <button wire:click="deleteRow({{ $r->id }})" wire:confirm="Xóa '{{ $r->ten }}'?" class="text-[11px] text-red-700 hover:underline ml-1">Xóa</button>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr><td colspan="8" class="border border-gray-300 p-6 text-center text-gray-400 italic">Không có dịch vụ.</td></tr>
                    @endforelse
                </tbody>
                @break

            @case('bac_si')
                <thead class="sticky top-0 z-10 bg-[#f1f3f4]"><tr>
                    <th class="{{ $thCls }} w-14 text-center">ID</th>
                    <th class="{{ $thCls }} w-20">Chức danh</th>
                    <th class="{{ $thCls }}">Họ tên</th>
                    <th class="{{ $thCls }} w-20 text-center">TV</th>
                    <th class="{{ $thCls }} w-20 text-center">Phút TV</th>
                    <th class="{{ $thCls }} w-20 text-center">KLS</th>
                    <th class="{{ $thCls }} w-20 text-center">Phút KLS</th>
                    <th class="{{ $thCls }} w-24 text-center">Giờ BĐ</th>
                    <th class="{{ $thCls }} w-24 text-center">Giờ KT</th>
                    <th class="{{ $thCls }} w-20 text-center">Active</th>
                    <th class="{{ $thCls }} w-28 text-center">Thao tác</th>
                </tr></thead>
                <tbody>
                    <tr class="bg-[#e8f0fe]">
                        <td class="{{ $tdCls }} text-center text-blue-600">+</td>
                        <td class="{{ $tdCls }}"><input wire:model="draft.chuc_danh" class="{{ $inpCls }}"></td>
                        <td class="{{ $tdCls }}"><input wire:model="draft.ten" placeholder="Họ tên *" class="{{ $inpCls }}"></td>
                        <td class="{{ $tdCls }} text-center"><input type="checkbox" wire:model="draft.nhan_tu_van"></td>
                        <td class="{{ $tdCls }}"><input type="number" wire:model="draft.phut_tu_van" class="{{ $inpCls }} text-center"></td>
                        <td class="{{ $tdCls }} text-center"><input type="checkbox" wire:model="draft.nhan_kham_ls"></td>
                        <td class="{{ $tdCls }}"><input type="number" wire:model="draft.phut_kham_ls" class="{{ $inpCls }} text-center"></td>
                        <td class="{{ $tdCls }}"><input type="time" wire:model="draft.gio_bat_dau" class="{{ $inpCls }} text-center"></td>
                        <td class="{{ $tdCls }}"><input type="time" wire:model="draft.gio_ket_thuc" class="{{ $inpCls }} text-center"></td>
                        <td class="{{ $tdCls }} text-center"><input type="checkbox" wire:model="draft.active"></td>
                        <td class="{{ $tdCls }} text-center"><button wire:click="addRow" class="text-[11px] font-semibold text-white bg-blue-600 hover:bg-blue-700 px-2 py-0.5 rounded">+ Thêm</button></td>
                    </tr>
                    @forelse ($rows as $r)
                        @php $e = isset($editing[$r->id]); @endphp
                        <tr class="{{ $r->active ? '' : 'bg-gray-50 text-gray-500' }} hover:bg-[#f8f9fa]">
                            <td class="{{ $tdCls }} text-center text-gray-500">{{ $r->id }}</td>
                            @if ($e)
                                <td class="{{ $tdCls }}"><input wire:model="editing.{{ $r->id }}.chuc_danh" class="{{ $inpCls }}"></td>
                                <td class="{{ $tdCls }}"><input wire:model="editing.{{ $r->id }}.ten" class="{{ $inpCls }}"></td>
                                <td class="{{ $tdCls }} text-center"><input type="checkbox" wire:model="editing.{{ $r->id }}.nhan_tu_van"></td>
                                <td class="{{ $tdCls }}"><input type="number" wire:model="editing.{{ $r->id }}.phut_tu_van" class="{{ $inpCls }} text-center"></td>
                                <td class="{{ $tdCls }} text-center"><input type="checkbox" wire:model="editing.{{ $r->id }}.nhan_kham_ls"></td>
                                <td class="{{ $tdCls }}"><input type="number" wire:model="editing.{{ $r->id }}.phut_kham_ls" class="{{ $inpCls }} text-center"></td>
                                <td class="{{ $tdCls }}"><input type="time" wire:model="editing.{{ $r->id }}.gio_bat_dau" class="{{ $inpCls }} text-center"></td>
                                <td class="{{ $tdCls }}"><input type="time" wire:model="editing.{{ $r->id }}.gio_ket_thuc" class="{{ $inpCls }} text-center"></td>
                                <td class="{{ $tdCls }} text-center"><input type="checkbox" wire:model="editing.{{ $r->id }}.active"></td>
                                <td class="{{ $tdCls }} text-center">
                                    <button wire:click="saveEdit({{ $r->id }})" class="text-[11px] text-green-700 hover:underline">💾</button>
                                    <button wire:click="cancelEdit({{ $r->id }})" class="text-[11px] text-gray-600 hover:underline ml-1">✕</button>
                                </td>
                            @else
                                <td class="{{ $tdCls }} text-[11px]">{{ $r->chuc_danh }}</td>
                                <td class="{{ $tdCls }}">{{ $r->ten }}</td>
                                <td class="{{ $tdCls }} text-center">{{ $r->nhan_tu_van ? '✓' : '—' }}</td>
                                <td class="{{ $tdCls }} text-center">{{ $r->phut_tu_van }}'</td>
                                <td class="{{ $tdCls }} text-center">{{ $r->nhan_kham_ls ? '✓' : '—' }}</td>
                                <td class="{{ $tdCls }} text-center">{{ $r->phut_kham_ls }}'</td>
                                <td class="{{ $tdCls }} text-center font-mono text-[11px]">{{ substr($r->gio_bat_dau, 0, 5) }}</td>
                                <td class="{{ $tdCls }} text-center font-mono text-[11px]">{{ substr($r->gio_ket_thuc, 0, 5) }}</td>
                                <td class="{{ $tdCls }} text-center">{{ $r->active ? '✓' : '—' }}</td>
                                <td class="{{ $tdCls }} text-center">
                                    <button wire:click="startEdit({{ $r->id }})" class="text-[11px] text-blue-700 hover:underline">Sửa</button>
                                    <button wire:click="deleteRow({{ $r->id }})" wire:confirm="Xóa '{{ $r->ten }}'?" class="text-[11px] text-red-700 hover:underline ml-1">Xóa</button>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr><td colspan="11" class="border border-gray-300 p-6 text-center text-gray-400 italic">Không có bác sĩ.</td></tr>
                    @endforelse
                </tbody>
                @break

            @case('phong')
                <thead class="sticky top-0 z-10 bg-[#f1f3f4]"><tr>
                    <th class="{{ $thCls }} w-14 text-center">ID</th>
                    <th class="{{ $thCls }}">Tên phòng</th>
                    <th class="{{ $thCls }} w-32">Kiểu phòng</th>
                    <th class="{{ $thCls }} w-24">Loại</th>
                    <th class="{{ $thCls }} w-20 text-center">Đặt TV</th>
                    <th class="{{ $thCls }} w-20 text-center">Slot max</th>
                    <th class="{{ $thCls }} w-24 text-center">Phút/khách</th>
                    <th class="{{ $thCls }} w-28">Trạng thái</th>
                    <th class="{{ $thCls }} w-28 text-center">Thao tác</th>
                </tr></thead>
                <tbody>
                    <tr class="bg-[#e8f0fe]">
                        <td class="{{ $tdCls }} text-center text-blue-600">+</td>
                        <td class="{{ $tdCls }}"><input wire:model="draft.ten" placeholder="Tên *" class="{{ $inpCls }}"></td>
                        <td class="{{ $tdCls }}">
                            <select wire:model="draft.kieu_phong" class="{{ $inpCls }}">
                                @foreach ($kieuOpts as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
                            </select>
                        </td>
                        <td class="{{ $tdCls }}">
                            <select wire:model="draft.loai" class="{{ $inpCls }}">
                                @foreach ($loaiOpts as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
                            </select>
                        </td>
                        <td class="{{ $tdCls }} text-center"><input type="checkbox" wire:model="draft.duoc_dat_tu_van"></td>
                        <td class="{{ $tdCls }}"><input type="number" wire:model="draft.so_slot_toi_da" class="{{ $inpCls }} text-center"></td>
                        <td class="{{ $tdCls }}"><input type="number" wire:model="draft.phut_moi_khach" class="{{ $inpCls }} text-center"></td>
                        <td class="{{ $tdCls }}">
                            <select wire:model="draft.trang_thai" class="{{ $inpCls }}">
                                @foreach ($ttrOpts as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
                            </select>
                        </td>
                        <td class="{{ $tdCls }} text-center"><button wire:click="addRow" class="text-[11px] font-semibold text-white bg-blue-600 hover:bg-blue-700 px-2 py-0.5 rounded">+ Thêm</button></td>
                    </tr>
                    @forelse ($rows as $r)
                        @php $e = isset($editing[$r->id]); @endphp
                        <tr class="hover:bg-[#f8f9fa]">
                            <td class="{{ $tdCls }} text-center text-gray-500">{{ $r->id }}</td>
                            @if ($e)
                                <td class="{{ $tdCls }}"><input wire:model="editing.{{ $r->id }}.ten" class="{{ $inpCls }}"></td>
                                <td class="{{ $tdCls }}">
                                    <select wire:model="editing.{{ $r->id }}.kieu_phong" class="{{ $inpCls }}">
                                        @foreach ($kieuOpts as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
                                    </select>
                                </td>
                                <td class="{{ $tdCls }}">
                                    <select wire:model="editing.{{ $r->id }}.loai" class="{{ $inpCls }}">
                                        @foreach ($loaiOpts as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
                                    </select>
                                </td>
                                <td class="{{ $tdCls }} text-center"><input type="checkbox" wire:model="editing.{{ $r->id }}.duoc_dat_tu_van"></td>
                                <td class="{{ $tdCls }}"><input type="number" wire:model="editing.{{ $r->id }}.so_slot_toi_da" class="{{ $inpCls }} text-center"></td>
                                <td class="{{ $tdCls }}"><input type="number" wire:model="editing.{{ $r->id }}.phut_moi_khach" class="{{ $inpCls }} text-center"></td>
                                <td class="{{ $tdCls }}">
                                    <select wire:model="editing.{{ $r->id }}.trang_thai" class="{{ $inpCls }}">
                                        @foreach ($ttrOpts as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
                                    </select>
                                </td>
                                <td class="{{ $tdCls }} text-center">
                                    <button wire:click="saveEdit({{ $r->id }})" class="text-[11px] text-green-700 hover:underline">💾</button>
                                    <button wire:click="cancelEdit({{ $r->id }})" class="text-[11px] text-gray-600 hover:underline ml-1">✕</button>
                                </td>
                            @else
                                <td class="{{ $tdCls }}">{{ $r->ten }}</td>
                                <td class="{{ $tdCls }} text-[11px]">{{ $kieuOpts[$r->kieu_phong] ?? $r->kieu_phong }}</td>
                                <td class="{{ $tdCls }} text-[11px]">{{ $loaiOpts[$r->loai] ?? $r->loai }}</td>
                                <td class="{{ $tdCls }} text-center">{{ $r->duoc_dat_tu_van ? '✓' : '—' }}</td>
                                <td class="{{ $tdCls }} text-center tabular-nums">{{ $r->so_slot_toi_da }}</td>
                                <td class="{{ $tdCls }} text-center tabular-nums">{{ $r->phut_moi_khach }}'</td>
                                <td class="{{ $tdCls }} text-[11px]">{{ $ttrOpts[$r->trang_thai] ?? $r->trang_thai }}</td>
                                <td class="{{ $tdCls }} text-center">
                                    <button wire:click="startEdit({{ $r->id }})" class="text-[11px] text-blue-700 hover:underline">Sửa</button>
                                    <button wire:click="deleteRow({{ $r->id }})" wire:confirm="Xóa '{{ $r->ten }}'?" class="text-[11px] text-red-700 hover:underline ml-1">Xóa</button>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr><td colspan="9" class="border border-gray-300 p-6 text-center text-gray-400 italic">Không có phòng.</td></tr>
                    @endforelse
                </tbody>
                @break

            @case('users')
                <thead class="sticky top-0 z-10 bg-[#f1f3f4]"><tr>
                    <th class="{{ $thCls }} w-14 text-center">ID</th>
                    <th class="{{ $thCls }}">Họ tên</th>
                    <th class="{{ $thCls }}">Email</th>
                    <th class="{{ $thCls }}">Username</th>
                    <th class="{{ $thCls }}">Chức danh</th>
                    <th class="{{ $thCls }} w-20 text-center">Tư vấn</th>
                    <th class="{{ $thCls }} w-20 text-center">Admin</th>
                    <th class="{{ $thCls }} w-28 text-center">Thao tác</th>
                </tr></thead>
                <tbody>
                    <tr class="bg-[#e8f0fe]">
                        <td class="{{ $tdCls }} text-center text-blue-600">+</td>
                        <td class="{{ $tdCls }}"><input wire:model="draft.name" placeholder="Họ tên *" class="{{ $inpCls }}"></td>
                        <td class="{{ $tdCls }}"><input wire:model="draft.email" type="email" placeholder="email@* " class="{{ $inpCls }}"></td>
                        <td class="{{ $tdCls }}"><input wire:model="draft.username" class="{{ $inpCls }}"></td>
                        <td class="{{ $tdCls }}"><input wire:model="draft.chuc_danh" class="{{ $inpCls }}"></td>
                        <td class="{{ $tdCls }} text-center"><input type="checkbox" wire:model="draft.is_tu_van"></td>
                        <td class="{{ $tdCls }} text-center"><input type="checkbox" wire:model="draft.is_admin"></td>
                        <td class="{{ $tdCls }} text-center"><button wire:click="addRow" class="text-[11px] font-semibold text-white bg-blue-600 hover:bg-blue-700 px-2 py-0.5 rounded">+ Thêm</button></td>
                    </tr>
                    @forelse ($rows as $r)
                        @php $e = isset($editing[$r->id]); @endphp
                        <tr class="hover:bg-[#f8f9fa]">
                            <td class="{{ $tdCls }} text-center text-gray-500">{{ $r->id }}</td>
                            @if ($e)
                                <td class="{{ $tdCls }}"><input wire:model="editing.{{ $r->id }}.name" class="{{ $inpCls }}"></td>
                                <td class="{{ $tdCls }}"><input wire:model="editing.{{ $r->id }}.email" type="email" class="{{ $inpCls }}"></td>
                                <td class="{{ $tdCls }}"><input wire:model="editing.{{ $r->id }}.username" class="{{ $inpCls }}"></td>
                                <td class="{{ $tdCls }}"><input wire:model="editing.{{ $r->id }}.chuc_danh" class="{{ $inpCls }}"></td>
                                <td class="{{ $tdCls }} text-center"><input type="checkbox" wire:model="editing.{{ $r->id }}.is_tu_van"></td>
                                <td class="{{ $tdCls }} text-center"><input type="checkbox" wire:model="editing.{{ $r->id }}.is_admin"></td>
                                <td class="{{ $tdCls }} text-center">
                                    <button wire:click="saveEdit({{ $r->id }})" class="text-[11px] text-green-700 hover:underline">💾</button>
                                    <button wire:click="cancelEdit({{ $r->id }})" class="text-[11px] text-gray-600 hover:underline ml-1">✕</button>
                                </td>
                            @else
                                <td class="{{ $tdCls }}">{{ $r->name }}</td>
                                <td class="{{ $tdCls }} text-sky-700">{{ $r->email }}</td>
                                <td class="{{ $tdCls }} font-mono text-[11px]">{{ $r->username }}</td>
                                <td class="{{ $tdCls }} text-[11px]">{{ $r->chuc_danh }}</td>
                                <td class="{{ $tdCls }} text-center">{{ $r->is_tu_van ? '✓' : '—' }}</td>
                                <td class="{{ $tdCls }} text-center">{{ $r->is_admin ? '✓' : '—' }}</td>
                                <td class="{{ $tdCls }} text-center">
                                    <button wire:click="startEdit({{ $r->id }})" class="text-[11px] text-blue-700 hover:underline">Sửa</button>
                                    <button wire:click="deleteRow({{ $r->id }})" wire:confirm="Xóa '{{ $r->name }}'?" class="text-[11px] text-red-700 hover:underline ml-1">Xóa</button>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr><td colspan="8" class="border border-gray-300 p-6 text-center text-gray-400 italic">Không có user.</td></tr>
                    @endforelse
                </tbody>
                @break

            @endswitch
        </table>
        <div class="px-3 py-2 bg-white border-t border-gray-200">{{ $rows->links() }}</div>
    </div>

    {{-- Bottom tabs --}}
    <div class="flex items-center gap-0.5 border-t border-gray-300 bg-[#f8f9fa] px-2 py-1 overflow-x-auto">
        <span class="text-[11px] text-gray-500 mr-2 shrink-0">Sheet:</span>
        @foreach ($tabs as $k => $label)
            <button wire:click="$set('tab', '{{ $k }}')"
                    class="text-[12px] px-3 py-1 rounded-t border-x border-t border-gray-300 shrink-0
                           {{ $tab === $k ? 'bg-white text-gray-900 font-semibold border-b-white -mb-px' : 'bg-[#e8eaed] text-gray-600 hover:bg-gray-200' }}">
                {{ $label }}
            </button>
        @endforeach
        <span class="ml-auto text-[11px] text-gray-500">{{ $rows->total() }} dòng · CS: {{ $coSoSlug }}</span>
    </div>
</div>
