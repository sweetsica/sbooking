<!DOCTYPE html>

<html class="light" lang="vi"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Tạo Lịch Hẹn - Longevity Booking Scheduler</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&amp;family=Manrope:wght@600;700;800&amp;family=JetBrains+Mono:wght@500;600&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script id="tailwind-config">
      tailwind.config = {
        darkMode: "class",
        theme: {
          extend: {
            "colors": {
                    "on-primary-fixed": "#131b2e",
                    "on-secondary-fixed-variant": "#004c6e",
                    "primary-fixed-dim": "#bec6e0",
                    "inverse-on-surface": "#eff1f3",
                    "secondary-fixed-dim": "#89ceff",
                    "tertiary-fixed": "#6ffbbe",
                    "secondary-container": "#39b8fd",
                    "primary-fixed": "#dae2fd",
                    "surface-container-low": "#f2f4f6",
                    "outline-variant": "#c6c6cd",
                    "on-error-container": "#93000a",
                    "outline": "#76777d",
                    "on-tertiary-container": "#009668",
                    "inverse-primary": "#bec6e0",
                    "on-background": "#191c1e",
                    "surface-container-lowest": "#ffffff",
                    "primary": "#000000",
                    "on-surface-variant": "#45464d",
                    "on-secondary-fixed": "#001e2f",
                    "surface": "#f7f9fb",
                    "error": "#ba1a1a",
                    "tertiary-container": "#002113",
                    "on-primary-container": "#7c839b",
                    "surface-container-high": "#e6e8ea",
                    "surface-bright": "#f7f9fb",
                    "on-primary": "#ffffff",
                    "on-secondary-container": "#004666",
                    "primary-container": "#131b2e",
                    "tertiary": "#000000",
                    "surface-tint": "#565e74",
                    "surface-dim": "#d8dadc",
                    "on-error": "#ffffff",
                    "on-tertiary": "#ffffff",
                    "error-container": "#ffdad6",
                    "background": "#f7f9fb",
                    "inverse-surface": "#2d3133",
                    "on-surface": "#191c1e",
                    "on-primary-fixed-variant": "#3f465c",
                    "tertiary-fixed-dim": "#4edea3",
                    "surface-container-highest": "#e0e3e5",
                    "secondary-fixed": "#c9e6ff",
                    "on-tertiary-fixed-variant": "#005236",
                    "on-tertiary-fixed": "#002113",
                    "on-secondary": "#ffffff",
                    "surface-variant": "#e0e3e5",
                    "secondary": "#006591",
                    "surface-container": "#eceef0"
            },
            "borderRadius": {
                    "DEFAULT": "0.125rem",
                    "lg": "0.25rem",
                    "xl": "0.5rem",
                    "full": "0.75rem"
            },
            "spacing": {
                    "gutter": "12px",
                    "container-margin": "24px",
                    "row-height-compact": "40px",
                    "unit": "4px",
                    "row-height-standard": "56px"
            },
            "fontFamily": {
                    "headline-md": ["Manrope"],
                    "time-slot": ["JetBrains Mono"],
                    "headline-lg": ["Manrope"],
                    "body-md": ["Inter"],
                    "body-sm": ["Inter"],
                    "label-caps": ["JetBrains Mono"]
            },
            "fontSize": {
                    "headline-md": ["18px", {"lineHeight": "24px", "fontWeight": "600"}],
                    "time-slot": ["12px", {"lineHeight": "16px", "fontWeight": "500"}],
                    "headline-lg": ["24px", {"lineHeight": "32px", "fontWeight": "700"}],
                    "body-md": ["14px", {"lineHeight": "20px", "fontWeight": "400"}],
                    "body-sm": ["13px", {"lineHeight": "18px", "fontWeight": "400"}],
                    "label-caps": ["11px", {"lineHeight": "16px", "letterSpacing": "0.05em", "fontWeight": "600"}]
            }
          },
        },
      }
    </script>
<style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        .form-input-focus:focus {
            outline: none;
            border-color: #006591;
            box-shadow: 0 0 0 1px #006591;
        }
        body {
            background-color: #f7f9fb;
        }
    </style>
</head>
<body class="font-body-md text-on-surface">
@php $bk = $bk ?? null; $editing = (bool) $bk; @endphp
<!-- Top Navigation Bar (Replacing SideNavBar) -->
@include('partials.topnav', ['active' => ($loaiDatLich ?? 'phong_kham') === 'dich_vu' ? 'dich-vu' : 'lich-hen'])
<!-- Main Content -->
<main class="pt-16 min-h-screen">
<div class="p-container-margin max-w-[1650px] mx-auto">
<!-- Breadcrumb -->
<div class="flex items-center gap-4 py-6">
<a href="{{ $editing ? '/'.$coSo->slug.'/danh-sach' : '/'.$coSo->slug.'/lich-hen' }}" class="p-2 hover:bg-surface-container-low rounded-full transition-all">
<span class="material-symbols-outlined">arrow_back</span>
</a>
<h2 class="text-headline-md font-headline-md font-extrabold text-on-surface">{{ $editing ? 'Sửa Lịch Hẹn' : 'Tạo Mới Lịch Hẹn' }}</h2>
<span class="ml-1 px-3 py-1 rounded-full bg-secondary-container/40 text-on-secondary-container text-body-sm font-semibold">{{ $coSo->ten }}</span>
</div>
<!-- Hero -->
<div class="mb-8 relative rounded-xl overflow-hidden h-40">
<div class="absolute inset-0 bg-primary-container z-0"></div>
<div class="relative z-10 p-8 flex flex-col justify-end h-full">
<h3 class="text-headline-lg font-headline-lg text-on-primary">{{ $editing ? 'Cập Nhật Lịch Hẹn' : 'Đăng Ký Dịch Vụ Longevity' }}</h3>
<p class="text-body-md text-on-primary-container opacity-90 max-w-lg">Nhập đầy đủ thông tin để khởi tạo quy trình điều trị chính xác.</p>
</div>
</div>

@if ($errors->any())
<div class="mb-6 p-4 rounded-xl bg-error-container border border-error/30 text-on-error-container">
<p class="font-semibold mb-1 flex items-center gap-2"><span class="material-symbols-outlined">error</span> Vui lòng kiểm tra lại các trường bắt buộc:</p>
<ul class="list-disc list-inside text-body-sm">
@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
</ul>
</div>
@endif

<!-- Main Form Card -->
<div class="bg-surface-container-lowest border border-outline-variant rounded-xl shadow-sm overflow-hidden mb-12">
@php $isDichVu = ($loaiDatLich ?? 'phong_kham') === 'dich_vu'; @endphp
<form class="p-8" id="booking-form" method="POST" action="{{ $editing ? '/'.$coSo->slug.'/sua-dat-phong/'.$bk->id : ($isDichVu ? '/'.$coSo->slug.'/dat-lich-dich-vu' : '/'.$coSo->slug.'/tao-moi') }}">
<input type="hidden" name="loai_dat_lich" value="{{ $isDichVu ? 'dich_vu' : 'phong_kham' }}"/>
@csrf
@if ($editing) @method('PUT') @endif
<!-- System Info -->
<div class="mb-10 grid grid-cols-1 md:grid-cols-2 gap-8">
<div>
<label class="block text-body-sm font-semibold text-on-surface-variant mb-1.5">Dấu thời gian</label>
<input class="w-full px-4 py-2.5 bg-surface-container-low border border-outline-variant rounded-lg text-body-md text-on-surface-variant cursor-not-allowed" readonly type="text" value="{{ ($editing ? $bk->created_at : now())->format('d/m/Y - h:i:s') }} ({{ ($editing ? $bk->created_at : now())->hour < 12 ? 'sáng' : 'tối' }})"/>
</div>
<div>
<label class="block text-body-sm font-semibold text-on-surface-variant mb-1.5">Nguồn</label>
<select name="nguon" class="w-full px-4 py-2.5 bg-surface border border-outline rounded-lg form-input-focus transition-all text-body-md">
@foreach (['Fanpage Facebook','Website','Hotline','Khách giới thiệu','Trực tiếp (Walk-in)'] as $ng)
<option @selected(old('nguon', $bk?->nguon)===$ng)>{{ $ng }}</option>
@endforeach
</select>
</div>
</div>

@if (! $isDichVu)
<!-- Loại chính: Tư vấn / Thăm khám lâm sàng (mutex, mặc định Tư vấn) -->
@php
    $loaiChinh = old('loai_chinh', $bk
        ? ($bk->dichVu?->thuoc_nhom ?: ($bk->co_kham_cls ? 'kham_ls' : 'tu_van'))
        : 'tu_van');
@endphp
<div class="mb-10">
<label class="block text-body-sm font-semibold text-on-surface-variant mb-2">Loại chính <span class="text-error">*</span> <span class="text-on-surface-variant/60 text-[11px]">— tự lọc bác sĩ + chia khung giờ theo phút</span></label>
<div class="grid grid-cols-1 md:grid-cols-3 gap-3">
<label class="flex items-center gap-3 p-3 bg-surface border border-outline rounded-lg cursor-pointer hover:bg-surface-container-low transition-colors has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50">
<input type="radio" name="loai_chinh" value="tu_van" @checked($loaiChinh === 'tu_van') class="w-4 h-4 text-emerald-500"/>
<span class="flex items-center gap-2 text-body-md"><span class="w-3 h-3 rounded-full bg-emerald-500"></span> Tư vấn / Đọc kết quả</span>
</label>
<label class="flex items-center gap-3 p-3 bg-surface border border-outline rounded-lg cursor-pointer hover:bg-surface-container-low transition-colors has-[:checked]:border-sky-500 has-[:checked]:bg-sky-50">
<input type="radio" name="loai_chinh" value="kham_ls" @checked($loaiChinh === 'kham_ls') class="w-4 h-4 text-sky-500"/>
<span class="flex items-center gap-2 text-body-md"><span class="w-3 h-3 rounded-full bg-sky-500"></span> Thăm khám lâm sàng</span>
</label>
<label class="flex items-center gap-3 p-3 bg-surface border border-outline rounded-lg cursor-pointer hover:bg-surface-container-low transition-colors has-[:checked]:border-amber-500 has-[:checked]:bg-amber-50">
<input type="radio" name="loai_chinh" value="khac" @checked($loaiChinh === 'khac') class="w-4 h-4 text-amber-500"/>
<span class="flex items-center gap-2 text-body-md"><span class="w-3 h-3 rounded-full bg-amber-500"></span> Khác (XQuang, Siêu âm…)</span>
</label>
</div>
{{-- Cờ co_tu_van/co_kham_cls vẫn lưu (backend không đổi schema) - đồng bộ từ radio bằng hidden input --}}
<input type="hidden" name="co_tu_van" id="co_tu_van" value="{{ $loaiChinh === 'tu_van' ? '1' : '0' }}"/>
<input type="hidden" name="co_kham_cls" id="co_kham_cls" value="{{ $loaiChinh === 'kham_ls' ? '1' : '0' }}"/>
</div>
@endif

<div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-10">
<!-- Section 1: Customer -->
<div class="space-y-6 order-4">
<div class="flex items-center gap-2 pb-2 border-b border-outline-variant">
<span class="material-symbols-outlined text-secondary">person</span>
<h3 class="text-headline-md font-headline-md">Thông tin Khách hàng</h3>
</div>
<div class="space-y-4">
<div>
<label class="block text-body-sm font-semibold text-on-surface-variant mb-1.5">Họ tên KH <span class="text-error">*</span></label>
<input name="ho_ten" value="{{ old('ho_ten', $bk?->khachHang?->ho_ten) }}" required class="w-full px-4 py-2.5 bg-surface border border-outline rounded-lg form-input-focus transition-all text-body-md" placeholder="Nhập họ và tên..." type="text"/>
</div>
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
<div>
<label class="block text-body-sm font-semibold text-on-surface-variant mb-1.5">Số điện thoại <span class="text-error">*</span></label>
<input id="sdt" name="so_dien_thoai" value="{{ old('so_dien_thoai', $bk?->khachHang?->so_dien_thoai) }}" required class="w-full px-4 py-2.5 bg-surface border border-outline rounded-lg form-input-focus transition-all text-body-md font-time-slot" placeholder="0xxx xxx xxx" type="tel"/>
<p id="sdt-msg" class="hidden mt-1 text-body-sm text-secondary font-medium"></p>
</div>
<div>
<label class="block text-body-sm font-semibold text-on-surface-variant mb-1.5">Địa chỉ Email</label>
<input name="email" value="{{ old('email', $bk?->khachHang?->email) }}" class="w-full px-4 py-2.5 bg-surface border border-outline rounded-lg form-input-focus transition-all text-body-md" placeholder="email (tuỳ chọn)" type="email"/>
</div>
</div>
</div>
</div>

<!-- Section 2: Schedule & Room -->
<div class="space-y-6 order-2">
<div class="flex items-center gap-2 pb-2 border-b border-outline-variant">
<span class="material-symbols-outlined text-secondary">calendar_today</span>
<h3 class="text-headline-md font-headline-md">Lịch trình &amp; Phòng <span class="text-error">*</span></h3>
</div>
<div class="space-y-4">
<div>
<label class="block text-body-sm font-semibold text-on-surface-variant mb-1.5">Ngày đặt lịch <span class="text-error">*</span></label>
<input name="ngay_dat" value="{{ old('ngay_dat', $bk ? $bk->ngay_dat->toDateString() : now()->toDateString()) }}" required class="w-full px-4 py-2.5 bg-surface border border-outline rounded-lg form-input-focus transition-all text-body-md" type="date"/>
</div>
<div class="grid grid-cols-2 gap-4">
<div>
<label class="block text-body-sm font-semibold text-on-surface-variant mb-1.5">Phòng <span class="text-error">*</span></label>
<select id="phong" name="phong_id" required class="w-full px-4 py-2.5 bg-surface border border-outline rounded-lg form-input-focus transition-all text-body-md">
@foreach ($phongs as $p)
<option value="{{ $p->id }}"
    data-kieu="{{ $p->kieu_phong }}"
    data-phut="{{ $p->phut_moi_khach }}"
    data-ktv="{{ $p->ktv_mac_dinh_id }}"
    @selected(old('phong_id', $bk?->phong_id)==$p->id)>{{ $p->ten }} ({{ $p->so_slot_toi_da }} slot)</option>
@endforeach
</select>
</div>
<div>
<label class="block text-body-sm font-semibold text-on-surface-variant mb-1.5">Khung giờ <span class="text-error">*</span></label>
<select id="khung_gio" name="khung_gio_id" required data-old="{{ old('khung_gio_id', $bk?->khung_gio_id) }}" class="w-full px-4 py-2.5 bg-surface border border-outline rounded-lg form-input-focus transition-all text-body-md font-time-slot"></select>
</div>
</div>
<div class="grid grid-cols-2 gap-4">
<div>
<label class="block text-body-sm font-semibold text-on-surface-variant mb-1.5">Giờ thực hiện DV <span class="text-on-surface-variant/60 text-[11px]">(theo khung giờ)</span></label>
<select id="gio_thuc_hien" name="gio_thuc_hien" data-old="{{ old('gio_thuc_hien', $bk && $bk->gio_thuc_hien ? substr($bk->gio_thuc_hien,0,5) : '') }}" class="w-full px-4 py-2.5 bg-surface border border-outline rounded-lg form-input-focus transition-all text-body-md font-time-slot">
<option value="">-- Chọn giờ --</option>
</select>
</div>
<div>
<label class="block text-body-sm font-semibold text-on-surface-variant mb-1.5">Giờ dự kiến kết thúc <span class="text-on-surface-variant/60 text-[11px]">(theo khung giờ)</span></label>
<select id="gio_ket_thuc" name="gio_ket_thuc" data-old="{{ old('gio_ket_thuc', $bk && $bk->gio_ket_thuc ? substr($bk->gio_ket_thuc,0,5) : '') }}" class="w-full px-4 py-2.5 bg-surface border border-outline rounded-lg form-input-focus transition-all text-body-md font-time-slot">
<option value="">-- Chọn giờ --</option>
</select>
</div>
</div>
</div>

{{-- Khung tham khảo quy tắc đặt lịch theo cơ sở + loại đặt lịch --}}
@php
    $lichTrinh = config('lich-trinh.'.$coSo->slug);
    $rows = $lichTrinh[$isDichVu ? 'phong_dich_vu' : 'phong_kham'] ?? [];
@endphp
@if ($lichTrinh && count($rows))
<details class="mt-4 bg-secondary-container/30 border border-outline-variant rounded-xl overflow-hidden" open>
<summary class="px-4 py-2.5 cursor-pointer flex items-center gap-2 hover:bg-secondary-container/50 select-none">
<span class="material-symbols-outlined text-secondary text-[20px]">info</span>
<span class="font-semibold text-on-secondary-container">Quy tắc đặt lịch — {{ $coSo->ten }}</span>
<span class="text-body-sm text-on-surface-variant ml-2">(giờ hoạt động: {{ $lichTrinh['gio_hoat_dong'] }})</span>
</summary>
<div class="overflow-x-auto bg-surface-container-lowest border-t border-outline-variant">
<table class="w-full text-body-sm">
@if ($isDichVu)
{{-- Bảng phòng dịch vụ: 3 cột --}}
<thead class="bg-surface-container-low text-left text-label-caps font-label-caps uppercase text-on-surface-variant">
<tr>
<th class="px-3 py-2">Tên chuyên khoa</th>
<th class="px-3 py-2">Địa điểm</th>
<th class="px-3 py-2">Slot</th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant/40">
@foreach ($rows as $r)
<tr class="hover:bg-surface-container-low/50 align-top">
<td class="px-3 py-2 font-medium whitespace-nowrap">{{ $r['phong'] }}</td>
<td class="px-3 py-2 text-on-surface-variant">{{ $r['dia_chi'] }}</td>
<td class="px-3 py-2">{{ $r['slot'] }}</td>
</tr>
@endforeach
</tbody>
@else
{{-- Bảng phòng khám: 4 cột --}}
<thead class="bg-surface-container-low text-left text-label-caps font-label-caps uppercase text-on-surface-variant">
<tr>
<th class="px-3 py-2">Phòng</th>
<th class="px-3 py-2">Bác sĩ / Vai trò</th>
<th class="px-3 py-2">Ưu tiên</th>
<th class="px-3 py-2">Ghi chú</th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant/40">
@foreach ($rows as $r)
<tr class="hover:bg-surface-container-low/50 align-top">
<td class="px-3 py-2 font-medium whitespace-nowrap">{{ $r['phong'] }}</td>
<td class="px-3 py-2">
@if ($r['bac_si'])
<div class="font-medium">{{ $r['bac_si'] }}</div>
@endif
@if ($r['vai_tro'])
<div class="text-[11px] text-on-surface-variant">{{ $r['vai_tro'] }}</div>
@endif
@if (! $r['bac_si']) <span class="text-on-surface-variant">—</span> @endif
</td>
<td class="px-3 py-2">
@forelse ($r['uu_tien'] as $u)
<div>{{ $u }}</div>
@empty
<span class="text-on-surface-variant">—</span>
@endforelse
</td>
<td class="px-3 py-2 text-on-surface-variant">
@forelse ($r['ghi_chu'] as $g)
<div>{{ $g }}</div>
@empty
—
@endforelse
</td>
</tr>
@endforeach
</tbody>
@endif
</table>
</div>
</details>
@endif
</div>

@if (! $isDichVu)
<!-- Section: Bác sĩ (chọn sau khung giờ, lọc theo phòng + còn trống) -->
<div class="space-y-6 order-3">
<div class="flex items-center gap-2 pb-2 border-b border-outline-variant">
<span class="material-symbols-outlined text-secondary">stethoscope</span>
<h3 class="text-headline-md font-headline-md">Bác sĩ <span class="text-on-surface-variant/60 text-[11px]" id="bs_hint"></span></h3>
</div>
<div>
<label class="block text-body-sm font-semibold text-on-surface-variant mb-1.5">Chọn bác sĩ <span class="text-on-surface-variant/60 text-[11px]">— bác sĩ của phòng, còn trống khung giờ</span></label>
<select id="bac_si" name="bac_si_user_id" class="w-full px-4 py-2.5 bg-surface border border-outline rounded-lg form-input-focus transition-all text-body-md">
<option value="">-- Chọn khung giờ trước --</option>
</select>
<p id="bs_lich_warn" class="hidden text-error text-body-sm mt-1.5 flex items-center gap-1"><span class="material-symbols-outlined text-[16px]">warning</span><span></span></p>
</div>
</div>
@endif

@if ($isDichVu)
<!-- Section 3 (Đặt dịch vụ): chỉ KTV -->
<div class="space-y-6 order-1">
<div class="flex items-center gap-2 pb-2 border-b border-outline-variant">
<span class="material-symbols-outlined text-secondary">engineering</span>
<h3 class="text-headline-md font-headline-md">Kỹ thuật viên</h3>
</div>
<div>
<label class="block text-body-sm font-semibold text-on-surface-variant mb-1.5">Kỹ thuật viên (KTV) <span class="text-on-surface-variant/60 text-[11px]">— tự chọn theo phòng</span></label>
<select name="ktv_user_id" class="w-full px-4 py-2.5 bg-surface border border-outline rounded-lg form-input-focus transition-all text-body-md">
<option value="">-- Chọn --</option>
@foreach ($ktvs as $k)
<option value="{{ $k->id }}" @selected(old('ktv_user_id', $bk?->ktv_user_id)==$k->id)>{{ $k->ten_day_du }}</option>
@endforeach
</select>
<p id="ktv_lich_warn" class="hidden text-error text-body-sm mt-1.5 flex items-center gap-1"><span class="material-symbols-outlined text-[16px]">warning</span><span></span></p>
</div>
</div>
@else
<!-- Section 3: Chi tiết Dịch vụ (đặt phòng khám) -->
<div class="space-y-6 order-1">
<div class="flex items-center gap-2 pb-2 border-b border-outline-variant">
<span class="material-symbols-outlined text-secondary">medical_information</span>
<h3 class="text-headline-md font-headline-md">Chi tiết Dịch vụ</h3>
</div>
<div class="space-y-4">
<div>
<label class="block text-body-sm font-semibold text-on-surface-variant mb-1.5">Dịch vụ <span class="text-error">*</span></label>
<select id="dich_vu" name="dich_vu_id" required class="w-full px-4 py-2.5 bg-surface border border-outline rounded-lg form-input-focus transition-all text-body-md">
@foreach ($dichVus as $dv)
<option value="{{ $dv->id }}" data-nhom="{{ $dv->thuoc_nhom }}" data-phut="{{ $dv->thoi_gian_phut }}" @selected(old('dich_vu_id', $bk?->dich_vu_id)==$dv->id)>{{ $dv->ten }}</option>
@endforeach
</select>
</div>
<div>
<label class="block text-body-sm font-semibold text-on-surface-variant mb-1.5">Số liệu trình</label>
<input name="so_lieu_trinh" value="{{ old('so_lieu_trinh', $bk?->so_lieu_trinh) }}" class="w-full px-4 py-2.5 bg-surface border border-outline rounded-lg form-input-focus transition-all text-body-md" placeholder="VD: 1/10" type="text"/>
</div>
<div>
<label class="block text-body-sm font-semibold text-on-surface-variant mb-1.5">Kỹ thuật viên (KTV)</label>
<select name="ktv_user_id" class="w-full px-4 py-2.5 bg-surface border border-outline rounded-lg form-input-focus transition-all text-body-md">
<option value="">-- Chọn --</option>
@foreach ($ktvs as $k)
<option value="{{ $k->id }}" @selected(old('ktv_user_id', $bk?->ktv_user_id)==$k->id)>{{ $k->ten_day_du }}</option>
@endforeach
</select>
</div>
<div class="pt-2 space-y-2">
<label class="flex items-center justify-between p-3 bg-surface border border-outline rounded-lg cursor-pointer hover:bg-surface-container-low transition-colors">
<span class="text-body-md font-medium text-on-surface">KH có SD kết hợp Medical không?</span>
<div class="relative inline-flex items-center cursor-pointer">
<input class="sr-only peer" type="checkbox" name="ket_hop_medical" value="1" @checked(old('ket_hop_medical', $bk?->ket_hop_medical))/>
<div class="w-11 h-6 bg-outline-variant peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-secondary"></div>
</div>
</label>
</div>
</div>
</div>
@endif

<!-- Section 4: Admin & Notes -->
<div class="space-y-6 order-5">
<div class="flex items-center gap-2 pb-2 border-b border-outline-variant">
<span class="material-symbols-outlined text-secondary">assignment_ind</span>
<h3 class="text-headline-md font-headline-md">Hành chính &amp; Ghi chú</h3>
</div>
<div class="space-y-4">
<div>
<label class="block text-body-sm font-semibold text-on-surface-variant mb-1.5">Sale phụ trách @if (! $isDichVu)<span class="text-error">*</span>@else<span class="text-on-surface-variant/60 text-[11px]">— không bắt buộc</span>@endif</label>
<select name="sale_id" @if (! $isDichVu) required @endif class="w-full px-4 py-2.5 bg-surface border border-outline rounded-lg form-input-focus transition-all text-body-md">
<option value="">-- Chọn nhân viên Sales --</option>
@foreach ($sales as $s)
<option value="{{ $s->id }}" @selected(old('sale_id', $bk?->sale_id)==$s->id)>{{ $s->name }}{{ $s->chuc_danh ? ' ('.$s->chuc_danh.')' : '' }}</option>
@endforeach
</select>
@if ($sales->isEmpty())
<p class="mt-1 text-body-sm text-error">Chưa có nhân viên thuộc phòng ban Sales cho cơ sở này.</p>
@endif
</div>
<div>
<label class="block text-body-sm font-semibold text-on-surface-variant mb-1.5">Menu (chọn nhiều)</label>
<div class="grid grid-cols-1 sm:grid-cols-2 gap-2 p-3 bg-surface border border-outline rounded-lg max-h-48 overflow-auto">
@forelse ($menus as $mn)
<label class="flex items-center gap-2 px-2 py-1.5 rounded-md hover:bg-surface-container-low cursor-pointer">
<input type="checkbox" name="menu_ids[]" value="{{ $mn->id }}" @checked(collect(old('menu_ids', $bk ? $bk->menus->pluck('id')->all() : []))->contains($mn->id)) class="w-4 h-4 rounded border-outline text-secondary focus:ring-secondary"/>
<span class="text-body-md">{{ $mn->ten }}</span>
</label>
@empty
<p class="text-body-sm text-on-surface-variant">Chưa có menu nào.</p>
@endforelse
</div>
</div>
<div>
<label class="block text-body-sm font-semibold text-on-surface-variant mb-1.5">Ghi chú</label>
<textarea name="ghi_chu" rows="3" class="w-full px-4 py-2.5 bg-surface border border-outline rounded-lg form-input-focus transition-all text-body-md resize-none" placeholder="Ghi chú thêm cho lịch hẹn...">{{ old('ghi_chu', $bk?->ghi_chu) }}</textarea>
</div>
</div>
</div>
</div>

<!-- Footer Actions -->
<div class="mt-12 grid grid-cols-2 items-stretch gap-4 pt-8 border-t border-outline-variant sm:flex sm:justify-between sm:items-center">
<a href="{{ $editing ? '/'.$coSo->slug.'/danh-sach' : '/'.$coSo->slug.'/lich-hen' }}" class="px-6 py-2.5 text-on-surface-variant font-semibold hover:bg-surface-container-high rounded-lg transition-colors flex items-center justify-center text-center">Hủy bỏ</a>
<button class="px-4 sm:px-8 py-2.5 bg-secondary-container text-on-secondary-container font-bold rounded-lg hover:opacity-90 active:scale-95 transition-all flex items-center justify-center gap-2 shadow-md whitespace-nowrap" type="submit">
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">{{ $editing ? 'save' : 'add_task' }}</span>
{{ $editing ? 'Lưu thay đổi' : 'Tạo Lịch Hẹn' }}
</button>
</div>
</form>
</div>
</div>
</main>

<script>
(function () {
    const slug = '{{ $coSo->slug }}';
    const editId = {{ $editing ? $bk->id : 'null' }};
    const phong = document.getElementById('phong');
    const khung = document.getElementById('khung_gio');
    const ketThuc = document.getElementById('gio_ket_thuc');
    const batDau = document.querySelector('[name="gio_thuc_hien"]');
    const ngay = document.querySelector('[name="ngay_dat"]');
    const oldKhung = khung.getAttribute('data-old');
    let fullHours = new Set();
    let firstRun = true;

    // Tải khung giờ theo phòng + ngày + BS + dịch vụ (để tách sub-slot theo phút BS)
    async function loadSlots() {
        let data = { slots: [] };
        if (phong && phong.value) {
            const dvEl = document.getElementById('dich_vu');
            const params = new URLSearchParams({ phong_id: phong.value, ngay: ngay ? ngay.value : '' });
            if (editId) params.append('except', editId);
            if (dvEl && dvEl.value) params.append('dich_vu_id', dvEl.value);
            try {
                const r = await fetch(`/${slug}/tao-moi/khung-gio?${params}`);
                data = await r.json();
            } catch (e) {}
        }
        const slots = data.slots || [];
        khung.innerHTML = slots.length
            ? slots.map(s => {
                const lbl = `${s.bd} - ${s.kt}` + (s.full ? ' — đã đầy 🔒' : '');
                return `<option value="${s.id}" data-bd="${s.bd}" data-kt="${s.kt}" ${s.full ? 'disabled' : ''}>${lbl}</option>`;
            }).join('')
            : '<option value="">(Phòng chưa cấu hình khung giờ)</option>';
        if (oldKhung) { const o = khung.querySelector(`option[value="${oldKhung}"]`); if (o && !o.disabled) khung.value = oldKhung; }
        if (khung.selectedOptions[0] && khung.selectedOptions[0].disabled) {
            const avail = khung.querySelector('option:not([disabled])');
            khung.value = avail ? avail.value : '';
        }
        applyTimeLocks();
        updateEnd();
        loadBacSi();
    }

    // Generate options cho gio_thuc_hien / gio_ket_thuc THEO khung giờ đã chọn (step 5 phút)
    function rebuildGioOptions() {
        const opt = khung.options[khung.selectedIndex];
        if (!opt || !batDau || !ketThuc) return;
        const bd = opt.getAttribute('data-bd') || '';
        const kt = opt.getAttribute('data-kt') || '';
        if (!bd || !kt) return;

        const toMin = t => parseInt(t.slice(0, 2)) * 60 + parseInt(t.slice(3, 5));
        const fmt = m => String(Math.floor(m / 60)).padStart(2, '0') + ':' + String(m % 60).padStart(2, '0');
        const s = toMin(bd), e = toMin(kt);

        // Lần đầu (mở form): khôi phục theo data-old từ server. Các lần sau (đổi ngày/phòng/dịch vụ/khung):
        // luôn đồng bộ theo khung giờ hiện tại — tránh giữ giá trị cũ ngắn hơn khung mới khiến slotLen sai.
        const bdOld = firstRun ? (batDau.dataset.old || '') : '';
        const ktOld = firstRun ? (ketThuc.dataset.old || '') : '';

        // Bắt đầu: từ s đến e-5 (bao gồm s, không bao gồm e)
        let opts = '<option value="">-- Chọn giờ --</option>';
        for (let t = s; t < e; t += 5) {
            const v = fmt(t);
            opts += `<option value="${v}">${v}</option>`;
        }
        batDau.innerHTML = opts;
        // Khôi phục nếu giá trị cũ vẫn fit khung
        if (bdOld && batDau.querySelector(`option[value="${bdOld}"]`)) batDau.value = bdOld;
        else batDau.value = bd;

        // Kết thúc: từ s+5 đến e (bao gồm e)
        opts = '<option value="">-- Chọn giờ --</option>';
        for (let t = s + 5; t <= e; t += 5) {
            const v = fmt(t);
            opts += `<option value="${v}">${v}</option>`;
        }
        ketThuc.innerHTML = opts;
        if (ktOld && ketThuc.querySelector(`option[value="${ktOld}"]`)) ketThuc.value = ktOld;
        else ketThuc.value = kt;
    }

    // Backward-compat: applyTimeLocks now just rebuilds options
    function applyTimeLocks() { rebuildGioOptions(); }

    // updateEnd: khi đổi khung giờ → rebuild + auto-fill bd/kt theo khung
    function updateEnd() {
        rebuildGioOptions();
        firstRun = false;
    }

    // Auto-fill KTV mặc định + cập nhật meta khi chọn phòng dịch vụ
    function syncPhongMeta() {
        const opt = phong.options[phong.selectedIndex];
        if (!opt) return;
        const ktvId = opt.dataset.ktv;
        const ktvSel = document.querySelector('[name="ktv_user_id"]');
        if (ktvSel && ktvId) {
            const target = ktvSel.querySelector(`option[value="${ktvId}"]`);
            if (target) ktvSel.value = ktvId;
        }
    }

    if (phong) {
        phong.addEventListener('change', () => { syncPhongMeta(); loadSlots(); });
        if (ngay) ngay.addEventListener('change', loadSlots);
        khung.addEventListener('change', updateEnd);
        loadSlots();
        syncPhongMeta();
    }

    // ===== Radio "Loại chính" (mutex Tư vấn / Khám LS / Không) =====
    const dichVu = document.getElementById('dich_vu');
    const bacSi = document.getElementById('bac_si');
    const bsHint = document.getElementById('bs_hint');
    const bsLichWarn = document.getElementById('bs_lich_warn');
    const ktvLichWarn = document.getElementById('ktv_lich_warn');
    const hCoTuVan = document.getElementById('co_tu_van');
    const hCoKhamCls = document.getElementById('co_kham_cls');
    const applyLoaiChinh = (v) => {
        if (hCoTuVan) hCoTuVan.value = v === 'tu_van' ? '1' : '0';
        if (hCoKhamCls) hCoKhamCls.value = v === 'kham_ls' ? '1' : '0';
        // Lọc dịch vụ theo nhóm đã chọn (Tư vấn / Khám LS): ẩn dịch vụ khác nhóm.
        if (v && dichVu) {
            let firstMatch = null;
            [...dichVu.options].forEach(o => {
                if (!o.value) return;
                const match = o.dataset.nhom === v;
                o.hidden = !match;
                o.disabled = !match;
                if (match && !firstMatch) firstMatch = o;
            });
            // Nếu dịch vụ đang chọn không thuộc nhóm → chuyển sang dịch vụ hợp lệ đầu tiên.
            const cur = dichVu.selectedOptions[0];
            if ((!cur || cur.dataset.nhom !== v) && firstMatch) dichVu.value = firstMatch.value;
        }
        loadSlots();
    };
    document.querySelectorAll('input[name="loai_chinh"]').forEach(r => {
        r.addEventListener('change', () => applyLoaiChinh(r.value));
    });
    // Apply ngay khi load: filter theo radio mặc định (tu_van)
    const initLoai = document.querySelector('input[name="loai_chinh"]:checked');
    if (initLoai) applyLoaiChinh(initLoai.value);

    let bsAbortCtl;
    let bsCoLich = true; // có lịch da_duyet cho tháng hay chưa

    // Hiện/ẩn dòng đỏ cảnh báo lịch làm việc cho ô <select> được chọn.
    // coLich=false → "chưa đăng ký lịch làm việc"; chọn người không trực → "không làm việc vào thời gian này".
    function updateLichWarn(sel, warnEl, coLich, nhan) {
        if (!sel || !warnEl) return;
        const opt = sel.selectedOptions[0];
        const msgEl = warnEl.querySelector('span:last-child');
        let msg = '';
        if (opt && opt.value) {
            if (opt.dataset.nghi === '1') msg = `${nhan} đang nghỉ (ngày nghỉ) vào ngày/ca này.`;
            else if (!coLich) msg = `${nhan} chưa đăng ký lịch làm việc.`;
            else if (opt.dataset.truc === '0') msg = `${nhan} không làm việc vào thời gian này.`;
        }
        if (msg) { if (msgEl) msgEl.textContent = msg; warnEl.classList.remove('hidden'); }
        else warnEl.classList.add('hidden');
    }

    async function loadBacSi() {
        if (!bacSi || !khung || !dichVu || !ngay || !phong) return;
        const opt = khung.options[khung.selectedIndex];
        const bd = (batDau && batDau.value) || (opt && opt.getAttribute('data-bd')) || '';
        const kt = (ketThuc && ketThuc.value) || (opt && opt.getAttribute('data-kt')) || '';
        if (!phong.value || !dichVu.value || !ngay.value || !bd || !kt) {
            bacSi.innerHTML = '<option value="">-- Chọn khung giờ trước --</option>';
            updateLichWarn(bacSi, bsLichWarn, bsCoLich, 'Bác sĩ');
            return;
        }
        const params = new URLSearchParams({
            phong_id: phong.value, dich_vu_id: dichVu.value, ngay: ngay.value,
            gio_bat_dau: bd, gio_ket_thuc: kt,
        });
        if (editId) params.append('except', editId);

        if (bsAbortCtl) bsAbortCtl.abort();
        bsAbortCtl = new AbortController();
        if (bsHint) bsHint.textContent = '(đang kiểm tra…)';

        try {
            const r = await fetch(`/${slug}/tao-moi/check-bac-si?${params}`, { signal: bsAbortCtl.signal });
            const j = await r.json();
            const list = j.list || [];
            bsCoLich = j.co_lich !== false;
            const cur = bacSi.value;
            bacSi.innerHTML = '<option value="">-- Chọn --</option>' + list.map(b =>
                `<option value="${b.id}" data-truc="${b.truc ? 1 : 0}" data-nghi="${b.nghi ? 1 : 0}" ${b.available ? '' : 'disabled'}>${b.name}${b.available ? '' : ' — (' + (b.reason || 'không khả dụng') + ')'}</option>`
            ).join('');
            if (cur && bacSi.querySelector(`option[value="${cur}"]:not([disabled])`)) bacSi.value = cur;
            if (bsHint) bsHint.textContent = list.length ? '' : '(không có bác sĩ phù hợp)';
            updateLichWarn(bacSi, bsLichWarn, bsCoLich, 'Bác sĩ');
        } catch (e) {
            if (e.name !== 'AbortError' && bsHint) bsHint.textContent = '';
        }
    }
    if (bacSi) {
        if (dichVu) dichVu.addEventListener('change', loadSlots);
        if (khung) khung.addEventListener('change', loadBacSi);
        if (batDau) batDau.addEventListener('change', loadBacSi);
        bacSi.addEventListener('change', () => updateLichWarn(bacSi, bsLichWarn, bsCoLich, 'Bác sĩ'));
        loadBacSi();
    }

    // ===== KTV động theo lịch trực (chỉ phòng dịch vụ) =====
    // Vẫn hiện TẤT CẢ KTV; người không trực phòng+ngày+ca → cảnh báo dòng đỏ (không chặn).
    const ktvSel = document.querySelector('[name="ktv_user_id"]');
    let ktvAbortCtl;
    let ktvCoLich = true;

    async function loadKtv() {
        if (!ktvSel || !phong || !ngay) return;
        const opt = phong.options[phong.selectedIndex];
        if (!opt || opt.dataset.kieu !== 'phong_dich_vu') return; // phòng khám: giữ KTV tĩnh
        const kg = khung && khung.options[khung.selectedIndex];
        const bd = (batDau && batDau.value) || (kg && kg.getAttribute('data-bd')) || '';
        const oldVal = ktvSel.dataset.old || '';
        const cur = ktvSel.value || oldVal;

        if (!phong.value || !ngay.value || !bd) {
            ktvSel.innerHTML = '<option value="">-- Chọn khung giờ trước --</option>';
            updateLichWarn(ktvSel, ktvLichWarn, ktvCoLich, 'KTV');
            return;
        }
        const params = new URLSearchParams({ phong_id: phong.value, ngay: ngay.value, gio_bat_dau: bd });

        if (ktvAbortCtl) ktvAbortCtl.abort();
        ktvAbortCtl = new AbortController();
        try {
            const r = await fetch(`/${slug}/tao-moi/check-ktv?${params}`, { signal: ktvAbortCtl.signal });
            const j = await r.json();
            const list = j.list || [];
            ktvCoLich = j.co_lich !== false;
            ktvSel.innerHTML = '<option value="">-- Chọn --</option>' + list.map(k =>
                `<option value="${k.id}" data-truc="${k.truc ? 1 : 0}" data-nghi="${k.nghi ? 1 : 0}">${k.name}</option>`
            ).join('');
            if (cur && ktvSel.querySelector(`option[value="${cur}"]`)) ktvSel.value = cur;
            updateLichWarn(ktvSel, ktvLichWarn, ktvCoLich, 'KTV');
        } catch (e) {
            if (e.name === 'AbortError') return;
        }
    }
    if (ktvSel) {
        if (ktvSel.value) ktvSel.dataset.old = ktvSel.value;
        if (phong) phong.addEventListener('change', loadKtv);
        if (ngay) ngay.addEventListener('change', loadKtv);
        if (khung) khung.addEventListener('change', loadKtv);
        if (batDau) batDau.addEventListener('change', loadKtv);
        ktvSel.addEventListener('change', () => updateLichWarn(ktvSel, ktvLichWarn, ktvCoLich, 'KTV'));
        loadKtv();
    }

    // Kiểm tra trùng số điện thoại
    const sdt = document.getElementById('sdt');
    const sdtMsg = document.getElementById('sdt-msg');
    let timer;
    if (sdt) sdt.addEventListener('input', () => {
        clearTimeout(timer);
        const v = sdt.value.replace(/\s+/g, '');
        if (v.length < 6) { sdtMsg.classList.add('hidden'); return; }
        timer = setTimeout(async () => {
            try {
                const r = await fetch(`/{{ $coSo->slug }}/tao-moi/check-sdt?sdt=${encodeURIComponent(v)}`);
                const j = await r.json();
                if (j.ton_tai) {
                    sdtMsg.textContent = '*đã tồn tại số điện thoại' + (j.ho_ten ? ' (' + j.ho_ten + ')' : '');
                    sdtMsg.classList.remove('hidden');
                } else { sdtMsg.classList.add('hidden'); }
            } catch (e) {}
        }, 350);
    });

    if (window.lucide) lucide.createIcons();
})();
</script>

@if (! is_null($allowedFields))
<script>
(function () {
    // Field-level enforcement: disable input/select/textarea không có quyền sửa.
    const allowed = @json($allowedFields);
    const form = document.querySelector('form[method="POST"]');
    if (! form) return;
    form.querySelectorAll('input[name], select[name], textarea[name]').forEach(function (el) {
        const n = el.getAttribute('name');
        // Bỏ qua các field không thuộc danh mục phân quyền (csrf, menu_ids[]).
        const trackable = ['ho_ten','so_dien_thoai','email','ngay_dat','phong_id','khung_gio_id','gio_thuc_hien','gio_ket_thuc','nguon','sale_id','dich_vu_id','so_lieu_trinh','ket_hop_medical','bac_si_user_id','ktv_user_id','ghi_chu'];
        if (! trackable.includes(n)) return;
        if (! allowed.includes(n)) {
            el.disabled = true;
            el.classList.add('opacity-50','cursor-not-allowed','bg-surface-container-high');
            const wrap = el.closest('label, .field, div');
            if (wrap) wrap.title = 'Bạn không có quyền sửa trường này.';
        }
    });
})();
</script>
@endif
@include('partials.datepicker')
</body></html>
