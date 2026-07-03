<!DOCTYPE html>
<html class="light" lang="vi"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Chi tiết Lịch Hẹn - Longevity Booking</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&amp;family=Manrope:wght@600;700;800&amp;family=JetBrains+Mono:wght@500;600&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script>tailwind.config={darkMode:"class",theme:{extend:{"colors":{"on-primary-fixed":"#131b2e","on-secondary-fixed-variant":"#004c6e","primary-fixed-dim":"#bec6e0","inverse-on-surface":"#eff1f3","secondary-fixed-dim":"#89ceff","tertiary-fixed":"#6ffbbe","secondary-container":"#39b8fd","primary-fixed":"#dae2fd","surface-container-low":"#f2f4f6","outline-variant":"#c6c6cd","on-error-container":"#93000a","outline":"#76777d","on-tertiary-container":"#009668","inverse-primary":"#bec6e0","on-background":"#191c1e","surface-container-lowest":"#ffffff","primary":"#000000","on-surface-variant":"#45464d","on-secondary-fixed":"#001e2f","surface":"#f7f9fb","error":"#ba1a1a","tertiary-container":"#002113","on-primary-container":"#7c839b","surface-container-high":"#e6e8ea","surface-bright":"#f7f9fb","on-primary":"#ffffff","on-secondary-container":"#004666","primary-container":"#131b2e","tertiary":"#000000","surface-tint":"#565e74","surface-dim":"#d8dadc","on-error":"#ffffff","on-tertiary":"#ffffff","error-container":"#ffdad6","background":"#f7f9fb","inverse-surface":"#2d3133","on-surface":"#191c1e","on-primary-fixed-variant":"#3f465c","tertiary-fixed-dim":"#4edea3","surface-container-highest":"#e0e3e5","secondary-fixed":"#c9e6ff","on-tertiary-fixed-variant":"#005236","on-tertiary-fixed":"#002113","on-secondary":"#ffffff","surface-variant":"#e0e3e5","secondary":"#006591","surface-container":"#eceef0"},"borderRadius":{"DEFAULT":"0.125rem","lg":"0.25rem","xl":"0.5rem","full":"0.75rem"},"spacing":{"gutter":"12px","container-margin":"24px","row-height-compact":"40px","unit":"4px","row-height-standard":"56px"},"fontFamily":{"headline-md":["Manrope"],"time-slot":["JetBrains Mono"],"headline-lg":["Manrope"],"body-md":["Inter"],"body-sm":["Inter"],"label-caps":["JetBrains Mono"]},"fontSize":{"headline-md":["18px",{"lineHeight":"24px","fontWeight":"600"}],"time-slot":["12px",{"lineHeight":"16px","fontWeight":"500"}],"headline-lg":["24px",{"lineHeight":"32px","fontWeight":"700"}],"body-md":["14px",{"lineHeight":"20px","fontWeight":"400"}],"body-sm":["13px",{"lineHeight":"18px","fontWeight":"400"}],"label-caps":["11px",{"lineHeight":"16px","letterSpacing":"0.05em","fontWeight":"600"}]}}}}</script>
<style>
.material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
body { background-color: #f7f9fb; }
.ro { width: 100%; padding: 10px 16px; background:#f2f4f6; border:1px solid #c6c6cd; border-radius:.25rem; color:#191c1e; cursor:not-allowed; }
</style>
</head>
<body class="font-body-md text-on-surface">
@include('partials.topnav', ['active' => 'lich-hen'])
<main class="pt-16 min-h-screen">
<div class="p-container-margin max-w-[1650px] mx-auto">
@php
    $canDuyet = $canDuyet ?? false;
    $approved = in_array($booking->trang_thai, ['da_duyet', 'da_xong'], true);
    $rejected = $booking->trang_thai === 'tu_choi';
    $done = $booking->trang_thai === 'da_xong';
@endphp
<div class="flex items-center gap-4 py-6">
<a href="javascript:history.back()" class="p-2 hover:bg-surface-container-low rounded-full transition-all">
<span class="material-symbols-outlined">arrow_back</span>
</a>
<h2 class="text-headline-md font-headline-md font-extrabold text-on-surface">Chi tiết Lịch Hẹn</h2>
<span class="ml-1 px-3 py-1 rounded-full bg-secondary-container/40 text-on-secondary-container text-body-sm font-semibold">{{ $coSo->ten }}</span>
@if ($booking->trang_thai)
@php
    $stBadge = match ($booking->trang_thai) {
        'da_xong'  => ['Đã xong', 'bg-primary/10 text-primary'],
        'da_duyet' => ['Đã duyệt', 'bg-tertiary-fixed-dim/40 text-on-tertiary-container'],
        'tu_choi'  => ['Từ chối', 'bg-red-100 text-red-700'],
        default    => ['Chờ duyệt', 'bg-secondary-container/40 text-on-secondary-container'],
    };
@endphp
<span class="px-3 py-1 rounded-full text-body-sm font-semibold {{ $stBadge[1] }}">{{ $stBadge[0] }}</span>
@endif

@if ($canDuyet && ! $done)
<div class="ml-auto flex items-center gap-2">
@unless ($approved)
<form method="POST" action="/{{ $coSo->slug }}/duyet-dat-phong/{{ $booking->id }}">
@csrf @method('PATCH')
<button type="submit" class="h-[40px] px-5 bg-on-tertiary-container text-white font-semibold rounded-lg flex items-center gap-2 hover:opacity-90 transition-opacity">
<span class="material-symbols-outlined text-[20px]">check_circle</span> Duyệt
</button>
</form>
@endunless
@unless ($rejected)
<button type="button" onclick="openReject()" class="h-[40px] px-5 border border-red-300 text-red-600 font-semibold rounded-lg flex items-center gap-2 hover:bg-red-500 hover:text-white hover:border-red-500 transition-colors">
<span class="material-symbols-outlined text-[20px]">block</span> Không duyệt
</button>
@endunless
</div>
@endif
</div>

@if ($booking->trang_thai === 'tu_choi' && $booking->ly_do_tu_choi)
<div class="mb-6 flex items-start gap-2 p-3 rounded-xl bg-red-50 border border-red-200 text-red-800 text-body-sm">
<span class="material-symbols-outlined text-red-500 text-[20px]">block</span>
<div><span class="font-semibold">Lý do từ chối:</span> {{ $booking->ly_do_tu_choi }}</div>
</div>
@endif

@include('longevity._phan_hoi_section', ['booking' => $booking, 'canPhanHoi' => $canPhanHoi ?? false, 'coSo' => $coSo])

<div class="mb-6 flex items-center gap-2 p-3 rounded-xl bg-secondary-container/20 border border-secondary/20 text-on-secondary-container text-body-sm">
<span class="material-symbols-outlined text-[20px]">visibility</span>
Chế độ xem chi tiết — chỉ đọc, không thể chỉnh sửa.
</div>

<div class="bg-surface-container-lowest border border-outline-variant rounded-xl shadow-sm overflow-hidden mb-12">
<div class="p-8">
<div class="mb-10 grid grid-cols-1 md:grid-cols-2 gap-8">
<div>
<label class="block text-body-sm font-semibold text-on-surface-variant mb-1.5">Thời gian tạo</label>
<div class="ro font-time-slot">{{ $booking->created_at?->format('d/m/Y - H:i:s') ?? '—' }}</div>
</div>
<div>
<label class="block text-body-sm font-semibold text-on-surface-variant mb-1.5">Nguồn</label>
<div class="ro">{{ $booking->nguon ?? '—' }}</div>
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
<div class="ro">{{ $booking->khachHang?->ho_ten ?? '—' }}</div>
</div>
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
<div>
<label class="block text-body-sm font-semibold text-on-surface-variant mb-1.5">Số điện thoại</label>
<div class="ro font-time-slot">{{ $booking->khachHang?->so_dien_thoai ?? '—' }}</div>
</div>
<div>
<label class="block text-body-sm font-semibold text-on-surface-variant mb-1.5">Email</label>
<div class="ro">{{ $booking->khachHang?->email ?? '—' }}</div>
</div>
</div>
</div>
</div>

<!-- Lịch trình & Phòng -->
<div class="space-y-6">
<div class="flex items-center gap-2 pb-2 border-b border-outline-variant">
<span class="material-symbols-outlined text-secondary">calendar_today</span>
<h3 class="text-headline-md font-headline-md">Lịch trình &amp; Phòng</h3>
</div>
<div class="space-y-4">
<div>
<label class="block text-body-sm font-semibold text-on-surface-variant mb-1.5">Ngày đặt lịch</label>
<div class="ro">{{ $booking->ngay_dat?->format('d/m/Y') ?? '—' }}</div>
</div>
<div class="grid grid-cols-2 gap-4">
<div>
<label class="block text-body-sm font-semibold text-on-surface-variant mb-1.5">Phòng</label>
<div class="ro">{{ $booking->phong?->ten ?? '—' }}</div>
</div>
<div>
<label class="block text-body-sm font-semibold text-on-surface-variant mb-1.5">Khung giờ</label>
<div class="ro font-time-slot">{{ $booking->khungGio?->nhan ?? '—' }}</div>
</div>
</div>
<div class="grid grid-cols-2 gap-4">
<div>
<label class="block text-body-sm font-semibold text-on-surface-variant mb-1.5">Giờ thực hiện</label>
<div class="ro font-time-slot">{{ $booking->gio_thuc_hien ? substr($booking->gio_thuc_hien,0,5) : '—' }}</div>
</div>
<div>
<label class="block text-body-sm font-semibold text-on-surface-variant mb-1.5">Giờ kết thúc</label>
<div class="ro font-time-slot">{{ $booking->gio_ket_thuc ? substr($booking->gio_ket_thuc,0,5) : '—' }}</div>
</div>
</div>
</div>
</div>

<!-- Dịch vụ -->
<div class="space-y-6">
<div class="flex items-center gap-2 pb-2 border-b border-outline-variant">
<span class="material-symbols-outlined text-secondary">medical_information</span>
<h3 class="text-headline-md font-headline-md">Chi tiết Dịch vụ</h3>
</div>
<div class="space-y-4">
<div>
<label class="block text-body-sm font-semibold text-on-surface-variant mb-1.5">Liệu pháp</label>
<div class="ro">{{ $booking->dichVu?->ten ?? '—' }}</div>
</div>
<div class="grid grid-cols-2 gap-4">
<div>
<label class="block text-body-sm font-semibold text-on-surface-variant mb-1.5">Số liệu trình</label>
<div class="ro">{{ $booking->so_lieu_trinh ?? '—' }}</div>
</div>
<div>
<label class="block text-body-sm font-semibold text-on-surface-variant mb-1.5">Bác sĩ</label>
<div class="ro">{{ $booking->bacSi?->ten_day_du ?? '—' }}</div>
</div>
<div>
<label class="block text-body-sm font-semibold text-on-surface-variant mb-1.5">KTV</label>
<div class="ro">{{ $booking->ktv?->ten_day_du ?? '—' }}</div>
</div>
</div>
<div class="pt-2 space-y-2">
<div class="flex items-center justify-between p-3 bg-surface border border-outline rounded-lg">
<span class="text-body-md font-medium text-on-surface">Tư vấn</span>
<span class="px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $booking->co_tu_van ? 'bg-tertiary-fixed-dim/40 text-on-tertiary-container' : 'bg-surface-container-high text-on-surface-variant' }}">{{ $booking->co_tu_van ? 'Có' : 'Không' }}</span>
</div>
<div class="flex items-center justify-between p-3 bg-surface border border-outline rounded-lg">
<span class="text-body-md font-medium text-on-surface">Thăm khám lâm sàng</span>
<span class="px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $booking->co_kham_cls ? 'bg-tertiary-fixed-dim/40 text-on-tertiary-container' : 'bg-surface-container-high text-on-surface-variant' }}">{{ $booking->co_kham_cls ? 'Có' : 'Không' }}</span>
</div>
<div class="flex items-center justify-between p-3 bg-surface border border-outline rounded-lg">
<span class="text-body-md font-medium text-on-surface">KH có SD kết hợp Medical không?</span>
<span class="px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $booking->ket_hop_medical ? 'bg-tertiary-fixed-dim/40 text-on-tertiary-container' : 'bg-surface-container-high text-on-surface-variant' }}">{{ $booking->ket_hop_medical ? 'Có' : 'Không' }}</span>
</div>
<div class="flex items-center justify-between p-3 bg-surface border border-outline rounded-lg">
<span class="text-body-md font-medium text-on-surface">Lần đầu khách tới</span>
<span class="px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $booking->lan_dau ? 'bg-tertiary-fixed-dim/40 text-on-tertiary-container' : 'bg-surface-container-high text-on-surface-variant' }}">{{ $booking->lan_dau ? 'Có' : 'Không' }}</span>
</div>
<div class="flex items-center justify-between p-3 bg-surface border border-outline rounded-lg">
<span class="text-body-md font-medium text-on-surface">Khách tặng & Ghi chú</span>
<span class="px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $booking->khach_tang === 'co' ? 'bg-tertiary-fixed-dim/40 text-on-tertiary-container' : 'bg-surface-container-high text-on-surface-variant' }}">{{ $booking->khach_tang === 'co' ? 'Có' : ($booking->khach_tang === 'khac' ? $booking->khach_tang_ghi_chu : 'Không') }}</span>
</div>
</div>
</div>
</div>

<!-- Hành chính & Ghi chú -->
<div class="space-y-6">
<div class="flex items-center gap-2 pb-2 border-b border-outline-variant">
<span class="material-symbols-outlined text-secondary">assignment_ind</span>
<h3 class="text-headline-md font-headline-md">Hành chính &amp; Ghi chú</h3>
</div>
<div class="space-y-4">
<div>
<label class="block text-body-sm font-semibold text-on-surface-variant mb-1.5">Sale phụ trách</label>
<div class="ro">{{ $booking->sale?->name ?? '—' }}</div>
</div>
<div>
<label class="block text-body-sm font-semibold text-on-surface-variant mb-1.5">Menu</label>
<div class="ro min-h-[44px]">
@forelse ($booking->menus as $mn)
<span class="inline-block px-2 py-0.5 mr-1 mb-1 rounded-full bg-secondary-container/30 text-on-secondary-container text-[12px]">{{ $mn->ten }}</span>
@empty
—
@endforelse
</div>
</div>
<div>
<label class="block text-body-sm font-semibold text-on-surface-variant mb-1.5">Ghi chú</label>
<div class="ro min-h-[80px] whitespace-pre-line">{{ $booking->ghi_chu ?: '—' }}</div>
</div>
</div>
</div>
</div>

<div class="mt-12 flex justify-between items-center pt-8 border-t border-outline-variant">
<a href="javascript:history.back()" class="px-6 py-2.5 text-on-surface-variant font-semibold hover:bg-surface-container-high rounded-lg transition-colors">Quay lại</a>
<a href="/{{ $coSo->slug }}/danh-sach" class="px-6 py-2.5 bg-primary text-on-primary font-semibold rounded-lg hover:opacity-90 transition-all flex items-center gap-2">
<span class="material-symbols-outlined text-[20px]">format_list_bulleted</span> Danh sách đặt phòng
</a>
</div>
</div>
</div>
</div>
</main>

@if ($canDuyet && ! $done)
<!-- Popup lý do từ chối -->
<div id="reject-modal" class="fixed inset-0 z-[60] hidden items-center justify-center bg-black/40 p-4">
<div class="w-full max-w-md bg-surface-container-lowest rounded-xl shadow-xl border border-outline-variant overflow-hidden">
<form method="POST" action="/{{ $coSo->slug }}/tu-choi-dat-phong/{{ $booking->id }}">
@csrf @method('PATCH')
<div class="p-5 border-b border-outline-variant flex items-center gap-2">
<span class="material-symbols-outlined text-red-500">block</span>
<h3 class="text-headline-md font-headline-md text-on-surface">Từ chối lịch hẹn</h3>
</div>
<div class="p-5 space-y-2">
<p class="text-body-sm text-on-surface-variant">Lịch hẹn của <span class="font-semibold text-on-surface">{{ $booking->khachHang?->ho_ten ?? 'khách' }}</span> sẽ chuyển sang trạng thái <span class="text-red-600 font-semibold">Từ chối</span>.</p>
<label class="text-label-caps font-label-caps text-red-600 block">Lý do từ chối<span class="text-red-500 ml-0.5">*</span></label>
<textarea name="ly_do_tu_choi" required rows="3" autofocus placeholder="Nhập lý do từ chối lịch hẹn này..." class="w-full px-3 py-2 rounded-lg text-body-md outline-none transition-all border border-red-300 bg-red-50/40 focus:border-red-500 focus:ring-1 focus:ring-red-500/20">{{ old('ly_do_tu_choi') }}</textarea>
</div>
<div class="p-4 bg-surface-container-low/50 border-t border-outline-variant flex justify-end gap-2">
<button type="button" onclick="closeReject()" class="px-4 py-2 text-on-surface-variant font-semibold rounded-lg hover:bg-surface-container-high transition-colors">Hủy</button>
<button type="submit" class="px-5 py-2 bg-red-600 text-white font-semibold rounded-lg flex items-center gap-2 hover:bg-red-700 transition-colors">
<span class="material-symbols-outlined text-[20px]">block</span> Xác nhận từ chối
</button>
</div>
</form>
</div>
</div>
<script>
function openReject(){var m=document.getElementById('reject-modal');m.classList.remove('hidden');m.classList.add('flex');document.body.style.overflow='hidden';var t=m.querySelector('textarea');if(t)setTimeout(function(){t.focus();},50);}
function closeReject(){var m=document.getElementById('reject-modal');m.classList.add('hidden');m.classList.remove('flex');document.body.style.overflow='';}
(function(){var m=document.getElementById('reject-modal');if(!m)return;m.addEventListener('click',function(e){if(e.target===this)closeReject();});document.addEventListener('keydown',function(e){if(e.key==='Escape')closeReject();});})();
@if ($errors->has('ly_do_tu_choi')) openReject(); @endif
</script>
@endif
</body></html>
