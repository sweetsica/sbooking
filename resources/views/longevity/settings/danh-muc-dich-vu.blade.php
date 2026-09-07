@extends('longevity.settings.layout')
@section('title', 'Danh mục dịch vụ (toàn hệ thống)')

@section('content')
@php
    $nhomLabel = ['tu_van' => 'Tư vấn', 'kham_ls' => 'Khám lâm sàng', 'khac' => 'Dịch vụ'];
    $nhomBadge = [
        'tu_van'  => 'bg-amber-100 text-amber-800 ring-1 ring-amber-200',
        'kham_ls' => 'bg-blue-100 text-blue-800 ring-1 ring-blue-200',
        'khac'    => 'bg-emerald-100 text-emerald-800 ring-1 ring-emerald-200',
    ];
@endphp

<div class="flex items-center gap-2 text-body-sm text-on-surface-variant mb-4">
<a href="/{{ $coSo->slug }}/thiet-lap" class="hover:text-secondary">Thiết lập</a>
<span class="material-symbols-outlined text-[16px]">chevron_right</span>
<span class="text-on-surface font-semibold">Danh mục dịch vụ</span>
</div>

<div class="flex items-start gap-3 mb-6">
<div class="w-12 h-12 rounded-xl bg-secondary-container/40 text-on-secondary-container flex items-center justify-center">
<span class="material-symbols-outlined">list_alt</span>
</div>
<div>
<h2 class="text-headline-lg font-headline-lg">Danh mục dịch vụ (toàn hệ thống)</h2>
<p class="text-body-sm text-on-surface-variant">Tra cứu nhanh: dịch vụ nào ở cơ sở nào, gắn với phòng nào, ai thực hiện. Đọc trực tiếp từ DB — thay đổi ở Thiết lập là hiện ở đây.</p>
<p class="text-body-sm text-on-surface-variant mt-1"><strong>{{ number_format($rows->count()) }}</strong> dịch vụ · <strong>{{ $rows->where('active', true)->count() }}</strong> đang hoạt động</p>
</div>
</div>

<div class="bg-surface-container-lowest border border-outline-variant rounded-xl overflow-hidden">
<div class="overflow-x-auto">
<table class="w-full min-w-[1100px] text-body-sm">
<thead>
<tr class="text-left text-label-caps font-label-caps uppercase text-on-surface-variant bg-surface-container-low border-b border-outline-variant">
<th class="px-3 py-3">ID</th>
<th class="px-3 py-3">Cơ sở</th>
<th class="px-3 py-3">Tên</th>
<th class="px-3 py-3 whitespace-nowrap">Thời gian</th>
<th class="px-3 py-3">Nhóm</th>
<th class="px-3 py-3 text-center whitespace-nowrap">Là dịch vụ?</th>
<th class="px-3 py-3">Phòng thực hiện</th>
<th class="px-3 py-3">Nhân sự thực hiện</th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant/60">
@forelse ($rows as $d)
<tr class="hover:bg-surface-container-low/40 {{ ! $d->active ? 'opacity-50' : '' }}">
<td class="px-3 py-2.5 font-mono">{{ $d->id }}</td>
<td class="px-3 py-2.5 font-semibold uppercase">{{ $d->coSo?->slug ?? '—' }}</td>
<td class="px-3 py-2.5 font-semibold">{{ $d->ten }}</td>
<td class="px-3 py-2.5 whitespace-nowrap">{{ $d->thoi_gian_phut }}'</td>
<td class="px-3 py-2.5">
<span class="px-2 py-0.5 rounded text-label-caps font-label-caps {{ $nhomBadge[$d->thuoc_nhom] ?? 'bg-slate-100 text-slate-700' }}">{{ $nhomLabel[$d->thuoc_nhom] ?? $d->thuoc_nhom }}</span>
</td>
<td class="px-3 py-2.5 text-center">{{ $d->la_dich_vu ? '1' : '' }}</td>
<td class="px-3 py-2.5 text-on-surface-variant">{{ $d->phongs->pluck('ten')->join(', ') ?: '—' }}</td>
<td class="px-3 py-2.5 text-on-surface-variant">{{ $d->bacSis->pluck('ten')->join(', ') ?: '—' }}</td>
</tr>
@empty
<tr><td colspan="8" class="px-4 py-10 text-center text-on-surface-variant">Chưa có dịch vụ nào.</td></tr>
@endforelse
</tbody>
</table>
</div>
</div>
@endsection
