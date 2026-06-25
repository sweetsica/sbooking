<!DOCTYPE html>
<html class="light" lang="vi"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Tìm kiếm lịch đặt | Longevity Booking</title>
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
@include('partials.topnav', ['active' => ''])

<main class="pt-[calc(56px+24px)] pb-12 px-container-margin max-w-[1650px] mx-auto">
<div class="mb-6">
<h2 class="font-headline-lg text-headline-lg text-primary mb-1">Kết quả tìm kiếm</h2>
<p class="text-on-surface-variant text-body-md">
@if ($q !== '')
Từ khóa: <span class="font-semibold text-on-surface">"{{ $q }}"</span> · {{ $bookings->count() }} kết quả đặt phòng
@else
Nhập tên hoặc số điện thoại khách hàng vào ô tìm kiếm phía trên.
@endif
</p>
</div>

<!-- Search box (cũng có ở topnav, lặp lại cho rõ) -->
<form method="GET" action="/{{ $coSo->slug }}/tim-kiem" class="bg-surface-container-lowest border border-outline-variant rounded-xl p-4 mb-8 flex items-center gap-3">
<div class="relative flex-1">
<span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[20px]">search</span>
<input name="q" value="{{ $q }}" autofocus class="w-full pl-10 pr-4 py-2.5 bg-surface border border-outline-variant rounded-lg text-body-md focus:border-secondary focus:ring-1 focus:ring-secondary/20 outline-none" placeholder="Tên hoặc số điện thoại khách hàng..." type="search"/>
</div>
<button type="submit" class="flex items-center gap-2 px-6 py-2.5 bg-primary text-on-primary rounded-lg font-semibold">
<span class="material-symbols-outlined text-[18px]">search</span> Tìm
</button>
</form>

@if ($q !== '' && $bookings->isEmpty() && $lichHens->isEmpty())
<div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-12 text-center text-on-surface-variant">
<span class="material-symbols-outlined text-[48px] opacity-40">search_off</span>
<p class="mt-2">Không tìm thấy lịch đặt nào khớp với "{{ $q }}".</p>
</div>
@endif

<!-- Đặt phòng -->
@if ($bookings->isNotEmpty())
<section class="mb-10">
<div class="flex items-center gap-2 mb-3">
<span class="material-symbols-outlined text-secondary">calendar_month</span>
<h3 class="font-headline-md text-headline-md">Đặt lịch phòng khám / dịch vụ ({{ $bookings->count() }})</h3>
</div>
<div class="bg-surface-container-lowest border border-outline-variant rounded-xl overflow-hidden">
<table class="w-full text-body-sm">
<thead class="bg-surface-container-low text-label-caps font-label-caps text-on-surface-variant">
<tr>
<th class="text-left px-4 py-3">KHÁCH HÀNG</th>
<th class="text-left px-4 py-3">SĐT</th>
<th class="text-left px-4 py-3">NGÀY ĐẶT</th>
<th class="text-left px-4 py-3">PHÒNG · KHUNG GIỜ</th>
<th class="text-left px-4 py-3">DỊCH VỤ</th>
<th class="px-4 py-3"></th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant/40">
@foreach ($bookings as $b)
<tr class="hover:bg-surface-variant/10">
<td class="px-4 py-3 font-semibold text-on-surface">{{ $b->khachHang?->ho_ten }}</td>
<td class="px-4 py-3 font-time-slot text-on-surface-variant">{{ $b->khachHang?->so_dien_thoai }}</td>
<td class="px-4 py-3 text-on-surface-variant">{{ $b->ngay_dat?->format('d/m/Y') }}</td>
<td class="px-4 py-3 text-on-surface-variant">{{ $b->phong?->ten }} · {{ $b->khungGio?->nhan ?? ($b->gio_thuc_hien ? substr($b->gio_thuc_hien,0,5) : '—') }}</td>
<td class="px-4 py-3 text-on-surface-variant">{{ $b->dichVu?->ten ?? '—' }}</td>
<td class="px-4 py-3 text-right">
<a href="/{{ $coSo->slug }}/xem-dat-phong/{{ $b->id }}" class="inline-flex items-center gap-1 text-secondary font-semibold hover:underline">
<span class="material-symbols-outlined text-[18px]">visibility</span> Xem
</a>
</td>
</tr>
@endforeach
</tbody>
</table>
</div>
</section>
@endif

{{-- Lịch tư vấn (LichHen) đã bị ẩn theo yêu cầu - giữ data cũ nhưng không hiển thị --}}
@if (false && $lichHens->isNotEmpty())
<section class="mb-10">
<div class="flex items-center gap-2 mb-3">
<span class="material-symbols-outlined text-secondary">stethoscope</span>
<h3 class="font-headline-md text-headline-md">Đặt lịch tư vấn ({{ $lichHens->count() }})</h3>
</div>
<div class="bg-surface-container-lowest border border-outline-variant rounded-xl overflow-hidden">
<table class="w-full text-body-sm">
<thead class="bg-surface-container-low text-label-caps font-label-caps text-on-surface-variant">
<tr>
<th class="text-left px-4 py-3">KHÁCH HÀNG</th>
<th class="text-left px-4 py-3">SĐT</th>
<th class="text-left px-4 py-3">NGÀY HẸN</th>
<th class="text-left px-4 py-3">BÁC SĨ · CA</th>
<th class="text-left px-4 py-3">TRẠNG THÁI</th>
<th class="px-4 py-3"></th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant/40">
@foreach ($lichHens as $lh)
@php $tt = $lh->trang_thai; @endphp
<tr class="hover:bg-surface-variant/10">
<td class="px-4 py-3 font-semibold text-on-surface">{{ $lh->khachHang?->ho_ten }}</td>
<td class="px-4 py-3 font-time-slot text-on-surface-variant">{{ $lh->khachHang?->so_dien_thoai }}</td>
<td class="px-4 py-3 text-on-surface-variant">{{ $lh->ngay_hen?->format('d/m/Y') }}</td>
<td class="px-4 py-3 text-on-surface-variant">{{ $lh->bacSiTuVan?->ten_day_du }} · {{ $lh->caKham?->nhan }}</td>
<td class="px-4 py-3">
<span class="px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $tt === 'da_duyet' ? 'bg-tertiary-fixed-dim/40 text-on-tertiary-container' : ($tt === 'tu_choi' ? 'bg-error-container text-on-error-container' : 'bg-secondary-container/40 text-on-secondary-container') }}">
{{ $tt === 'da_duyet' ? 'Đã duyệt' : ($tt === 'tu_choi' ? 'Từ chối' : 'Chờ duyệt') }}
</span>
</td>
<td class="px-4 py-3 text-right">
<a href="/{{ $coSo->slug }}/xem-tu-van/{{ $lh->id }}" class="inline-flex items-center gap-1 text-secondary font-semibold hover:underline">
<span class="material-symbols-outlined text-[18px]">visibility</span> Xem
</a>
</td>
</tr>
@endforeach
</tbody>
</table>
</div>
</section>
@endif
</main>
</body></html>
