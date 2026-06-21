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
        'thoi_gian_kham' => 20, 'gio_bat_dau' => '08:00', 'gio_ket_thuc' => '17:00'];
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

@if ($key === 'quyen')
{{-- Ma trận phân quyền sửa trường: phòng ban × trường --}}
@php $phongBans = $quyen['phongBans']; $fields = $quyen['fields']; $allowed = $quyen['allowed']; @endphp
<form method="POST" action="{{ $action }}">
@csrf
<div class="flex flex-wrap items-center justify-between gap-3 mb-4">
<p class="text-body-sm text-on-surface-variant">Tick ô để cho phép <strong>phòng ban</strong> được sửa <strong>trường</strong> tương ứng — {{ count($fields) }} trường × {{ $phongBans->count() }} phòng ban.</p>
<button type="submit" class="px-5 py-2 bg-primary text-on-primary font-semibold rounded-lg flex items-center gap-2"><span class="material-symbols-outlined text-[20px]">save</span> Lưu phân quyền</button>
</div>
@if ($phongBans->isEmpty())
<div class="p-6 bg-surface-container-lowest border border-outline-variant rounded-xl text-center text-on-surface-variant">Chưa có phòng ban nào. Hãy tạo phòng ban trước.</div>
@else
<div class="bg-surface-container-lowest border border-outline-variant rounded-xl overflow-x-auto">
<table class="w-full text-body-md border-collapse">
<thead>
<tr class="bg-surface-container-low border-b border-outline-variant">
<th class="px-4 py-3 text-left text-label-caps font-label-caps text-on-surface-variant sticky left-0 bg-surface-container-low min-w-[210px] z-10">Trường &bsol; Phòng ban</th>
@foreach ($phongBans as $pb)
<th class="px-3 py-3 text-center align-top min-w-[130px]">
<div class="font-semibold">{{ $pb->ten }}</div>
<label class="inline-flex items-center gap-1 mt-1 text-[11px] text-on-surface-variant cursor-pointer">
<input type="checkbox" onclick="document.querySelectorAll('.col-{{ $pb->id }}').forEach(c => c.checked = this.checked)" class="w-3.5 h-3.5 rounded border-outline text-secondary"/> chọn tất cả
</label>
</th>
@endforeach
</tr>
</thead>
<tbody class="divide-y divide-outline-variant/50">
@foreach ($fields as $fkey => $flabel)
@php $isApprove = str_starts_with($fkey, 'duyet_'); @endphp
<tr class="hover:bg-surface-container-low/40">
<td class="px-4 py-2.5 sticky left-0 bg-surface-container-lowest font-medium">
<span class="{{ $isApprove ? 'text-secondary font-semibold' : '' }}">{{ $flabel }}</span>
@if ($isApprove)<span class="ml-1 text-[10px] px-1.5 py-0.5 rounded bg-secondary-container/40 text-on-secondary-container uppercase">mới</span>@endif
</td>
@foreach ($phongBans as $pb)
<td class="px-3 py-2.5 text-center">
<input type="checkbox" name="allow[{{ $pb->id }}][]" value="{{ $fkey }}" @checked(in_array($fkey, $allowed->get($pb->id, []))) class="col-{{ $pb->id }} w-4 h-4 rounded border-outline text-secondary focus:ring-secondary"/>
</td>
@endforeach
</tr>
@endforeach
</tbody>
</table>
</div>
<div class="mt-4 flex justify-end">
<button type="submit" class="px-5 py-2 bg-primary text-on-primary font-semibold rounded-lg flex items-center gap-2"><span class="material-symbols-outlined text-[20px]">save</span> Lưu phân quyền</button>
</div>
@endif
</form>
@else
@if ($editable)
{{-- Form thêm mới --}}
<div x-data="{ open: false }" class="mb-5">
<button @click="open = !open" class="px-4 py-2 bg-secondary-container text-on-secondary-container font-semibold rounded-lg flex items-center gap-2 hover:opacity-90">
<span class="material-symbols-outlined text-[20px]" x-text="open ? 'close' : 'add'">add</span>
<span x-text="open ? 'Đóng' : 'Thêm mới'">Thêm mới</span>
</button>
<div x-show="open" x-cloak class="mt-3 p-4 bg-surface-container-lowest border border-outline-variant rounded-xl">
<form method="POST" action="{{ $action }}" class="flex flex-wrap items-end gap-3">
@csrf
@foreach ($config['fields'] as $fn => $ff)
<div class="flex flex-col gap-1 {{ $ff['type'] === 'toggle' ? 'justify-end pb-2' : 'min-w-[150px]' }}">
@if ($ff['type'] !== 'toggle')<label class="text-label-caps font-label-caps text-on-surface-variant">{{ $ff['label'] }}</label>@endif
@include('longevity.settings._field', ['name' => $fn, 'f' => $ff, 'value' => old($fn, $defaults[$fn] ?? '')])
</div>
@endforeach
<button type="submit" class="px-5 py-2 bg-primary text-on-primary font-semibold rounded-lg flex items-center gap-2">
<span class="material-symbols-outlined text-[20px]">save</span> Lưu
</button>
</form>
</div>
</div>
@endif

<div class="bg-surface-container-lowest border border-outline-variant rounded-xl overflow-hidden">
<div class="overflow-x-auto">
<table class="w-full text-body-md">
<thead>
<tr class="text-left text-label-caps font-label-caps uppercase text-on-surface-variant bg-surface-container-low border-b border-outline-variant">
@if ($editable)
@foreach ($cols as $fn => $ff)<th class="px-4 py-3">{{ $ff['label'] }}</th>@endforeach
@if ($key === 'phong')<th class="px-4 py-3">Khung giờ</th>@endif
@else
@switch($key)
@case('nguoi-dung')<th class="px-4 py-3">Tên</th><th class="px-4 py-3">Email</th><th class="px-4 py-3">Phòng ban</th><th class="px-4 py-3">Vai trò</th>@break
@case('co-so')<th class="px-4 py-3">Tên cơ sở</th><th class="px-4 py-3">Slug</th><th class="px-4 py-3">Địa chỉ</th>@break
@default<th class="px-4 py-3">Nội dung</th>
@endswitch
@endif
<th class="px-4 py-3 text-right">Thao tác</th>
</tr>
</thead>

@if ($editable)
@forelse ($rows as $r)
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
@php $vals = []; foreach ($config['fields'] as $fn => $ff) { $vals[$fn] = data_get($r, $fn); } if ($key === 'phong') { $vals['gio_mo'] = $gmo; $vals['gio_dong'] = $gdong; } @endphp
<form method="POST" action="{{ $action }}/{{ $r->id }}" class="flex flex-wrap items-end gap-3">
@csrf @method('PUT')
@foreach ($config['fields'] as $fn => $ff)
<div class="flex flex-col gap-1 {{ $ff['type'] === 'toggle' ? 'justify-end pb-2' : 'min-w-[150px]' }}">
@if ($ff['type'] !== 'toggle')<label class="text-label-caps font-label-caps text-on-surface-variant">{{ $ff['label'] }}</label>@endif
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
