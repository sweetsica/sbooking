<!DOCTYPE html>

<html class="light" lang="vi"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Quản lý Đặt lịch Dịch vụ YHCT</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&amp;family=Manrope:wght@600;700;800&amp;family=JetBrains+Mono:wght@500;600&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "surface-bright": "#f7f9fb",
                        "surface-tint": "#565e74",
                        "on-surface": "#191c1e",
                        "primary-fixed": "#dae2fd",
                        "secondary-fixed-dim": "#89ceff",
                        "inverse-on-surface": "#eff1f3",
                        "on-primary-container": "#7c839b",
                        "on-primary": "#ffffff",
                        "tertiary": "#000000",
                        "tertiary-fixed": "#6ffbbe",
                        "background": "#f7f9fb",
                        "surface-container-high": "#e6e8ea",
                        "tertiary-fixed-dim": "#4edea3",
                        "surface-container-highest": "#e0e3e5",
                        "surface-container-lowest": "#ffffff",
                        "error": "#ba1a1a",
                        "on-tertiary": "#ffffff",
                        "on-error": "#ffffff",
                        "primary": "#000000",
                        "on-secondary-fixed": "#001e2f",
                        "error-container": "#ffdad6",
                        "surface-variant": "#e0e3e5",
                        "on-tertiary-container": "#009668",
                        "primary-container": "#131b2e",
                        "tertiary-container": "#002113",
                        "surface": "#f7f9fb",
                        "primary-fixed-dim": "#bec6e0",
                        "on-primary-fixed-variant": "#3f465c",
                        "secondary": "#006591",
                        "surface-container": "#eceef0",
                        "outline-variant": "#c6c6cd",
                        "on-tertiary-fixed": "#002113",
                        "secondary-container": "#39b8fd",
                        "inverse-primary": "#bec6e0",
                        "on-surface-variant": "#45464d",
                        "on-primary-fixed": "#131b2e",
                        "on-secondary-fixed-variant": "#004c6e",
                        "surface-dim": "#d8dadc",
                        "on-background": "#191c1e",
                        "on-error-container": "#93000a",
                        "on-tertiary-fixed-variant": "#005236",
                        "on-secondary": "#ffffff",
                        "inverse-surface": "#2d3133",
                        "on-secondary-container": "#004666",
                        "surface-container-low": "#f2f4f6",
                        "secondary-fixed": "#c9e6ff",
                        "outline": "#76777d"
                    },
                    "borderRadius": {
                        "DEFAULT": "0.125rem",
                        "lg": "0.25rem",
                        "xl": "0.5rem",
                        "full": "0.75rem"
                    },
                    "spacing": {
                        "row-height-standard": "56px",
                        "unit": "4px",
                        "container-margin": "24px",
                        "row-height-compact": "40px",
                        "gutter": "12px"
                    },
                    "fontFamily": {
                        "body-md": ["Inter"],
                        "label-caps": ["JetBrains Mono"],
                        "time-slot": ["JetBrains Mono"],
                        "headline-lg": ["Manrope"],
                        "body-sm": ["Inter"],
                        "headline-md": ["Manrope"]
                    },
                    "fontSize": {
                        "body-md": ["14px", {"lineHeight": "20px", "fontWeight": "400"}],
                        "label-caps": ["11px", {"lineHeight": "16px", "letterSpacing": "0.05em", "fontWeight": "600"}],
                        "time-slot": ["12px", {"lineHeight": "16px", "fontWeight": "500"}],
                        "headline-lg": ["24px", {"lineHeight": "32px", "fontWeight": "700"}],
                        "body-sm": ["13px", {"lineHeight": "18px", "fontWeight": "400"}],
                        "headline-md": ["18px", {"lineHeight": "24px", "fontWeight": "600"}]
                    }
                },
            },
        }
    </script>
<style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            vertical-align: middle;
        }
        .timeline-grid {
            display: grid;
            grid-template-columns: 80px repeat(3, 1fr);
            grid-auto-rows: 64px;
        }
        .custom-scroll::-webkit-scrollbar { width: 10px; height: 10px; }
        .custom-scroll::-webkit-scrollbar-track { background: #f2f4f6; border-radius: 5px; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #c6c6cd; border-radius: 5px; }
        .custom-scroll::-webkit-scrollbar-thumb:hover { background: #9ca3af; }
        .custom-scroll::-webkit-scrollbar-corner { background: #f2f4f6; }
        .active-tab {
            @@apply border-b-2 border-secondary text-secondary;
        }
    </style>
</head>
<body class="bg-surface font-body-md text-on-surface">
<!-- Top Navigation Bar -->
@include('partials.topnav', ['active' => 'lich-hen'])
<!-- Main Content Area -->
<main class="min-h-screen pt-24 pb-12 px-container-margin">
<div class="max-w-[1600px] mx-auto">
<!-- Header with View Switcher -->
<div class="flex flex-wrap justify-between items-center mb-8 gap-4">
<h2 class="text-headline-lg font-headline-lg font-extrabold text-on-surface uppercase tracking-tight">Quản lý Đặt lịch</h2>
<div class="flex bg-surface-container-low rounded-xl p-1">
<button class="px-6 py-1.5 rounded-lg text-body-md font-semibold transition-all bg-surface-container-lowest shadow-sm text-secondary" id="btn-timeline" onclick="switchView('timeline')">Timeline</button>
<a href="/{{ $coSo->slug }}/danh-sach" class="px-6 py-1.5 rounded-lg text-body-md font-semibold transition-all text-on-surface-variant hover:text-on-surface inline-block">Danh sách</a>
</div>
</div>
<!-- Filters Bar -->
<form method="GET" class="flex flex-wrap items-end gap-4 mb-8 bg-surface-container-lowest p-6 rounded-xl border border-outline-variant shadow-sm">
<div class="space-y-1.5">
<label class="text-label-caps text-on-surface-variant block">NGÀY</label>
<input name="ngay" value="{{ $date->format('Y-m-d') }}" class="form-input border-outline-variant rounded-lg bg-surface focus:ring-secondary focus:border-secondary text-body-md" type="date"/>
</div>
<div class="space-y-1.5 flex-1 min-w-[240px]">
<label class="text-label-caps text-on-surface-variant block">PHÒNG / KHU VỰC</label>
<select name="phong_id" onchange="this.form.submit()" class="form-select w-full border-outline-variant rounded-lg bg-surface focus:ring-secondary focus:border-secondary text-body-md">
@foreach ($rooms as $rm)
<option value="{{ $rm->id }}" @selected($room && $room->id === $rm->id)>{{ $rm->ten }}@if($rm->trang_thai==='bao_tri') (bảo trì)@endif</option>
@endforeach
</select>
</div>
<div class="flex items-center gap-2 ml-auto">
<button type="submit" class="h-[42px] px-5 text-on-surface-variant border border-outline-variant rounded-lg flex items-center gap-2 hover:bg-surface-container-low transition-colors">
<span class="material-symbols-outlined text-[20px]">filter_list</span>
<span>Xem</span>
</button>
<a href="/{{ $coSo->slug }}/tao-moi" class="h-[42px] px-6 bg-primary text-on-primary font-semibold rounded-lg flex items-center gap-2 hover:opacity-90 transition-opacity">
<span class="material-symbols-outlined text-[20px]">add</span>
<span class="">Tạo Booking</span>
</a>
</div>
</form>
<!-- View Container: Timeline -->
<div class="bg-surface-container-lowest rounded-xl border border-outline-variant overflow-hidden shadow-sm transition-all duration-300 opacity-100" id="view-timeline">
<div class="p-4 border-b border-outline-variant bg-surface-container-low flex flex-wrap justify-between items-center gap-3">
<h3 class="font-headline-md text-on-surface">Lịch biểu {{ $room?->ten ?? '—' }} — <span class="text-secondary font-bold">{{ $date->translatedFormat('l, d/m/Y') }}</span></h3>
<div class="flex items-center gap-5">
<div class="flex items-center gap-2 text-body-sm">
<span class="font-bold text-on-surface">{{ $stats['fill'] }}%</span>
<span class="text-on-surface-variant">lấp đầy</span>
<span class="text-on-surface-variant">·</span>
<span class="text-on-surface-variant">{{ $stats['total'] }} lịch ({{ $stats['approved'] }} duyệt / {{ $stats['pending'] }} chờ)</span>
</div>
<div class="flex gap-4">
<div class="flex items-center gap-2">
<div class="w-3 h-3 rounded-full bg-tertiary-fixed-dim"></div>
<span class="text-body-sm text-on-surface-variant">Đã duyệt</span>
</div>
<div class="flex items-center gap-2">
<div class="w-3 h-3 rounded-full bg-secondary-fixed"></div>
<span class="text-body-sm text-on-surface-variant">Chờ duyệt</span>
</div>
</div>
</div>
</div>
<div class="relative overflow-auto max-h-[600px] custom-scroll">
<!-- Timeline Grid -->
<div class="timeline-grid min-w-[700px]" style="grid-template-columns: 80px repeat({{ max($beds,1) }}, minmax(160px,1fr));">
<!-- Header -->
<div class="sticky top-0 z-20 bg-surface-container-low border-b border-r border-outline-variant flex items-center justify-center">
<span class="text-label-caps text-on-surface-variant">GIỜ</span>
</div>
@for ($i = 1; $i <= max($beds, 1); $i++)
<div class="sticky top-0 z-20 bg-surface-container-low border-b {{ $i < $beds ? 'border-r' : '' }} border-outline-variant px-4 flex items-center justify-center font-bold {{ $i === 1 ? '' : 'text-on-surface-variant' }}">Giường {{ $i }}</div>
@endfor
@forelse ($grid as $g)
<div class="border-b border-r border-outline-variant flex items-center justify-center">
<span class="text-time-slot text-on-surface-variant">{{ substr($g['slot']->gio_bat_dau, 0, 5) }}</span>
</div>
@foreach ($g['beds'] as $bi => $bk)
<div class="relative border-b {{ $bi < $beds - 1 ? 'border-r' : '' }} border-outline-variant min-h-[64px] hover:bg-surface-container-low transition-colors">
@if ($bk)
@php $appr = $bk->trang_thai === 'da_duyet'; @endphp
<a href="/{{ $coSo->slug }}/danh-sach" class="absolute inset-x-2 top-1.5 bottom-1.5 z-10 rounded-lg p-2 shadow-sm border-l-4 overflow-hidden block {{ $appr ? 'bg-tertiary-fixed-dim/20 border-tertiary-fixed-dim' : 'bg-secondary-fixed/30 border-secondary-container' }}">
<div class="flex justify-between items-start gap-1">
<span class="font-bold text-body-sm {{ $appr ? 'text-tertiary-container' : '' }} truncate">{{ $bk->khachHang?->ho_ten }}</span>
@if ($appr)
<span class="material-symbols-outlined text-[16px] text-on-tertiary-container shrink-0" style="font-variation-settings: 'FILL' 1;">check_circle</span>
@else
<span class="text-[10px] bg-secondary-container text-on-secondary-container px-1.5 rounded uppercase font-bold shrink-0">Chờ</span>
@endif
</div>
<div class="text-[11px] italic text-on-surface-variant truncate">{{ $bk->dichVu?->ten }}</div>
<div class="text-[11px] font-time-slot text-on-surface-variant">{{ $bk->khungGio?->nhan }}</div>
</a>
@endif
</div>
@endforeach
@empty
<div class="border-b border-outline-variant p-10 text-center text-on-surface-variant" style="grid-column: 1 / -1;">
{{ $room && $room->trang_thai === 'bao_tri' ? 'Phòng đang bảo trì.' : 'Phòng chưa cấu hình khung giờ phục vụ.' }}
</div>
@endforelse
</div>
</div>
</div>
<!-- View Container: List (Hidden by default) -->
<div class="hidden bg-surface-container-lowest rounded-xl border border-outline-variant overflow-hidden shadow-sm opacity-0 transition-all duration-300" id="view-list">
<div class="p-6 border-b border-outline-variant flex justify-between items-center">
<h3 class="font-headline-md text-on-surface">Danh sách Đặt lịch chi tiết</h3>
<div class="flex gap-2">
<button class="px-4 py-2 text-body-sm border border-outline-variant rounded-lg flex items-center gap-2 hover:bg-surface-container-low transition-colors">
<span class="material-symbols-outlined text-[20px]">filter_list</span>
                            Lọc bảng
                        </button>
<button class="px-4 py-2 text-body-sm border border-outline-variant rounded-lg flex items-center gap-2 hover:bg-surface-container-low transition-colors">
<span class="material-symbols-outlined text-[20px]">download</span>
                            Xuất Excel
                        </button>
</div>
</div>
<div class="overflow-x-auto">
<table class="w-full text-left border-collapse">
<thead>
<tr class="bg-surface-container-low text-label-caps text-on-surface-variant">
<th class="px-6 py-4 font-semibold">THỜI GIAN ĐẶT</th>
<th class="px-6 py-4 font-semibold">KHÁCH HÀNG</th>
<th class="px-6 py-4 font-semibold">PHÒNG</th>
<th class="px-6 py-4 font-semibold">DỊCH VỤ</th>
<th class="px-6 py-4 font-semibold">SALE PHỤ TRÁCH</th>
<th class="px-6 py-4 font-semibold">KTV / BÁC SĨ</th>
<th class="px-6 py-4 font-semibold text-center">TRẠNG THÁI</th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant">
<tr class="hover:bg-surface-container-low transition-colors group">
<td class="px-6 py-4 text-body-sm font-time-slot">12/06/2026 08:03</td>
<td class="px-6 py-4">
<div class="font-semibold">Quốc Anh</div>
<div class="text-body-sm text-on-surface-variant">03267688...</div>
</td>
<td class="px-6 py-4 text-body-sm">VIP 1 (Giường 1)</td>
<td class="px-6 py-4 text-body-sm">AP (60 phút)</td>
<td class="px-6 py-4 text-body-sm text-on-surface-variant">Trần Thị Thu Giang</td>
<td class="px-6 py-4 text-body-sm">KTV YHCT VI</td>
<td class="px-6 py-4 text-center">
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-tertiary-fixed-dim/20 text-on-tertiary-container">
                                        ĐÃ DUYỆT
                                    </span>
</td>
</tr>
<tr class="hover:bg-surface-container-low transition-colors group">
<td class="px-6 py-4 text-body-sm font-time-slot">12/06/2026 08:18</td>
<td class="px-6 py-4">
<div class="font-semibold">Test 1</div>
<div class="text-body-sm text-on-surface-variant">03267688...</div>
</td>
<td class="px-6 py-4 text-body-sm">VIP 1 (Giường 2)</td>
<td class="px-6 py-4 text-body-sm">AP (60 phút)</td>
<td class="px-6 py-4 text-body-sm text-on-surface-variant">Trần Thị Thu Giang</td>
<td class="px-6 py-4 text-body-sm">KTV YHCT</td>
<td class="px-6 py-4 text-center">
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-secondary-fixed text-on-secondary-container">
                                        CHỜ DUYỆT
                                    </span>
</td>
</tr>
</tbody>
</table>
</div>
</div>
<!-- Stats & Reports Cards -->
<div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
<div class="bg-surface-container-lowest p-6 rounded-xl border border-outline-variant flex flex-col gap-4 shadow-sm">
<div class="flex items-center justify-between">
<span class="text-label-caps text-on-surface-variant">TỶ LỆ LẤY ĐẦY PHÒNG</span>
<span class="text-headline-md font-bold text-secondary">68%</span>
</div>
<div class="w-full bg-surface-container-high h-2 rounded-full overflow-hidden">
<div class="bg-secondary h-full w-[68%]"></div>
</div>
<p class="text-[11px] text-on-surface-variant italic">Phòng VIP 1 đang có hiệu suất cao nhất trong ngày hôm nay.</p>
</div>
<div class="bg-surface-container-lowest p-6 rounded-xl border border-outline-variant flex flex-col justify-between shadow-sm">
<div class="flex items-center justify-between mb-4">
<span class="text-label-caps text-on-surface-variant">TỔNG LỊCH HẸN TRONG NGÀY</span>
<span class="text-headline-md font-bold text-on-surface">24</span>
</div>
<div class="flex gap-2">
<span class="px-3 py-1 rounded bg-tertiary-fixed-dim/20 text-on-tertiary-container text-[11px] font-bold">18 ĐÃ DUYỆT</span>
<span class="px-3 py-1 rounded bg-secondary-fixed text-on-secondary-container text-[11px] font-bold">6 CHỜ</span>
</div>
</div>
<div class="bg-surface-container-lowest p-6 rounded-xl border border-outline-variant flex items-center gap-4 group cursor-pointer hover:bg-surface-container-low transition-colors shadow-sm">
<div class="w-12 h-12 rounded-xl bg-tertiary-container flex items-center justify-center text-tertiary-fixed shadow-sm">
<span class="material-symbols-outlined text-[28px]">assessment</span>
</div>
<div>
<h4 class="font-bold text-body-md">Báo cáo chi tiết</h4>
<p class="text-body-sm text-on-surface-variant">Xem phân tích doanh thu dịch vụ</p>
</div>
<span class="material-symbols-outlined ml-auto text-on-surface-variant group-hover:translate-x-1 transition-transform">chevron_right</span>
</div>
</div>
</div>
</main>
<!-- Floating Action Button -->
<button onclick="window.location.href='/{{ $coSo->slug }}/tao-moi'" class="fixed bottom-8 right-8 w-14 h-14 bg-primary text-on-primary rounded-full shadow-lg flex items-center justify-center hover:scale-110 active:scale-95 transition-all z-50 group">
<span class="material-symbols-outlined text-[28px]">add</span>
<span class="absolute right-full mr-4 px-3 py-1.5 bg-inverse-surface text-inverse-on-surface text-body-sm rounded-lg opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">Tạo nhanh booking</span>
</button>
<script>
        function switchView(view) {
            const timelineView = document.getElementById('view-timeline');
            const listView = document.getElementById('view-list');
            const btnTimeline = document.getElementById('btn-timeline');
            const btnList = document.getElementById('btn-list');

            if (view === 'timeline') {
                timelineView.classList.remove('hidden');
                setTimeout(() => timelineView.classList.replace('opacity-0', 'opacity-100'), 10);
                listView.classList.add('hidden');
                listView.classList.replace('opacity-100', 'opacity-0');

                btnTimeline.classList.add('bg-surface-container-lowest', 'shadow-sm', 'text-secondary');
                btnTimeline.classList.remove('text-on-surface-variant');
                btnList.classList.remove('bg-surface-container-lowest', 'shadow-sm', 'text-secondary');
                btnList.classList.add('text-on-surface-variant');
            } else {
                listView.classList.remove('hidden');
                setTimeout(() => listView.classList.replace('opacity-0', 'opacity-100'), 10);
                timelineView.classList.add('hidden');
                timelineView.classList.replace('opacity-100', 'opacity-0');

                btnList.classList.add('bg-surface-container-lowest', 'shadow-sm', 'text-secondary');
                btnList.classList.remove('text-on-surface-variant');
                btnTimeline.classList.remove('bg-surface-container-lowest', 'shadow-sm', 'text-secondary');
                btnTimeline.classList.add('text-on-surface-variant');
            }
        }

        // Pulse effect for the current time line
        setInterval(() => {
            const line = document.getElementById('current-time-line');
            if (line) {
                line.style.opacity = line.style.opacity === '0.5' ? '1' : '0.5';
            }
        }, 1000);
    </script>
</body></html>
