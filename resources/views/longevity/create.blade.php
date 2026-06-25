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
<label class="block text-body-sm font-semibold text-on-surface-variant mb-1.5">Dấu thời gian (Timestamp)</label>
<input class="w-full px-4 py-2.5 bg-surface-container-low border border-outline-variant rounded-lg text-body-md text-on-surface-variant cursor-not-allowed" readonly type="text" value="{{ ($editing ? $bk->created_at : now())->format('d/m/Y - h:i:s') }} ({{ ($editing ? $bk->created_at : now())->hour < 12 ? 'sáng' : 'tối' }})"/>
</div>
<div>
<label class="block text-body-sm font-semibold text-on-surface-variant mb-1.5">Nguồn (Source)</label>
<select name="nguon" class="w-full px-4 py-2.5 bg-surface border border-outline rounded-lg form-input-focus transition-all text-body-md">
@foreach (['Fanpage Facebook','Website','Hotline','Khách giới thiệu','Trực tiếp (Walk-in)'] as $ng)
<option @selected(old('nguon', $bk?->nguon)===$ng)>{{ $ng }}</option>
@endforeach
</select>
</div>
</div>

@if (! $isDichVu)
<!-- Loại chính: Tư vấn / Thăm khám lâm sàng (mutex) -->
@php
    $loaiChinh = old('loai_chinh', $bk?->co_tu_van ? 'tu_van' : ($bk?->co_kham_cls ? 'kham_ls' : ''));
@endphp
<div class="mb-10">
<label class="block text-body-sm font-semibold text-on-surface-variant mb-2">Loại chính (chọn để tự lọc bác sĩ + chia khung giờ theo phút)</label>
<div class="grid grid-cols-1 md:grid-cols-3 gap-3">
<label class="flex items-center gap-3 p-3 bg-surface border border-outline rounded-lg cursor-pointer hover:bg-surface-container-low transition-colors has-[:checked]:border-secondary has-[:checked]:bg-secondary-container/30">
<input type="radio" name="loai_chinh" value="" @checked($loaiChinh === '') class="w-4 h-4 text-secondary"/>
<span class="text-body-md">Không chọn (dùng dịch vụ thường)</span>
</label>
<label class="flex items-center gap-3 p-3 bg-surface border border-outline rounded-lg cursor-pointer hover:bg-surface-container-low transition-colors has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50">
<input type="radio" name="loai_chinh" value="tu_van" @checked($loaiChinh === 'tu_van') class="w-4 h-4 text-emerald-500"/>
<span class="flex items-center gap-2 text-body-md"><span class="w-3 h-3 rounded-full bg-emerald-500"></span> Tư vấn / Đọc kết quả</span>
</label>
<label class="flex items-center gap-3 p-3 bg-surface border border-outline rounded-lg cursor-pointer hover:bg-surface-container-low transition-colors has-[:checked]:border-sky-500 has-[:checked]:bg-sky-50">
<input type="radio" name="loai_chinh" value="kham_ls" @checked($loaiChinh === 'kham_ls') class="w-4 h-4 text-sky-500"/>
<span class="flex items-center gap-2 text-body-md"><span class="w-3 h-3 rounded-full bg-sky-500"></span> Thăm khám lâm sàng</span>
</label>
</div>
{{-- Cờ co_tu_van/co_kham_cls vẫn lưu (backend không đổi schema) - đồng bộ từ radio bằng hidden input --}}
<input type="hidden" name="co_tu_van" id="co_tu_van" value="{{ $loaiChinh === 'tu_van' ? '1' : '0' }}"/>
<input type="hidden" name="co_kham_cls" id="co_kham_cls" value="{{ $loaiChinh === 'kham_ls' ? '1' : '0' }}"/>
</div>
@endif

<div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-10">
<!-- Section 1: Customer -->
<div class="space-y-6 order-2">
<div class="flex items-center gap-2 pb-2 border-b border-outline-variant">
<span class="material-symbols-outlined text-secondary">person</span>
<h3 class="text-headline-md font-headline-md">Thông tin Khách hàng</h3>
</div>
<div class="space-y-4">
<div>
<label class="block text-body-sm font-semibold text-on-surface-variant mb-1.5">Họ tên KH (Customer Full Name) <span class="text-error">*</span></label>
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
<div class="space-y-6 order-3">
<div class="flex items-center gap-2 pb-2 border-b border-outline-variant">
<span class="material-symbols-outlined text-secondary">calendar_today</span>
<h3 class="text-headline-md font-headline-md">Lịch trình &amp; Phòng <span class="text-error">*</span></h3>
</div>
<div class="space-y-4">
<div>
<label class="block text-body-sm font-semibold text-on-surface-variant mb-1.5">Ngày đặt lịch (Booking Date) <span class="text-error">*</span></label>
<input name="ngay_dat" value="{{ old('ngay_dat', $bk ? $bk->ngay_dat->toDateString() : now()->toDateString()) }}" required class="w-full px-4 py-2.5 bg-surface border border-outline rounded-lg form-input-focus transition-all text-body-md" type="date"/>
</div>
<div class="grid grid-cols-2 gap-4">
<div>
<label class="block text-body-sm font-semibold text-on-surface-variant mb-1.5">Phòng (Room) <span class="text-error">*</span></label>
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
<label class="block text-body-sm font-semibold text-on-surface-variant mb-1.5">Khung giờ (Time Slot) <span class="text-error">*</span></label>
<select id="khung_gio" name="khung_gio_id" required data-old="{{ old('khung_gio_id', $bk?->khung_gio_id) }}" class="w-full px-4 py-2.5 bg-surface border border-outline rounded-lg form-input-focus transition-all text-body-md font-time-slot"></select>
</div>
</div>
<div class="grid grid-cols-2 gap-4">
<div>
<label class="block text-body-sm font-semibold text-on-surface-variant mb-1.5">Giờ thực hiện DV <span class="text-on-surface-variant/60 text-[11px]">(phút 00/30)</span></label>
<select name="gio_thuc_hien" class="w-full px-4 py-2.5 bg-surface border border-outline rounded-lg form-input-focus transition-all text-body-md font-time-slot">
<option value="">-- Chọn giờ --</option>
@for ($h = 8; $h <= 20; $h++)
@foreach (['00','30'] as $mnt)
@php $val = sprintf('%02d:%s', $h, $mnt); @endphp
<option value="{{ $val }}" @selected(old('gio_thuc_hien', $bk && $bk->gio_thuc_hien ? substr($bk->gio_thuc_hien,0,5) : '')===$val)>{{ $val }}</option>
@endforeach
@endfor
</select>
</div>
<div>
<label class="block text-body-sm font-semibold text-on-surface-variant mb-1.5">Giờ dự kiến kết thúc <span class="text-on-surface-variant/60 text-[11px]">(phút 00/30)</span></label>
<select id="gio_ket_thuc" name="gio_ket_thuc" data-old="{{ old('gio_ket_thuc') }}" class="w-full px-4 py-2.5 bg-surface border border-outline rounded-lg form-input-focus transition-all text-body-md font-time-slot">
<option value="">-- Chọn giờ --</option>
@for ($h = 8; $h <= 21; $h++)
@foreach ($h === 21 ? ['00'] : ['00','30'] as $mnt)
@php $valKt = sprintf('%02d:%s', $h, $mnt); @endphp
<option value="{{ $valKt }}" @selected(old('gio_ket_thuc', $bk && $bk->gio_ket_thuc ? substr($bk->gio_ket_thuc,0,5) : '')===$valKt)>{{ $valKt }}</option>
@endforeach
@endfor
</select>
</div>
</div>
</div>

{{-- Khung tham khảo lịch trình bác sĩ / phòng theo cơ sở --}}
@php $lichTrinh = config('lich-trinh.'.$coSo->slug); @endphp
@if ($lichTrinh)
<details class="mt-4 bg-secondary-container/30 border border-outline-variant rounded-xl overflow-hidden" open>
<summary class="px-4 py-2.5 cursor-pointer flex items-center gap-2 hover:bg-secondary-container/50 select-none">
<span class="material-symbols-outlined text-secondary text-[20px]">info</span>
<span class="font-semibold text-on-secondary-container">Quy tắc đặt lịch — {{ $coSo->ten }}</span>
<span class="text-body-sm text-on-surface-variant ml-2">(giờ hoạt động: {{ $lichTrinh['gio_hoat_dong'] }})</span>
</summary>
<div class="overflow-x-auto bg-surface-container-lowest border-t border-outline-variant">
<table class="w-full text-body-sm">
<thead class="bg-surface-container-low text-left text-label-caps font-label-caps uppercase text-on-surface-variant">
<tr>
<th class="px-3 py-2">Phòng</th>
<th class="px-3 py-2">Bác sĩ / Vai trò</th>
<th class="px-3 py-2">Ưu tiên</th>
<th class="px-3 py-2">Ghi chú</th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant/40">
@foreach ($lichTrinh['rows'] as $r)
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
</table>
</div>
</details>
@endif
</div>

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
<label class="block text-body-sm font-semibold text-on-surface-variant mb-1.5">Liệu pháp (Therapy/Service) <span class="text-error">*</span></label>
<select id="dich_vu" name="dich_vu_id" required class="w-full px-4 py-2.5 bg-surface border border-outline rounded-lg form-input-focus transition-all text-body-md">
@foreach ($dichVus as $dv)
<option value="{{ $dv->id }}" data-nhom="{{ $dv->thuoc_nhom }}" data-phut="{{ $dv->thoi_gian_phut }}" @selected(old('dich_vu_id', $bk?->dich_vu_id)==$dv->id)>{{ $dv->ten }}</option>
@endforeach
</select>
</div>
<div class="grid grid-cols-2 gap-4">
<div>
<label class="block text-body-sm font-semibold text-on-surface-variant mb-1.5">Số liệu trình</label>
<input name="so_lieu_trinh" value="{{ old('so_lieu_trinh', $bk?->so_lieu_trinh) }}" class="w-full px-4 py-2.5 bg-surface border border-outline rounded-lg form-input-focus transition-all text-body-md" placeholder="VD: 1/10" type="text"/>
</div>
<div>
<label class="block text-body-sm font-semibold text-on-surface-variant mb-1.5">Bác sĩ <span class="text-on-surface-variant/60 text-[11px]" id="bs_hint"></span></label>
<select id="bac_si" name="bac_si_user_id" class="w-full px-4 py-2.5 bg-surface border border-outline rounded-lg form-input-focus transition-all text-body-md">
<option value="">-- Chọn --</option>
@foreach ($bacSis as $bs)
<option value="{{ $bs->id }}" @selected(old('bac_si_user_id', $bk?->bac_si_user_id)==$bs->id)>{{ $bs->ten_day_du }}</option>
@endforeach
</select>
</div>
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
<div class="space-y-6 order-4">
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
<div class="mt-12 flex justify-between items-center pt-8 border-t border-outline-variant">
<a href="{{ $editing ? '/'.$coSo->slug.'/danh-sach' : '/'.$coSo->slug.'/lich-hen' }}" class="px-6 py-2.5 text-on-surface-variant font-semibold hover:bg-surface-container-high rounded-lg transition-colors">Hủy bỏ (Cancel)</a>
<div class="flex gap-4">
<button class="px-8 py-2.5 bg-secondary-container text-on-secondary-container font-bold rounded-lg hover:opacity-90 active:scale-95 transition-all flex items-center gap-2 shadow-md" type="submit">
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">{{ $editing ? 'save' : 'add_task' }}</span>
{{ $editing ? 'Lưu thay đổi' : 'Tạo Lịch Hẹn (Create)' }}
</button>
</div>
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
            const bsEl = document.getElementById('bac_si');
            const params = new URLSearchParams({ phong_id: phong.value, ngay: ngay ? ngay.value : '' });
            if (editId) params.append('except', editId);
            if (bsEl && bsEl.value) params.append('bac_si_id', bsEl.value);
            if (dvEl && dvEl.value) params.append('dich_vu_id', dvEl.value);
            try {
                const r = await fetch(`/${slug}/tao-moi/khung-gio?${params}`);
                data = await r.json();
            } catch (e) {}
        }
        const slots = data.slots || [];
        fullHours = new Set(slots.filter(s => s.full).map(s => s.gio));
        khung.innerHTML = slots.length
            ? slots.flatMap(s => {
                // Nếu có sub-slot (đã chọn BS + dịch vụ) → render từng sub-slot
                if (s.sub_slots && s.sub_slots.length) {
                    return s.sub_slots.map(ss => {
                        const lbl = `${ss.bd} - ${ss.kt}` + (ss.full ? ' — đã đặt 🔒' : '');
                        return `<option value="${s.id}" data-kt="${ss.kt}" data-bd="${ss.bd}" data-gio="${s.gio}" ${ss.full ? 'disabled' : ''}>${lbl}</option>`;
                    });
                }
                const trong = s.capacity > 1 ? ` (còn ${s.capacity - s.booked}/${s.capacity})` : '';
                return [`<option value="${s.id}" data-kt="${s.kt}" data-bd="${s.bd}" data-gio="${s.gio}" ${s.full ? 'disabled' : ''}>${s.nhan}${s.full ? ' — đã đầy 🔒' : trong}</option>`];
            }).join('')
            : '<option value="">(Phòng chưa cấu hình khung giờ)</option>';
        if (oldKhung) { const o = khung.querySelector(`option[value="${oldKhung}"]`); if (o && !o.disabled) khung.value = oldKhung; }
        if (khung.selectedOptions[0] && khung.selectedOptions[0].disabled) {
            const avail = khung.querySelector('option:not([disabled])');
            khung.value = avail ? avail.value : '';
        }
        applyTimeLocks();
        updateEnd();
    }

    // Khóa các mốc giờ bắt đầu / kết thúc thuộc khung giờ đã đầy
    function applyTimeLocks() {
        if (batDau) [...batDau.options].forEach(o => {
            if (o.value) o.disabled = fullHours.has(parseInt(o.value.slice(0, 2)));
        });
        [...ketThuc.options].forEach(o => {
            if (!o.value) return;
            const h = parseInt(o.value.slice(0, 2)), m = o.value.slice(3, 5);
            o.disabled = fullHours.has(m === '00' ? h - 1 : h);
        });
    }

    // Gợi ý giờ thực hiện + giờ kết thúc theo khung giờ (hoặc sub-slot) đã chọn
    function updateEnd() {
        if (firstRun) { firstRun = false; if (!ketThuc.dataset.old) return; }

        const opt = khung.options[khung.selectedIndex];
        if (!opt) return;
        const bd = opt.getAttribute('data-bd') || '';
        const kt = opt.getAttribute('data-kt') || '';

        // Đảm bảo giờ bd/kt tồn tại trong dropdown (insert nếu thiếu, vì có phút lẻ 25, 05...)
        const ensureOption = (sel, val) => {
            if (!val) return;
            if (![...sel.options].some(o => o.value === val)) {
                const opt = document.createElement('option');
                opt.value = val; opt.textContent = val;
                sel.appendChild(opt);
            }
        };
        if (batDau && bd) { ensureOption(batDau, bd); batDau.value = bd; }
        if (kt) { ensureOption(ketThuc, kt); ketThuc.value = kt; }
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
    const hCoTuVan = document.getElementById('co_tu_van');
    const hCoKhamCls = document.getElementById('co_kham_cls');
    document.querySelectorAll('input[name="loai_chinh"]').forEach(r => {
        r.addEventListener('change', () => {
            const v = r.value;
            if (hCoTuVan) hCoTuVan.value = v === 'tu_van' ? '1' : '0';
            if (hCoKhamCls) hCoKhamCls.value = v === 'kham_ls' ? '1' : '0';

            // Auto-select dịch vụ matching nhóm
            if (v && dichVu) {
                const match = [...dichVu.options].find(o => o.dataset.nhom === v);
                if (match) dichVu.value = match.value;
            }
            loadBacSi();
            loadSlots();
        });
    });

    let bsAbortCtl;

    async function loadBacSi() {
        if (!bacSi || !khung || !dichVu || !ngay) return;
        const params = new URLSearchParams({
            khung_gio_id: khung.value || '',
            dich_vu_id: dichVu.value || '',
            ngay: ngay.value || '',
        });
        if (editId) params.append('except', editId);
        if (!khung.value || !dichVu.value || !ngay.value) return;

        if (bsAbortCtl) bsAbortCtl.abort();
        bsAbortCtl = new AbortController();
        if (bsHint) bsHint.textContent = '(đang kiểm tra…)';

        try {
            const r = await fetch(`/${slug}/tao-moi/check-bac-si?${params}`, { signal: bsAbortCtl.signal });
            const j = await r.json();
            const map = {};
            (j.list || []).forEach(b => { map[b.id] = b; });

            [...bacSi.options].forEach(o => {
                if (!o.value) return;
                const info = map[o.value];
                if (!info) { o.disabled = false; o.textContent = o.dataset.origin || o.textContent; return; }
                if (!o.dataset.origin) o.dataset.origin = o.textContent;
                if (info.available) {
                    o.disabled = false;
                    o.textContent = o.dataset.origin;
                } else {
                    o.disabled = true;
                    o.textContent = o.dataset.origin + ' — đã kín lịch 🔒';
                }
            });
            // Nếu BS đang chọn bị disable thì auto bỏ chọn
            if (bacSi.selectedOptions[0] && bacSi.selectedOptions[0].disabled) {
                bacSi.value = '';
            }
            if (bsHint) bsHint.textContent = '';
        } catch (e) {
            if (e.name !== 'AbortError' && bsHint) bsHint.textContent = '';
        }
    }
    if (bacSi) {
        if (dichVu) {
            dichVu.addEventListener('change', () => { loadBacSi(); loadSlots(); });
        }
        if (khung) khung.addEventListener('change', loadBacSi);
        if (ngay) ngay.addEventListener('change', loadBacSi);
        bacSi.addEventListener('change', loadSlots);
        loadBacSi();
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
