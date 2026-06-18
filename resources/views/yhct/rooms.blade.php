<!DOCTYPE html>

<html class="light" lang="vi"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Quản lý Phòng | Precision Wellness</title>
<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&amp;family=Manrope:wght@600;700;800&amp;family=JetBrains+Mono:wght@500;600&amp;display=swap" rel="stylesheet"/>
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
                    "surface-container-high": "#e6e8ea",
                    "on-error": "#ffffff",
                    "on-tertiary-fixed-variant": "#005236",
                    "tertiary-container": "#002113",
                    "error": "#ba1a1a",
                    "inverse-on-surface": "#eff1f3",
                    "surface-dim": "#d8dadc",
                    "on-error-container": "#93000a",
                    "primary-fixed": "#dae2fd",
                    "surface-container-low": "#f2f4f6",
                    "outline-variant": "#c6c6cd",
                    "error-container": "#ffdad6",
                    "on-primary-fixed-variant": "#3f465c",
                    "on-tertiary-container": "#009668",
                    "surface-container-lowest": "#ffffff",
                    "primary-fixed-dim": "#bec6e0",
                    "tertiary-fixed-dim": "#4edea3",
                    "inverse-primary": "#bec6e0",
                    "secondary-fixed": "#c9e6ff",
                    "on-primary": "#ffffff",
                    "surface-tint": "#565e74",
                    "surface-variant": "#e0e3e5",
                    "secondary-fixed-dim": "#89ceff",
                    "secondary-container": "#39b8fd",
                    "on-secondary-fixed": "#001e2f",
                    "inverse-surface": "#2d3133",
                    "background": "#f7f9fb",
                    "surface-bright": "#f7f9fb",
                    "secondary": "#006591",
                    "tertiary": "#000000",
                    "on-primary-container": "#7c839b",
                    "primary-container": "#131b2e",
                    "primary": "#000000",
                    "on-tertiary": "#ffffff",
                    "on-secondary-fixed-variant": "#004c6e",
                    "tertiary-fixed": "#6ffbbe",
                    "surface-container": "#eceef0",
                    "on-primary-fixed": "#131b2e",
                    "on-tertiary-fixed": "#002113",
                    "on-secondary-container": "#004666",
                    "on-background": "#191c1e",
                    "on-surface-variant": "#45464d",
                    "surface": "#f7f9fb",
                    "outline": "#76777d",
                    "surface-container-highest": "#e0e3e5",
                    "on-surface": "#191c1e",
                    "on-secondary": "#ffffff"
            },
            "borderRadius": {
                    "DEFAULT": "0.125rem",
                    "lg": "0.25rem",
                    "xl": "0.5rem",
                    "full": "0.75rem"
            },
            "spacing": {
                    "container-margin": "24px",
                    "row-height-compact": "40px",
                    "row-height-standard": "56px",
                    "gutter": "12px",
                    "unit": "4px"
            },
            "fontFamily": {
                    "label-caps": ["JetBrains Mono"],
                    "body-sm": ["Inter"],
                    "headline-lg": ["Manrope"],
                    "time-slot": ["JetBrains Mono"],
                    "headline-md": ["Manrope"],
                    "body-md": ["Inter"]
            },
            "fontSize": {
                    "label-caps": ["11px", {"lineHeight": "16px", "letterSpacing": "0.05em", "fontWeight": "600"}],
                    "body-sm": ["13px", {"lineHeight": "18px", "fontWeight": "400"}],
                    "headline-lg": ["24px", {"lineHeight": "32px", "fontWeight": "700"}],
                    "time-slot": ["12px", {"lineHeight": "16px", "fontWeight": "500"}],
                    "headline-md": ["18px", {"lineHeight": "24px", "fontWeight": "600"}],
                    "body-md": ["14px", {"lineHeight": "20px", "fontWeight": "400"}]
            }
          },
        },
      }
    </script>
<style>
        body { font-family: 'Inter', sans-serif; background-color: #f7f9fb; }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            vertical-align: middle;
        }
        .slot-pill {
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .slot-pill:hover {
            transform: scale(1.1);
        }
    </style>
</head>
<body class="bg-background text-on-surface">
<!-- TopNavBar -->
@include('partials.topnav', ['active' => 'phong'])
<!-- Main Content -->
<main class="pt-[calc(56px+24px)] pb-12 px-container-margin max-w-[1440px] mx-auto">
<!-- Header & Filters -->
<header class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-8">
<div>
<h1 class="font-headline-lg text-headline-lg text-primary mb-1">Quản lý Phòng Trị liệu</h1>
<p class="font-body-md text-body-md text-on-surface-variant">Theo dõi tình trạng chiếm chỗ và hiệu suất sử dụng phòng thời gian thực.</p>
</div>
<div class="flex flex-wrap gap-3">
<div class="flex bg-surface-container rounded-lg p-1">
<button class="px-4 py-1.5 bg-surface shadow-sm rounded-md font-body-sm text-body-sm font-semibold text-secondary">Tất cả</button>
<button class="px-4 py-1.5 hover:bg-surface-variant/50 rounded-md font-body-sm text-body-sm text-on-surface-variant transition-colors">Phòng VIP (2)</button>
<button class="px-4 py-1.5 hover:bg-surface-variant/50 rounded-md font-body-sm text-body-sm text-on-surface-variant transition-colors">Phòng Cộng đồng (12)</button>
</div>
<button class="flex items-center gap-2 px-4 py-2 bg-primary text-on-primary rounded-xl font-body-md text-body-md font-semibold transition-transform active:scale-95">
<span class="material-symbols-outlined text-[20px]">add</span>
                Thêm Phòng mới
            </button>
</div>
</header>
<!-- Room Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-gutter">
<!-- Room Card: VIP -->
<div class="bg-surface border border-outline-variant rounded-xl overflow-hidden hover:shadow-lg transition-shadow">
<div class="p-5 border-b border-outline-variant flex justify-between items-start">
<div>
<div class="flex items-center gap-2 mb-1">
<span class="bg-secondary-container text-on-secondary-container text-[10px] font-bold px-2 py-0.5 rounded uppercase tracking-wider">Phòng VIP</span>
<span class="flex items-center gap-1 text-on-tertiary-container text-body-sm font-medium">
<span class="w-2 h-2 bg-on-tertiary-container rounded-full"></span> Hoạt động
                        </span>
</div>
<h3 class="font-headline-md text-headline-md text-primary">Phòng Trị liệu VIP 01</h3>
</div>
<div class="text-right">
<span class="font-label-caps text-label-caps text-on-surface-variant block uppercase">Tỉ lệ sử dụng</span>
<span class="font-headline-md text-headline-md text-secondary">50%</span>
</div>
</div>
<div class="p-5">
<p class="font-label-caps text-label-caps text-outline mb-4 uppercase">Sơ đồ vị trí (1/2)</p>
<div class="flex gap-4 h-24 items-center justify-center bg-surface-container-low rounded-lg border border-dashed border-outline-variant">
<!-- Occupied Slot -->
<div class="flex flex-col items-center gap-2">
<div class="w-12 h-16 bg-secondary-container border-2 border-secondary rounded-md flex items-center justify-center slot-pill cursor-pointer">
<span class="material-symbols-outlined text-on-secondary-container" style='font-variation-settings: "FILL" 1;'>person</span>
</div>
<span class="font-label-caps text-[9px] text-secondary uppercase font-bold">Đang dùng</span>
</div>
<!-- Available Slot -->
<div class="flex flex-col items-center gap-2">
<div class="w-12 h-16 bg-surface-container-highest border-2 border-on-tertiary-fixed-variant rounded-md flex items-center justify-center slot-pill cursor-pointer group">
<span class="material-symbols-outlined text-on-secondary-container" style='font-variation-settings: "FILL" 1;'>person</span>
</div>
<span class="font-label-caps text-[9px] text-on-tertiary-fixed-variant uppercase font-bold">Trống</span>
</div>
</div>
<div class="mt-4 pt-4 border-t border-outline-variant/30">
<p class="font-label-caps text-label-caps text-outline mb-2 uppercase">Lịch trình hôm nay (08:00 - 21:00)</p>
<div class="flex flex-wrap gap-1">
<div class="flex-1 h-4 bg-secondary-container rounded-sm flex items-center justify-center text-[8px] text-on-secondary-container font-bold" title="08:00">08</div>
<div class="flex-1 h-4 bg-secondary-container rounded-sm flex items-center justify-center text-[8px] text-on-secondary-container font-bold" title="09:00">09</div>
<div class="flex-1 h-4 bg-secondary-container/40 rounded-sm flex items-center justify-center text-[8px] text-on-secondary-container font-bold" title="10:00">10</div>
<div class="flex-1 h-4 bg-tertiary-fixed-dim rounded-sm flex items-center justify-center text-[8px] text-on-tertiary-fixed-variant font-bold" title="11:00">11</div>
<div class="flex-1 h-4 bg-tertiary-fixed-dim rounded-sm flex items-center justify-center text-[8px] text-on-tertiary-fixed-variant font-bold" title="12:00">12</div>
<div class="flex-1 h-4 bg-secondary-container/40 rounded-sm flex items-center justify-center text-[8px] text-on-secondary-container font-bold" title="13:00">13</div>
<div class="flex-1 h-4 bg-secondary-container rounded-sm flex items-center justify-center text-[8px] text-on-secondary-container font-bold" title="14:00">14</div>
<div class="flex-1 h-4 bg-secondary-container rounded-sm flex items-center justify-center text-[8px] text-on-secondary-container font-bold" title="15:00">15</div>
<div class="flex-1 h-4 bg-tertiary-fixed-dim rounded-sm flex items-center justify-center text-[8px] text-on-tertiary-fixed-variant font-bold" title="16:00">16</div>
<div class="flex-1 h-4 bg-tertiary-fixed-dim rounded-sm flex items-center justify-center text-[8px] text-on-tertiary-fixed-variant font-bold" title="17:00">17</div>
<div class="flex-1 h-4 bg-surface-container-highest rounded-sm flex items-center justify-center text-[8px] text-outline font-bold" title="18:00">18</div>
<div class="flex-1 h-4 bg-surface-container-highest rounded-sm flex items-center justify-center text-[8px] text-outline font-bold" title="19:00">19</div>
<div class="flex-1 h-4 bg-surface-container-highest rounded-sm flex items-center justify-center text-[8px] text-outline font-bold" title="20:00">20</div>
<div class="flex-1 h-4 bg-surface-container-highest rounded-sm flex items-center justify-center text-[8px] text-outline font-bold" title="21:00">21</div>
</div>
<div class="flex justify-between mt-1">
<span class="text-[9px] font-label-caps text-outline">08:00</span>
<span class="text-[9px] font-label-caps text-outline">21:00</span>
</div>
</div>
</div>
<div class="px-5 py-4 bg-surface-container-lowest flex gap-3">
<button class="flex-1 py-2 px-4 border border-secondary text-secondary font-body-sm text-body-sm font-bold rounded-lg hover:bg-secondary-container/10 transition-colors">Chi tiết Lịch</button>
<button class="p-2 border border-outline-variant text-on-surface-variant rounded-lg hover:bg-surface-container transition-colors">
<span class="material-symbols-outlined">edit</span>
</button>
</div>
</div>
<!-- Room Card: Community -->
<div class="bg-surface border border-outline-variant rounded-xl overflow-hidden hover:shadow-lg transition-shadow">
<div class="p-5 border-b border-outline-variant flex justify-between items-start">
<div>
<div class="flex items-center gap-2 mb-1">
<span class="bg-surface-container-highest text-on-surface-variant text-[10px] font-bold px-2 py-0.5 rounded uppercase tracking-wider">Cộng đồng</span>
<span class="flex items-center gap-1 text-on-tertiary-container text-body-sm font-medium">
<span class="w-2 h-2 bg-on-tertiary-container rounded-full"></span> Hoạt động
                        </span>
</div>
<h3 class="font-headline-md text-headline-md text-primary">Phòng Trị liệu Tổng hợp A</h3>
</div>
<div class="text-right">
<span class="font-label-caps text-label-caps text-on-surface-variant block uppercase">Tỉ lệ sử dụng</span>
<span class="font-headline-md text-headline-md text-secondary">67%</span>
</div>
</div>
<div class="p-5">
<p class="font-label-caps text-label-caps text-outline mb-4 uppercase">Sơ đồ vị trí (8/12)</p>
<div class="grid grid-cols-6 gap-2 p-3 bg-surface-container-low rounded-lg border border-outline-variant">
<div class="aspect-square bg-secondary-container rounded-sm flex items-center justify-center slot-pill" title="Occupied"><span class="material-symbols-outlined text-[14px] text-on-secondary-container" style="font-variation-settings: 'FILL' 1;">person</span></div>
<div class="aspect-square bg-secondary-container rounded-sm flex items-center justify-center slot-pill"><span class="material-symbols-outlined text-[14px] text-on-secondary-container" style="font-variation-settings: 'FILL' 1;">person</span></div>
<div class="aspect-square bg-secondary-container rounded-sm flex items-center justify-center slot-pill"><span class="material-symbols-outlined text-[14px] text-on-secondary-container" style="font-variation-settings: 'FILL' 1;">person</span></div>
<div class="aspect-square bg-tertiary-fixed-dim/30 border border-on-tertiary-fixed-variant/20 rounded-sm flex items-center justify-center slot-pill"><span class="material-symbols-outlined text-[14px] text-on-tertiary-fixed-variant">circle</span></div>
<div class="aspect-square bg-secondary-container rounded-sm flex items-center justify-center slot-pill"><span class="material-symbols-outlined text-[14px] text-on-secondary-container" style="font-variation-settings: 'FILL' 1;">person</span></div>
<div class="aspect-square bg-secondary-container rounded-sm flex items-center justify-center slot-pill"><span class="material-symbols-outlined text-[14px] text-on-secondary-container" style="font-variation-settings: 'FILL' 1;">person</span></div>
<div class="aspect-square bg-surface-dim/40 rounded-sm flex items-center justify-center slot-pill" title="Maintenance"><span class="material-symbols-outlined text-[14px] text-outline">block</span></div>
<div class="aspect-square bg-tertiary-fixed-dim/30 border border-on-tertiary-fixed-variant/20 rounded-sm flex items-center justify-center slot-pill"><span class="material-symbols-outlined text-[14px] text-on-tertiary-fixed-variant">circle</span></div>
<div class="aspect-square bg-secondary-container rounded-sm flex items-center justify-center slot-pill"><span class="material-symbols-outlined text-[14px] text-on-secondary-container" style="font-variation-settings: 'FILL' 1;">person</span></div>
<div class="aspect-square bg-secondary-container rounded-sm flex items-center justify-center slot-pill"><span class="material-symbols-outlined text-[14px] text-on-secondary-container" style="font-variation-settings: 'FILL' 1;">person</span></div>
<div class="aspect-square bg-tertiary-fixed-dim/30 border border-on-tertiary-fixed-variant/20 rounded-sm flex items-center justify-center slot-pill"><span class="material-symbols-outlined text-[14px] text-on-tertiary-fixed-variant">circle</span></div>
<div class="aspect-square bg-secondary-container rounded-sm flex items-center justify-center slot-pill"><span class="material-symbols-outlined text-[14px] text-on-secondary-container" style="font-variation-settings: 'FILL' 1;">person</span></div>
</div>
<div class="mt-4 pt-4 border-t border-outline-variant/30">
<p class="font-label-caps text-label-caps text-outline mb-2 uppercase">Lịch trình hôm nay (08:00 - 21:00)</p>
<div class="flex flex-wrap gap-1">
<div class="flex-1 h-4 bg-secondary-container rounded-sm flex items-center justify-center text-[8px] text-on-secondary-container font-bold" title="08:00">08</div>
<div class="flex-1 h-4 bg-secondary-container rounded-sm flex items-center justify-center text-[8px] text-on-secondary-container font-bold" title="09:00">09</div>
<div class="flex-1 h-4 bg-secondary-container rounded-sm flex items-center justify-center text-[8px] text-on-secondary-container font-bold" title="10:00">10</div>
<div class="flex-1 h-4 bg-secondary-container/40 rounded-sm flex items-center justify-center text-[8px] text-on-secondary-container font-bold" title="11:00">11</div>
<div class="flex-1 h-4 bg-secondary-container/40 rounded-sm flex items-center justify-center text-[8px] text-on-secondary-container font-bold" title="12:00">12</div>
<div class="flex-1 h-4 bg-secondary-container/40 rounded-sm flex items-center justify-center text-[8px] text-on-secondary-container font-bold" title="13:00">13</div>
<div class="flex-1 h-4 bg-secondary-container rounded-sm flex items-center justify-center text-[8px] text-on-secondary-container font-bold" title="14:00">14</div>
<div class="flex-1 h-4 bg-secondary-container rounded-sm flex items-center justify-center text-[8px] text-on-secondary-container font-bold" title="15:00">15</div>
<div class="flex-1 h-4 bg-secondary-container/40 rounded-sm flex items-center justify-center text-[8px] text-on-secondary-container font-bold" title="16:00">16</div>
<div class="flex-1 h-4 bg-tertiary-fixed-dim rounded-sm flex items-center justify-center text-[8px] text-on-tertiary-fixed-variant font-bold" title="17:00">17</div>
<div class="flex-1 h-4 bg-tertiary-fixed-dim rounded-sm flex items-center justify-center text-[8px] text-on-tertiary-fixed-variant font-bold" title="18:00">18</div>
<div class="flex-1 h-4 bg-surface-container-highest rounded-sm flex items-center justify-center text-[8px] text-outline font-bold" title="19:00">19</div>
<div class="flex-1 h-4 bg-surface-container-highest rounded-sm flex items-center justify-center text-[8px] text-outline font-bold" title="20:00">20</div>
<div class="flex-1 h-4 bg-surface-container-highest rounded-sm flex items-center justify-center text-[8px] text-outline font-bold" title="21:00">21</div>
</div>
<div class="flex justify-between mt-1">
<span class="text-[9px] font-label-caps text-outline">08:00</span>
<span class="text-[9px] font-label-caps text-outline">21:00</span>
</div>
</div>
</div>
<div class="px-5 py-4 bg-surface-container-lowest flex gap-3">
<button class="flex-1 py-2 px-4 border border-secondary text-secondary font-body-sm text-body-sm font-bold rounded-lg hover:bg-secondary-container/10 transition-colors">Chi tiết Lịch</button>
<button class="p-2 border border-outline-variant text-on-surface-variant rounded-lg hover:bg-surface-container transition-colors">
<span class="material-symbols-outlined">edit</span>
</button>
</div>
</div>
<!-- Room Card: Maintenance State -->
<div class="bg-surface border border-outline-variant rounded-xl overflow-hidden grayscale opacity-75">
<div class="p-5 border-b border-outline-variant flex justify-between items-start">
<div>
<div class="flex items-center gap-2 mb-1">
<span class="bg-surface-container-highest text-on-surface-variant text-[10px] font-bold px-2 py-0.5 rounded uppercase tracking-wider">Phòng VIP</span>
<span class="flex items-center gap-1 text-error text-body-sm font-medium">
<span class="w-2 h-2 bg-error rounded-full animate-pulse"></span> Bảo trì
                        </span>
</div>
<h3 class="font-headline-md text-headline-md text-primary">Phòng Trị liệu VIP 02</h3>
</div>
<div class="text-right">
<span class="font-label-caps text-label-caps text-on-surface-variant block uppercase">Tỉ lệ sử dụng</span>
<span class="font-headline-md text-headline-md text-outline">0%</span>
</div>
</div>
<div class="p-5">
<p class="font-label-caps text-label-caps text-outline mb-4 uppercase">Trạng thái (0/2)</p>
<div class="flex gap-4 h-24 items-center justify-center bg-surface-container-low rounded-lg border border-dashed border-error/20">
<div class="text-center">
<span class="material-symbols-outlined text-error text-[32px] mb-1">build</span>
<p class="text-error font-body-sm text-body-sm font-semibold uppercase">Đang bảo trì định kỳ</p>
</div>
</div>
</div>
<div class="px-5 py-4 bg-surface-container-lowest flex gap-3">
<button class="flex-1 py-2 px-4 bg-surface-container text-outline font-body-sm text-body-sm font-bold rounded-lg cursor-not-allowed">Chi tiết Lịch</button>
<button class="p-2 border border-outline-variant text-on-surface-variant rounded-lg hover:bg-surface-container transition-colors">
<span class="material-symbols-outlined">edit</span>
</button>
</div>
</div>
<!-- Room Card: VIP 03 -->
<div class="bg-surface border border-outline-variant rounded-xl overflow-hidden hover:shadow-lg transition-shadow">
<div class="p-5 border-b border-outline-variant flex justify-between items-start">
<div>
<div class="flex items-center gap-2 mb-1">
<span class="bg-secondary-container text-on-secondary-container text-[10px] font-bold px-2 py-0.5 rounded uppercase tracking-wider">Phòng VIP</span>
<span class="flex items-center gap-1 text-on-tertiary-container text-body-sm font-medium">
<span class="w-2 h-2 bg-on-tertiary-container rounded-full"></span> Hoạt động
                        </span>
</div>
<h3 class="font-headline-md text-headline-md text-primary">Phòng Trị liệu VIP 03</h3>
</div>
<div class="text-right">
<span class="font-label-caps text-label-caps text-on-surface-variant block uppercase">Tỉ lệ sử dụng</span>
<span class="font-headline-md text-headline-md text-secondary">100%</span>
</div>
</div>
<div class="p-5">
<p class="font-label-caps text-label-caps text-outline mb-4 uppercase">Sơ đồ vị trí (2/2)</p>
<div class="flex gap-4 h-24 items-center justify-center bg-surface-container-low rounded-lg border border-dashed border-outline-variant">
<div class="flex flex-col items-center gap-2">
<div class="w-12 h-16 bg-secondary-container border-2 border-secondary rounded-md flex items-center justify-center slot-pill cursor-pointer">
<span class="material-symbols-outlined text-on-secondary-container" style='font-variation-settings: "FILL" 1;'>person</span>
</div>
<span class="font-label-caps text-[9px] text-secondary uppercase font-bold">Đang dùng</span>
</div>
<div class="flex flex-col items-center gap-2">
<div class="w-12 h-16 bg-secondary-container border-2 border-secondary rounded-md flex items-center justify-center slot-pill cursor-pointer">
<span class="material-symbols-outlined text-on-secondary-container" style='font-variation-settings: "FILL" 1;'>person</span>
</div>
<span class="font-label-caps text-[9px] text-secondary uppercase font-bold">Đang dùng</span>
</div>
</div>
<div class="mt-4 pt-4 border-t border-outline-variant/30">
<p class="font-label-caps text-label-caps text-outline mb-2 uppercase">Lịch trình hôm nay (08:00 - 21:00)</p>
<div class="flex flex-wrap gap-1">
<div class="flex-1 h-4 bg-secondary-container rounded-sm flex items-center justify-center text-[8px] text-on-secondary-container font-bold" title="08:00">08</div>
<div class="flex-1 h-4 bg-secondary-container rounded-sm flex items-center justify-center text-[8px] text-on-secondary-container font-bold" title="09:00">09</div>
<div class="flex-1 h-4 bg-secondary-container rounded-sm flex items-center justify-center text-[8px] text-on-secondary-container font-bold" title="10:00">10</div>
<div class="flex-1 h-4 bg-secondary-container rounded-sm flex items-center justify-center text-[8px] text-on-secondary-container font-bold" title="11:00">11</div>
<div class="flex-1 h-4 bg-secondary-container rounded-sm flex items-center justify-center text-[8px] text-on-secondary-container font-bold" title="12:00">12</div>
<div class="flex-1 h-4 bg-secondary-container rounded-sm flex items-center justify-center text-[8px] text-on-secondary-container font-bold" title="13:00">13</div>
<div class="flex-1 h-4 bg-secondary-container rounded-sm flex items-center justify-center text-[8px] text-on-secondary-container font-bold" title="14:00">14</div>
<div class="flex-1 h-4 bg-secondary-container rounded-sm flex items-center justify-center text-[8px] text-on-secondary-container font-bold" title="15:00">15</div>
<div class="flex-1 h-4 bg-secondary-container rounded-sm flex items-center justify-center text-[8px] text-on-secondary-container font-bold" title="16:00">16</div>
<div class="flex-1 h-4 bg-secondary-container rounded-sm flex items-center justify-center text-[8px] text-on-secondary-container font-bold" title="17:00">17</div>
<div class="flex-1 h-4 bg-tertiary-fixed-dim rounded-sm flex items-center justify-center text-[8px] text-on-tertiary-fixed-variant font-bold" title="18:00">18</div>
<div class="flex-1 h-4 bg-tertiary-fixed-dim rounded-sm flex items-center justify-center text-[8px] text-on-tertiary-fixed-variant font-bold" title="19:00">19</div>
<div class="flex-1 h-4 bg-surface-container-highest rounded-sm flex items-center justify-center text-[8px] text-outline font-bold" title="20:00">20</div>
<div class="flex-1 h-4 bg-surface-container-highest rounded-sm flex items-center justify-center text-[8px] text-outline font-bold" title="21:00">21</div>
</div>
<div class="flex justify-between mt-1">
<span class="text-[9px] font-label-caps text-outline">08:00</span>
<span class="text-[9px] font-label-caps text-outline">21:00</span>
</div>
</div>
</div>
<div class="px-5 py-4 bg-surface-container-lowest flex gap-3">
<button class="flex-1 py-2 px-4 border border-secondary text-secondary font-body-sm text-body-sm font-bold rounded-lg hover:bg-secondary-container/10 transition-colors">Chi tiết Lịch</button>
<button class="p-2 border border-outline-variant text-on-surface-variant rounded-lg hover:bg-surface-container transition-colors">
<span class="material-symbols-outlined">edit</span>
</button>
</div>
</div>
<!-- Room Card: Community B -->
<div class="bg-surface border border-outline-variant rounded-xl overflow-hidden hover:shadow-lg transition-shadow">
<div class="p-5 border-b border-outline-variant flex justify-between items-start">
<div>
<div class="flex items-center gap-2 mb-1">
<span class="bg-surface-container-highest text-on-surface-variant text-[10px] font-bold px-2 py-0.5 rounded uppercase tracking-wider">Cộng đồng</span>
<span class="flex items-center gap-1 text-on-tertiary-container text-body-sm font-medium">
<span class="w-2 h-2 bg-on-tertiary-container rounded-full"></span> Hoạt động
                        </span>
</div>
<h3 class="font-headline-md text-headline-md text-primary">Phòng Trị liệu Tổng hợp B</h3>
</div>
<div class="text-right">
<span class="font-label-caps text-label-caps text-on-surface-variant block uppercase">Tỉ lệ sử dụng</span>
<span class="font-headline-md text-headline-md text-secondary">25%</span>
</div>
</div>
<div class="p-5">
<p class="font-label-caps text-label-caps text-outline mb-4 uppercase">Sơ đồ vị trí (3/12)</p>
<div class="grid grid-cols-6 gap-2 p-3 bg-surface-container-low rounded-lg border border-outline-variant">
<div class="aspect-square bg-secondary-container rounded-sm flex items-center justify-center slot-pill"><span class="material-symbols-outlined text-[14px] text-on-secondary-container" style="font-variation-settings: 'FILL' 1;">person</span></div>
<div class="aspect-square bg-secondary-container rounded-sm flex items-center justify-center slot-pill"><span class="material-symbols-outlined text-[14px] text-on-secondary-container" style="font-variation-settings: 'FILL' 1;">person</span></div>
<div class="aspect-square bg-secondary-container rounded-sm flex items-center justify-center slot-pill"><span class="material-symbols-outlined text-[14px] text-on-secondary-container" style="font-variation-settings: 'FILL' 1;">person</span></div>
<div class="aspect-square bg-tertiary-fixed-dim/30 border border-on-tertiary-fixed-variant/20 rounded-sm flex items-center justify-center slot-pill"><span class="material-symbols-outlined text-[14px] text-on-tertiary-fixed-variant">circle</span></div>
<div class="aspect-square bg-tertiary-fixed-dim/30 border border-on-tertiary-fixed-variant/20 rounded-sm flex items-center justify-center slot-pill"><span class="material-symbols-outlined text-[14px] text-on-tertiary-fixed-variant">circle</span></div>
<div class="aspect-square bg-tertiary-fixed-dim/30 border border-on-tertiary-fixed-variant/20 rounded-sm flex items-center justify-center slot-pill"><span class="material-symbols-outlined text-[14px] text-on-tertiary-fixed-variant">circle</span></div>
<div class="aspect-square bg-tertiary-fixed-dim/30 border border-on-tertiary-fixed-variant/20 rounded-sm flex items-center justify-center slot-pill"><span class="material-symbols-outlined text-[14px] text-on-tertiary-fixed-variant">circle</span></div>
<div class="aspect-square bg-tertiary-fixed-dim/30 border border-on-tertiary-fixed-variant/20 rounded-sm flex items-center justify-center slot-pill"><span class="material-symbols-outlined text-[14px] text-on-tertiary-fixed-variant">circle</span></div>
<div class="aspect-square bg-tertiary-fixed-dim/30 border border-on-tertiary-fixed-variant/20 rounded-sm flex items-center justify-center slot-pill"><span class="material-symbols-outlined text-[14px] text-on-tertiary-fixed-variant">circle</span></div>
<div class="aspect-square bg-tertiary-fixed-dim/30 border border-on-tertiary-fixed-variant/20 rounded-sm flex items-center justify-center slot-pill"><span class="material-symbols-outlined text-[14px] text-on-tertiary-fixed-variant">circle</span></div>
<div class="aspect-square bg-tertiary-fixed-dim/30 border border-on-tertiary-fixed-variant/20 rounded-sm flex items-center justify-center slot-pill"><span class="material-symbols-outlined text-[14px] text-on-tertiary-fixed-variant">circle</span></div>
<div class="aspect-square bg-tertiary-fixed-dim/30 border border-on-tertiary-fixed-variant/20 rounded-sm flex items-center justify-center slot-pill"><span class="material-symbols-outlined text-[14px] text-on-tertiary-fixed-variant">circle</span></div>
</div>
<div class="mt-4 pt-4 border-t border-outline-variant/30">
<p class="font-label-caps text-label-caps text-outline mb-2 uppercase">Lịch trình hôm nay (08:00 - 21:00)</p>
<div class="flex flex-wrap gap-1">
<div class="flex-1 h-4 bg-tertiary-fixed-dim rounded-sm flex items-center justify-center text-[8px] text-on-tertiary-fixed-variant font-bold" title="08:00">08</div>
<div class="flex-1 h-4 bg-tertiary-fixed-dim rounded-sm flex items-center justify-center text-[8px] text-on-tertiary-fixed-variant font-bold" title="09:00">09</div>
<div class="flex-1 h-4 bg-secondary-container/40 rounded-sm flex items-center justify-center text-[8px] text-on-secondary-container font-bold" title="10:00">10</div>
<div class="flex-1 h-4 bg-secondary-container/40 rounded-sm flex items-center justify-center text-[8px] text-on-secondary-container font-bold" title="11:00">11</div>
<div class="flex-1 h-4 bg-tertiary-fixed-dim rounded-sm flex items-center justify-center text-[8px] text-on-tertiary-fixed-variant font-bold" title="12:00">12</div>
<div class="flex-1 h-4 bg-tertiary-fixed-dim rounded-sm flex items-center justify-center text-[8px] text-on-tertiary-fixed-variant font-bold" title="13:00">13</div>
<div class="flex-1 h-4 bg-tertiary-fixed-dim rounded-sm flex items-center justify-center text-[8px] text-on-tertiary-fixed-variant font-bold" title="14:00">14</div>
<div class="flex-1 h-4 bg-tertiary-fixed-dim rounded-sm flex items-center justify-center text-[8px] text-on-tertiary-fixed-variant font-bold" title="15:00">15</div>
<div class="flex-1 h-4 bg-tertiary-fixed-dim rounded-sm flex items-center justify-center text-[8px] text-on-tertiary-fixed-variant font-bold" title="16:00">16</div>
<div class="flex-1 h-4 bg-tertiary-fixed-dim rounded-sm flex items-center justify-center text-[8px] text-on-tertiary-fixed-variant font-bold" title="17:00">17</div>
<div class="flex-1 h-4 bg-tertiary-fixed-dim rounded-sm flex items-center justify-center text-[8px] text-on-tertiary-fixed-variant font-bold" title="18:00">18</div>
<div class="flex-1 h-4 bg-surface-container-highest rounded-sm flex items-center justify-center text-[8px] text-outline font-bold" title="19:00">19</div>
<div class="flex-1 h-4 bg-surface-container-highest rounded-sm flex items-center justify-center text-[8px] text-outline font-bold" title="20:00">20</div>
<div class="flex-1 h-4 bg-surface-container-highest rounded-sm flex items-center justify-center text-[8px] text-outline font-bold" title="21:00">21</div>
</div>
<div class="flex justify-between mt-1">
<span class="text-[9px] font-label-caps text-outline">08:00</span>
<span class="text-[9px] font-label-caps text-outline">21:00</span>
</div>
</div>
</div>
<div class="px-5 py-4 bg-surface-container-lowest flex gap-3">
<button class="flex-1 py-2 px-4 border border-secondary text-secondary font-body-sm text-body-sm font-bold rounded-lg hover:bg-secondary-container/10 transition-colors">Chi tiết Lịch</button>
<button class="p-2 border border-outline-variant text-on-surface-variant rounded-lg hover:bg-surface-container transition-colors">
<span class="material-symbols-outlined">edit</span>
</button>
</div>
</div>
<!-- Empty State / Add New -->
<div class="bg-surface border-2 border-dashed border-outline-variant rounded-xl flex flex-col items-center justify-center p-12 text-center group cursor-pointer hover:bg-surface-container-low transition-colors">
<div class="w-14 h-14 bg-surface-container-highest rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
<span class="material-symbols-outlined text-outline text-[32px]">add_home_work</span>
</div>
<h4 class="font-headline-md text-headline-md text-primary mb-1">Thêm Khu vực mới</h4>
<p class="font-body-sm text-body-sm text-on-surface-variant max-w-[200px]">Cấu hình sơ đồ giường và thiết bị cho khu vực trị liệu mới.</p>
</div>
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
<span class="font-body-sm text-body-sm text-on-surface font-medium">Không khả dụng (Bảo trì/Khóa)</span>
</div>
</div>
<div class="flex items-center gap-4 text-on-surface-variant">
<span class="font-body-sm text-body-sm">Cập nhật lần cuối: <strong>14:30:22</strong></span>
<button class="p-2 hover:bg-surface-container rounded-full transition-colors active:rotate-180 duration-500">
<span class="material-symbols-outlined">refresh</span>
</button>
</div>
</section>
</main>
<!-- Bottom Navigation for Mobile -->
<div class="md:hidden fixed bottom-0 left-0 right-0 bg-surface border-t border-outline-variant px-6 h-16 flex items-center justify-between z-50">
<button class="flex flex-col items-center gap-1 text-on-surface-variant">
<span class="material-symbols-outlined">dashboard</span>
<span class="text-[10px] font-bold uppercase">Tổng quan</span>
</button>
<button class="flex flex-col items-center gap-1 text-on-surface-variant">
<span class="material-symbols-outlined">calendar_month</span>
<span class="text-[10px] font-bold uppercase">Lịch hẹn</span>
</button>
<button class="flex flex-col items-center gap-1 text-secondary">
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">meeting_room</span>
<span class="text-[10px] font-bold uppercase">Phòng bệnh</span>
</button>
<button class="flex flex-col items-center gap-1 text-on-surface-variant">
<span class="material-symbols-outlined">person</span>
<span class="text-[10px] font-bold uppercase">Cá nhân</span>
</button>
</div>
<script>
    // Simple micro-interactions
    document.querySelectorAll('.slot-pill').forEach(pill => {
        pill.addEventListener('click', function() {
            this.classList.toggle('scale-110');
            setTimeout(() => this.classList.remove('scale-110'), 200);
        });
    });
</script>
</body></html>
