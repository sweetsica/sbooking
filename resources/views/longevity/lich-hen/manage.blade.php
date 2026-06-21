<!DOCTYPE html>
<html class="light" lang="vi"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Quản lý Lịch Tư Vấn | Longevity Booking</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&amp;family=Manrope:wght@600;700;800&amp;family=JetBrains+Mono:wght@500;600&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script>tailwind.config={darkMode:"class",theme:{extend:{"colors":{"on-primary-fixed":"#131b2e","on-secondary-fixed-variant":"#004c6e","primary-fixed-dim":"#bec6e0","inverse-on-surface":"#eff1f3","secondary-fixed-dim":"#89ceff","tertiary-fixed":"#6ffbbe","secondary-container":"#39b8fd","primary-fixed":"#dae2fd","surface-container-low":"#f2f4f6","outline-variant":"#c6c6cd","on-error-container":"#93000a","outline":"#76777d","on-tertiary-container":"#009668","inverse-primary":"#bec6e0","on-background":"#191c1e","surface-container-lowest":"#ffffff","primary":"#000000","on-surface-variant":"#45464d","on-secondary-fixed":"#001e2f","surface":"#f7f9fb","error":"#ba1a1a","tertiary-container":"#002113","on-primary-container":"#7c839b","surface-container-high":"#e6e8ea","surface-bright":"#f7f9fb","on-primary":"#ffffff","on-secondary-container":"#004666","primary-container":"#131b2e","tertiary":"#000000","surface-tint":"#565e74","surface-dim":"#d8dadc","on-error":"#ffffff","on-tertiary":"#ffffff","error-container":"#ffdad6","background":"#f7f9fb","inverse-surface":"#2d3133","on-surface":"#191c1e","on-primary-fixed-variant":"#3f465c","tertiary-fixed-dim":"#4edea3","surface-container-highest":"#e0e3e5","secondary-fixed":"#c9e6ff","on-tertiary-fixed-variant":"#005236","on-tertiary-fixed":"#002113","on-secondary":"#ffffff","surface-variant":"#e0e3e5","secondary":"#006591","surface-container":"#eceef0"},"borderRadius":{"DEFAULT":"0.125rem","lg":"0.25rem","xl":"0.5rem","full":"0.75rem"},"spacing":{"gutter":"12px","container-margin":"24px","row-height-compact":"40px","unit":"4px","row-height-standard":"56px"},"fontFamily":{"headline-md":["Manrope"],"time-slot":["JetBrains Mono"],"headline-lg":["Manrope"],"body-md":["Inter"],"body-sm":["Inter"],"label-caps":["JetBrains Mono"]},"fontSize":{"headline-md":["18px",{"lineHeight":"24px","fontWeight":"600"}],"time-slot":["12px",{"lineHeight":"16px","fontWeight":"500"}],"headline-lg":["24px",{"lineHeight":"32px","fontWeight":"700"}],"body-md":["14px",{"lineHeight":"20px","fontWeight":"400"}],"body-sm":["13px",{"lineHeight":"18px","fontWeight":"400"}],"label-caps":["11px",{"lineHeight":"16px","letterSpacing":"0.05em","fontWeight":"600"}]}}}}</script>
<style>
body { font-family: 'Inter', sans-serif; background-color: #f7f9fb; }
.material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; display: inline-block; vertical-align: middle; }
.filled-icon { font-variation-settings: 'FILL' 1; }
.timeline-scroll::-webkit-scrollbar { height: 4px; }
.timeline-scroll::-webkit-scrollbar-track { background: transparent; }
.timeline-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
.card-hover { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
.card-hover:hover { transform: translateY(-2px); box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05); }
</style>
</head>
<body class="bg-surface text-on-surface">
@include('partials.topnav', ['active' => 'tu-van'])

<main class="pt-[calc(56px+24px)] pb-12 px-container-margin max-w-[1600px] mx-auto">
<!-- Header -->
<div class="mb-6">
<h2 class="font-headline-lg text-headline-lg text-primary mb-1">Quản lý Lịch hẹn Tư vấn</h2>
<p class="text-on-surface-variant text-body-md">Giám sát và điều phối ca khám của đội ngũ bác sĩ theo thời gian thực.</p>
</div>

<!-- Date Filter -->
<div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-5 mb-6 flex flex-wrap items-end gap-4">
<form method="GET" class="flex flex-wrap items-end gap-4 w-full">
<div class="flex flex-col gap-1">
<label class="text-label-caps font-label-caps text-on-surface-variant">NGÀY</label>
<input name="ngay" value="{{ $date->toDateString() }}" class="border border-outline-variant rounded-lg px-3 py-2 text-body-md focus:border-secondary focus:ring-1 focus:ring-secondary/20 outline-none bg-surface" type="date"/>
</div>
<div class="flex flex-col gap-1">
<label class="text-label-caps font-label-caps text-on-surface-variant">CƠ SỞ</label>
<select onchange="if(this.value)window.location.href=this.value" class="border border-outline-variant rounded-lg px-3 py-2 text-body-md focus:border-secondary focus:ring-1 focus:ring-secondary/20 outline-none bg-surface min-w-[180px]">
@foreach ($danhSachCoSo as $cs)
<option value="/{{ $cs->slug }}/lich-tu-van" @selected($cs->id === $coSo->id)>{{ $cs->ten }}</option>
@endforeach
</select>
</div>
<button type="submit" class="flex items-center gap-2 px-5 py-2 bg-primary text-on-primary rounded-lg font-semibold">
<span class="material-symbols-outlined text-[18px]">filter_list</span> Lọc
</button>
<span class="text-body-sm text-on-surface-variant ml-2">{{ $date->format('d/m/Y') }} · {{ $cards->count() }} bác sĩ</span>
<a href="/{{ $coSo->slug }}/ds-tu-van" class="ml-auto flex items-center gap-2 px-4 py-2 text-on-surface-variant border border-outline-variant rounded-lg hover:bg-surface-variant transition-colors text-body-md font-semibold">
<span class="material-symbols-outlined text-[18px]">format_list_bulleted</span> Danh sách
</a>
<a href="/{{ $coSo->slug }}/dat-kham" class="flex items-center gap-2 px-5 py-2 bg-secondary text-on-secondary rounded-lg font-semibold">
<span class="material-symbols-outlined text-[18px]">add</span> Tạo Booking
</a>
</form>
</div>

<!-- Stats summary -->
<div class="grid grid-cols-3 gap-4 mb-8">
<div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-4 text-center">
<div class="text-headline-lg font-headline-lg text-primary">{{ $stats['total'] }}</div>
<div class="text-body-sm text-on-surface-variant">Tổng lịch hẹn</div>
</div>
<div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-4 text-center">
<div class="text-headline-lg font-headline-lg text-on-tertiary-container">{{ $stats['approved'] }}</div>
<div class="text-body-sm text-on-surface-variant">Đã duyệt</div>
</div>
<div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-4 text-center">
<div class="text-headline-lg font-headline-lg text-secondary">{{ $stats['pending'] }}</div>
<div class="text-body-sm text-on-surface-variant">Chờ duyệt</div>
</div>
</div>

<!-- Grid of Doctor Cards -->
<div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
@forelse ($cards as $card)
@php $bs = $card['bs']; $active = $bs->active; @endphp
<div class="rounded-xl p-5 card-hover relative overflow-hidden {{ $active ? 'bg-surface-container-lowest border border-outline-variant' : 'bg-surface border border-outline-variant opacity-70 grayscale-[0.5]' }}">
<div class="flex flex-col sm:flex-row gap-5 mb-6">
<!-- Profile -->
<div class="flex items-start gap-4 flex-1">
<div class="relative">
<div class="w-16 h-16 rounded-2xl bg-primary-container flex items-center justify-center border-2 border-white shadow-sm">
<span class="material-symbols-outlined text-on-primary text-[28px]">stethoscope</span>
</div>
<span class="absolute -bottom-1 -right-1 w-4 h-4 {{ $active ? 'bg-tertiary-fixed-dim' : 'bg-error-container' }} border-2 border-white rounded-full"></span>
</div>
<div>
<h3 class="font-headline-md text-headline-md {{ $active ? 'text-primary' : 'text-on-surface-variant' }} leading-tight">{{ $bs->ten_day_du }}</h3>
<p class="text-on-surface-variant text-body-sm mb-2">Ca {{ $bs->thoi_gian_kham }} phút · {{ substr($bs->gio_bat_dau, 0, 5) }}–{{ substr($bs->gio_ket_thuc, 0, 5) }}</p>
@if ($active)
<span class="bg-on-tertiary-container/10 text-on-tertiary-container text-[11px] font-bold px-2 py-0.5 rounded-full flex items-center w-fit gap-1">
<span class="w-1.5 h-1.5 bg-on-tertiary-container rounded-full"></span> ĐANG LÀM VIỆC
</span>
@else
<span class="bg-error-container/30 text-on-error-container text-[11px] font-bold px-2 py-0.5 rounded-full flex items-center w-fit gap-1">
<span class="w-1.5 h-1.5 bg-error rounded-full"></span> NGHỈ PHÉP
</span>
@endif
</div>
</div>
<!-- Stats -->
<div class="flex flex-row sm:flex-col items-end justify-between gap-2 border-l border-outline-variant/30 pl-5">
<div class="text-right">
<span class="text-label-caps font-label-caps text-on-surface-variant block">TỶ LỆ LẤP ĐẦY</span>
<span class="text-headline-md font-bold {{ $card['rate'] > 0 ? 'text-secondary' : 'text-outline' }}">{{ $card['rate'] }}%</span>
</div>
<div class="text-right">
<span class="text-label-caps font-label-caps text-on-surface-variant block">ĐÃ ĐẶT</span>
<span class="text-headline-md font-bold text-on-surface">{{ $card['booked'] }}/{{ $card['total'] }}</span>
</div>
</div>
</div>
<!-- Timeline -->
<div class="mt-4">
<div class="flex items-center justify-between mb-2">
@php $first = $card['timeline']->first()['ck'] ?? null; $last = $card['timeline']->last()['ck'] ?? null; @endphp
<span class="font-label-caps text-label-caps text-outline uppercase">Lịch trình ngày {{ $date->format('d/m') }}@if($first) ({{ substr($first->gio_bat_dau, 0, 5) }} - {{ substr($last->gio_ket_thuc, 0, 5) }})@endif</span>
<div class="flex gap-3">
<div class="flex items-center gap-1.5"><div class="w-2.5 h-2.5 rounded-sm bg-surface-container-highest"></div><span class="text-[10px] text-on-surface-variant font-medium">Trống</span></div>
<div class="flex items-center gap-1.5"><div class="w-2.5 h-2.5 rounded-sm bg-secondary-container/40"></div><span class="text-[10px] text-on-surface-variant font-medium">Chờ duyệt</span></div>
<div class="flex items-center gap-1.5"><div class="w-2.5 h-2.5 rounded-sm bg-secondary-container"></div><span class="text-[10px] text-on-surface-variant font-medium">Đã duyệt</span></div>
</div>
</div>
@if ($card['total'] > 0)
<div class="flex flex-wrap gap-1">
@foreach ($card['timeline'] as $slot)
@php
$ck = $slot['ck']; $lh = $slot['lh'];
$bgClass = match($slot['state']) {
    'dang_kham' => 'bg-secondary-container',
    'co_lich'   => 'bg-secondary-container/40',
    default     => 'bg-surface-container-highest',
};
$textClass = $slot['state'] === 'trong' ? 'text-outline' : 'text-on-secondary-container';
$tip = $lh ? $lh->khachHang?->ho_ten . ' · ' . $ck->nhan . ($lh->sale ? ' · '.$lh->sale->name : '') : 'Trống · ' . $ck->nhan;
@endphp
<div class="flex-1 h-4 {{ $bgClass }} rounded-sm flex items-center justify-center text-[8px] {{ $textClass }} font-bold" title="{{ $tip }}">{{ substr($ck->gio_bat_dau, 0, 2) }}</div>
@endforeach
</div>
<div class="flex justify-between mt-1">
<span class="text-[9px] font-label-caps text-outline">{{ substr($first->gio_bat_dau, 0, 5) }}</span>
<span class="text-[9px] font-label-caps text-outline">{{ substr($last->gio_ket_thuc, 0, 5) }}</span>
</div>
@else
<div class="flex items-center gap-2 text-on-surface-variant/60 py-4">
<span class="material-symbols-outlined text-[18px]">event_busy</span>
<span class="text-body-sm">Chưa cấu hình ca khám.
<a href="/{{ $coSo->slug }}/thiet-lap/nguoi-dung" class="text-secondary underline">Thiết lập</a>
</span>
</div>
@endif
</div>
</div>
@empty
<div class="xl:col-span-2 bg-surface-container-lowest border border-outline-variant rounded-xl p-12 text-center text-on-surface-variant">
<span class="material-symbols-outlined text-[48px] opacity-40">stethoscope</span>
<p class="mt-2">Chưa có bác sĩ tư vấn. Vui lòng thêm trong <a href="/{{ $coSo->slug }}/thiet-lap/nguoi-dung" class="text-secondary underline">Thiết lập</a>.</p>
</div>
@endforelse
</div>

<!-- Footer / Legend -->
<div class="mt-8 flex flex-col sm:flex-row items-center justify-between border-t border-outline-variant pt-6 gap-gutter">
<div class="flex items-center gap-6">
<div class="flex items-center gap-2">
<span class="w-3 h-3 rounded-full bg-tertiary-fixed-dim"></span>
<span class="text-body-sm text-on-surface-variant">Đang làm việc</span>
</div>
<div class="flex items-center gap-2">
<span class="w-3 h-3 rounded-full bg-error"></span>
<span class="text-body-sm text-on-surface-variant">Nghỉ phép</span>
</div>
<div class="flex items-center gap-2">
<span class="w-3 h-3 rounded-sm bg-secondary-container"></span>
<span class="text-body-sm text-on-surface-variant">Đã duyệt khám</span>
</div>
</div>
<div class="flex items-center gap-4 text-on-surface-variant text-body-sm">
<span>Ngày {{ $date->format('d/m/Y') }}</span>
<a href="{{ url()->current() }}?ngay={{ $date->toDateString() }}" class="flex items-center gap-1 text-secondary font-bold hover:underline">
<span class="material-symbols-outlined text-[16px]">refresh</span> Làm mới dữ liệu
</a>
</div>
</div>
</main>

<a href="/{{ $coSo->slug }}/dat-kham" class="fixed bottom-8 right-8 w-14 h-14 bg-secondary text-on-secondary rounded-full shadow-lg hover:scale-105 active:scale-95 transition-all flex items-center justify-center z-50 group">
<span class="material-symbols-outlined text-[28px]">add</span>
<span class="absolute right-full mr-4 px-3 py-1.5 bg-inverse-surface text-inverse-on-surface text-body-sm rounded-lg opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">Tạo Booking</span>
</a>
</body></html>
