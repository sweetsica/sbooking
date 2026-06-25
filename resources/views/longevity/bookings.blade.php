<!DOCTYPE html>

<html class="light" lang="vi"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Quản lý Đặt lịch - Chế độ Danh sách</title>
<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&amp;family=Inter:wght@400;500;600&amp;family=JetBrains+Mono:wght@500;600&amp;display=swap" rel="stylesheet"/>
<!-- Material Symbols -->
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<!-- Tailwind CSS -->
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "on-tertiary-fixed": "#002113",
                        "surface-bright": "#f7f9fb",
                        "on-surface": "#191c1e",
                        "on-secondary": "#ffffff",
                        "inverse-primary": "#bec6e0",
                        "tertiary-fixed-dim": "#4edea3",
                        "secondary": "#006591",
                        "tertiary-fixed": "#6ffbbe",
                        "primary-container": "#131b2e",
                        "on-tertiary-container": "#009668",
                        "on-tertiary-fixed-variant": "#005236",
                        "on-primary-fixed": "#131b2e",
                        "on-surface-variant": "#45464d",
                        "on-primary-fixed-variant": "#3f465c",
                        "inverse-on-surface": "#eff1f3",
                        "surface-container-lowest": "#ffffff",
                        "on-secondary-container": "#004666",
                        "surface-container-low": "#f2f4f6",
                        "surface-container-highest": "#e0e3e5",
                        "on-primary-container": "#7c839b",
                        "on-secondary-fixed": "#001e2f",
                        "surface": "#f7f9fb",
                        "on-primary": "#ffffff",
                        "error-container": "#ffdad6",
                        "surface-variant": "#e0e3e5",
                        "on-tertiary": "#ffffff",
                        "on-error": "#ffffff",
                        "surface-tint": "#565e74",
                        "surface-dim": "#d8dadc",
                        "inverse-surface": "#2d3133",
                        "outline-variant": "#c6c6cd",
                        "secondary-fixed": "#c9e6ff",
                        "on-secondary-fixed-variant": "#004c6e",
                        "tertiary": "#000000",
                        "surface-container": "#eceef0",
                        "outline": "#76777d",
                        "secondary-container": "#39b8fd",
                        "background": "#f7f9fb",
                        "primary-fixed": "#dae2fd",
                        "secondary-fixed-dim": "#89ceff",
                        "on-background": "#191c1e",
                        "tertiary-container": "#002113",
                        "primary": "#000000",
                        "surface-container-high": "#e6e8ea",
                        "primary-fixed-dim": "#bec6e0",
                        "error": "#ba1a1a",
                        "on-error-container": "#93000a"
                    },
                    "borderRadius": {
                        "DEFAULT": "0.125rem",
                        "lg": "0.25rem",
                        "xl": "0.5rem",
                        "full": "0.75rem"
                    },
                    "spacing": {
                        "container-margin": "24px",
                        "gutter": "12px",
                        "row-height-standard": "56px",
                        "row-height-compact": "40px",
                        "unit": "4px"
                    },
                    "fontFamily": {
                        "headline-md": ["Manrope"],
                        "time-slot": ["JetBrains Mono"],
                        "body-md": ["Inter"],
                        "body-sm": ["Inter"],
                        "headline-lg": ["Manrope"],
                        "label-caps": ["JetBrains Mono"]
                    },
                    "fontSize": {
                        "headline-md": ["18px", {"lineHeight": "24px", "fontWeight": "600"}],
                        "time-slot": ["12px", {"lineHeight": "16px", "fontWeight": "500"}],
                        "body-md": ["14px", {"lineHeight": "20px", "fontWeight": "400"}],
                        "body-sm": ["13px", {"lineHeight": "18px", "fontWeight": "400"}],
                        "headline-lg": ["24px", {"lineHeight": "32px", "fontWeight": "700"}],
                        "label-caps": ["11px", {"lineHeight": "16px", "letterSpacing": "0.05em", "fontWeight": "600"}]
                    }
                },
            },
        }
    </script>
<style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f7f9fb;
        }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            vertical-align: middle;
        }
        .custom-scrollbar::-webkit-scrollbar {
            width: 10px;
            height: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #c6c6cd;
            border-radius: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #9ca3af;
        }
        .custom-scrollbar {
            scrollbar-width: thin;
            scrollbar-color: #c6c6cd #f1f1f1;
        }
        .sticky-col {
            position: sticky;
            background-color: inherit;
            z-index: 10;
        }
        .sticky-left-0 { left: 0; }
        .sticky-right-0 { right: 0; }
    </style>
</head>
<body class="bg-surface text-on-surface">
@if (session('ok'))
<div class="fixed top-20 left-1/2 -translate-x-1/2 z-[60] px-5 py-3 rounded-xl bg-tertiary-fixed-dim/90 text-on-tertiary-container shadow-lg flex items-center gap-2 text-body-md font-semibold" id="flash-ok">
<span class="material-symbols-outlined">check_circle</span> {{ session('ok') }}
</div>
<script>setTimeout(()=>document.getElementById('flash-ok')?.remove(), 4000);</script>
@endif
@if (session('warning'))
<div class="fixed top-32 left-1/2 -translate-x-1/2 z-[60] max-w-md px-5 py-3 rounded-xl bg-error-container/95 text-on-error-container shadow-lg flex items-start gap-2 text-body-md font-semibold" id="flash-warning">
<span class="material-symbols-outlined">warning</span> {{ session('warning') }}
</div>
<script>setTimeout(()=>document.getElementById('flash-warning')?.remove(), 8000);</script>
@endif
<!-- Top Navigation Bar -->
@php $approvalMode = $approvalMode ?? false; @endphp
@include('partials.topnav', ['active' => $approvalMode ? 'duyet-lich' : 'lich-hen'])
<!-- Main Content -->
<main class="pt-24 pb-12 px-container-margin">
<div class="max-w-[1650px] mx-auto">
<!-- Header & View Switcher -->
<div class="flex items-center justify-between mb-6">
<h2 class="text-headline-lg font-extrabold text-on-surface uppercase tracking-tight">{{ $approvalMode ? 'Duyệt lịch — đơn chờ duyệt' : 'Quản lý Đặt lịch' }}</h2>
@unless ($approvalMode)
<div class="flex items-center gap-1 bg-surface-container-low p-1 rounded-xl w-fit">
<a href="/{{ $coSo->slug }}/lich-hen" class="px-6 py-2 text-body-md font-semibold text-on-surface-variant hover:text-on-surface transition-all inline-block">Lịch trình</a>
<button class="px-6 py-2 bg-surface-container-lowest text-on-surface font-bold rounded-lg shadow-sm border border-outline-variant/30 flex items-center gap-2">
<span class="material-symbols-outlined text-[18px]">format_list_bulleted</span>
                    Danh sách chi tiết
                </button>
</div>
@endunless
</div>
<!-- Advanced Filters -->
<form method="GET" class="bg-surface-container-lowest p-6 rounded-2xl border border-outline-variant shadow-sm space-y-4 mb-8">
<div class="flex flex-wrap items-end gap-4">
<div class="flex flex-col gap-1.5">
<label class="text-label-caps font-label-caps text-on-surface-variant ml-1">KHOẢNG THỜI GIAN</label>
<div class="flex items-center gap-2">
<input name="ngay_tu" value="{{ $filters['ngay_tu'] ?? '' }}" class="border border-outline-variant rounded-lg px-3 py-2 text-body-md focus:border-secondary focus:ring-1 focus:ring-secondary/20 outline-none transition-all bg-surface" type="date"/>
<span class="text-on-surface-variant">đến</span>
<input name="ngay_den" value="{{ $filters['ngay_den'] ?? '' }}" class="border border-outline-variant rounded-lg px-3 py-2 text-body-md focus:border-secondary focus:ring-1 focus:ring-secondary/20 outline-none transition-all bg-surface" type="date"/>
</div>
</div>
<div class="flex flex-col gap-1.5">
<label class="text-label-caps font-label-caps text-on-surface-variant ml-1">PHÒNG</label>
<select name="phong_id" class="border border-outline-variant rounded-lg px-3 py-2 text-body-md focus:border-secondary focus:ring-1 focus:ring-secondary/20 outline-none transition-all bg-surface min-w-[160px]">
<option value="">Tất cả phòng</option>
@foreach ($phongs as $p)
<option value="{{ $p->id }}" @selected(($filters['phong_id'] ?? '')==$p->id)>{{ $p->ten }}</option>
@endforeach
</select>
</div>
<div class="flex flex-col gap-1.5">
<label class="text-label-caps font-label-caps text-on-surface-variant ml-1">BÁC SĨ</label>
<select name="bac_si_id" class="border border-outline-variant rounded-lg px-3 py-2 text-body-md focus:border-secondary focus:ring-1 focus:ring-secondary/20 outline-none transition-all bg-surface min-w-[160px]">
<option value="">Tất cả bác sĩ</option>
@foreach ($bacSis as $bs)
<option value="{{ $bs->id }}" @selected(($filters['bac_si_id'] ?? '')==$bs->id)>{{ trim(($bs->chuc_danh ? $bs->chuc_danh.' ' : '').$bs->name) }}</option>
@endforeach
</select>
</div>
<div class="flex flex-col gap-1.5">
<label class="text-label-caps font-label-caps text-on-surface-variant ml-1">NV PHỤ TRÁCH</label>
<select name="sale_id" class="border border-outline-variant rounded-lg px-3 py-2 text-body-md focus:border-secondary focus:ring-1 focus:ring-secondary/20 outline-none transition-all bg-surface min-w-[160px]">
<option value="">Tất cả NV</option>
@foreach ($sales as $s)
<option value="{{ $s->id }}" @selected(($filters['sale_id'] ?? '')==$s->id)>{{ $s->name }}{{ $s->chuc_danh ? ' ('.$s->chuc_danh.')' : '' }}</option>
@endforeach
</select>
</div>
<div class="flex flex-col gap-1.5">
<label class="text-label-caps font-label-caps text-on-surface-variant ml-1">NGUỒN</label>
<select name="nguon" class="border border-outline-variant rounded-lg px-3 py-2 text-body-md focus:border-secondary focus:ring-1 focus:ring-secondary/20 outline-none transition-all bg-surface min-w-[160px]">
<option value="">Tất cả nguồn</option>
@foreach ($nguons as $ng)
<option value="{{ $ng }}" @selected(($filters['nguon'] ?? '')===$ng)>{{ $ng }}</option>
@endforeach
</select>
</div>
@unless ($approvalMode)
<div class="flex flex-col gap-1.5">
<label class="text-label-caps font-label-caps text-on-surface-variant ml-1">TRẠNG THÁI</label>
<select name="trang_thai" class="border border-outline-variant rounded-lg px-3 py-2 text-body-md focus:border-secondary focus:ring-1 focus:ring-secondary/20 outline-none transition-all bg-surface min-w-[160px]">
<option value="">Tất cả</option>
<option value="cho_duyet" @selected(($filters['trang_thai'] ?? '')==='cho_duyet')>Chờ duyệt</option>
<option value="da_duyet" @selected(($filters['trang_thai'] ?? '')==='da_duyet')>Đã duyệt</option>
<option value="da_xong" @selected(($filters['trang_thai'] ?? '')==='da_xong')>Đã xong</option>
<option value="tu_choi" @selected(($filters['trang_thai'] ?? '')==='tu_choi')>Từ chối</option>
</select>
</div>
@endunless
@php
    $pbId = auth()->user()->phong_ban_id;
    $vtId = auth()->user()->vai_tro_id;
    $isAdmin = auth()->user()->is_admin;
    $canExportBooking = $isAdmin || \App\Models\PhanQuyen::where(fn($q) => $q->where('phong_ban_id', $pbId)->orWhere('vai_tro_id', $vtId))->where('truong', 'xuat_lich_dat_phong')->exists();
    $canDeleteBooking = $isAdmin || \App\Models\PhanQuyen::where(fn($q) => $q->where('phong_ban_id', $pbId)->orWhere('vai_tro_id', $vtId))->where('truong', 'xoa_booking')->exists();
    $canEditBooking = $isAdmin || \App\Models\PhanQuyen::where(fn($q) => $q->where('phong_ban_id', $pbId)->orWhere('vai_tro_id', $vtId))->where('truong', 'sua_booking')->exists();
    $canDuyet = $isAdmin || \App\Models\PhanQuyen::where(fn($q) => $q->where('phong_ban_id', $pbId)->orWhere('vai_tro_id', $vtId))->where('truong', 'duyet_booking')->exists();
@endphp
<div class="ml-auto flex items-center gap-2">
<a href="/{{ $coSo->slug }}/{{ $approvalMode ? 'duyet-lich' : 'danh-sach' }}" class="flex items-center gap-2 px-4 py-2.5 text-body-sm font-semibold text-on-surface-variant bg-surface border border-outline-variant rounded-lg hover:bg-surface-variant transition-colors">
<span class="material-symbols-outlined text-[18px]">restart_alt</span> Đặt lại
</a>
<button type="submit" class="flex items-center gap-2 px-4 py-2.5 text-body-sm font-semibold bg-primary text-on-primary rounded-lg hover:opacity-90 transition-opacity">
<span class="material-symbols-outlined text-[18px]">filter_list</span> Lọc dữ liệu
</button>
@if ($canExportBooking)
<a href="/{{ $coSo->slug }}/xuat-booking" class="flex items-center gap-2 px-4 py-2.5 text-body-sm font-semibold bg-on-tertiary-container text-on-primary rounded-lg hover:opacity-90 transition-opacity">
<span class="material-symbols-outlined text-[18px]">download</span> Xuất Excel
</a>
<label class="flex items-center gap-2 px-4 py-2.5 text-body-sm font-semibold text-on-surface-variant bg-surface border border-outline-variant rounded-lg cursor-pointer hover:bg-surface-variant transition-colors">
<span class="material-symbols-outlined text-[18px]">upload</span> Chọn file
<input type="file" name="file" form="import-booking" accept=".xlsx,.xls,.csv" class="hidden" onchange="this.form.submit()"/>
</label>
@endif
</div>
</div>
</form>
@if ($canExportBooking)
<form id="import-booking" method="POST" action="/{{ $coSo->slug }}/nhap-booking" enctype="multipart/form-data" class="hidden">@csrf</form>
@endif
<!-- Data Table Container -->
<div class="bg-surface-container-lowest rounded-2xl border border-outline-variant shadow-sm overflow-hidden flex flex-col">
<div class="overflow-x-auto custom-scrollbar w-full min-w-0">
<table class="w-full text-left border-collapse table-auto min-w-[1800px] whitespace-nowrap">
<thead>
<tr class="bg-surface-container-low border-b border-outline-variant">
<th class="px-4 py-4 text-label-caps font-label-caps text-on-surface-variant sticky-col sticky-left-0 bg-surface-container-low shadow-[2px_0_5px_rgba(0,0,0,0.05)]">DẤU THỜI GIAN</th>
<th class="px-4 py-4 text-label-caps font-label-caps text-on-surface-variant">HỌ TÊN KH</th>
<th class="px-4 py-4 text-label-caps font-label-caps text-on-surface-variant">SỐ ĐIỆN THOẠI</th>
<th class="px-4 py-4 text-label-caps font-label-caps text-on-surface-variant">ĐỊA CHỈ EMAIL</th>
<th class="px-4 py-4 text-label-caps font-label-caps text-on-surface-variant">NGÀY ĐẶT</th>
<th class="px-4 py-4 text-label-caps font-label-caps text-on-surface-variant">PHÒNG</th>
<th class="px-4 py-4 text-label-caps font-label-caps text-on-surface-variant">KHUNG GIỜ</th>
<th class="px-4 py-4 text-label-caps font-label-caps text-on-surface-variant">THỰC HIỆN DV</th>
<th class="px-4 py-4 text-label-caps font-label-caps text-on-surface-variant">DỰ KIẾN KẾT THÚC</th>
<th class="px-4 py-4 text-label-caps font-label-caps text-on-surface-variant">NGUỒN</th>
<th class="px-4 py-4 text-label-caps font-label-caps text-on-surface-variant">SALE</th>
<th class="px-4 py-4 text-label-caps font-label-caps text-on-surface-variant">LIỆU PHÁP</th>
<th class="px-4 py-4 text-label-caps font-label-caps text-on-surface-variant">SỐ LIỆU TRÌNH</th>
<th class="px-4 py-4 text-label-caps font-label-caps text-on-surface-variant">KẾT HỢP MEDICAL?</th>
<th class="px-4 py-4 text-label-caps font-label-caps text-on-surface-variant">BÁC SĨ</th>
<th class="px-4 py-4 text-label-caps font-label-caps text-on-surface-variant">KTV</th>
<th class="px-4 py-4 text-label-caps font-label-caps text-on-surface-variant">GHI CHÚ</th>
<th class="px-4 py-4 text-label-caps font-label-caps text-on-surface-variant sticky-col sticky-right-0 bg-surface-container-low text-right shadow-[-2px_0_5px_rgba(0,0,0,0.05)]">THAO TÁC</th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant/30">
@forelse ($bookings as $b)
@php $rejected = $b->trang_thai === 'tu_choi'; $rowBg = $rejected ? 'bg-red-50' : 'bg-surface-container-lowest'; @endphp
<tr class="transition-colors {{ $rejected ? 'bg-red-50 hover:bg-red-100/60' : 'hover:bg-surface-variant/10' }}">
<td class="px-4 py-4 sticky-col sticky-left-0 {{ $rowBg }} shadow-[2px_0_5px_rgba(0,0,0,0.05)]">
<span class="text-body-sm font-time-slot text-on-surface-variant">{{ $b->created_at->format('d/m H:i') }}</span>
</td>
<td class="px-4 py-4 font-bold text-on-surface">{{ $b->khachHang?->ho_ten }}</td>
<td class="px-4 py-4 text-body-sm font-time-slot">{{ $b->khachHang?->so_dien_thoai }}</td>
<td class="px-4 py-4 text-body-sm text-on-surface-variant">{{ $b->khachHang?->email ?: '—' }}</td>
<td class="px-4 py-4 text-body-sm">{{ $b->ngay_dat?->format('d/m/Y') }}</td>
<td class="px-4 py-4 text-body-sm font-semibold">{{ $b->phong?->ten ?? '—' }}</td>
<td class="px-4 py-4 text-body-sm font-time-slot">{{ $b->khungGio?->nhan ?? '—' }}</td>
<td class="px-4 py-4 text-body-sm font-time-slot">{{ $b->gio_thuc_hien ? substr($b->gio_thuc_hien,0,5) : '—' }}</td>
<td class="px-4 py-4 text-body-sm font-time-slot">{{ $b->gio_ket_thuc ? substr($b->gio_ket_thuc,0,5) : ($b->khungGio ? substr($b->khungGio->gio_ket_thuc,0,5) : '—') }}</td>
<td class="px-4 py-4"><span class="px-2 py-0.5 bg-surface-container-high rounded text-[11px]">{{ $b->nguon ?? '—' }}</span></td>
<td class="px-4 py-4 text-body-sm">{{ $b->sale?->name ?? '—' }}</td>
<td class="px-4 py-4 text-body-sm">{{ $b->dichVu?->ten ?? '—' }}</td>
<td class="px-4 py-4 text-body-sm text-center">{{ $b->so_lieu_trinh ?? '—' }}</td>
<td class="px-4 py-4 text-center">
@if ($b->ket_hop_medical)
<span class="material-symbols-outlined text-on-tertiary-container text-[20px]">check_circle</span>
@else
<span class="text-on-surface-variant text-body-sm">—</span>
@endif
</td>
<td class="px-4 py-4 text-body-sm">{{ $b->bacSi?->ten_day_du ?? '—' }}</td>
<td class="px-4 py-4 text-body-sm">{{ $b->ktv?->ten_day_du ?? '—' }}</td>
<td class="px-4 py-4 text-body-sm text-on-surface-variant italic truncate max-w-[150px]" title="{{ $b->ghi_chu }}">{{ $b->ghi_chu ?: '—' }}</td>
<td class="px-4 py-4 sticky-col sticky-right-0 {{ $rowBg }} shadow-[-2px_0_5px_rgba(0,0,0,0.05)]">
<div class="flex items-center justify-end gap-1">
@php
    $approved = $b->trang_thai === 'da_duyet';
    $done = $b->trang_thai === 'da_xong';
    $badge = $done
        ? ['Đã xong', 'bg-primary/10 text-primary']
        : ($approved ? ['Đã duyệt', 'bg-tertiary-fixed-dim/40 text-on-tertiary-container']
        : ($rejected ? ['Từ chối', 'bg-red-100 text-red-700']
                     : ['Chờ duyệt', 'bg-secondary-container/40 text-on-secondary-container']));
@endphp
<span class="px-2 py-0.5 mr-1 rounded-full text-[11px] font-semibold {{ $badge[1] }}" @if($rejected && $b->ly_do_tu_choi) title="Lý do từ chối: {{ $b->ly_do_tu_choi }}" @endif>{{ $badge[0] }}</span>
@if ($canDuyet)
@unless ($done)
<form method="POST" action="/{{ $coSo->slug }}/duyet-dat-phong/{{ $b->id }}" class="inline">
@csrf @method('PATCH')
<button type="submit" title="{{ $approved ? 'Bỏ duyệt' : 'Duyệt' }}" class="w-7 h-7 rounded-full text-[12px] font-bold border flex items-center justify-center transition-colors {{ $approved ? 'bg-on-tertiary-container text-white border-on-tertiary-container hover:bg-error hover:border-error' : 'text-outline border-outline-variant hover:border-on-tertiary-container hover:text-on-tertiary-container' }}">
<span class="material-symbols-outlined text-[16px]">{{ $approved ? 'close' : 'check' }}</span>
</button>
</form>
@endunless
@unless ($done || $rejected)
<button type="button" onclick="openReject({{ $b->id }}, @js($b->khachHang?->ho_ten ?? 'khách'))" title="Từ chối (không duyệt)" class="w-7 h-7 rounded-full text-[12px] font-bold border flex items-center justify-center transition-colors text-red-400 border-red-200 hover:bg-red-500 hover:text-white hover:border-red-500">
<span class="material-symbols-outlined text-[16px]">block</span>
</button>
@endunless
@if ($approved || $done)
<form method="POST" action="/{{ $coSo->slug }}/xong-dat-phong/{{ $b->id }}" class="inline">
@csrf @method('PATCH')
<button type="submit" title="{{ $done ? 'Hoàn tác về Đã duyệt' : 'Đánh dấu Đã xong' }}" class="w-7 h-7 rounded-full text-[12px] font-bold border flex items-center justify-center transition-colors {{ $done ? 'bg-primary text-on-primary border-primary hover:opacity-80' : 'text-outline border-outline-variant hover:border-primary hover:text-primary' }}">
<span class="material-symbols-outlined text-[16px]">{{ $done ? 'undo' : 'task_alt' }}</span>
</button>
</form>
@endif
@endif
<a href="/{{ $coSo->slug }}/xem-dat-phong/{{ $b->id }}" title="Xem chi tiết" class="p-1.5 rounded-lg text-on-surface-variant hover:bg-surface-variant transition-colors">
<span class="material-symbols-outlined text-[18px]">visibility</span>
</a>
@if ($canEditBooking)
<a href="/{{ $coSo->slug }}/sua-dat-phong/{{ $b->id }}" title="Sửa" class="p-1.5 rounded-lg text-secondary hover:bg-secondary-container/30 transition-colors">
<span class="material-symbols-outlined text-[18px]">edit</span>
</a>
@endif
@if ($canDeleteBooking)
<form method="POST" action="/{{ $coSo->slug }}/xoa-dat-phong/{{ $b->id }}" class="inline" onsubmit="return confirm('Xóa lịch hẹn đặt phòng này? Hành động không thể hoàn tác.')">
@csrf @method('DELETE')
<button type="submit" title="Xóa" class="p-1.5 rounded-lg text-error hover:bg-error-container transition-colors">
<span class="material-symbols-outlined text-[18px]">delete</span>
</button>
</form>
@endif
</div>
</td>
</tr>
@empty
<tr>
<td colspan="18" class="px-4 py-16 text-center">
<div class="flex flex-col items-center gap-2 text-on-surface-variant">
<span class="material-symbols-outlined text-[40px] opacity-50">{{ $approvalMode ? 'task_alt' : 'event_busy' }}</span>
<p class="text-body-md">{{ $approvalMode ? 'Không còn đơn nào đang chờ duyệt.' : 'Chưa có lịch hẹn nào khớp bộ lọc.' }}</p>
@unless ($approvalMode)
<a href="/{{ $coSo->slug }}/tao-moi" class="mt-2 px-4 py-2 bg-secondary-container text-on-secondary-container rounded-lg font-semibold text-body-sm">+ Đặt lịch phòng khám</a>
@endunless
</div>
</td>
</tr>
@endforelse
</tbody>
</table>
</div>
<!-- Pagination -->
<div class="px-6 py-4 bg-surface-container-low border-t border-outline-variant flex items-center justify-between gap-4">
<p class="text-body-sm text-on-surface-variant">
@if ($bookings->total() > 0)
Hiển thị {{ $bookings->firstItem() }} - {{ $bookings->lastItem() }} trên tổng số {{ $bookings->total() }} kết quả
@else
Không có kết quả
@endif
</p>
<div>{{ $bookings->links() }}</div>
</div>
</div>
</div>
</main>
<!-- Floating Action Button -->
<a href="/{{ $coSo->slug }}/tao-moi" class="fixed bottom-8 right-8 w-14 h-14 bg-primary text-on-primary rounded-full shadow-lg hover:scale-105 active:scale-95 transition-all flex items-center justify-center z-50 group">
<span class="material-symbols-outlined text-[28px]">add</span>
<span class="absolute right-full mr-4 px-3 py-1.5 bg-inverse-surface text-inverse-on-surface text-body-sm rounded-lg opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">Thêm lịch hẹn mới</span>
</a>
<script>
    // Simple table interaction enhancement
    const tableContainer = document.querySelector('.overflow-x-auto');
    let isDown = false;
    let startX;
    let scrollLeft;

    tableContainer.addEventListener('mousedown', (e) => {
        isDown = true;
        tableContainer.classList.add('active');
        startX = e.pageX - tableContainer.offsetLeft;
        scrollLeft = tableContainer.scrollLeft;
    });
    tableContainer.addEventListener('mouseleave', () => {
        isDown = false;
    });
    tableContainer.addEventListener('mouseup', () => {
        isDown = false;
    });
    tableContainer.addEventListener('mousemove', (e) => {
        if (!isDown) return;
        e.preventDefault();
        const x = e.pageX - tableContainer.offsetLeft;
        const walk = (x - startX) * 2;
        tableContainer.scrollLeft = scrollLeft - walk;
    });
</script>

@if ($canDuyet)
<!-- Popup lý do từ chối (dùng chung cho mọi dòng) -->
<div id="reject-modal" class="fixed inset-0 z-[60] hidden items-center justify-center bg-black/40 p-4">
<div class="w-full max-w-md bg-surface-container-lowest rounded-xl shadow-xl border border-outline-variant overflow-hidden">
<form id="reject-form" method="POST" action="">
@csrf @method('PATCH')
<div class="p-5 border-b border-outline-variant flex items-center gap-2">
<span class="material-symbols-outlined text-red-500">block</span>
<h3 class="text-headline-md font-headline-md text-on-surface">Từ chối lịch hẹn</h3>
</div>
<div class="p-5 space-y-2">
<p class="text-body-sm text-on-surface-variant">Lịch hẹn của <span id="reject-name" class="font-semibold text-on-surface"></span> sẽ chuyển sang trạng thái <span class="text-red-600 font-semibold">Từ chối</span>.</p>
<label class="text-label-caps font-label-caps text-red-600 block">Lý do từ chối<span class="text-red-500 ml-0.5">*</span></label>
<textarea name="ly_do_tu_choi" required rows="3" placeholder="Nhập lý do từ chối lịch hẹn này..." class="w-full px-3 py-2 rounded-lg text-body-md outline-none transition-all border border-red-300 bg-red-50/40 focus:border-red-500 focus:ring-1 focus:ring-red-500/20"></textarea>
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
(function(){
    var base = "/{{ $coSo->slug }}/tu-choi-dat-phong/";
    var m = document.getElementById('reject-modal');
    var f = document.getElementById('reject-form');
    window.openReject = function(id, name){
        f.action = base + id;
        document.getElementById('reject-name').textContent = name || 'khách';
        f.ly_do_tu_choi.value = '';
        m.classList.remove('hidden'); m.classList.add('flex');
        document.body.style.overflow = 'hidden';
        setTimeout(function(){ f.ly_do_tu_choi.focus(); }, 50);
    };
    window.closeReject = function(){
        m.classList.add('hidden'); m.classList.remove('flex');
        document.body.style.overflow = '';
    };
    m.addEventListener('click', function(e){ if(e.target === this) closeReject(); });
    document.addEventListener('keydown', function(e){ if(e.key === 'Escape') closeReject(); });
})();
</script>
@endif
@include('partials.datepicker')
</body></html>
