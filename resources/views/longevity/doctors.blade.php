<!DOCTYPE html>
<html class="light" lang="vi"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Bác sĩ | Longevity Booking</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&amp;family=Manrope:wght@600;700;800&amp;family=JetBrains+Mono:wght@500;600&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script>tailwind.config={darkMode:"class",theme:{extend:{"colors":{"on-primary-fixed":"#131b2e","on-secondary-fixed-variant":"#004c6e","primary-fixed-dim":"#bec6e0","inverse-on-surface":"#eff1f3","secondary-fixed-dim":"#89ceff","tertiary-fixed":"#6ffbbe","secondary-container":"#39b8fd","primary-fixed":"#dae2fd","surface-container-low":"#f2f4f6","outline-variant":"#c6c6cd","on-error-container":"#93000a","outline":"#76777d","on-tertiary-container":"#009668","inverse-primary":"#bec6e0","on-background":"#191c1e","surface-container-lowest":"#ffffff","primary":"#000000","on-surface-variant":"#45464d","on-secondary-fixed":"#001e2f","surface":"#f7f9fb","error":"#ba1a1a","tertiary-container":"#002113","on-primary-container":"#7c839b","surface-container-high":"#e6e8ea","surface-bright":"#f7f9fb","on-primary":"#ffffff","on-secondary-container":"#004666","primary-container":"#131b2e","tertiary":"#000000","surface-tint":"#565e74","surface-dim":"#d8dadc","on-error":"#ffffff","on-tertiary":"#ffffff","error-container":"#ffdad6","background":"#f7f9fb","inverse-surface":"#2d3133","on-surface":"#191c1e","on-primary-fixed-variant":"#3f465c","tertiary-fixed-dim":"#4edea3","surface-container-highest":"#e0e3e5","secondary-fixed":"#c9e6ff","on-tertiary-fixed-variant":"#005236","on-tertiary-fixed":"#002113","on-secondary":"#ffffff","surface-variant":"#e0e3e5","secondary":"#006591","surface-container":"#eceef0"},"borderRadius":{"DEFAULT":"0.125rem","lg":"0.25rem","xl":"0.5rem","full":"0.75rem"},"spacing":{"gutter":"12px","container-margin":"24px","row-height-compact":"40px","unit":"4px","row-height-standard":"56px"},"fontFamily":{"headline-md":["Manrope"],"time-slot":["JetBrains Mono"],"headline-lg":["Manrope"],"body-md":["Inter"],"body-sm":["Inter"],"label-caps":["JetBrains Mono"]},"fontSize":{"headline-md":["18px",{"lineHeight":"24px","fontWeight":"600"}],"time-slot":["12px",{"lineHeight":"16px","fontWeight":"500"}],"headline-lg":["24px",{"lineHeight":"32px","fontWeight":"700"}],"body-md":["14px",{"lineHeight":"20px","fontWeight":"400"}],"body-sm":["13px",{"lineHeight":"18px","fontWeight":"400"}],"label-caps":["11px",{"lineHeight":"16px","letterSpacing":"0.05em","fontWeight":"600"}]}}}}</script>
<style>
body { font-family: 'Inter', sans-serif; background-color: #f7f9fb; }
.material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; display: inline-block; vertical-align: middle; }
.card-hover { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
.card-hover:hover { transform: translateY(-2px); box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05); }
</style>
</head>
<body class="bg-surface text-on-surface">
@include('partials.topnav', ['active' => 'bac-si'])

<main class="pt-[calc(56px+24px)] pb-12 px-container-margin max-w-[1650px] mx-auto">
<!-- Header -->
<div class="mb-6">
<h2 class="font-headline-lg text-headline-lg text-primary mb-1">Lịch theo Bác sĩ</h2>
<p class="text-on-surface-variant text-body-md">Lịch làm việc của từng bác sĩ, tổng hợp từ các lịch đặt phòng trong ngày.</p>
</div>

<!-- Date Filter (đồng bộ với trang Lịch biểu) -->
<form method="GET" class="flex flex-wrap items-end gap-4 mb-8 bg-surface-container-lowest p-6 rounded-xl border border-outline-variant shadow-sm">
<div class="space-y-1.5">
<label class="text-label-caps text-on-surface-variant block">NGÀY</label>
<input name="ngay" value="{{ $date->format('Y-m-d') }}" class="form-input border-outline-variant rounded-lg bg-surface focus:ring-secondary focus:border-secondary text-body-md" type="date"/>
</div>
<div class="space-y-1.5 flex-1 min-w-[240px]">
<label class="text-label-caps text-on-surface-variant block">CƠ SỞ</label>
<select onchange="if(this.value)window.location.href=this.value" class="form-select w-full border-outline-variant rounded-lg bg-surface focus:ring-secondary focus:border-secondary text-body-md">
@foreach ($danhSachCoSo as $cs)
<option value="/{{ $cs->slug }}/bac-si" @selected($cs->id === $coSo->id)>{{ $cs->ten }}</option>
@endforeach
</select>
</div>
<div class="flex items-center gap-2 ml-auto">
<button type="submit" class="h-[42px] px-5 text-on-surface-variant border border-outline-variant rounded-lg flex items-center gap-2 hover:bg-surface-container-low transition-colors">
<span class="material-symbols-outlined text-[20px]">filter_list</span>
<span>Lọc</span>
</button>
<a href="/{{ $coSo->slug }}/tao-moi" class="h-[42px] px-6 bg-primary text-on-primary font-semibold rounded-lg flex items-center gap-2 hover:opacity-90 transition-opacity">
<span class="material-symbols-outlined text-[20px]">add</span>
<span>Tạo Booking</span>
</a>
</div>
</form>

<!-- Stats summary -->
<div class="grid grid-cols-3 gap-4 mb-8">
<div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-4 text-center">
<div class="text-headline-lg font-headline-lg text-primary">{{ $stats['total'] }}</div>
<div class="text-body-sm text-on-surface-variant">Tổng lịch hẹn</div>
</div>
<div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-4 text-center">
<div class="text-headline-lg font-headline-lg text-on-tertiary-container">{{ $stats['approved'] }}</div>
<div class="text-body-sm text-on-surface-variant">Đã duyệt / xong</div>
</div>
<div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-4 text-center">
<div class="text-headline-lg font-headline-lg text-secondary">{{ $stats['pending'] }}</div>
<div class="text-body-sm text-on-surface-variant">Chờ duyệt</div>
</div>
</div>

@if ($unassigned->total() > 0)
<!-- Lịch chưa gán bác sĩ -->
<div class="bg-surface-container-lowest border-2 border-dashed border-amber-300 rounded-xl p-5 mb-6">
<div class="flex items-center gap-2 mb-3">
<span class="material-symbols-outlined text-amber-500">help</span>
<h3 class="font-headline-md text-headline-md text-on-surface">Chưa phân bác sĩ <span class="text-body-sm font-normal text-on-surface-variant">({{ $unassigned->total() }} lịch)</span></h3>
</div>
<div class="space-y-2">
@foreach ($unassigned as $b)
@php
    $done = $b->trang_thai === 'da_xong';
    $badge = $done ? ['Đã xong', 'bg-primary/10 text-primary']
        : ($b->trang_thai === 'da_duyet' ? ['Đã duyệt', 'bg-tertiary-fixed-dim/40 text-on-tertiary-container']
        : ['Chờ duyệt', 'bg-amber-100 text-amber-800']);
    $isTV = $b->co_tu_van;
    $isTK = $b->co_kham_cls;
    $border = $isTV ? 'border-l-emerald-500' : ($isTK ? 'border-l-sky-500' : 'border-l-outline-variant');
    $bg = $isTV ? 'bg-emerald-50' : ($isTK ? 'bg-sky-50' : 'bg-surface');
@endphp
<a href="/{{ $coSo->slug }}/xem-dat-phong/{{ $b->id }}" class="flex items-center gap-3 p-3 rounded-lg border border-outline-variant/60 border-l-4 {{ $border }} {{ $bg }} hover:shadow-sm transition-all">
<div class="font-time-slot text-body-sm text-on-surface-variant w-24 shrink-0">
{{ $b->gio_thuc_hien ? substr($b->gio_thuc_hien,0,5) . ($b->gio_ket_thuc ? '–'.substr($b->gio_ket_thuc,0,5) : '') : ($b->khungGio?->nhan ?? '—') }}
</div>
<div class="flex-1 min-w-0">
<div class="font-semibold text-on-surface truncate">{{ $b->khachHang?->ho_ten ?? '—' }}</div>
<div class="text-body-sm text-on-surface-variant truncate">{{ $b->phong?->ten ?? '—' }} · {{ $b->dichVu?->ten ?? '—' }}</div>
</div>
<div class="flex items-center gap-1.5 shrink-0">
@if ($isTV)<span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700">Tư vấn</span>@endif
@if ($isTK)<span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-sky-100 text-sky-700">Thăm khám</span>@endif
<span class="px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $badge[1] }}">{{ $badge[0] }}</span>
</div>
</a>
@endforeach
</div>
@if ($unassigned->hasPages())
<div class="mt-3">{{ $unassigned->onEachSide(0)->links() }}</div>
@endif
</div>
@endif

<!-- Grid of Doctor Cards -->
<div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
@forelse ($cards as $card)
@php $bs = $card['bs']; @endphp
<div class="rounded-xl p-5 card-hover bg-surface-container-lowest border border-outline-variant">
<!-- Profile -->
<div class="flex items-start gap-4 mb-4">
<div class="w-14 h-14 rounded-2xl bg-primary-container flex items-center justify-center shrink-0">
<span class="material-symbols-outlined text-on-primary text-[26px]">stethoscope</span>
</div>
<div class="flex-1">
<h3 class="font-headline-md text-headline-md text-primary leading-tight">{{ $bs->ten_day_du }}</h3>
<p class="text-on-surface-variant text-body-sm">{{ $bs->phongBan?->ten ?? 'Bác sĩ' }}</p>
</div>
<div class="text-right">
<span class="text-label-caps font-label-caps text-on-surface-variant block">ĐÃ ĐẶT</span>
<span class="text-headline-md font-bold text-on-surface">{{ $card['total'] }}</span>
</div>
</div>

<!-- Bookings list (5 gần nhất / trang) -->
@if ($card['total'] > 0)
<div class="space-y-2">
@foreach ($card['items'] as $b)
@php
    $done = $b->trang_thai === 'da_xong';
    $badge = $done ? ['Đã xong', 'bg-primary/10 text-primary']
        : ($b->trang_thai === 'da_duyet' ? ['Đã duyệt', 'bg-tertiary-fixed-dim/40 text-on-tertiary-container']
        : ['Chờ duyệt', 'bg-amber-100 text-amber-800']);
    $isTV = $b->co_tu_van;
    $isTK = $b->co_kham_cls;
    $border = $isTV ? 'border-l-emerald-500' : ($isTK ? 'border-l-sky-500' : 'border-l-outline-variant');
    $bg = $isTV ? 'bg-emerald-50' : ($isTK ? 'bg-sky-50' : 'bg-surface');
@endphp
<a href="/{{ $coSo->slug }}/xem-dat-phong/{{ $b->id }}" class="flex items-center gap-3 p-3 rounded-lg border border-outline-variant/60 border-l-4 {{ $border }} {{ $bg }} hover:shadow-sm transition-all">
<div class="font-time-slot text-body-sm text-on-surface-variant w-24 shrink-0">
{{ $b->gio_thuc_hien ? substr($b->gio_thuc_hien,0,5) . ($b->gio_ket_thuc ? '–'.substr($b->gio_ket_thuc,0,5) : '') : ($b->khungGio?->nhan ?? '—') }}
</div>
<div class="flex-1 min-w-0">
<div class="font-semibold text-on-surface truncate">{{ $b->khachHang?->ho_ten ?? '—' }}</div>
<div class="text-body-sm text-on-surface-variant truncate">{{ $b->phong?->ten ?? '—' }} · {{ $b->dichVu?->ten ?? '—' }}</div>
</div>
<div class="flex items-center gap-1.5 shrink-0">
@if ($isTV)<span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700">Tư vấn</span>@endif
@if ($isTK)<span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-sky-100 text-sky-700">Thăm khám</span>@endif
<span class="px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $badge[1] }}">{{ $badge[0] }}</span>
</div>
</a>
@endforeach
</div>
@if ($card['items']->hasPages())
<div class="mt-3">{{ $card['items']->onEachSide(0)->links() }}</div>
@endif
@else
<div class="flex items-center gap-2 text-on-surface-variant/60 py-6 justify-center">
<span class="material-symbols-outlined text-[18px]">event_busy</span>
<span class="text-body-sm">Chưa có lịch đặt phòng.</span>
</div>
@endif
</div>
@empty
<div class="xl:col-span-2 bg-surface-container-lowest border border-outline-variant rounded-xl p-12 text-center text-on-surface-variant">
<span class="material-symbols-outlined text-[48px] opacity-40">stethoscope</span>
<p class="mt-2">Chưa có bác sĩ nào. Vui lòng thêm trong <a href="/{{ $coSo->slug }}/thiet-lap/nguoi-dung" class="text-secondary underline">Thiết lập</a>.</p>
</div>
@endforelse
</div>
</main>
@include('partials.datepicker')
</body></html>
