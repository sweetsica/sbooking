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
{{-- 2026-08-03 fix: thiếu block 'error' → bấm Duyệt fail silently không thấy thông báo (VD BS không nhận tư vấn). --}}
@if (session('error'))
<div class="fixed top-20 left-1/2 -translate-x-1/2 z-[60] max-w-lg px-5 py-3 rounded-xl bg-error text-on-error shadow-lg flex items-start gap-2 text-body-md font-semibold" id="flash-error">
<span class="material-symbols-outlined">error</span> {{ session('error') }}
<button onclick="document.getElementById('flash-error')?.remove()" class="ml-2 material-symbols-outlined text-lg opacity-70 hover:opacity-100">close</button>
</div>
<script>setTimeout(()=>document.getElementById('flash-error')?.remove(), 10000);</script>
@endif
<!-- Top Navigation Bar -->
@php $approvalMode = $approvalMode ?? false; @endphp
@include('partials.topnav', ['active' => $approvalMode ? 'duyet-lich' : 'lich-hen'])
<!-- Main Content -->
<main class="pt-24 pb-32 sm:pb-12 px-container-margin">
<div class="max-w-[1650px] mx-auto">
<!-- Header & View Switcher -->
<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 gap-3 sm:gap-4">
<h2 class="text-headline-lg font-extrabold text-on-surface uppercase tracking-tight">{{ $approvalMode ? 'Duyệt lịch — đơn chờ duyệt' : 'Quản lý Đặt lịch' }}</h2>
@unless ($approvalMode)
<div class="flex items-stretch gap-1 bg-surface-container-low p-1 rounded-xl w-full sm:w-fit">
<a href="/{{ $coSo->slug }}/lich-hen" class="flex-1 sm:flex-none px-6 py-2 text-body-md font-semibold text-on-surface-variant hover:text-on-surface transition-all inline-flex items-center justify-center whitespace-nowrap">Lịch trình</a>
<button class="flex-1 sm:flex-none px-6 py-2 bg-surface-container-lowest text-on-surface font-bold rounded-lg shadow-sm border border-outline-variant/30 flex items-center justify-center gap-2 whitespace-nowrap">
<span class="material-symbols-outlined text-[18px]">format_list_bulleted</span>
                    Danh sách chi tiết
                </button>
</div>
@endunless
</div>
@php
    // Preset khoảng thời gian: xác định preset đang active bằng cách so 2 mốc ngay_tu / ngay_den.
    $today = now()->toDateString();
    $wStart = now()->startOfWeek(\Carbon\CarbonInterface::MONDAY)->toDateString();
    $wEnd   = now()->endOfWeek(\Carbon\CarbonInterface::SUNDAY)->toDateString();
    $mStart = now()->startOfMonth()->toDateString();
    $mEnd   = now()->endOfMonth()->toDateString();
    $tu = $filters['ngay_tu'] ?? null;
    $den = $filters['ngay_den'] ?? null;
    $isDay   = $tu === $today  && $den === $today;
    $isWeek  = $tu === $wStart && $den === $wEnd;
    $isMonth = $tu === $mStart && $den === $mEnd;
    $presetUrl = fn ($from, $to) => request()->fullUrlWithQuery(['ngay_tu' => $from, 'ngay_den' => $to, 'page' => null]);

    // 2026-08-05: move permission compute ra ngoài form filter — bảng bên dưới cũng dùng $canEditBookingRow.
    $pbId = auth()->user()->phong_ban_id;
    $vtId = auth()->user()->vai_tro_id;
    $isAdmin = auth()->user()->is_admin;
    $canExportBooking = $isAdmin || \App\Models\PhanQuyen::where(fn($q) => $q->where('phong_ban_id', $pbId)->orWhere('vai_tro_id', $vtId))->where('truong', 'xuat_lich_dat_phong')->exists();
    $canDeleteBooking = $isAdmin || \App\Models\PhanQuyen::where(fn($q) => $q->where('phong_ban_id', $pbId)->orWhere('vai_tro_id', $vtId))->where('truong', 'xoa_booking')->exists();
    $mySuaPerms = $isAdmin
        ? ['sua_booking', 'sua_booking_lien_quan', 'sua_booking_dich_vu_cua_toi']
        : \App\Models\PhanQuyen::where(fn($q) => $q->where('phong_ban_id', $pbId)->orWhere('vai_tro_id', $vtId))
            ->whereIn('truong', ['sua_booking', 'sua_booking_lien_quan', 'sua_booking_dich_vu_cua_toi'])
            ->pluck('truong')->all();
    $canEditAll       = in_array('sua_booking', $mySuaPerms, true);
    $canEditLienQuan  = in_array('sua_booking_lien_quan', $mySuaPerms, true);
    $canEditDichVuMe  = in_array('sua_booking_dich_vu_cua_toi', $mySuaPerms, true);
    $authUid = auth()->id();
    $canEditBookingRow = function ($b) use ($canEditAll, $canEditLienQuan, $canEditDichVuMe, $authUid) {
        if ($canEditAll) return true;
        $lienQuan = in_array($authUid, array_filter([$b->nguoi_tao_id, $b->bac_si_id, $b->ktv_user_id, $b->sale_id]), true);
        if ($canEditLienQuan && $lienQuan) return true;
        return $canEditDichVuMe && $b->loai_dat_lich === 'dich_vu' && $lienQuan;
    };
    $canDuyet = $isAdmin || \App\Models\PhanQuyen::where(fn($q) => $q->where('phong_ban_id', $pbId)->orWhere('vai_tro_id', $vtId))->where('truong', 'duyet_booking')->exists();
    $canCheckIn = $isAdmin || \App\Models\PhanQuyen::where(fn($q) => $q->where('phong_ban_id', $pbId)->orWhere('vai_tro_id', $vtId))->where('truong', 'cap_nhat_trang_thai_khach')->exists();

    // Class chuẩn cho input trong filter bar — dùng chung mọi field cho đồng đều.
    $filterInputCls = 'w-full h-10 border border-outline-variant rounded-lg px-3 text-body-md focus:border-secondary focus:ring-1 focus:ring-secondary/20 outline-none transition-all bg-surface';
@endphp
<!-- Khung giờ hiện tại — theo dõi nhanh khách đang / sắp đến, độc lập với bộ lọc phía dưới -->
@isset ($currentSlotBookings)
<div class="mb-6 rounded-2xl border border-secondary/30 bg-secondary-container/15 overflow-hidden">
<div class="flex items-center gap-2 px-5 py-3 border-b border-secondary/20 bg-secondary-container/25">
<span class="material-symbols-outlined text-secondary">schedule</span>
<div class="flex-1 flex flex-wrap items-baseline gap-x-3 gap-y-1">
<h3 class="text-body-md font-semibold text-on-secondary-container">Dữ liệu trong khung giờ</h3>
<span class="font-time-slot text-body-md font-semibold text-on-secondary-container">{{ $currentSlotLabel }}</span>
<span class="text-body-sm text-on-surface-variant">({{ $currentSlotBookings->count() }} lịch — hôm nay {{ now()->format('d/m') }})</span>
</div>
</div>
@if ($currentSlotBookings->count())
<div class="overflow-x-auto">
<table class="w-full text-body-sm">
<thead class="bg-secondary-container/10 text-label-caps font-label-caps text-on-surface-variant text-left">
<tr>
<th class="px-5 py-2 whitespace-nowrap w-[110px]">Giờ</th>
<th class="px-3 py-2 whitespace-nowrap">Khách hàng</th>
<th class="px-3 py-2 whitespace-nowrap w-[130px]">SĐT</th>
<th class="px-3 py-2 whitespace-nowrap">Phòng</th>
<th class="px-3 py-2 whitespace-nowrap">Dịch vụ</th>
<th class="px-3 py-2 whitespace-nowrap">BS / KTV</th>
<th class="px-5 py-2 whitespace-nowrap text-right w-[110px]">Trạng thái</th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant/40">
@foreach ($currentSlotBookings as $cs)
@php
    $ttKhach = $cs->trang_thai_khach;
    $ttBadge = match ($ttKhach) {
        'dung_gio' => ['Đúng giờ', 'bg-emerald-100 text-emerald-700'],
        'muon'     => ['Muộn',     'bg-amber-100 text-amber-700'],
        'huy'      => ['Hủy',      'bg-red-100 text-red-700'],
        default    => null,
    };
    $viewUrl = '/'.$coSo->slug.'/xem-dat-phong/'.$cs->id;
@endphp
<tr class="hover:bg-surface-container-low/60 cursor-pointer" onclick="window.location='{{ $viewUrl }}'">
<td class="px-5 py-2 font-time-slot text-on-surface font-semibold whitespace-nowrap">{{ substr($cs->gio_thuc_hien, 0, 5) }}{{ $cs->gio_ket_thuc ? '-'.substr($cs->gio_ket_thuc, 0, 5) : '' }}</td>
<td class="px-3 py-2 font-semibold text-on-surface truncate max-w-[220px]">{{ $cs->khachHang?->ho_ten ?? '—' }}</td>
<td class="px-3 py-2 text-on-surface-variant font-time-slot whitespace-nowrap">{{ $cs->khachHang?->so_dien_thoai }}</td>
<td class="px-3 py-2 text-on-surface-variant truncate max-w-[200px]" title="{{ $cs->phong?->ten }}">{{ $cs->phong?->ten ?? '—' }}</td>
<td class="px-3 py-2 text-on-surface-variant truncate max-w-[200px]" title="{{ $cs->dichVu?->ten }}">{{ $cs->dichVu?->ten ?? '—' }}</td>
<td class="px-3 py-2 text-on-surface-variant truncate max-w-[220px]" title="{{ $cs->bacSi?->ten_day_du ?? $cs->ktv?->ten_day_du }}">{{ $cs->bacSi?->ten_day_du ?? $cs->ktv?->ten_day_du ?? '—' }}</td>
<td class="px-5 py-2 text-right whitespace-nowrap">
@if ($ttBadge)
<span class="px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $ttBadge[1] }}">{{ $ttBadge[0] }}</span>
@else
<span class="text-on-surface-variant/60 text-[11px]">—</span>
@endif
</td>
</tr>
@endforeach
</tbody>
</table>
</div>
@else
<div class="px-5 py-4 text-body-sm text-on-surface-variant italic">Không có lịch nào trong khung giờ này.</div>
@endif
</div>
@endisset

{{-- Advanced Filters — 2026-08-05 refactor: dùng x-longevity.filter-bar chuẩn hoá UI. --}}
<x-longevity.filter-bar :cols="4">
    <x-slot:toolbar>
        <span class="text-label-caps font-label-caps text-on-surface-variant">Preset thời gian:</span>
        <div class="inline-flex rounded-lg border border-outline-variant overflow-hidden bg-surface">
            <a href="{{ $presetUrl($today, $today) }}" class="px-3 py-1 text-[12px] font-semibold border-r border-outline-variant transition-colors {{ $isDay  ? 'bg-secondary-container/60 text-on-secondary-container' : 'text-on-surface-variant hover:bg-surface-container-low' }}">Ngày</a>
            <a href="{{ $presetUrl($wStart, $wEnd) }}" class="px-3 py-1 text-[12px] font-semibold border-r border-outline-variant transition-colors {{ $isWeek ? 'bg-secondary-container/60 text-on-secondary-container' : 'text-on-surface-variant hover:bg-surface-container-low' }}">Tuần</a>
            <a href="{{ $presetUrl($mStart, $mEnd) }}" class="px-3 py-1 text-[12px] font-semibold transition-colors {{ $isMonth? 'bg-secondary-container/60 text-on-secondary-container' : 'text-on-surface-variant hover:bg-surface-container-low' }}">Tháng</a>
        </div>
    </x-slot:toolbar>

    <x-longevity.filter-field label="MÃ BOOKING / MÃ KH">
        <input type="search" name="q_ma" value="{{ $filters['q_ma'] ?? '' }}" placeholder="BKG-… / KH-…" class="{{ $filterInputCls }} font-mono"/>
    </x-longevity.filter-field>

    <x-longevity.filter-field label="TỪ NGÀY">
        <input type="date" name="ngay_tu" value="{{ $filters['ngay_tu'] ?? '' }}" class="{{ $filterInputCls }}"/>
    </x-longevity.filter-field>

    <x-longevity.filter-field label="ĐẾN NGÀY">
        <input type="date" name="ngay_den" value="{{ $filters['ngay_den'] ?? '' }}" class="{{ $filterInputCls }}"/>
    </x-longevity.filter-field>

    <x-longevity.filter-field label="PHÒNG">
        <select name="phong_id" class="{{ $filterInputCls }}">
            <option value="">Tất cả phòng</option>
            @foreach ($phongs as $p)
                <option value="{{ $p->id }}" @selected(($filters['phong_id'] ?? '')==$p->id)>{{ $p->ten }}</option>
            @endforeach
        </select>
    </x-longevity.filter-field>

    <x-longevity.filter-field label="BÁC SĨ">
        <select name="bac_si_id" class="{{ $filterInputCls }}">
            <option value="">Tất cả bác sĩ</option>
            @foreach ($bacSis as $bs)
                <option value="{{ $bs->id }}" @selected(($filters['bac_si_id'] ?? '')==$bs->id)>{{ $bs->ten_day_du }}</option>
            @endforeach
        </select>
    </x-longevity.filter-field>

    <x-longevity.filter-field label="NV PHỤ TRÁCH">
        <select name="sale_id" class="{{ $filterInputCls }}">
            <option value="">Tất cả NV</option>
            @foreach ($sales as $s)
                <option value="{{ $s->id }}" @selected(($filters['sale_id'] ?? '')==$s->id)>{{ $s->name }}{{ $s->chuc_danh ? ' ('.$s->chuc_danh.')' : '' }}</option>
            @endforeach
        </select>
    </x-longevity.filter-field>

    <x-longevity.filter-field label="NGUỒN">
        <select name="nguon" class="{{ $filterInputCls }}">
            <option value="">Tất cả nguồn</option>
            @foreach ($nguons as $ng)
                <option value="{{ $ng }}" @selected(($filters['nguon'] ?? '')===$ng)>{{ $ng }}</option>
            @endforeach
        </select>
    </x-longevity.filter-field>

    @unless ($approvalMode)
        <x-longevity.filter-field label="TRẠNG THÁI">
            <select name="trang_thai" class="{{ $filterInputCls }}">
                <option value="">Tất cả</option>
                <option value="cho_duyet" @selected(($filters['trang_thai'] ?? '')==='cho_duyet')>Chờ duyệt</option>
                <option value="da_duyet" @selected(($filters['trang_thai'] ?? '')==='da_duyet')>Đã duyệt</option>
                <option value="da_xong" @selected(($filters['trang_thai'] ?? '')==='da_xong')>Đã xong</option>
                <option value="tu_choi" @selected(($filters['trang_thai'] ?? '')==='tu_choi')>Từ chối</option>
            </select>
        </x-longevity.filter-field>
    @endunless

    <x-slot:actions>
        <button type="submit" class="inline-flex items-center gap-1.5 px-4 h-10 text-body-sm font-semibold bg-primary text-on-primary rounded-lg hover:opacity-90 transition-opacity">
            <span class="material-symbols-outlined text-[18px]">filter_list</span> Lọc dữ liệu
        </button>
        <a href="/{{ $coSo->slug }}/{{ $approvalMode ? 'duyet-lich' : 'danh-sach' }}" class="inline-flex items-center gap-1.5 px-4 h-10 text-body-sm font-semibold text-on-surface-variant bg-surface border border-outline-variant rounded-lg hover:bg-surface-container-low transition-colors">
            <span class="material-symbols-outlined text-[18px]">restart_alt</span> Đặt lại
        </a>
        @if ($canExportBooking)
            <a href="/{{ $coSo->slug }}/xuat-booking" class="ml-auto inline-flex items-center gap-1.5 px-4 h-10 text-body-sm font-semibold bg-on-tertiary-container text-on-primary rounded-lg hover:opacity-90 transition-opacity">
                <span class="material-symbols-outlined text-[18px]">download</span> Xuất Excel
            </a>
            <label class="inline-flex items-center gap-1.5 px-4 h-10 text-body-sm font-semibold text-on-surface-variant bg-surface border border-outline-variant rounded-lg cursor-pointer hover:bg-surface-container-low transition-colors">
                <span class="material-symbols-outlined text-[18px]">upload</span> Chọn file
                <input type="file" name="file" form="import-booking" accept=".xlsx,.xls,.csv" class="hidden" onchange="this.form.submit()"/>
            </label>
        @endif
    </x-slot:actions>
</x-longevity.filter-bar>
@if ($canExportBooking)
<form id="import-booking" method="POST" action="/{{ $coSo->slug }}/nhap-booking" enctype="multipart/form-data" class="hidden">@csrf</form>
@endif
<!-- Data Table Container -->
<div class="bg-surface-container-lowest rounded-2xl border border-outline-variant shadow-sm overflow-hidden flex flex-col">
<div class="overflow-x-auto custom-scrollbar w-full min-w-0">
<table class="w-full text-left border-collapse table-auto min-w-[1800px] whitespace-nowrap">
<thead>
@php
    // Helper build URL sort + icon indicator
    $sortUrl = function ($col) use ($sort, $dir) {
        $nextDir = ($sort === $col && $dir === 'desc') ? 'asc' : 'desc';
        return request()->fullUrlWithQuery(['sort' => $col, 'dir' => $nextDir, 'page' => null]);
    };
    $sortIcon = function ($col) use ($sort, $dir) {
        if ($sort !== $col) return '<span class="material-symbols-outlined text-[14px] opacity-30 align-middle">unfold_more</span>';
        return $dir === 'asc'
            ? '<span class="material-symbols-outlined text-[14px] text-secondary align-middle">arrow_upward</span>'
            : '<span class="material-symbols-outlined text-[14px] text-secondary align-middle">arrow_downward</span>';
    };
@endphp
<tr class="bg-surface-container-low border-b border-outline-variant">
<th class="px-4 py-4 text-label-caps font-label-caps text-on-surface-variant sticky-col sticky-left-0 bg-surface-container-low shadow-[2px_0_5px_rgba(0,0,0,0.05)]">ID</th>
<th class="px-4 py-4 text-label-caps font-label-caps text-on-surface-variant">
<a href="{{ $sortUrl('ngay_dat') }}" class="inline-flex items-center gap-1 hover:text-on-surface transition-colors">NGÀY ĐẶT {!! $sortIcon('ngay_dat') !!}</a>
</th>
<th class="px-4 py-4 text-label-caps font-label-caps text-on-surface-variant">HỌ TÊN KH</th>
<th class="px-4 py-4 text-label-caps font-label-caps text-on-surface-variant">SỐ ĐIỆN THOẠI</th>
<th class="px-4 py-4 text-label-caps font-label-caps text-on-surface-variant">ĐỊA CHỈ EMAIL</th>
<th class="px-4 py-4 text-label-caps font-label-caps text-on-surface-variant">PHÒNG</th>
<th class="px-4 py-4 text-label-caps font-label-caps text-on-surface-variant">
<a href="{{ $sortUrl('khung_gio') }}" class="inline-flex items-center gap-1 hover:text-on-surface transition-colors">KHUNG GIỜ {!! $sortIcon('khung_gio') !!}</a>
</th>
<th class="px-4 py-4 text-label-caps font-label-caps text-on-surface-variant">THỰC HIỆN DV</th>
<th class="px-4 py-4 text-label-caps font-label-caps text-on-surface-variant">DỰ KIẾN KẾT THÚC</th>
<th class="px-4 py-4 text-label-caps font-label-caps text-on-surface-variant">NGUỒN</th>
<th class="px-4 py-4 text-label-caps font-label-caps text-on-surface-variant">SALE</th>
<th class="px-4 py-4 text-label-caps font-label-caps text-on-surface-variant">LIỆU PHÁP</th>
<th class="px-4 py-4 text-label-caps font-label-caps text-on-surface-variant">SỐ LƯỢNG</th>
<th class="px-4 py-4 text-label-caps font-label-caps text-on-surface-variant">KẾT HỢP MEDICAL?</th>
<th class="px-4 py-4 text-label-caps font-label-caps text-on-surface-variant">BÁC SĨ</th>
<th class="px-4 py-4 text-label-caps font-label-caps text-on-surface-variant">KTV</th>
<th class="px-4 py-4 text-label-caps font-label-caps text-on-surface-variant">GHI CHÚ</th>
<th class="px-4 py-4 text-label-caps font-label-caps text-on-surface-variant">DẤU THỜI GIAN</th>
<th class="px-4 py-4 text-label-caps font-label-caps text-on-surface-variant sticky-col sticky-right-0 bg-surface-container-low text-right shadow-[-2px_0_5px_rgba(0,0,0,0.05)]">THAO TÁC</th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant/30">
{{-- Safelist cho Tailwind CDN JIT: đảm bảo các class màu mới có trong CSS
     ngay cả khi chưa có booking nào ứng với trạng thái tương ứng. --}}
<tr class="hidden bg-emerald-50 bg-amber-50 hover:bg-emerald-100/60 hover:bg-amber-100/60"></tr>
@forelse ($bookings as $b)
@php
    $rejected = $b->trang_thai === 'tu_choi';
    [$rowBg, $rowHover] = match ($b->trang_thai_khach) {
        'dung_gio' => ['bg-emerald-50',  'hover:bg-emerald-100/60'],
        'muon'     => ['bg-amber-50',    'hover:bg-amber-100/60'],
        'huy'      => ['bg-red-50',      'hover:bg-red-100/60'],
        default    => $rejected
            ? ['bg-red-50',                  'hover:bg-red-100/60']
            : ['bg-surface-container-lowest', 'hover:bg-surface-variant/10'],
    };
@endphp
<tr data-booking-id="{{ $b->id }}" class="transition-colors {{ $rowBg }} {{ $rowHover }}">
<td class="px-4 py-4 sticky-col sticky-left-0 {{ $rowBg }} shadow-[2px_0_5px_rgba(0,0,0,0.05)] text-body-sm font-time-slot text-on-surface-variant">#{{ $b->id }}</td>
<td class="px-4 py-4 text-body-sm font-semibold">{{ $b->ngay_dat?->format('d/m/Y') }}</td>
<td class="px-4 py-4 font-bold text-on-surface">{{ $b->khachHang?->ho_ten }}</td>
<td class="px-4 py-4 text-body-sm font-time-slot">{{ $b->khachHang?->so_dien_thoai }}</td>
<td class="px-4 py-4 text-body-sm text-on-surface-variant">{{ $b->khachHang?->email ?: '—' }}</td>
<td class="px-4 py-4 text-body-sm font-semibold">{{ $b->phong?->ten ?? '—' }}</td>
<td class="px-4 py-4 text-body-sm font-time-slot">{{ $b->khungGio?->nhan ?? '—' }}</td>
<td class="px-4 py-4 text-body-sm font-time-slot">{{ $b->gio_thuc_hien ? substr($b->gio_thuc_hien,0,5) : '—' }}</td>
<td class="px-4 py-4 text-body-sm font-time-slot">{{ $b->gio_ket_thuc ? substr($b->gio_ket_thuc,0,5) : ($b->khungGio ? substr($b->khungGio->gio_ket_thuc,0,5) : '—') }}</td>
<td class="px-4 py-4"><span class="px-2 py-0.5 bg-surface-container-high rounded text-[11px]">{{ $b->nguon ?? '—' }}</span></td>
<td class="px-4 py-4 text-body-sm">{{ $b->sale?->name ?? '—' }}</td>
<td class="px-4 py-4 text-body-sm">{{ $b->dichVu?->ten ?? '—' }}</td>
<td class="px-4 py-4 text-body-sm text-center">{{ $b->so_luong ?? '—' }}</td>
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
<td class="px-4 py-4 text-body-sm font-time-slot text-on-surface-variant">{{ $b->created_at->format('d/m H:i') }}</td>
<td class="px-4 py-4 sticky-col sticky-right-0 {{ $rowBg }} shadow-[-2px_0_5px_rgba(0,0,0,0.05)]">
<div class="flex items-center justify-end gap-1">
@php
    $approved = $b->trang_thai === 'da_duyet';
    $done = $b->trang_thai === 'da_xong';
    $checkedIn = in_array($b->trang_thai_khach, ['da_toi', 'toi_tre'], true);
    // Phase C1.b rev6 2026-08-01: badge phân biệt "Đã duyệt · Chờ check-in" và "Đã check-in".
    $badge = $done
        ? ['Đã xong', 'bg-primary/10 text-primary']
        : ($approved && $checkedIn
            ? ['Đã check-in', 'bg-emerald-100 text-emerald-700']
            : ($approved
                ? ['Đã duyệt · Chờ check-in', 'bg-tertiary-fixed-dim/40 text-on-tertiary-container']
                : ($rejected
                    ? ['Từ chối', 'bg-red-100 text-red-700']
                    : ['Chờ duyệt', 'bg-secondary-container/40 text-on-secondary-container'])));
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
{{-- Phase C1.b rev6 2026-08-01: nút Check-in — chỉ show khi đã duyệt + chưa check-in + chưa xong. --}}
@if ($approved && $canCheckIn)
<form method="POST" action="/{{ $coSo->slug }}/trang-thai-khach/{{ $b->id }}" class="inline">
@csrf @method('PATCH')
<input type="hidden" name="trang_thai_khach" value="da_toi">
<button type="submit" title="{{ $checkedIn ? 'Bỏ check-in (khách chưa tới)' : 'Check-in — khách đã tới' }}" class="w-7 h-7 rounded-full text-[12px] font-bold border flex items-center justify-center transition-colors {{ $checkedIn ? 'bg-emerald-500 text-white border-emerald-500 hover:bg-emerald-600' : 'text-outline border-outline-variant hover:border-emerald-500 hover:text-emerald-600' }}">
<span class="material-symbols-outlined text-[16px]">{{ $checkedIn ? 'how_to_reg' : 'login' }}</span>
</button>
</form>
@endif
{{-- Phase 6.25.C — Nút "Đang tiếp đón / Hoàn tất" cho sale được gán (manual bên scrm phase 3 hoặc auto UPS). --}}
@if ($b->tiep_don_user_id === auth()->id() || $b->sale_id === auth()->id())
    @php $tdBusy = $b->trang_thai_tiep_don === 'dang_tiep_don'; @endphp
    <form method="POST" action="/{{ $coSo->slug }}/tiep-don/{{ $b->id }}" class="inline">
        @csrf @method('PATCH')
        <input type="hidden" name="trang_thai_tiep_don" value="{{ $tdBusy ? 'hoan_tat' : 'dang_tiep_don' }}">
        <button type="submit" title="{{ $tdBusy ? 'Hoàn tất tiếp đón (bỏ bận)' : 'Đang tiếp đón (đánh dấu bận)' }}"
                class="w-7 h-7 rounded-full text-[12px] font-bold border flex items-center justify-center transition-colors {{ $tdBusy ? 'bg-amber-500 text-white border-amber-500 hover:bg-amber-600' : 'text-outline border-outline-variant hover:border-amber-500 hover:text-amber-600' }}">
            <span class="material-symbols-outlined text-[16px]">{{ $tdBusy ? 'done_all' : 'record_voice_over' }}</span>
        </button>
    </form>
@endif
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
@if ($canEditBookingRow($b))
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
<td colspan="19" class="px-4 py-16 text-center">
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

{{-- Phase 6.25.C — Realtime lắng nghe scrm broadcast: khi UPS auto-chia sale hoặc sale bận/rảnh, hiện toast + reload nhẹ. --}}
<script src="https://cdn.jsdelivr.net/npm/pusher-js@8.4.0/dist/web/pusher.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>
<script>
(function(){
    if (typeof Echo === 'undefined' || typeof Pusher === 'undefined') return;
    try {
        window.ScrmEcho = new Echo({
            broadcaster: 'reverb',
            key: 'local-app-key',
            wsHost: '127.0.0.1',
            wsPort: 8080,
            wssPort: 8080,
            forceTLS: false,
            enabledTransports: ['ws','wss'],
        });

        const showToast = (msg, tone) => {
            const t = document.createElement('div');
            const bg = tone === 'ok' ? 'background:#059669' : (tone === 'warn' ? 'background:#d97706' : 'background:#1f2937');
            t.style.cssText = 'position:fixed;top:16px;right:16px;z-index:9999;padding:10px 14px;color:#fff;border-radius:8px;box-shadow:0 6px 20px rgba(0,0,0,.15);font-size:14px;max-width:340px;'+bg;
            t.textContent = msg;
            document.body.appendChild(t);
            setTimeout(()=>t.remove(), 6000);
        };

        window.ScrmEcho.channel('ups.presence')
            .listen('.App\\Events\\UpsBusyChanged', (e) => {
                showToast(`${e.user_name}: ${e.is_busy ? 'Đang tiếp đón' : 'Đã rảnh'}`, e.is_busy ? 'warn' : 'ok');
            });

        // Lắng nghe theo booking id có mặt trong DOM để reload khi có gán sale mới
        document.querySelectorAll('[data-booking-id]').forEach(el => {
            const id = el.getAttribute('data-booking-id');
            window.ScrmEcho.channel('ups.booking.'+id).listen('.App\\Events\\UpsSaleAssigned', (e) => {
                showToast(`UPS: Đã chia sale ${e.sale_name} cho booking #${id}. Trang sẽ tải lại.`, 'ok');
                setTimeout(()=>location.reload(), 1500);
            });
        });
    } catch (err) { console.warn('Echo init failed', err); }
})();
</script>
</body></html>
