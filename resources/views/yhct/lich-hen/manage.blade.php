<!DOCTYPE html>
<html class="light" lang="vi"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Quản lý Lịch Tư Vấn | Precision Wellness</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&amp;family=Manrope:wght@600;700;800&amp;family=JetBrains+Mono:wght@500;600&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script>tailwind.config={darkMode:"class",theme:{extend:{"colors":{"on-primary-fixed":"#131b2e","on-secondary-fixed-variant":"#004c6e","primary-fixed-dim":"#bec6e0","inverse-on-surface":"#eff1f3","secondary-fixed-dim":"#89ceff","tertiary-fixed":"#6ffbbe","secondary-container":"#39b8fd","primary-fixed":"#dae2fd","surface-container-low":"#f2f4f6","outline-variant":"#c6c6cd","on-error-container":"#93000a","outline":"#76777d","on-tertiary-container":"#009668","inverse-primary":"#bec6e0","on-background":"#191c1e","surface-container-lowest":"#ffffff","primary":"#000000","on-surface-variant":"#45464d","on-secondary-fixed":"#001e2f","surface":"#f7f9fb","error":"#ba1a1a","tertiary-container":"#002113","on-primary-container":"#7c839b","surface-container-high":"#e6e8ea","surface-bright":"#f7f9fb","on-primary":"#ffffff","on-secondary-container":"#004666","primary-container":"#131b2e","tertiary":"#000000","surface-tint":"#565e74","surface-dim":"#d8dadc","on-error":"#ffffff","on-tertiary":"#ffffff","error-container":"#ffdad6","background":"#f7f9fb","inverse-surface":"#2d3133","on-surface":"#191c1e","on-primary-fixed-variant":"#3f465c","tertiary-fixed-dim":"#4edea3","surface-container-highest":"#e0e3e5","secondary-fixed":"#c9e6ff","on-tertiary-fixed-variant":"#005236","on-tertiary-fixed":"#002113","on-secondary":"#ffffff","surface-variant":"#e0e3e5","secondary":"#006591","surface-container":"#eceef0"},"borderRadius":{"DEFAULT":"0.125rem","lg":"0.25rem","xl":"0.5rem","full":"0.75rem"},"spacing":{"gutter":"12px","container-margin":"24px","row-height-compact":"40px","unit":"4px","row-height-standard":"56px"},"fontFamily":{"headline-md":["Manrope"],"time-slot":["JetBrains Mono"],"headline-lg":["Manrope"],"body-md":["Inter"],"body-sm":["Inter"],"label-caps":["JetBrains Mono"]},"fontSize":{"headline-md":["18px",{"lineHeight":"24px","fontWeight":"600"}],"time-slot":["12px",{"lineHeight":"16px","fontWeight":"500"}],"headline-lg":["24px",{"lineHeight":"32px","fontWeight":"700"}],"body-md":["14px",{"lineHeight":"20px","fontWeight":"400"}],"body-sm":["13px",{"lineHeight":"18px","fontWeight":"400"}],"label-caps":["11px",{"lineHeight":"16px","letterSpacing":"0.05em","fontWeight":"600"}]}}}}</script>
<style>
body { font-family: 'Inter', sans-serif; background-color: #f7f9fb; }
.material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; vertical-align: middle; }
</style>
</head>
<body class="bg-surface text-on-surface">
@include('partials.topnav', ['active' => 'tu-van'])

<main class="pt-[calc(56px+24px)] pb-12 px-container-margin max-w-[1440px] mx-auto">
<header class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-8">
<div>
<h1 class="font-headline-lg text-headline-lg text-primary mb-1">Quản lý Lịch Tư Vấn</h1>
<p class="font-body-md text-body-md text-on-surface-variant">Theo dõi lịch hẹn tư vấn theo ngày & bác sĩ.</p>
</div>
<div class="flex flex-wrap gap-3">
<div class="flex bg-surface-container rounded-lg p-1">
@foreach ($danhSachCoSo as $cs)
<a href="/{{ $cs->slug }}/lich-tu-van" class="px-4 py-1.5 rounded-md font-body-sm text-body-sm transition-colors {{ $cs->id === $coSo->id ? 'bg-surface shadow-sm font-semibold text-secondary' : 'hover:bg-surface-variant/50 text-on-surface-variant' }}">{{ $cs->ten }}</a>
@endforeach
</div>
<a href="/{{ $coSo->slug }}/dat-kham" class="flex items-center gap-2 px-4 py-2 bg-primary text-on-primary rounded-xl font-body-md text-body-md font-semibold transition-transform active:scale-95">
<span class="material-symbols-outlined text-[20px]">add</span>
Tạo lịch tư vấn
</a>
</div>
</header>

<!-- Filters -->
<div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-5 mb-6 flex flex-wrap items-end gap-4">
<form method="GET" class="flex flex-wrap items-end gap-4 w-full">
<div class="flex flex-col gap-1">
<label class="text-label-caps font-label-caps text-on-surface-variant">NGÀY</label>
<input name="ngay" value="{{ $date->toDateString() }}" class="border border-outline-variant rounded-lg px-3 py-2 text-body-md focus:border-secondary focus:ring-1 focus:ring-secondary/20 outline-none bg-surface" type="date"/>
</div>
<div class="flex flex-col gap-1">
<label class="text-label-caps font-label-caps text-on-surface-variant">BÁC SĨ TƯ VẤN</label>
<select name="bac_si_id" class="border border-outline-variant rounded-lg px-3 py-2 text-body-md focus:border-secondary focus:ring-1 focus:ring-secondary/20 outline-none bg-surface min-w-[200px]">
@foreach ($bacSis as $b)
<option value="{{ $b->id }}" @selected($bs && $bs->id === $b->id)>{{ $b->ten_day_du }} ({{ $b->thoi_gian_kham }}p)</option>
@endforeach
</select>
</div>
<button type="submit" class="flex items-center gap-2 px-5 py-2 bg-primary text-on-primary rounded-lg font-semibold">
<span class="material-symbols-outlined text-[18px]">filter_list</span> Lọc
</button>
<a href="/{{ $coSo->slug }}/ds-tu-van" class="ml-auto flex items-center gap-2 px-4 py-2 text-on-surface-variant border border-outline-variant rounded-lg hover:bg-surface-variant transition-colors text-body-md font-semibold">
<span class="material-symbols-outlined text-[18px]">format_list_bulleted</span> Danh sách chi tiết
</a>
</form>
</div>

<!-- Stats -->
<div class="grid grid-cols-3 gap-4 mb-6">
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

@if ($bs)
<!-- Timeline Grid -->
<div class="bg-surface-container-lowest border border-outline-variant rounded-xl overflow-hidden">
<div class="px-5 py-3 bg-surface-container-low border-b border-outline-variant flex items-center gap-3">
<span class="material-symbols-outlined text-secondary">stethoscope</span>
<span class="font-semibold">{{ $bs->ten_day_du }}</span>
<span class="text-body-sm text-on-surface-variant">({{ $bs->thoi_gian_kham }} phút/ca · {{ $date->format('d/m/Y') }})</span>
</div>
<div class="divide-y divide-outline-variant/40">
@forelse ($grid as $item)
@php $ck = $item['slot']; $lh = $item['lichHen']; @endphp
<div class="flex items-stretch hover:bg-surface-variant/10 transition-colors">
<div class="w-36 shrink-0 flex items-center justify-center bg-surface-container-low/50 border-r border-outline-variant/40 font-time-slot text-time-slot text-on-surface-variant py-4">
{{ $ck->nhan }}
</div>
<div class="flex-1 p-4">
@if ($lh)
<div class="flex items-center justify-between">
<div>
<span class="font-semibold text-on-surface">{{ $lh->khachHang?->ho_ten }}</span>
<span class="text-body-sm text-on-surface-variant ml-2">{{ $lh->khachHang?->so_dien_thoai }}</span>
</div>
<div class="flex items-center gap-2">
<span class="text-body-sm text-on-surface-variant">{{ $lh->sale?->name }}</span>
@php $tt = $lh->trang_thai; @endphp
<span class="px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $tt === 'da_duyet' ? 'bg-tertiary-fixed-dim/40 text-on-tertiary-container' : ($tt === 'tu_choi' ? 'bg-error-container text-on-error-container' : 'bg-secondary-container/40 text-on-secondary-container') }}">
{{ $tt === 'da_duyet' ? 'Đã duyệt' : ($tt === 'tu_choi' ? 'Từ chối' : 'Chờ duyệt') }}
</span>
</div>
</div>
@if ($lh->ghi_chu)
<p class="text-body-sm text-on-surface-variant mt-1 italic">{{ $lh->ghi_chu }}</p>
@endif
@else
<div class="flex items-center gap-2 text-on-surface-variant/50">
<span class="material-symbols-outlined text-[18px]">event_available</span>
<span class="text-body-sm">Trống</span>
</div>
@endif
</div>
</div>
@empty
<div class="p-8 text-center text-on-surface-variant">Bác sĩ chưa có ca khám. Vui lòng cấu hình trong <a href="/{{ $coSo->slug }}/thiet-lap/bac-si-tu-van" class="text-secondary underline">Thiết lập</a>.</div>
@endforelse
</div>
</div>
@else
<div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-12 text-center text-on-surface-variant">
<span class="material-symbols-outlined text-[48px] opacity-40">stethoscope</span>
<p class="mt-2">Chưa có bác sĩ tư vấn. Vui lòng thêm trong <a href="/{{ $coSo->slug }}/thiet-lap/bac-si-tu-van" class="text-secondary underline">Thiết lập</a>.</p>
</div>
@endif
</main>

<a href="/{{ $coSo->slug }}/dat-kham" class="fixed bottom-8 right-8 w-14 h-14 bg-primary text-on-primary rounded-full shadow-lg hover:scale-105 active:scale-95 transition-all flex items-center justify-center z-50 group">
<span class="material-symbols-outlined text-[28px]">add</span>
<span class="absolute right-full mr-4 px-3 py-1.5 bg-inverse-surface text-inverse-on-surface text-body-sm rounded-lg opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">Thêm lịch tư vấn</span>
</a>
</body></html>
