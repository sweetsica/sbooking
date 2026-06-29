<!DOCTYPE html>
<html class="light" lang="vi"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Lịch làm việc | Longevity Booking</title>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&amp;family=Inter:wght@400;500;600&amp;family=JetBrains+Mono:wght@500;600&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<script>tailwind.config={darkMode:"class",theme:{extend:{"colors":{"on-tertiary-fixed":"#002113","surface-bright":"#f7f9fb","on-surface":"#191c1e","on-secondary":"#ffffff","inverse-primary":"#bec6e0","tertiary-fixed-dim":"#4edea3","secondary":"#006591","tertiary-fixed":"#6ffbbe","primary-container":"#131b2e","on-tertiary-container":"#009668","on-tertiary-fixed-variant":"#005236","on-primary-fixed":"#131b2e","on-surface-variant":"#45464d","on-primary-fixed-variant":"#3f465c","inverse-on-surface":"#eff1f3","surface-container-lowest":"#ffffff","on-secondary-container":"#004666","surface-container-low":"#f2f4f6","surface-container-highest":"#e0e3e5","on-primary-container":"#7c839b","on-secondary-fixed":"#001e2f","surface":"#f7f9fb","on-primary":"#ffffff","error-container":"#ffdad6","surface-variant":"#e0e3e5","on-tertiary":"#ffffff","on-error":"#ffffff","surface-tint":"#565e74","surface-dim":"#d8dadc","inverse-surface":"#2d3133","outline-variant":"#c6c6cd","secondary-fixed":"#c9e6ff","on-secondary-fixed-variant":"#004c6e","tertiary":"#000000","surface-container":"#eceef0","outline":"#76777d","secondary-container":"#39b8fd","background":"#f7f9fb","primary-fixed":"#dae2fd","secondary-fixed-dim":"#89ceff","on-background":"#191c1e","tertiary-container":"#002113","primary":"#000000","surface-container-high":"#e6e8ea","primary-fixed-dim":"#bec6e0","error":"#ba1a1a","on-error-container":"#93000a"},"borderRadius":{"DEFAULT":"0.125rem","lg":"0.25rem","xl":"0.5rem","full":"0.75rem"},"spacing":{"container-margin":"24px","gutter":"12px","row-height-standard":"56px","row-height-compact":"40px","unit":"4px"},"fontFamily":{"headline-md":["Manrope"],"time-slot":["JetBrains Mono"],"body-md":["Inter"],"body-sm":["Inter"],"headline-lg":["Manrope"],"label-caps":["JetBrains Mono"]},"fontSize":{"headline-md":["18px",{"lineHeight":"24px","fontWeight":"600"}],"time-slot":["12px",{"lineHeight":"16px","fontWeight":"500"}],"body-md":["14px",{"lineHeight":"20px","fontWeight":"400"}],"body-sm":["13px",{"lineHeight":"18px","fontWeight":"400"}],"headline-lg":["24px",{"lineHeight":"32px","fontWeight":"700"}],"label-caps":["11px",{"lineHeight":"16px","letterSpacing":"0.05em","fontWeight":"600"}]}}}}</script>
<style>
body { font-family: 'Inter', sans-serif; background-color: #f7f9fb; }
.material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; vertical-align: middle; }
</style>
</head>
<body class="bg-surface text-on-surface">
@php
    $badges = [
        'nhap'      => ['Nháp', 'bg-surface-container-highest text-on-surface-variant'],
        'cho_duyet' => ['Chờ duyệt', 'bg-amber-100 text-amber-700'],
        'da_duyet'  => ['Đã duyệt', 'bg-tertiary-fixed-dim/40 text-on-tertiary-container'],
        'tu_choi'   => ['Từ chối', 'bg-red-100 text-red-700'],
    ];
@endphp
@if (session('ok'))
<div class="fixed top-20 left-1/2 -translate-x-1/2 z-[60] px-5 py-3 rounded-xl bg-tertiary-fixed-dim/90 text-on-tertiary-container shadow-lg flex items-center gap-2 text-body-md font-semibold" id="flash-ok">
<span class="material-symbols-outlined">check_circle</span> {{ session('ok') }}
</div>
<script>setTimeout(()=>document.getElementById('flash-ok')?.remove(), 4000);</script>
@endif
@if (session('err'))
<div class="fixed top-20 left-1/2 -translate-x-1/2 z-[60] px-5 py-3 rounded-xl bg-error/90 text-on-error shadow-lg flex items-center gap-2 text-body-md font-semibold" id="flash-err">
<span class="material-symbols-outlined">error</span> {{ session('err') }}
</div>
<script>setTimeout(()=>document.getElementById('flash-err')?.remove(), 5000);</script>
@endif
@if ($errors->any())
<div class="fixed top-20 left-1/2 -translate-x-1/2 z-[60] px-5 py-3 rounded-xl bg-error/90 text-on-error shadow-lg text-body-md font-semibold max-w-md">
<div class="flex items-center gap-2"><span class="material-symbols-outlined">error</span> Có lỗi:</div>
<ul class="list-disc ml-7 mt-1 text-body-sm font-normal">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif
@include('partials.topnav', ['active' => 'lich-lam-viec'])
<main class="pt-24 pb-20 px-container-margin">
<div class="max-w-[1200px] mx-auto">

<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 gap-3">
<div>
<h2 class="text-headline-lg font-extrabold text-on-surface uppercase tracking-tight">Lịch làm việc</h2>
<p class="text-body-sm text-on-surface-variant mt-1">Upload lịch trực theo tháng (bác sĩ / KTV trực phòng nào, ca Sáng/Chiều, ngày nào) → duyệt → áp dụng. Ngày không phân công = đóng phòng ca đó.</p>
</div>
<div class="flex items-center gap-2 shrink-0">
<a href="/{{ $coSo->slug }}/lich-lam-viec/mau" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg border border-outline-variant text-body-md font-semibold text-on-surface hover:bg-surface-container-low transition-colors">
<span class="material-symbols-outlined text-[20px]">download</span> Tải mẫu
</a>
@if ($canUpload)
<button onclick="document.getElementById('upload-modal').classList.remove('hidden');document.getElementById('upload-modal').classList.add('flex')" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-primary text-on-primary text-body-md font-semibold hover:opacity-90 transition-opacity">
<span class="material-symbols-outlined text-[20px]">upload_file</span> Upload lịch
</button>
@endif
</div>
</div>

<div class="bg-surface-container-lowest rounded-2xl border border-outline-variant shadow-sm overflow-hidden">
<div class="overflow-x-auto">
<table class="w-full text-left">
<thead class="bg-surface-container-low border-b border-outline-variant">
<tr class="text-label-caps font-label-caps text-on-surface-variant uppercase">
<th class="px-4 py-3">Tháng</th>
<th class="px-4 py-3">Trạng thái</th>
<th class="px-4 py-3">Người tạo</th>
<th class="px-4 py-3">Người duyệt</th>
<th class="px-4 py-3">Áp dụng lúc</th>
<th class="px-4 py-3 text-right">Hành động</th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant/60">
@forelse ($dsLich as $lich)
@php [$bLabel, $bClass] = $badges[$lich->trang_thai] ?? ['?', 'bg-surface-container-highest']; @endphp
<tr class="hover:bg-surface-container-low/50 transition-colors">
<td class="px-4 py-4 font-time-slot font-semibold text-on-surface">{{ $lich->thang->format('m/Y') }}</td>
<td class="px-4 py-4"><span class="inline-flex px-2.5 py-1 rounded-full text-body-sm font-semibold {{ $bClass }}">{{ $bLabel }}</span>
@if ($lich->trang_thai === 'tu_choi' && $lich->ly_do_tu_choi)<div class="text-[11px] text-red-600 mt-1 max-w-[200px] truncate" title="{{ $lich->ly_do_tu_choi }}">{{ $lich->ly_do_tu_choi }}</div>@endif
</td>
<td class="px-4 py-4 text-body-sm text-on-surface-variant">{{ $lich->nguoiTao?->name ?? '—' }}</td>
<td class="px-4 py-4 text-body-sm text-on-surface-variant">{{ $lich->nguoiDuyet?->name ?? '—' }}</td>
<td class="px-4 py-4 text-body-sm text-on-surface-variant font-time-slot">{{ $lich->applied_at?->format('d/m/Y H:i') ?? '—' }}</td>
<td class="px-4 py-4">
<div class="flex items-center justify-end gap-1">
<a href="/{{ $coSo->slug }}/lich-lam-viec/{{ $lich->id }}" title="Xem chi tiết" class="p-2 rounded-lg text-on-surface-variant hover:bg-surface-container-high transition-colors"><span class="material-symbols-outlined text-[20px]">visibility</span></a>
@if ($canUpload && in_array($lich->trang_thai, ['nhap', 'tu_choi']))
<form method="POST" action="/{{ $coSo->slug }}/lich-lam-viec/{{ $lich->id }}/gui-duyet">@csrf
<button type="submit" title="Gửi duyệt" class="p-2 rounded-lg text-secondary hover:bg-secondary/10 transition-colors"><span class="material-symbols-outlined text-[20px]">send</span></button>
</form>
@endif
@if ($canDuyet && $lich->trang_thai === 'cho_duyet')
<form method="POST" action="/{{ $coSo->slug }}/lich-lam-viec/{{ $lich->id }}/duyet" onsubmit="return confirm('Duyệt và ÁP DỤNG lịch tháng {{ $lich->thang->format('m/Y') }} vào hệ thống?')">@csrf @method('PATCH')
<button type="submit" title="Duyệt & áp dụng" class="p-2 rounded-lg text-emerald-600 hover:bg-emerald-50 transition-colors"><span class="material-symbols-outlined text-[20px]">verified</span></button>
</form>
<button onclick="openReject({{ $lich->id }}, '{{ $lich->thang->format('m/Y') }}')" title="Từ chối" class="p-2 rounded-lg text-red-600 hover:bg-red-50 transition-colors"><span class="material-symbols-outlined text-[20px]">block</span></button>
@endif
@if ($canUpload && $lich->trang_thai !== 'da_duyet')
<form method="POST" action="/{{ $coSo->slug }}/lich-lam-viec/{{ $lich->id }}" onsubmit="return confirm('Xóa bản lịch tháng {{ $lich->thang->format('m/Y') }}?')">@csrf @method('DELETE')
<button type="submit" title="Xóa" class="p-2 rounded-lg text-on-surface-variant hover:bg-red-50 hover:text-red-600 transition-colors"><span class="material-symbols-outlined text-[20px]">delete</span></button>
</form>
@endif
</div>
</td>
</tr>
@empty
<tr><td colspan="6" class="px-4 py-16 text-center text-on-surface-variant">
<span class="material-symbols-outlined text-[48px] text-outline-variant block mb-2">event_busy</span>
Chưa có lịch làm việc nào. @if ($canUpload) Bấm <b>Upload lịch</b> để bắt đầu. @endif
</td></tr>
@endforelse
</tbody>
</table>
</div>
</div>

</div>
</main>

@if ($canUpload)
<!-- Modal Upload -->
<div id="upload-modal" class="hidden fixed inset-0 z-[70] bg-black/40 items-center justify-center p-4" onclick="if(event.target===this){this.classList.add('hidden');this.classList.remove('flex')}">
<div class="bg-surface-container-lowest rounded-2xl shadow-xl w-full max-w-md p-6">
<div class="flex items-center justify-between mb-4">
<h3 class="text-headline-md font-bold text-on-surface">Upload lịch làm việc</h3>
<button onclick="document.getElementById('upload-modal').classList.add('hidden');document.getElementById('upload-modal').classList.remove('flex')" class="p-1.5 rounded-lg text-on-surface-variant hover:bg-surface-container-high"><span class="material-symbols-outlined">close</span></button>
</div>
<form method="POST" action="/{{ $coSo->slug }}/lich-lam-viec/preview" enctype="multipart/form-data" class="space-y-4" onsubmit="document.getElementById('llv-loading').classList.remove('hidden');document.getElementById('llv-loading').classList.add('flex')">
@csrf
<div>
<label class="text-label-caps font-label-caps text-on-surface-variant block mb-1.5">THÁNG ÁP DỤNG <span class="text-error">*</span></label>
<input name="thang" type="month" required value="{{ now()->addMonth()->format('Y-m') }}" class="w-full border border-outline-variant rounded-lg px-3 py-2 text-body-md focus:border-secondary focus:ring-1 focus:ring-secondary/20 outline-none bg-surface"/>
</div>
<div>
<label class="text-label-caps font-label-caps text-on-surface-variant block mb-1.5">FILE EXCEL (.xlsx) <span class="text-error">*</span></label>
<input name="file" type="file" accept=".xlsx,.xls" required class="w-full text-body-sm file:mr-3 file:px-3 file:py-2 file:rounded-lg file:border-0 file:bg-secondary-container/40 file:text-on-secondary-container file:font-semibold file:cursor-pointer"/>
<p class="text-[11px] text-on-surface-variant mt-1">Dùng đúng file <b>Tải mẫu</b> (lưới phòng × ca × ngày — điền <b>username</b> bác sĩ/KTV). Sau khi tải lên sẽ có bước <b>xem trước</b> rồi mới lưu.</p>
</div>
<div>
<label class="text-label-caps font-label-caps text-on-surface-variant block mb-1.5">GHI CHÚ</label>
<textarea name="ghi_chu" rows="2" class="w-full border border-outline-variant rounded-lg px-3 py-2 text-body-md focus:border-secondary focus:ring-1 focus:ring-secondary/20 outline-none bg-surface" placeholder="Tuỳ chọn"></textarea>
</div>
<div class="flex justify-end gap-2 pt-2">
<button type="button" onclick="document.getElementById('upload-modal').classList.add('hidden');document.getElementById('upload-modal').classList.remove('flex')" class="px-4 py-2 rounded-lg text-body-md font-semibold text-on-surface-variant hover:bg-surface-container-high">Hủy</button>
<button type="submit" class="px-4 py-2 rounded-lg bg-primary text-on-primary text-body-md font-semibold hover:opacity-90">Tải lên</button>
</div>
</form>
</div>
</div>
@endif

@if ($canDuyet)
<!-- Modal Từ chối -->
<div id="reject-modal" class="hidden fixed inset-0 z-[70] bg-black/40 items-center justify-center p-4" onclick="if(event.target===this){closeReject()}">
<div class="bg-surface-container-lowest rounded-2xl shadow-xl w-full max-w-md p-6">
<h3 class="text-headline-md font-bold text-on-surface mb-1">Từ chối lịch làm việc</h3>
<p class="text-body-sm text-on-surface-variant mb-4">Tháng <b id="reject-thang"></b></p>
<form id="reject-form" method="POST">@csrf @method('PATCH')
<textarea name="ly_do_tu_choi" rows="3" required class="w-full border border-outline-variant rounded-lg px-3 py-2 text-body-md focus:border-secondary focus:ring-1 focus:ring-secondary/20 outline-none bg-surface" placeholder="Lý do từ chối..."></textarea>
<div class="flex justify-end gap-2 pt-4">
<button type="button" onclick="closeReject()" class="px-4 py-2 rounded-lg text-body-md font-semibold text-on-surface-variant hover:bg-surface-container-high">Hủy</button>
<button type="submit" class="px-4 py-2 rounded-lg bg-error text-on-error text-body-md font-semibold hover:opacity-90">Từ chối</button>
</div>
</form>
</div>
</div>
<script>
function openReject(id, thang) {
    const m = document.getElementById('reject-modal');
    document.getElementById('reject-form').action = '/{{ $coSo->slug }}/lich-lam-viec/' + id + '/tu-choi';
    document.getElementById('reject-thang').textContent = thang;
    m.classList.remove('hidden'); m.classList.add('flex');
}
function closeReject() {
    const m = document.getElementById('reject-modal');
    m.classList.add('hidden'); m.classList.remove('flex');
}
</script>
@endif

<!-- Overlay: đang đọc dữ liệu tải lên -->
<div id="llv-loading" class="hidden fixed inset-0 z-[80] bg-black/50 items-center justify-center">
<div class="bg-surface-container-lowest rounded-2xl shadow-xl px-8 py-7 flex flex-col items-center gap-3">
<svg class="animate-spin h-9 w-9 text-secondary" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>
<div class="text-body-md font-semibold text-on-surface">Đang đọc dữ liệu tải lên…</div>
<div class="text-body-sm text-on-surface-variant">Vui lòng đợi giây lát</div>
</div>
</div>
</body></html>
