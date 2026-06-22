<!DOCTYPE html>
<html class="light" lang="vi"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Danh sách Lịch Tư Vấn | Longevity Booking</title>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&amp;family=Inter:wght@400;500;600&amp;family=JetBrains+Mono:wght@500;600&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<script>tailwind.config={darkMode:"class",theme:{extend:{"colors":{"on-tertiary-fixed":"#002113","surface-bright":"#f7f9fb","on-surface":"#191c1e","on-secondary":"#ffffff","inverse-primary":"#bec6e0","tertiary-fixed-dim":"#4edea3","secondary":"#006591","tertiary-fixed":"#6ffbbe","primary-container":"#131b2e","on-tertiary-container":"#009668","on-tertiary-fixed-variant":"#005236","on-primary-fixed":"#131b2e","on-surface-variant":"#45464d","on-primary-fixed-variant":"#3f465c","inverse-on-surface":"#eff1f3","surface-container-lowest":"#ffffff","on-secondary-container":"#004666","surface-container-low":"#f2f4f6","surface-container-highest":"#e0e3e5","on-primary-container":"#7c839b","on-secondary-fixed":"#001e2f","surface":"#f7f9fb","on-primary":"#ffffff","error-container":"#ffdad6","surface-variant":"#e0e3e5","on-tertiary":"#ffffff","on-error":"#ffffff","surface-tint":"#565e74","surface-dim":"#d8dadc","inverse-surface":"#2d3133","outline-variant":"#c6c6cd","secondary-fixed":"#c9e6ff","on-secondary-fixed-variant":"#004c6e","tertiary":"#000000","surface-container":"#eceef0","outline":"#76777d","secondary-container":"#39b8fd","background":"#f7f9fb","primary-fixed":"#dae2fd","secondary-fixed-dim":"#89ceff","on-background":"#191c1e","tertiary-container":"#002113","primary":"#000000","surface-container-high":"#e6e8ea","primary-fixed-dim":"#bec6e0","error":"#ba1a1a","on-error-container":"#93000a"},"borderRadius":{"DEFAULT":"0.125rem","lg":"0.25rem","xl":"0.5rem","full":"0.75rem"},"spacing":{"container-margin":"24px","gutter":"12px","row-height-standard":"56px","row-height-compact":"40px","unit":"4px"},"fontFamily":{"headline-md":["Manrope"],"time-slot":["JetBrains Mono"],"body-md":["Inter"],"body-sm":["Inter"],"headline-lg":["Manrope"],"label-caps":["JetBrains Mono"]},"fontSize":{"headline-md":["18px",{"lineHeight":"24px","fontWeight":"600"}],"time-slot":["12px",{"lineHeight":"16px","fontWeight":"500"}],"body-md":["14px",{"lineHeight":"20px","fontWeight":"400"}],"body-sm":["13px",{"lineHeight":"18px","fontWeight":"400"}],"headline-lg":["24px",{"lineHeight":"32px","fontWeight":"700"}],"label-caps":["11px",{"lineHeight":"16px","letterSpacing":"0.05em","fontWeight":"600"}]}}}}</script>
<style>
body { font-family: 'Inter', sans-serif; background-color: #f7f9fb; }
.material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; vertical-align: middle; }
.sticky-col { position: sticky; background-color: inherit; z-index: 10; }
.sticky-left-0 { left: 0; } .sticky-right-0 { right: 0; }
.custom-scrollbar::-webkit-scrollbar { height: 10px; }
.custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #c6c6cd; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #9ca3af; }
.custom-scrollbar { scrollbar-width: thin; scrollbar-color: #c6c6cd #f1f1f1; }
</style>
</head>
<body class="bg-surface text-on-surface">
@if (session('ok'))
<div class="fixed top-20 left-1/2 -translate-x-1/2 z-[60] px-5 py-3 rounded-xl bg-tertiary-fixed-dim/90 text-on-tertiary-container shadow-lg flex items-center gap-2 text-body-md font-semibold" id="flash-ok">
<span class="material-symbols-outlined">check_circle</span> {{ session('ok') }}
</div>
<script>setTimeout(()=>document.getElementById('flash-ok')?.remove(), 4000);</script>
@endif
@include('partials.topnav', ['active' => 'tu-van'])
<main class="pt-24 pb-12 px-container-margin">
<div class="max-w-[1600px] mx-auto">
<div class="flex items-center justify-between mb-6">
<h2 class="text-headline-lg font-extrabold text-on-surface uppercase tracking-tight">Quản lý Đặt lịch Bác sĩ</h2>
<div class="flex bg-surface-container-low rounded-xl p-1">
<a href="/{{ $coSo->slug }}/lich-tu-van" class="px-6 py-1.5 rounded-lg text-body-md font-semibold transition-all text-on-surface-variant hover:text-on-surface inline-block">Timeline</a>
<button class="px-6 py-1.5 rounded-lg text-body-md font-semibold transition-all bg-surface-container-lowest shadow-sm text-secondary">Danh sách</button>
</div>
</div>

<!-- Filters -->
<form method="GET" class="bg-surface-container-lowest p-6 rounded-2xl border border-outline-variant shadow-sm space-y-4 mb-8">
<div class="flex flex-wrap items-end gap-4">
<div class="flex flex-col gap-1.5">
<label class="text-label-caps font-label-caps text-on-surface-variant ml-1">KHOẢNG THỜI GIAN</label>
<div class="flex items-center gap-2">
<input name="ngay_tu" value="{{ $filters['ngay_tu'] ?? '' }}" class="border border-outline-variant rounded-lg px-3 py-2 text-body-md focus:border-secondary focus:ring-1 focus:ring-secondary/20 outline-none bg-surface" type="date"/>
<span class="text-on-surface-variant">đến</span>
<input name="ngay_den" value="{{ $filters['ngay_den'] ?? '' }}" class="border border-outline-variant rounded-lg px-3 py-2 text-body-md focus:border-secondary focus:ring-1 focus:ring-secondary/20 outline-none bg-surface" type="date"/>
</div>
</div>
<div class="flex flex-col gap-1.5">
<label class="text-label-caps font-label-caps text-on-surface-variant ml-1">BÁC SĨ TƯ VẤN</label>
<select name="bac_si_id" class="border border-outline-variant rounded-lg px-3 py-2 text-body-md focus:border-secondary focus:ring-1 focus:ring-secondary/20 outline-none bg-surface min-w-[200px]">
<option value="">Tất cả bác sĩ</option>
@foreach ($bacSis as $b)
<option value="{{ $b->id }}" @selected(($filters['bac_si_id'] ?? '')==$b->id)>{{ $b->ten_day_du }}</option>
@endforeach
</select>
</div>
<div class="flex flex-col gap-1.5">
<label class="text-label-caps font-label-caps text-on-surface-variant ml-1">NGUỒN</label>
<select name="nguon" class="border border-outline-variant rounded-lg px-3 py-2 text-body-md focus:border-secondary focus:ring-1 focus:ring-secondary/20 outline-none bg-surface min-w-[160px]">
<option value="">Tất cả nguồn</option>
@foreach ($nguons as $ng)
<option value="{{ $ng }}" @selected(($filters['nguon'] ?? '')===$ng)>{{ $ng }}</option>
@endforeach
</select>
</div>
<div class="flex flex-col gap-1.5">
<label class="text-label-caps font-label-caps text-on-surface-variant ml-1">TRẠNG THÁI</label>
<select name="trang_thai" class="border border-outline-variant rounded-lg px-3 py-2 text-body-md focus:border-secondary focus:ring-1 focus:ring-secondary/20 outline-none bg-surface min-w-[160px]">
<option value="">Tất cả</option>
<option value="cho_duyet" @selected(($filters['trang_thai'] ?? '')==='cho_duyet')>Chờ duyệt</option>
<option value="da_duyet" @selected(($filters['trang_thai'] ?? '')==='da_duyet')>Đã duyệt</option>
</select>
</div>
@php
    $pbId = auth()->user()->phong_ban_id;
    $vtId = auth()->user()->vai_tro_id;
    $isAdmin = auth()->user()->is_admin;
    $canExportTuVan = $isAdmin || \App\Models\PhanQuyen::where(fn($q) => $q->where('phong_ban_id', $pbId)->orWhere('vai_tro_id', $vtId))->where('truong', 'xuat_lich_tu_van')->exists();
    $canDeleteTuVan = $isAdmin || \App\Models\PhanQuyen::where(fn($q) => $q->where('phong_ban_id', $pbId)->orWhere('vai_tro_id', $vtId))->where('truong', 'xoa_lich_tu_van')->exists();
    $canEditTuVan = $isAdmin || \App\Models\PhanQuyen::where(fn($q) => $q->where('phong_ban_id', $pbId)->orWhere('vai_tro_id', $vtId))->where('truong', 'sua_lich_tu_van')->exists();
    $canDuyet = $isAdmin || \App\Models\PhanQuyen::where(fn($q) => $q->where('phong_ban_id', $pbId)->orWhere('vai_tro_id', $vtId))->where('truong', 'duyet_tu_van')->exists();
@endphp
<div class="ml-auto flex items-center gap-2">
<a href="/{{ $coSo->slug }}/ds-tu-van" class="flex items-center gap-2 px-4 py-2.5 text-body-sm font-semibold text-on-surface-variant bg-surface border border-outline-variant rounded-lg hover:bg-surface-variant transition-colors">
<span class="material-symbols-outlined text-[18px]">restart_alt</span> Đặt lại
</a>
<button type="submit" class="flex items-center gap-2 px-4 py-2.5 text-body-sm font-semibold bg-primary text-on-primary rounded-lg hover:opacity-90 transition-opacity">
<span class="material-symbols-outlined text-[18px]">filter_list</span> Lọc dữ liệu
</button>
@if ($canExportTuVan)
<a href="/{{ $coSo->slug }}/xuat-tu-van" class="flex items-center gap-2 px-4 py-2.5 text-body-sm font-semibold bg-on-tertiary-container text-on-primary rounded-lg hover:opacity-90 transition-opacity">
<span class="material-symbols-outlined text-[18px]">download</span> Xuất Excel
</a>
<label class="flex items-center gap-2 px-4 py-2.5 text-body-sm font-semibold text-on-surface-variant bg-surface border border-outline-variant rounded-lg cursor-pointer hover:bg-surface-variant transition-colors">
<span class="material-symbols-outlined text-[18px]">upload</span> Chọn file
<input type="file" name="file" form="import-tu-van" accept=".xlsx,.xls,.csv" class="hidden" onchange="this.form.submit()"/>
</label>
@endif
</div>
</div>
</div>
</form>
@if ($canExportTuVan)
<form id="import-tu-van" method="POST" action="/{{ $coSo->slug }}/nhap-tu-van" enctype="multipart/form-data" class="hidden">@csrf</form>
@endif

<!-- Data Table -->
<div class="bg-surface-container-lowest rounded-2xl border border-outline-variant shadow-sm overflow-hidden flex flex-col">
<div class="overflow-x-auto custom-scrollbar pb-1">
<table class="w-full text-left border-collapse table-auto min-w-[1300px] whitespace-nowrap">
<thead>
<tr class="bg-surface-container-low border-b border-outline-variant">
<th class="px-4 py-4 text-label-caps font-label-caps text-on-surface-variant sticky-col sticky-left-0 bg-surface-container-low shadow-[2px_0_5px_rgba(0,0,0,0.05)]">DẤU THỜI GIAN</th>
<th class="px-4 py-4 text-label-caps font-label-caps text-on-surface-variant">HỌ TÊN KH</th>
<th class="px-4 py-4 text-label-caps font-label-caps text-on-surface-variant">SỐ ĐIỆN THOẠI</th>
<th class="px-4 py-4 text-label-caps font-label-caps text-on-surface-variant">EMAIL</th>
<th class="px-4 py-4 text-label-caps font-label-caps text-on-surface-variant">NGÀY HẸN</th>
<th class="px-4 py-4 text-label-caps font-label-caps text-on-surface-variant">BÁC SĨ TƯ VẤN</th>
<th class="px-4 py-4 text-label-caps font-label-caps text-on-surface-variant">CA KHÁM</th>
<th class="px-4 py-4 text-label-caps font-label-caps text-on-surface-variant">NGUỒN</th>
<th class="px-4 py-4 text-label-caps font-label-caps text-on-surface-variant">SALE</th>
<th class="px-4 py-4 text-label-caps font-label-caps text-on-surface-variant">GHI CHÚ</th>
<th class="px-4 py-4 text-label-caps font-label-caps text-on-surface-variant sticky-col sticky-right-0 bg-surface-container-low text-right shadow-[-2px_0_5px_rgba(0,0,0,0.05)]">TRẠNG THÁI / THAO TÁC</th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant/30">
@forelse ($lichHens as $lh)
<tr class="hover:bg-surface-variant/10 transition-colors">
<td class="px-4 py-4 sticky-col sticky-left-0 bg-surface-container-lowest shadow-[2px_0_5px_rgba(0,0,0,0.05)]">
<span class="text-body-sm font-time-slot text-on-surface-variant">{{ $lh->created_at->format('d/m H:i') }}</span>
</td>
<td class="px-4 py-4 font-bold text-on-surface">{{ $lh->khachHang?->ho_ten }}</td>
<td class="px-4 py-4 text-body-sm font-time-slot">{{ $lh->khachHang?->so_dien_thoai }}</td>
<td class="px-4 py-4 text-body-sm text-on-surface-variant">{{ $lh->khachHang?->email ?: '—' }}</td>
<td class="px-4 py-4 text-body-sm">{{ $lh->ngay_hen?->format('d/m/Y') }}</td>
<td class="px-4 py-4 text-body-sm font-semibold">{{ $lh->bacSiTuVan?->ten_day_du ?? '—' }}</td>
<td class="px-4 py-4 text-body-sm font-time-slot">{{ $lh->caKham?->nhan ?? '—' }}</td>
<td class="px-4 py-4"><span class="px-2 py-0.5 bg-surface-container-high rounded text-[11px]">{{ $lh->nguon ?? '—' }}</span></td>
<td class="px-4 py-4 text-body-sm">{{ $lh->sale?->name ?? '—' }}</td>
<td class="px-4 py-4 text-body-sm text-on-surface-variant italic truncate max-w-[150px]" title="{{ $lh->ghi_chu }}">{{ $lh->ghi_chu ?: '—' }}</td>
<td class="px-4 py-4 sticky-col sticky-right-0 bg-surface-container-lowest shadow-[-2px_0_5px_rgba(0,0,0,0.05)]">
<div class="flex items-center justify-end gap-1">
@php $approved = $lh->trang_thai === 'da_duyet'; @endphp
<span class="px-2 py-0.5 mr-1 rounded-full text-[11px] font-semibold {{ $approved ? 'bg-tertiary-fixed-dim/40 text-on-tertiary-container' : 'bg-secondary-container/40 text-on-secondary-container' }}">{{ $approved ? 'Đã duyệt' : 'Chờ duyệt' }}</span>
@if ($canDuyet)
<form method="POST" action="/{{ $coSo->slug }}/duyet-tu-van/{{ $lh->id }}" class="inline">
@csrf @method('PATCH')
<button type="submit" title="{{ $approved ? 'Bỏ duyệt' : 'Duyệt' }}" class="w-7 h-7 rounded-full text-[12px] font-bold border flex items-center justify-center transition-colors {{ $approved ? 'bg-on-tertiary-container text-white border-on-tertiary-container hover:bg-error hover:border-error' : 'text-outline border-outline-variant hover:border-on-tertiary-container hover:text-on-tertiary-container' }}">
<span class="material-symbols-outlined text-[16px]">{{ $approved ? 'close' : 'check' }}</span>
</button>
</form>
@endif
<a href="/{{ $coSo->slug }}/xem-tu-van/{{ $lh->id }}" title="Xem chi tiết" class="p-1.5 rounded-lg text-on-surface-variant hover:bg-surface-variant transition-colors">
<span class="material-symbols-outlined text-[18px]">visibility</span>
</a>
@if ($canEditTuVan)
<a href="/{{ $coSo->slug }}/sua-tu-van/{{ $lh->id }}" title="Sửa" class="p-1.5 rounded-lg text-secondary hover:bg-secondary-container/30 transition-colors">
<span class="material-symbols-outlined text-[18px]">edit</span>
</a>
@endif
@if ($canDeleteTuVan)
<form method="POST" action="/{{ $coSo->slug }}/xoa-tu-van/{{ $lh->id }}" class="inline" onsubmit="return confirm('Xóa lịch tư vấn này? Hành động không thể hoàn tác.')">
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
<td colspan="11" class="px-4 py-16 text-center">
<div class="flex flex-col items-center gap-2 text-on-surface-variant">
<span class="material-symbols-outlined text-[40px] opacity-50">event_busy</span>
<p class="text-body-md">Chưa có lịch tư vấn nào khớp bộ lọc.</p>
<a href="/{{ $coSo->slug }}/dat-kham" class="mt-2 px-4 py-2 bg-secondary-container text-on-secondary-container rounded-lg font-semibold text-body-sm">+ Tạo lịch tư vấn</a>
</div>
</td>
</tr>
@endforelse
</tbody>
</table>
</div>
<div class="px-6 py-4 bg-surface-container-low border-t border-outline-variant flex items-center justify-between gap-4">
<p class="text-body-sm text-on-surface-variant">
@if ($lichHens->total() > 0)
Hiển thị {{ $lichHens->firstItem() }} - {{ $lichHens->lastItem() }} trên tổng số {{ $lichHens->total() }} kết quả
@else
Không có kết quả
@endif
</p>
<div>{{ $lichHens->links() }}</div>
</div>
</div>
</div>
</main>

<a href="/{{ $coSo->slug }}/dat-kham" class="fixed bottom-8 right-8 w-14 h-14 bg-primary text-on-primary rounded-full shadow-lg hover:scale-105 active:scale-95 transition-all flex items-center justify-center z-50 group">
<span class="material-symbols-outlined text-[28px]">add</span>
<span class="absolute right-full mr-4 px-3 py-1.5 bg-inverse-surface text-inverse-on-surface text-body-sm rounded-lg opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">Thêm lịch tư vấn</span>
</a>
</body></html>
