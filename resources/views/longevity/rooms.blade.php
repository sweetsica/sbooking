<!DOCTYPE html>
<html class="light" lang="vi"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Quản lý Phòng | Longevity Booking</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&amp;family=Manrope:wght@600;700;800&amp;family=JetBrains+Mono:wght@500;600&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<script>tailwind.config={darkMode:"class",theme:{extend:{"colors":{"surface-container-high":"#e6e8ea","on-error":"#ffffff","on-tertiary-fixed-variant":"#005236","tertiary-container":"#002113","error":"#ba1a1a","inverse-on-surface":"#eff1f3","surface-dim":"#d8dadc","on-error-container":"#93000a","primary-fixed":"#dae2fd","surface-container-low":"#f2f4f6","outline-variant":"#c6c6cd","error-container":"#ffdad6","on-primary-fixed-variant":"#3f465c","on-tertiary-container":"#009668","surface-container-lowest":"#ffffff","primary-fixed-dim":"#bec6e0","tertiary-fixed-dim":"#4edea3","inverse-primary":"#bec6e0","secondary-fixed":"#c9e6ff","on-primary":"#ffffff","surface-tint":"#565e74","surface-variant":"#e0e3e5","secondary-fixed-dim":"#89ceff","secondary-container":"#39b8fd","on-secondary-fixed":"#001e2f","inverse-surface":"#2d3133","background":"#f7f9fb","surface-bright":"#f7f9fb","secondary":"#006591","tertiary":"#000000","on-primary-container":"#7c839b","primary-container":"#131b2e","primary":"#000000","on-tertiary":"#ffffff","on-secondary-fixed-variant":"#004c6e","tertiary-fixed":"#6ffbbe","surface-container":"#eceef0","on-primary-fixed":"#131b2e","on-tertiary-fixed":"#002113","on-secondary-container":"#004666","on-background":"#191c1e","on-surface-variant":"#45464d","surface":"#f7f9fb","outline":"#76777d","surface-container-highest":"#e0e3e5","on-surface":"#191c1e","on-secondary":"#ffffff"},"borderRadius":{"DEFAULT":"0.125rem","lg":"0.25rem","xl":"0.5rem","full":"0.75rem"},"spacing":{"container-margin":"24px","row-height-compact":"40px","row-height-standard":"56px","gutter":"12px","unit":"4px"},"fontFamily":{"label-caps":["JetBrains Mono"],"body-sm":["Inter"],"headline-lg":["Manrope"],"time-slot":["JetBrains Mono"],"headline-md":["Manrope"],"body-md":["Inter"]},"fontSize":{"label-caps":["11px",{"lineHeight":"16px","letterSpacing":"0.05em","fontWeight":"600"}],"body-sm":["13px",{"lineHeight":"18px","fontWeight":"400"}],"headline-lg":["24px",{"lineHeight":"32px","fontWeight":"700"}],"time-slot":["12px",{"lineHeight":"16px","fontWeight":"500"}],"headline-md":["18px",{"lineHeight":"24px","fontWeight":"600"}],"body-md":["14px",{"lineHeight":"20px","fontWeight":"400"}]}}}}</script>
<style>
body { font-family: 'Inter', sans-serif; background-color: #f7f9fb; }
.material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; vertical-align: middle; }
.slot-pill { transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); }
.slot-pill:hover { transform: scale(1.1); }
</style>
</head>
<body class="bg-background text-on-surface">
@include('partials.topnav', ['active' => 'phong'])
<main class="pt-[calc(56px+24px)] pb-12 px-container-margin max-w-[1650px] mx-auto">
<!-- Header -->
<div class="mb-6">
<h1 class="font-headline-lg text-headline-lg text-primary mb-1">Quản lý Phòng Trị liệu</h1>
<p class="font-body-md text-body-md text-on-surface-variant">Theo dõi tình trạng chiếm chỗ và hiệu suất sử dụng phòng thời gian thực · {{ $date->format('d/m/Y') }} · {{ $roomData->count() }} phòng.</p>
</div>

<!-- Date Filter (đồng bộ với trang Bác sĩ) -->
<form method="GET" class="flex flex-wrap items-end gap-4 mb-6 bg-surface-container-lowest p-6 rounded-xl border border-outline-variant shadow-sm">
<div class="space-y-1.5">
<label class="text-label-caps text-on-surface-variant block">NGÀY</label>
<input name="ngay" value="{{ $date->toDateString() }}" class="border border-outline-variant rounded-lg px-3 py-2 text-body-md focus:border-secondary focus:ring-1 focus:ring-secondary/20 outline-none bg-surface" type="date"/>
</div>
<div class="space-y-1.5 flex-1 min-w-[240px]">
<label class="text-label-caps text-on-surface-variant block">CƠ SỞ</label>
<select onchange="if(this.value)window.location.href=this.value" class="w-full border border-outline-variant rounded-lg px-3 py-2 text-body-md focus:border-secondary focus:ring-1 focus:ring-secondary/20 outline-none bg-surface">
@foreach ($danhSachCoSo as $cs)
<option value="/{{ $cs->slug }}/phong" @selected($cs->id === $coSo->id)>{{ $cs->ten }}</option>
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
<span>Tạo booking</span>
</a>
</div>
</form>

<!-- Room Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-gutter">
@forelse ($roomData as $rd)
@php
$phong = $rd['phong'];
$isMaintenance = $phong->trang_thai === 'bao_tri';
$isVip = $phong->loai === 'vip';
$beds = $rd['beds'];
$occupied = $rd['occupied'];
$fill = $rd['fill'];
$slotStatus = $rd['slotStatus'];
$bedStatus = $rd['bedStatus'];
$slots = $phong->khungGios;
@endphp
<div class="bg-surface border border-outline-variant rounded-xl overflow-hidden {{ $isMaintenance ? 'grayscale opacity-75' : 'hover:shadow-lg transition-shadow' }}">
<div class="p-5 border-b border-outline-variant flex justify-between items-start">
<div>
<div class="flex items-center gap-2 mb-1">
<span class="{{ $isVip ? 'bg-secondary-container text-on-secondary-container' : 'bg-surface-container-highest text-on-surface-variant' }} text-[10px] font-bold px-2 py-0.5 rounded uppercase tracking-wider">{{ $isVip ? 'Phòng VIP' : 'Cộng đồng' }}</span>
@if ($isMaintenance)
<span class="flex items-center gap-1 text-error text-body-sm font-medium">
<span class="w-2 h-2 bg-error rounded-full animate-pulse"></span> Bảo trì
</span>
@else
<span class="flex items-center gap-1 text-on-tertiary-container text-body-sm font-medium">
<span class="w-2 h-2 bg-on-tertiary-container rounded-full"></span> Hoạt động
</span>
@endif
</div>
<h3 class="font-headline-md text-headline-md text-primary">{{ $phong->ten }}</h3>
</div>
<div class="text-right">
<span class="font-label-caps text-label-caps text-on-surface-variant block uppercase">Tỉ lệ sử dụng</span>
<span class="font-headline-md text-headline-md {{ $isMaintenance ? 'text-outline' : 'text-secondary' }}">{{ $fill }}%</span>
</div>
</div>
<div class="p-5">
@if ($isMaintenance)
<p class="font-label-caps text-label-caps text-outline mb-4 uppercase">Trạng thái (0/{{ $beds }})</p>
<div class="flex gap-4 h-24 items-center justify-center bg-surface-container-low rounded-lg border border-dashed border-error/20">
<div class="text-center">
<span class="material-symbols-outlined text-error text-[32px] mb-1">build</span>
<p class="text-error font-body-sm text-body-sm font-semibold uppercase">Đang bảo trì</p>
</div>
</div>
@else
@php
$currentSlotBookings = 0;
if ($slots->count() > 0) {
    $now = now()->format('H:i:s');
    $currentSlot = $slots->first(fn($s) => $s->gio_bat_dau <= $now && $s->gio_ket_thuc > $now) ?? $slots->first();
}
@endphp
<p class="font-label-caps text-label-caps text-outline mb-4 uppercase">Sơ đồ vị trí ({{ count(array_filter($bedStatus, fn($s) => $s === 'occupied')) }}/{{ $beds }})</p>
@if ($beds <= 4)
<div class="flex gap-4 h-24 items-center justify-center bg-surface-container-low rounded-lg border border-dashed border-outline-variant">
@foreach ($bedStatus as $status)
<div class="flex flex-col items-center gap-2">
<div class="w-12 h-16 {{ $status === 'occupied' ? 'bg-secondary-container border-2 border-secondary' : 'bg-surface-container-highest border-2 border-on-tertiary-fixed-variant' }} rounded-md flex items-center justify-center slot-pill cursor-pointer">
<span class="material-symbols-outlined text-on-secondary-container" style='font-variation-settings: "FILL" 1;'>person</span>
</div>
<span class="font-label-caps text-[9px] {{ $status === 'occupied' ? 'text-secondary' : 'text-on-tertiary-fixed-variant' }} uppercase font-bold">{{ $status === 'occupied' ? 'Đang dùng' : 'Trống' }}</span>
</div>
@endforeach
</div>
@else
<div class="grid grid-cols-6 gap-2 p-3 bg-surface-container-low rounded-lg border border-outline-variant">
@foreach ($bedStatus as $status)
<div class="aspect-square {{ $status === 'occupied' ? 'bg-secondary-container' : 'bg-tertiary-fixed-dim/30 border border-on-tertiary-fixed-variant/20' }} rounded-sm flex items-center justify-center slot-pill">
<span class="material-symbols-outlined text-[14px] {{ $status === 'occupied' ? 'text-on-secondary-container' : 'text-on-tertiary-fixed-variant' }}" style="font-variation-settings: 'FILL' {{ $status === 'occupied' ? '1' : '0' }};">{{ $status === 'occupied' ? 'person' : 'circle' }}</span>
</div>
@endforeach
</div>
@endif
<div class="mt-4 pt-4 border-t border-outline-variant/30">
<p class="font-label-caps text-label-caps text-outline mb-2 uppercase">Lịch trình ngày {{ $date->format('d/m') }} ({{ substr($slots->first()?->gio_bat_dau, 0, 5) }} - {{ substr($slots->last()?->gio_ket_thuc, 0, 5) }})</p>
<div class="flex flex-wrap gap-1">
@foreach ($slotStatus as $i => $st)
@php
$hour = substr($slots[$i]->gio_bat_dau, 0, 2);
$bgClass = match($st) {
    'full' => 'bg-secondary-container',
    'partial' => 'bg-secondary-container/40',
    default => 'bg-surface-container-highest',
};
$textClass = match($st) {
    'full' => 'text-on-secondary-container',
    'partial' => 'text-on-secondary-container',
    default => 'text-outline',
};
@endphp
<div class="flex-1 h-4 {{ $bgClass }} rounded-sm flex items-center justify-center text-[8px] {{ $textClass }} font-bold" title="{{ substr($slots[$i]->gio_bat_dau, 0, 5) }}">{{ $hour }}</div>
@endforeach
</div>
<div class="flex justify-between mt-1">
<span class="text-[9px] font-label-caps text-outline">{{ substr($slots->first()?->gio_bat_dau, 0, 5) }}</span>
<span class="text-[9px] font-label-caps text-outline">{{ substr($slots->last()?->gio_ket_thuc, 0, 5) }}</span>
</div>
</div>
@endif
</div>
<div class="px-5 py-4 bg-surface-container-lowest flex gap-3">
@if ($isMaintenance)
<button class="flex-1 py-2 px-4 bg-surface-container text-outline font-body-sm text-body-sm font-bold rounded-lg cursor-not-allowed">Chi tiết Lịch</button>
@else
<a href="/{{ $coSo->slug }}/lich-hen?phong_id={{ $phong->id }}&ngay={{ $date->toDateString() }}" class="flex-1 py-2 px-4 border border-secondary text-secondary font-body-sm text-body-sm font-bold rounded-lg hover:bg-secondary-container/10 transition-colors text-center">Chi tiết Lịch</a>
@endif
</div>
</div>
@empty
<div class="col-span-full bg-surface border border-outline-variant rounded-xl p-12 text-center text-on-surface-variant">
<span class="material-symbols-outlined text-[48px] opacity-40">meeting_room</span>
<p class="mt-2">Chưa có phòng nào. Vui lòng thêm trong <a href="/{{ $coSo->slug }}/thiet-lap/phong" class="text-secondary underline">Thiết lập</a>.</p>
</div>
@endforelse
</div>

<!-- Legend -->
<section class="mt-12 p-6 bg-surface border border-outline-variant rounded-xl flex flex-col md:flex-row items-center justify-between gap-6">
<div class="flex flex-wrap gap-8">
<div class="flex items-center gap-3">
<div class="w-4 h-4 bg-tertiary-fixed-dim border border-on-tertiary-fixed-variant/20 rounded"></div>
<span class="font-body-sm text-body-sm text-on-surface font-medium">Sẵn sàng (Trống)</span>
</div>
<div class="flex items-center gap-3">
<div class="w-4 h-4 bg-secondary-container border border-secondary rounded"></div>
<span class="font-body-sm text-body-sm text-on-surface font-medium">Đang sử dụng (Occupied)</span>
</div>
<div class="flex items-center gap-3">
<div class="w-4 h-4 bg-surface-dim border border-outline rounded"></div>
<span class="font-body-sm text-body-sm text-on-surface font-medium">Không khả dụng (Bảo trì)</span>
</div>
</div>
</section>
</main>
@include('partials.datepicker')
</body></html>
