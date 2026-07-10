@extends('longevity.settings.layout')
@section('title', $meta[0])

@section('content')
@php
    [$ten, $icon, $mota] = $meta;
    $editable = (bool) $config;
    $action = '/'.$coSo->slug.'/thiet-lap/'.$key;
    // cột hiển thị = field không virtual
    $cols = [];
    if ($editable) {
        foreach ($config['fields'] as $fn => $ff) {
            if (empty($ff['virtual'])) $cols[$fn] = $ff;
        }
    }
    $defaults = ['active' => 1, 'loai' => 'cong_dong', 'trang_thai' => 'hoat_dong',
        'so_slot_toi_da' => 1, 'gio_mo' => '08:00', 'gio_dong' => '21:00', 'chuc_danh' => '', 'ten' => '',
        'gio_bat_dau' => '08:00', 'gio_ket_thuc' => '17:00',
        'thoi_gian_phut' => 30, 'thuoc_nhom' => 'khac', 'la_dich_vu' => 0,
        'phut_tu_van' => 30, 'phut_kham_ls' => 5,
        'kieu_phong' => 'phong_kham', 'phut_moi_khach' => 30, 'ktv_mac_dinh_id' => '',
        'co_so_id' => $coSo->id];
    $hasExtra = in_array($key, ['phong']);
    $colspan = count($cols) + ($hasExtra ? 1 : 0) + 1;
@endphp

<div class="flex items-center gap-2 text-body-sm text-on-surface-variant mb-4">
<a href="/{{ $coSo->slug }}/thiet-lap" class="hover:text-secondary">Thiết lập</a>
<span class="material-symbols-outlined text-[16px]">chevron_right</span>
<span class="text-on-surface font-semibold">{{ $ten }}</span>
</div>

<div class="flex items-start justify-between gap-4 mb-6">
<div class="flex items-center gap-3">
<div class="w-12 h-12 rounded-xl bg-secondary-container/40 text-on-secondary-container flex items-center justify-center">
<span class="material-symbols-outlined">{{ $icon }}</span>
</div>
<div>
<h2 class="text-headline-lg font-headline-lg">{{ $ten }}</h2>
<p class="text-body-sm text-on-surface-variant">{{ $mota }}</p>
</div>
</div>
</div>

@if ($key === 'bao-cao')
@include('longevity.settings.bao-cao')
@elseif ($key === 'quyen')
{{-- Ma trận phân quyền: vai trò × trường, gom theo nhóm --}}
@php $vaiTros = $quyen['vaiTros']; $groups = $quyen['groups']; $allowed = $quyen['allowed']; @endphp
<form method="POST" action="{{ $action }}">
@csrf
<div class="flex flex-wrap items-center justify-between gap-3 mb-4">
<p class="text-body-sm text-on-surface-variant">Tick ô để cấp quyền cho <strong>vai trò</strong> tương ứng — gom theo nhóm: booking, nhập/xuất, duyệt.</p>
<button type="submit" class="px-5 py-2 bg-primary text-on-primary font-semibold rounded-lg flex items-center gap-2"><span class="material-symbols-outlined text-[20px]">save</span> Lưu phân quyền</button>
</div>
@if ($vaiTros->isEmpty())
<div class="p-6 bg-surface-container-lowest border border-outline-variant rounded-xl text-center text-on-surface-variant">Chưa có vai trò nào. Hãy tạo vai trò trước.</div>
@else
<div class="bg-surface-container-lowest border border-outline-variant rounded-xl overflow-x-auto">
<table class="w-full text-body-md border-collapse">
<thead>
<tr class="bg-surface-container-low border-b border-outline-variant">
<th class="px-4 py-3 text-left text-label-caps font-label-caps text-on-surface-variant sticky left-0 bg-surface-container-low min-w-[260px] z-10">Trường &bsol; Vai trò</th>
@foreach ($vaiTros as $pb)
<th class="px-3 py-3 text-center align-bottom min-w-[130px]">
<div class="flex flex-col items-center gap-1">
<div class="font-semibold leading-tight flex items-center justify-center text-center min-h-[2.6rem]">{{ $pb->ten }}</div>
<label class="inline-flex items-center gap-1 text-[11px] text-on-surface-variant cursor-pointer whitespace-nowrap">
<input type="checkbox" onclick="document.querySelectorAll('.col-{{ $pb->id }}').forEach(c => c.checked = this.checked)" class="w-3.5 h-3.5 rounded border-outline text-secondary"/> chọn tất cả
</label>
</div>
</th>
@endforeach
</tr>
</thead>
<tbody class="divide-y divide-outline-variant/50">
@foreach ($groups as $groupName => $group)
@php $groupKey = \Illuminate\Support\Str::slug($groupName); @endphp
<tr class="bg-secondary-container/30">
<td class="px-4 py-2.5 sticky left-0 bg-secondary-container/30 font-semibold text-on-secondary-container z-10">
<div class="flex items-center gap-2">
<span class="material-symbols-outlined text-[18px]">{{ $group['icon'] }}</span>
<span class="uppercase text-label-caps font-label-caps">{{ $groupName }}</span>
</div>
</td>
@foreach ($vaiTros as $pb)
<td class="px-3 py-2.5 text-center">
<input type="checkbox" onclick="document.querySelectorAll('.grp-{{ $groupKey }}-{{ $pb->id }}').forEach(c => { c.checked = this.checked; c.dispatchEvent(new Event('change')); });" class="w-3.5 h-3.5 rounded border-outline text-secondary" title="Chọn cả nhóm cho vai trò này"/>
</td>
@endforeach
</tr>
@foreach ($group['fields'] as $fkey => $flabel)
@php
    $isApprove = str_starts_with($fkey, 'duyet_');
    $subFields = $group['sub'][$fkey] ?? [];
    $hasSub = ! empty($subFields);
@endphp
<tr class="hover:bg-surface-container-low/40">
<td class="px-4 py-2.5 pl-10 sticky left-0 bg-surface-container-lowest font-medium">
<span class="{{ $isApprove ? 'text-secondary font-semibold' : '' }}{{ $hasSub ? ' text-on-surface font-semibold' : '' }}">{{ $flabel }}</span>
@if ($isApprove)<span class="ml-1 text-[10px] px-1.5 py-0.5 rounded bg-secondary-container/40 text-on-secondary-container uppercase">duyệt</span>@endif
@if ($hasSub)<span class="ml-1 text-[10px] px-1.5 py-0.5 rounded bg-surface-container-high text-on-surface-variant uppercase">{{ count($subFields) }} trường</span>@endif
</td>
@foreach ($vaiTros as $pb)
<td class="px-3 py-2.5 text-center">
<input type="checkbox"
    name="allow[{{ $pb->id }}][]"
    value="{{ $fkey }}"
    @checked(in_array($fkey, $allowed->get($pb->id, [])))
    class="col-{{ $pb->id }} grp-{{ $groupKey }}-{{ $pb->id }} w-4 h-4 rounded border-outline text-secondary focus:ring-secondary"
    @if ($hasSub) data-master="sua" data-pbid="{{ $pb->id }}" @endif/>
</td>
@endforeach
</tr>
@foreach ($subFields as $subKey => $subLabel)
<tr class="hover:bg-surface-container-low/40 bg-surface-container-low/20">
<td class="px-4 py-2 pl-16 sticky left-0 bg-surface-container-lowest text-body-sm text-on-surface-variant">
<span class="inline-block mr-1 text-on-surface-variant/60">└</span> {{ $subLabel }}
</td>
@foreach ($vaiTros as $pb)
<td class="px-3 py-2 text-center">
<input type="checkbox"
    name="allow[{{ $pb->id }}][]"
    value="{{ $subKey }}"
    @checked(in_array($subKey, $allowed->get($pb->id, [])))
    class="col-{{ $pb->id }} grp-{{ $groupKey }}-{{ $pb->id }} sub-of-sua-{{ $pb->id }} w-4 h-4 rounded border-outline text-secondary focus:ring-secondary"/>
</td>
@endforeach
</tr>
@endforeach
@endforeach
@endforeach
</tbody>
</table>
</div>
<div class="mt-4 flex justify-end">
<button type="submit" class="px-5 py-2 bg-primary text-on-primary font-semibold rounded-lg flex items-center gap-2"><span class="material-symbols-outlined text-[20px]">save</span> Lưu phân quyền</button>
</div>
@endif
</form>

<script>
(function () {
    // Master 'Sửa booking' điều khiển các trường con: tắt → uncheck + disable.
    document.querySelectorAll('input[type="checkbox"][data-master="sua"]').forEach(function (master) {
        var pbId = master.dataset.pbid;
        var subs = document.querySelectorAll('.sub-of-sua-' + pbId);
        function sync() {
            subs.forEach(function (s) {
                if (! master.checked) { s.checked = false; s.disabled = true; }
                else { s.disabled = false; }
            });
        }
        master.addEventListener('change', sync);
        sync();
    });
})();
</script>
@else
@if ($editable)
{{-- Form thêm mới --}}
<div x-data="{ open: {{ $errors->any() ? 'true' : 'false' }} }" class="mb-5">
<div class="flex items-center gap-2 flex-wrap">
<button @click="open = !open" class="px-4 py-2 bg-secondary-container text-on-secondary-container font-semibold rounded-lg flex items-center gap-2 hover:opacity-90">
<span class="material-symbols-outlined text-[20px]" x-text="open ? 'close' : 'add'">add</span>
<span x-text="open ? 'Đóng' : 'Thêm mới'">Thêm mới</span>
</button>
@if ($key === 'nguoi-dung')
<a href="/{{ $coSo->slug }}/thiet-lap/nguoi-dung/xuat" class="px-4 py-2 bg-surface-container-high text-on-surface font-semibold rounded-lg flex items-center gap-2 hover:opacity-90" title="Xuất danh sách người dùng ra Excel (cột Mật khẩu để trống)">
<span class="material-symbols-outlined text-[20px]">download</span> Xuất dữ liệu
</a>
@endif
</div>
@php $hasRequired = collect($config['fields'])->contains(fn ($ff) => ! empty($ff['required'])); @endphp
<div x-show="open" x-cloak class="mt-3 bg-surface-container-lowest border border-outline-variant rounded-xl overflow-hidden shadow-sm">
<form method="POST" action="{{ $action }}">
@csrf
<div class="p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-4 gap-y-4">
@foreach ($config['fields'] as $fn => $ff)
@php $isReq = ! empty($ff['required']); @endphp
<div class="flex flex-col gap-1.5 {{ $ff['type'] === 'toggle' ? 'sm:col-span-2 lg:col-span-3 justify-end' : '' }}">
@if ($ff['type'] !== 'toggle')
<label class="text-label-caps font-label-caps {{ $isReq ? 'text-red-600' : 'text-on-surface-variant' }}">{{ $ff['label'] }}@if ($isReq)<span class="text-red-500 ml-0.5">*</span>@endif</label>
@endif
@include('longevity.settings._field', ['name' => $fn, 'f' => $ff, 'value' => old($fn, $defaults[$fn] ?? '')])
@if (! empty($ff['hint']))<span class="text-[11px] text-on-surface-variant/70">{{ $ff['hint'] }}</span>@endif
</div>
@endforeach
</div>
<div class="px-5 py-3 bg-surface-container-low/50 border-t border-outline-variant flex items-center justify-between gap-3">
@if ($hasRequired)
<p class="text-body-sm text-on-surface-variant flex items-center gap-1"><span class="text-red-500 font-bold">*</span> Trường bắt buộc nhập</p>
@else
<span></span>
@endif
<button type="submit" class="px-5 py-2 bg-primary text-on-primary font-semibold rounded-lg flex items-center gap-2 hover:opacity-90">
<span class="material-symbols-outlined text-[20px]">save</span> Lưu
</button>
</div>
</form>
</div>
</div>
@endif

@if ($key === 'nguoi-dung' && $userFilters)
{{-- Form lọc người dùng --}}
<form method="GET" action="{{ $action }}" class="mb-4 bg-surface-container-lowest border border-outline-variant rounded-xl p-4">
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
<div class="flex flex-col gap-1">
<label class="text-label-caps font-label-caps text-on-surface-variant">Tên</label>
<input type="text" name="q" value="{{ $userFilters['current']['q'] ?? '' }}" placeholder="Tìm theo họ tên..."
class="px-3 py-2 bg-surface-container-low border border-outline-variant rounded-lg focus:outline-none focus:border-secondary"/>
</div>
<div class="flex flex-col gap-1">
<label class="text-label-caps font-label-caps text-on-surface-variant">Vai trò</label>
<select name="vai_tro_id" class="px-3 py-2 bg-surface-container-low border border-outline-variant rounded-lg focus:outline-none focus:border-secondary">
<option value="">— Tất cả —</option>
@foreach ($userFilters['vaiTros'] as $vt)
<option value="{{ $vt->id }}" @selected(($userFilters['current']['vai_tro_id'] ?? '') == $vt->id)>{{ $vt->ten }}</option>
@endforeach
</select>
</div>
<div class="flex flex-col gap-1">
<label class="text-label-caps font-label-caps text-on-surface-variant">Chức danh</label>
<select name="chuc_danh" class="px-3 py-2 bg-surface-container-low border border-outline-variant rounded-lg focus:outline-none focus:border-secondary">
<option value="">— Tất cả —</option>
@foreach ($userFilters['chucDanhs'] as $cd)
<option value="{{ $cd }}" @selected(($userFilters['current']['chuc_danh'] ?? '') === $cd)>{{ $cd }}</option>
@endforeach
</select>
</div>
<div class="flex flex-col gap-1">
<label class="text-label-caps font-label-caps text-on-surface-variant">Phòng ban</label>
<select name="phong_ban_id" class="px-3 py-2 bg-surface-container-low border border-outline-variant rounded-lg focus:outline-none focus:border-secondary">
<option value="">— Tất cả —</option>
@foreach ($userFilters['phongBans'] as $pb)
<option value="{{ $pb->id }}" @selected(($userFilters['current']['phong_ban_id'] ?? '') == $pb->id)>{{ $pb->ten }}</option>
@endforeach
</select>
</div>
</div>
<div class="flex items-center gap-2 mt-3">
<button type="submit" class="px-4 py-2 bg-primary text-on-primary font-semibold rounded-lg flex items-center gap-2 hover:opacity-90">
<span class="material-symbols-outlined text-[20px]">search</span> Lọc
</button>
<a href="{{ $action }}" class="px-4 py-2 text-on-surface-variant hover:bg-surface-container-high rounded-lg">Xóa lọc</a>
<span class="text-body-sm text-on-surface-variant ml-auto">{{ $rows->count() }} người dùng</span>
</div>
</form>
@endif

<div class="bg-surface-container-lowest border border-outline-variant rounded-xl overflow-hidden">
<div class="overflow-x-auto">
<table class="w-full min-w-[640px] text-body-md">
<thead>
<tr class="text-left text-label-caps font-label-caps uppercase text-on-surface-variant bg-surface-container-low border-b border-outline-variant">
@if ($editable)
@foreach ($cols as $fn => $ff)<th class="px-4 py-3 whitespace-nowrap">{{ $ff['label'] }}</th>@endforeach
@if ($key === 'phong')<th class="px-4 py-3 whitespace-nowrap">Khung giờ</th>@endif
@else
@switch($key)
@case('nguoi-dung')<th class="px-4 py-3 whitespace-nowrap">Tên</th><th class="px-4 py-3 whitespace-nowrap">Email</th><th class="px-4 py-3 whitespace-nowrap">Phòng ban</th><th class="px-4 py-3 whitespace-nowrap">Vai trò</th>@break
@case('co-so')<th class="px-4 py-3 whitespace-nowrap">Tên cơ sở</th><th class="px-4 py-3 whitespace-nowrap">Slug</th><th class="px-4 py-3 whitespace-nowrap">Địa chỉ</th>@break
@default<th class="px-4 py-3 whitespace-nowrap">Nội dung</th>
@endswitch
@endif
<th class="px-4 py-3 text-right whitespace-nowrap">Thao tác</th>
</tr>
</thead>

@if ($editable)
@php $ndGroup = null; @endphp
@forelse ($rows as $r)
@if ($key === 'nguoi-dung')
@php $g = is_null($r->co_so_id) ? 'he_thong' : 'co_so'; @endphp
@if ($g !== $ndGroup)
@php $ndGroup = $g; @endphp
<tbody><tr class="bg-surface-container-low border-y border-outline-variant"><td colspan="{{ $colspan }}" class="px-4 py-2 text-label-caps font-label-caps uppercase text-on-surface-variant">{{ $g === 'he_thong' ? 'Tài khoản hệ thống (mọi cơ sở)' : 'Người dùng cơ sở — '.$coSo->ten }}</td></tr></tbody>
@endif
@endif
@php
    $gmo = '08:00'; $gdong = '21:00';
    if ($key === 'phong') {
        if ($f0 = $r->khungGios->first()) $gmo = substr($f0->gio_bat_dau, 0, 5);
        if ($l0 = $r->khungGios->last()) $gdong = substr($l0->gio_ket_thuc, 0, 5);
    }
@endphp
<tbody x-data="{ edit: false }" class="border-b border-outline-variant/40">
<tr x-show="!edit" class="hover:bg-surface-container-low/50">
@foreach ($cols as $fn => $ff)
<td class="px-4 py-3">
@switch($ff['type'])
@case('toggle')
<span class="px-2 py-0.5 rounded-full text-label-caps font-label-caps {{ $r->$fn ? 'bg-tertiary-fixed-dim/40 text-on-tertiary-container' : 'bg-surface-container-high text-on-surface-variant' }}">{{ $r->$fn ? 'Bật' : 'Tắt' }}</span>
@break
@case('select')
{{ $ff['options'][$r->$fn] ?? $r->$fn }}
@break
@default
<span class="{{ $fn === 'ten' ? 'font-semibold' : '' }}">{{ $r->$fn }}</span>
@endswitch
</td>
@endforeach
@if ($key === 'phong')<td class="px-4 py-3 text-body-sm text-on-surface-variant">{{ $r->khungGios->count() }} khung (1 tiếng)</td>@endif
<td class="px-4 py-3">
<div class="flex items-center justify-end gap-1">
<button type="button" @click="edit = true" class="p-1.5 text-on-surface-variant hover:text-secondary hover:bg-secondary/10 rounded-lg" title="Sửa"><span class="material-symbols-outlined text-[18px]">edit</span></button>
<form method="POST" action="{{ $action }}/{{ $r->id }}" onsubmit="return confirm('Xóa mục này?')">
@csrf @method('DELETE')
<button class="p-1.5 text-on-surface-variant hover:text-error hover:bg-error/10 rounded-lg" title="Xóa"><span class="material-symbols-outlined text-[18px]">delete</span></button>
</form>
</div>
</td>
</tr>
<tr x-show="edit" x-cloak>
<td colspan="{{ $colspan }}" class="px-4 py-4 bg-surface-container-low/50">
@php $vals = []; foreach ($config['fields'] as $fn => $ff) { $vals[$fn] = data_get($r, $fn); } if ($key === 'phong') { $vals['gio_mo'] = $gmo; $vals['gio_dong'] = $gdong; $vals['bac_si_ids'] = $r->bacSis->pluck('id')->all(); } @endphp
<form method="POST" action="{{ $action }}/{{ $r->id }}" class="flex flex-wrap items-end gap-3">
@csrf @method('PUT')
@foreach ($config['fields'] as $fn => $ff)
@php $isReq = ! empty($ff['required']); @endphp
<div class="flex flex-col gap-1 {{ $ff['type'] === 'toggle' ? 'justify-end pb-2' : 'min-w-[150px]' }}">
@if ($ff['type'] !== 'toggle')<label class="text-label-caps font-label-caps {{ $isReq ? 'text-red-600' : 'text-on-surface-variant' }}">{{ $ff['label'] }}@if ($isReq)<span class="text-red-500 ml-0.5">*</span>@endif</label>@endif
@include('longevity.settings._field', ['name' => $fn, 'f' => $ff, 'value' => old($fn, $vals[$fn])])
</div>
@endforeach
<button type="submit" class="px-5 py-2 bg-primary text-on-primary font-semibold rounded-lg flex items-center gap-2"><span class="material-symbols-outlined text-[20px]">save</span> Cập nhật</button>
<button type="button" @click="edit = false" class="px-4 py-2 text-on-surface-variant hover:bg-surface-container-high rounded-lg">Hủy</button>
</form>
</td>
</tr>
</tbody>
@empty
<tbody><tr><td colspan="{{ $colspan }}" class="px-4 py-10 text-center text-on-surface-variant">Chưa có dữ liệu. Bấm "Thêm mới" để tạo.</td></tr></tbody>
@endforelse

@else
{{-- Read-only (chưa có CRUD ở Phase 2) --}}
<tbody class="divide-y divide-outline-variant/60">
@forelse ($rows as $r)
<tr class="hover:bg-surface-container-low/50">
@switch($key)
@case('nguoi-dung')
<td class="px-4 py-3 font-semibold">{{ $r->name }}</td>
<td class="px-4 py-3 text-on-surface-variant">{{ $r->email }}</td>
<td class="px-4 py-3">{{ $r->phongBan?->ten ?? '—' }}</td>
<td class="px-4 py-3">{{ $r->vaiTro?->ten ?? '—' }}</td>
@break
@case('co-so')
<td class="px-4 py-3 font-semibold">{{ $r->ten }}</td>
<td class="px-4 py-3 font-label-caps text-secondary">/{{ $r->slug }}</td>
<td class="px-4 py-3 text-on-surface-variant">{{ $r->dia_chi }}</td>
@break
@endswitch
<td class="px-4 py-3 text-right"><div class="inline-flex items-center gap-1 text-on-surface-variant opacity-40"><span class="material-symbols-outlined text-[18px]">lock</span></div></td>
</tr>
@empty
<tr><td colspan="4" class="px-4 py-10 text-center text-on-surface-variant">
@if ($key === 'quyen')
Phân quyền (Xem / Sửa tất cả / Sửa từng trường / Xóa theo phòng ban) sẽ được xây dựng ở phase sau.
@else
Chưa có dữ liệu.
@endif
</td></tr>
@endforelse
</tbody>
@endif
</table>
</div>
</div>

@if (! $editable && $key !== 'quyen')
<p class="mt-3 text-body-sm text-on-surface-variant flex items-center gap-2"><span class="material-symbols-outlined text-[18px]">lock</span> Mục này sẽ có thao tác Thêm/Sửa/Xóa ở phase sau.</p>
@endif
@endif
@endsection
