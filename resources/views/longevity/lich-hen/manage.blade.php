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
.material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; vertical-align: middle; }
.custom-scroll::-webkit-scrollbar { width: 10px; height: 10px; }
.custom-scroll::-webkit-scrollbar-track { background: #f2f4f6; border-radius: 5px; }
.custom-scroll::-webkit-scrollbar-thumb { background: #c6c6cd; border-radius: 5px; }
.custom-scroll::-webkit-scrollbar-thumb:hover { background: #9ca3af; }
.custom-scroll::-webkit-scrollbar-corner { background: #f2f4f6; }
</style>
</head>
<body class="bg-surface font-body-md text-on-surface">
@include('partials.topnav', ['active' => 'tu-van'])

<main class="min-h-screen pt-24 pb-32 sm:pb-12 px-container-margin">
<div class="max-w-[1600px] mx-auto">
{{-- Header with View Switcher --}}
<div class="flex flex-wrap justify-between items-center mb-8 gap-4">
<h2 class="text-headline-lg font-headline-lg font-extrabold text-on-surface uppercase tracking-tight">Quản lý Đặt lịch Bác sĩ</h2>
<div class="flex flex-wrap items-center gap-2 sm:gap-3 w-full sm:w-auto">
<div class="flex bg-surface-container-low rounded-xl p-1 flex-1 sm:flex-none">
<a href="?{{ http_build_query(array_merge(request()->query(), ['loai' => 'tu_van'])) }}" class="flex-1 sm:flex-none text-center whitespace-nowrap px-5 py-1.5 rounded-lg text-body-md font-semibold transition-all inline-block {{ ($loai ?? 'tu_van') === 'tu_van' ? 'bg-surface-container-lowest shadow-sm text-secondary' : 'text-on-surface-variant hover:text-on-surface' }}">BS Tư vấn</a>
<a href="?{{ http_build_query(array_merge(request()->query(), ['loai' => 'tham_kham'])) }}" class="flex-1 sm:flex-none text-center whitespace-nowrap px-5 py-1.5 rounded-lg text-body-md font-semibold transition-all inline-block {{ ($loai ?? '') === 'tham_kham' ? 'bg-surface-container-lowest shadow-sm text-secondary' : 'text-on-surface-variant hover:text-on-surface' }}">BS Thăm khám</a>
</div>
<div class="flex bg-surface-container-low rounded-xl p-1 flex-1 sm:flex-none">
<button class="flex-1 sm:flex-none text-center whitespace-nowrap px-5 py-1.5 rounded-lg text-body-md font-semibold transition-all bg-surface-container-lowest shadow-sm text-secondary">Timeline</button>
<a href="/{{ $coSo->slug }}/ds-tu-van" class="flex-1 sm:flex-none text-center whitespace-nowrap px-5 py-1.5 rounded-lg text-body-md font-semibold transition-all text-on-surface-variant hover:text-on-surface inline-block">Danh sách</a>
</div>
</div>
</div>

{{-- Filters Bar --}}
<form method="GET" class="grid grid-cols-2 gap-4 mb-8 sm:flex sm:flex-wrap sm:items-end bg-surface-container-lowest p-6 rounded-xl border border-outline-variant shadow-sm">
<div class="space-y-1.5">
<label class="text-label-caps text-on-surface-variant block">NGÀY</label>
<input name="ngay" value="{{ $date->format('Y-m-d') }}" class="form-input h-[42px] w-full sm:w-auto border-outline-variant rounded-lg bg-surface focus:ring-secondary focus:border-secondary text-body-md" type="date"/>
</div>
<button type="submit" class="h-[42px] w-full sm:w-auto px-5 text-on-surface-variant border border-outline-variant rounded-lg flex items-center justify-center gap-2 hover:bg-surface-container-low transition-colors self-end">
<span class="material-symbols-outlined text-[20px]">filter_list</span>
<span>Xem</span>
</button>
<a href="/{{ $coSo->slug }}/dat-lich-tu-van" class="col-span-2 sm:col-auto sm:ml-auto h-[42px] w-full sm:w-auto px-6 bg-primary text-on-primary font-semibold rounded-lg flex items-center justify-center gap-2 whitespace-nowrap hover:opacity-90 transition-opacity">
<span class="material-symbols-outlined text-[20px]">add</span>
<span>Tạo Booking</span>
</a>
</form>

{{-- Timeline Grid --}}
<div class="bg-surface-container-lowest rounded-xl border border-outline-variant overflow-hidden shadow-sm">
<div class="p-4 border-b border-outline-variant bg-surface-container-low flex flex-wrap justify-between items-center gap-3">
<h3 class="font-headline-md text-on-surface">Lịch tư vấn bác sĩ — <span class="text-secondary font-bold">{{ $date->translatedFormat('l, d/m/Y') }}</span></h3>
<div class="flex items-center gap-5">
<div class="flex items-center gap-2 text-body-sm">
<span class="text-on-surface-variant">{{ $stats['total'] }} lịch ({{ $stats['approved'] }} duyệt / {{ $stats['pending'] }} chờ)</span>
</div>
<div class="flex flex-wrap gap-3">
<div class="flex items-center gap-1.5">
<div class="w-3 h-3 rounded-full bg-emerald-500"></div>
<span class="text-body-sm text-on-surface-variant">Đã duyệt</span>
</div>
<div class="flex items-center gap-1.5">
<div class="w-3 h-3 rounded-full bg-amber-400"></div>
<span class="text-body-sm text-on-surface-variant">Chờ duyệt</span>
</div>
<div class="flex items-center gap-1.5">
<div class="w-3 h-3 rounded-sm bg-surface-container-highest"></div>
<span class="text-body-sm text-on-surface-variant">Trống</span>
</div>
</div>
</div>
</div>

@if ($doctorColumns->isEmpty())
<div class="p-12 text-center text-on-surface-variant">
<span class="material-symbols-outlined text-[48px] opacity-40">stethoscope</span>
<p class="mt-2">Chưa có bác sĩ tư vấn. Vui lòng thêm trong <a href="/{{ $coSo->slug }}/thiet-lap/nguoi-dung" class="text-secondary underline">Thiết lập</a>.</p>
</div>
@else
<div class="overflow-auto max-h-[640px] custom-scroll">
<div class="min-w-max">
{{-- Header: GIỜ + các bác sĩ --}}
<div class="flex sticky top-0 z-20">
<div class="w-[64px] shrink-0 bg-surface-container-low border-b border-r border-outline-variant flex items-center justify-center sticky left-0 z-30">
<span class="text-label-caps text-on-surface-variant">GIỜ</span>
</div>
@foreach ($doctorColumns as $col)
<div class="w-[180px] shrink-0 bg-surface-container-low border-b border-r border-outline-variant py-3 px-2 text-center">
<div class="font-bold text-body-sm text-on-surface truncate" title="{{ $col['bs']->ten_day_du }}">{{ $col['bs']->ten_day_du }}</div>
<div class="text-[10px] text-on-surface-variant">{{ $col['booked'] }}/{{ $col['total'] }} ca</div>
</div>
@endforeach
</div>

{{-- Body: cột giờ + các cột bác sĩ --}}
<div class="flex relative">
{{-- Cột giờ --}}
<div class="w-[64px] shrink-0 relative border-r border-outline-variant bg-surface-container-lowest sticky left-0 z-10" style="height: {{ $bodyHeight + 1 }}px">
@foreach ($hours as $i => $h)
<div class="absolute left-0 right-0 flex items-start justify-center" style="top: {{ $i * $hourPx - 7 }}px">
<span class="text-time-slot text-on-surface-variant">{{ sprintf('%02d:00', $h) }}</span>
</div>
@endforeach
</div>

{{-- Các cột bác sĩ --}}
@foreach ($doctorColumns as $col)
<div class="w-[180px] shrink-0 relative border-r border-outline-variant" style="height: {{ $bodyHeight + 1 }}px">
{{-- Đường kẻ giờ --}}
@foreach ($hours as $i => $h)
<div class="absolute left-0 right-0 border-t border-outline-variant" style="top: {{ $i * $hourPx }}px; height: {{ $hourPx }}px"></div>
@endforeach
<div class="absolute left-0 right-0 border-t border-outline-variant" style="top: {{ $bodyHeight }}px"></div>

{{-- Các ca khám --}}
@foreach ($col['events'] as $ev)
@php
    $ck = $ev['ck'];
    $lh = $ev['lh'];
    $hasLh = $lh && $lh->trang_thai !== 'tu_choi';
    $approved = $hasLh && $lh->trang_thai === 'da_duyet';

    if (!$hasLh) {
        $cardCls = 'bg-surface-container-high/60 border-outline-variant';
        $textCls = 'text-outline';
    } elseif ($approved) {
        $cardCls = 'bg-emerald-100 border-emerald-500';
        $textCls = 'text-emerald-900';
    } else {
        $cardCls = 'bg-amber-100 border-amber-400';
        $textCls = 'text-amber-900';
    }
@endphp
<div class="absolute left-1 right-1 rounded-lg px-2 py-1 border-l-4 overflow-hidden {{ $cardCls }}"
     style="top: {{ $ev['top'] + 1 }}px; height: {{ max($ev['height'] - 2, 20) }}px;"
     title="{{ $hasLh ? $lh->khachHang?->ho_ten . ' · ' . $ck->nhan : 'Trống · ' . $ck->nhan }}">
@if ($hasLh)
<div class="flex justify-between items-start gap-1">
<span class="font-bold text-[11px] {{ $textCls }} truncate">{{ $lh->khachHang?->ho_ten }}</span>
@if ($approved)
<span class="material-symbols-outlined text-[14px] text-emerald-600 shrink-0" style="font-variation-settings: 'FILL' 1;">check_circle</span>
@else
<span class="text-[8px] bg-amber-400 text-amber-950 px-1 rounded uppercase font-bold shrink-0">Chờ</span>
@endif
</div>
<div class="text-[9px] font-time-slot {{ $textCls }} truncate">{{ substr($ck->gio_bat_dau, 0, 5) }}–{{ substr($ck->gio_ket_thuc, 0, 5) }}</div>
@if ($lh->sale && $ev['height'] > 40)
<div class="text-[9px] italic {{ $textCls }} truncate">{{ $lh->sale->name }}</div>
@endif
@else
<div class="text-[9px] font-time-slot {{ $textCls }}">{{ substr($ck->gio_bat_dau, 0, 5) }}–{{ substr($ck->gio_ket_thuc, 0, 5) }}</div>
<div class="text-[9px] {{ $textCls }}">Trống</div>
@endif
</div>
@endforeach
</div>
@endforeach

{{-- Đường kẻ đỏ: thời gian hiện tại --}}
<div id="current-time-line" class="absolute left-[64px] right-0 h-0.5 bg-error z-[15] pointer-events-none" style="display:none">
<span class="absolute -left-[62px] -top-2 w-[58px] text-right pr-1 text-[10px] font-bold text-error font-time-slot" id="current-time-label"></span>
<span class="absolute -left-1 -top-[3px] w-2 h-2 rounded-full bg-error"></span>
</div>
</div>
</div>
</div>
@endif
</div>
</div>
</main>

<a href="/{{ $coSo->slug }}/dat-lich-tu-van" class="fixed bottom-8 right-8 w-14 h-14 bg-primary text-on-primary rounded-full shadow-lg hover:scale-105 active:scale-95 transition-all flex items-center justify-center z-50 group">
<span class="material-symbols-outlined text-[28px]">add</span>
<span class="absolute right-full mr-4 px-3 py-1.5 bg-inverse-surface text-inverse-on-surface text-body-sm rounded-lg opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">Tạo lịch tư vấn</span>
</a>

<script>
const TL = { startMin: {{ $startHour * 60 }}, endMin: {{ $endHour * 60 }}, hourPx: {{ $hourPx }}, isToday: @json($date->isToday()) };
function placeNowLine() {
    const line = document.getElementById('current-time-line');
    const label = document.getElementById('current-time-label');
    if (!line) return;
    const now = new Date();
    const m = now.getHours() * 60 + now.getMinutes();
    if (!TL.isToday || m < TL.startMin || m > TL.endMin) { line.style.display = 'none'; return; }
    line.style.top = ((m - TL.startMin) / 60 * TL.hourPx) + 'px';
    line.style.display = 'block';
    if (label) label.textContent = String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0');
}
placeNowLine();
setInterval(placeNowLine, 60000);
</script>
@include('partials.datepicker')
</body></html>
