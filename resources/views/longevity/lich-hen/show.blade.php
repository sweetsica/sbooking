<!DOCTYPE html>
<html class="light" lang="vi"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Chi tiết Lịch Tư Vấn - Longevity Booking</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&amp;family=Manrope:wght@600;700;800&amp;family=JetBrains+Mono:wght@500;600&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script>tailwind.config={darkMode:"class",theme:{extend:{"colors":{"on-primary-fixed":"#131b2e","on-secondary-fixed-variant":"#004c6e","primary-fixed-dim":"#bec6e0","inverse-on-surface":"#eff1f3","secondary-fixed-dim":"#89ceff","tertiary-fixed":"#6ffbbe","secondary-container":"#39b8fd","primary-fixed":"#dae2fd","surface-container-low":"#f2f4f6","outline-variant":"#c6c6cd","on-error-container":"#93000a","outline":"#76777d","on-tertiary-container":"#009668","inverse-primary":"#bec6e0","on-background":"#191c1e","surface-container-lowest":"#ffffff","primary":"#000000","on-surface-variant":"#45464d","on-secondary-fixed":"#001e2f","surface":"#f7f9fb","error":"#ba1a1a","tertiary-container":"#002113","on-primary-container":"#7c839b","surface-container-high":"#e6e8ea","surface-bright":"#f7f9fb","on-primary":"#ffffff","on-secondary-container":"#004666","primary-container":"#131b2e","tertiary":"#000000","surface-tint":"#565e74","surface-dim":"#d8dadc","on-error":"#ffffff","on-tertiary":"#ffffff","error-container":"#ffdad6","background":"#f7f9fb","inverse-surface":"#2d3133","on-surface":"#191c1e","on-primary-fixed-variant":"#3f465c","tertiary-fixed-dim":"#4edea3","surface-container-highest":"#e0e3e5","secondary-fixed":"#c9e6ff","on-tertiary-fixed-variant":"#005236","on-tertiary-fixed":"#002113","on-secondary":"#ffffff","surface-variant":"#e0e3e5","secondary":"#006591","surface-container":"#eceef0"},"borderRadius":{"DEFAULT":"0.125rem","lg":"0.25rem","xl":"0.5rem","full":"0.75rem"},"spacing":{"gutter":"12px","container-margin":"24px","row-height-compact":"40px","unit":"4px","row-height-standard":"56px"},"fontFamily":{"headline-md":["Manrope"],"time-slot":["JetBrains Mono"],"headline-lg":["Manrope"],"body-md":["Inter"],"body-sm":["Inter"],"label-caps":["JetBrains Mono"]},"fontSize":{"headline-md":["18px",{"lineHeight":"24px","fontWeight":"600"}],"time-slot":["12px",{"lineHeight":"16px","fontWeight":"500"}],"headline-lg":["24px",{"lineHeight":"32px","fontWeight":"700"}],"body-md":["14px",{"lineHeight":"20px","fontWeight":"400"}],"body-sm":["13px",{"lineHeight":"18px","fontWeight":"400"}],"label-caps":["11px",{"lineHeight":"16px","letterSpacing":"0.05em","fontWeight":"600"}]}}}}</script>
<style>
.material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; vertical-align: middle; }
body { background-color: #f7f9fb; }
.ro { width: 100%; padding: 10px 16px; background:#f2f4f6; border:1px solid #c6c6cd; border-radius:.25rem; color:#191c1e; cursor:not-allowed; }
</style>
</head>
<body class="font-body-md text-on-surface">
@include('partials.topnav', ['active' => 'tu-van'])
<main class="pt-16 min-h-screen">
<div class="p-container-margin max-w-5xl mx-auto">
@php $tt = $lichHen->trang_thai; @endphp
<div class="flex items-center gap-4 py-6">
<a href="javascript:history.back()" class="p-2 hover:bg-surface-container-low rounded-full transition-all">
<span class="material-symbols-outlined">arrow_back</span>
</a>
<h2 class="text-headline-md font-headline-md font-extrabold text-on-surface">Chi tiết Lịch Tư Vấn</h2>
<span class="ml-1 px-3 py-1 rounded-full bg-secondary-container/40 text-on-secondary-container text-body-sm font-semibold">{{ $coSo->ten }}</span>
<span class="px-3 py-1 rounded-full text-body-sm font-semibold {{ $tt === 'da_duyet' ? 'bg-tertiary-fixed-dim/40 text-on-tertiary-container' : ($tt === 'tu_choi' ? 'bg-error-container text-on-error-container' : 'bg-secondary-container/40 text-on-secondary-container') }}">
{{ $tt === 'da_duyet' ? 'Đã duyệt' : ($tt === 'tu_choi' ? 'Từ chối' : 'Chờ duyệt') }}
</span>
</div>

<div class="mb-6 flex items-center gap-2 p-3 rounded-xl bg-secondary-container/20 border border-secondary/20 text-on-secondary-container text-body-sm">
<span class="material-symbols-outlined text-[20px]">visibility</span>
Chế độ xem chi tiết — chỉ đọc, không thể chỉnh sửa.
</div>

<div class="bg-surface-container-lowest border border-outline-variant rounded-xl shadow-sm overflow-hidden mb-12">
<div class="p-8">
<div class="mb-10 grid grid-cols-1 md:grid-cols-2 gap-8">
<div>
<label class="block text-body-sm font-semibold text-on-surface-variant mb-1.5">Thời gian tạo</label>
<div class="ro font-time-slot">{{ $lichHen->created_at?->format('d/m/Y - H:i:s') ?? '—' }}</div>
</div>
<div>
<label class="block text-body-sm font-semibold text-on-surface-variant mb-1.5">Nguồn</label>
<div class="ro">{{ \App\Support\BookingFields::sourceLabel($lichHen->nguon) }}</div>
</div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-10">
<!-- Khách hàng -->
<div class="space-y-6">
<div class="flex items-center gap-2 pb-2 border-b border-outline-variant">
<span class="material-symbols-outlined text-secondary">person</span>
<h3 class="text-headline-md font-headline-md">Thông tin Khách hàng</h3>
</div>
<div class="space-y-4">
<div>
<label class="block text-body-sm font-semibold text-on-surface-variant mb-1.5">Họ tên KH</label>
<div class="ro">{{ $lichHen->khachHang?->ho_ten ?? '—' }}</div>
</div>
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
<div>
<label class="block text-body-sm font-semibold text-on-surface-variant mb-1.5">Số điện thoại</label>
<div class="ro font-time-slot">{{ $lichHen->khachHang?->so_dien_thoai ?? '—' }}</div>
</div>
<div>
<label class="block text-body-sm font-semibold text-on-surface-variant mb-1.5">Email</label>
<div class="ro">{{ $lichHen->khachHang?->email ?? '—' }}</div>
</div>
</div>
</div>
</div>

<!-- Lịch trình & Bác sĩ -->
<div class="space-y-6">
<div class="flex items-center gap-2 pb-2 border-b border-outline-variant">
<span class="material-symbols-outlined text-secondary">calendar_month</span>
<h3 class="text-headline-md font-headline-md">Lịch trình &amp; Bác sĩ</h3>
</div>
<div class="space-y-4">
<div>
<label class="block text-body-sm font-semibold text-on-surface-variant mb-1.5">Ngày hẹn</label>
<div class="ro">{{ $lichHen->ngay_hen?->format('d/m/Y') ?? '—' }}</div>
</div>
<div>
<label class="block text-body-sm font-semibold text-on-surface-variant mb-1.5">Bác sĩ tư vấn</label>
<div class="ro">{{ $lichHen->bacSiTuVan?->ten_day_du ?? '—' }}</div>
</div>
<div>
<label class="block text-body-sm font-semibold text-on-surface-variant mb-1.5">Ca khám</label>
<div class="ro font-time-slot">{{ $lichHen->caKham?->nhan ?? '—' }}</div>
</div>
</div>
</div>

<!-- Sale phụ trách -->
<div class="space-y-6">
<div class="flex items-center gap-2 pb-2 border-b border-outline-variant">
<span class="material-symbols-outlined text-secondary">support_agent</span>
<h3 class="text-headline-md font-headline-md">Sale phụ trách</h3>
</div>
<div class="ro">{{ $lichHen->sale?->name ?? '—' }}</div>
</div>

<!-- Ghi chú -->
<div class="space-y-6">
<div class="flex items-center gap-2 pb-2 border-b border-outline-variant">
<span class="material-symbols-outlined text-secondary">notes</span>
<h3 class="text-headline-md font-headline-md">Ghi chú</h3>
</div>
<div class="ro min-h-[80px] whitespace-pre-line">{{ $lichHen->ghi_chu ?: '—' }}</div>
</div>
</div>

<div class="mt-10 pt-8 border-t border-outline-variant flex justify-end gap-3">
<a href="javascript:history.back()" class="px-6 py-2.5 text-on-surface-variant font-semibold hover:bg-surface-container-high rounded-lg transition-colors">Quay lại</a>
<a href="/{{ $coSo->slug }}/ds-tu-van" class="px-6 py-2.5 bg-primary text-on-primary font-semibold rounded-lg hover:opacity-90 transition-all flex items-center gap-2">
<span class="material-symbols-outlined text-[20px]">format_list_bulleted</span> Danh sách tư vấn
</a>
</div>
</div>
</div>
</div>
</main>
</body></html>
