<!DOCTYPE html>
<html class="light" lang="vi"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Chi tiết lịch trực {{ $lich->thang->format('m/Y') }} | Longevity Booking</title>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&amp;family=Inter:wght@400;500;600&amp;family=JetBrains+Mono:wght@500;600&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<script>tailwind.config={darkMode:"class",theme:{extend:{"colors":{"on-tertiary-fixed":"#002113","surface-bright":"#f7f9fb","on-surface":"#191c1e","on-secondary":"#ffffff","inverse-primary":"#bec6e0","tertiary-fixed-dim":"#4edea3","secondary":"#006591","tertiary-fixed":"#6ffbbe","primary-container":"#131b2e","on-tertiary-container":"#009668","on-tertiary-fixed-variant":"#005236","on-primary-fixed":"#131b2e","on-surface-variant":"#45464d","on-primary-fixed-variant":"#3f465c","inverse-on-surface":"#eff1f3","surface-container-lowest":"#ffffff","on-secondary-container":"#004666","surface-container-low":"#f2f4f6","surface-container-highest":"#e0e3e5","on-primary-container":"#7c839b","on-secondary-fixed":"#001e2f","surface":"#f7f9fb","on-primary":"#ffffff","error-container":"#ffdad6","surface-variant":"#e0e3e5","on-tertiary":"#ffffff","on-error":"#ffffff","surface-tint":"#565e74","surface-dim":"#d8dadc","inverse-surface":"#2d3133","outline-variant":"#c6c6cd","secondary-fixed":"#c9e6ff","on-secondary-fixed-variant":"#004c6e","tertiary":"#000000","surface-container":"#eceef0","outline":"#76777d","secondary-container":"#39b8fd","background":"#f7f9fb","primary-fixed":"#dae2fd","secondary-fixed-dim":"#89ceff","on-background":"#191c1e","tertiary-container":"#002113","primary":"#000000","surface-container-high":"#e6e8ea","primary-fixed-dim":"#bec6e0","error":"#ba1a1a","on-error-container":"#93000a"},"borderRadius":{"DEFAULT":"0.125rem","lg":"0.25rem","xl":"0.5rem","full":"0.75rem"},"spacing":{"container-margin":"24px"},"fontFamily":{"headline-md":["Manrope"],"time-slot":["JetBrains Mono"],"body-md":["Inter"],"body-sm":["Inter"],"headline-lg":["Manrope"],"label-caps":["JetBrains Mono"]},"fontSize":{"headline-md":["18px",{"lineHeight":"24px","fontWeight":"600"}],"time-slot":["12px",{"lineHeight":"16px","fontWeight":"500"}],"body-md":["14px",{"lineHeight":"20px","fontWeight":"400"}],"body-sm":["13px",{"lineHeight":"18px","fontWeight":"400"}],"headline-lg":["24px",{"lineHeight":"32px","fontWeight":"700"}],"label-caps":["11px",{"lineHeight":"16px","letterSpacing":"0.05em","fontWeight":"600"}]}}}}</script>
<style>
body { font-family: 'Inter', sans-serif; background-color: #f7f9fb; }
.material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; vertical-align: middle; }
.gcell { min-width: 56px; }
.gsticky { position: sticky; left: 0; background: #fff; z-index: 5; }
.gsticky2 { position: sticky; left: 150px; background: #fff; z-index: 5; }
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
    [$bLabel, $bClass] = $badges[$lich->trang_thai] ?? ['?', ''];
    $caMeta = \App\Models\LichLamViec::CA;
    $days = $grid['days'];
    $wd = ['T2','T3','T4','T5','T6','T7','CN'];
    $ym = $lich->thang->format('Y-m');
@endphp
@if (session('ok'))
<div class="fixed top-20 left-1/2 -translate-x-1/2 z-[60] px-5 py-3 rounded-xl bg-tertiary-fixed-dim/90 text-on-tertiary-container shadow-lg flex items-center gap-2 text-body-md font-semibold" id="flash-ok"><span class="material-symbols-outlined">check_circle</span> {{ session('ok') }}</div>
<script>setTimeout(()=>document.getElementById('flash-ok')?.remove(), 4000);</script>
@endif
@if (session('err'))
<div class="fixed top-20 left-1/2 -translate-x-1/2 z-[60] px-5 py-3 rounded-xl bg-error/90 text-on-error shadow-lg flex items-center gap-2 text-body-md font-semibold" id="flash-err"><span class="material-symbols-outlined">error</span> {{ session('err') }}</div>
<script>setTimeout(()=>document.getElementById('flash-err')?.remove(), 5000);</script>
@endif
@include('partials.topnav', ['active' => 'lich-lam-viec'])
<main class="pt-24 pb-20 px-container-margin">
<div class="max-w-[1600px] mx-auto">

<a href="/{{ $coSo->slug }}/lich-lam-viec" class="inline-flex items-center gap-1 text-body-sm text-secondary hover:underline mb-4"><span class="material-symbols-outlined text-[18px]">arrow_back</span> Danh sách lịch làm việc</a>

<div class="bg-surface-container-lowest rounded-2xl border border-outline-variant shadow-sm p-6 mb-6">
<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
<div>
<div class="flex items-center gap-3">
<h2 class="text-headline-lg font-extrabold text-on-surface">Lịch trực {{ $lich->thang->format('m/Y') }}</h2>
<span class="inline-flex px-2.5 py-1 rounded-full text-body-sm font-semibold {{ $bClass }}">{{ $bLabel }}</span>
</div>
<div class="text-body-sm text-on-surface-variant mt-2 space-y-0.5">
<div>Người tạo: <b class="text-on-surface">{{ $lich->nguoiTao?->name ?? '—' }}</b> · {{ count($lich->chiTiets) }} ca trực</div>
@if ($lich->nguoiDuyet)<div>Người duyệt: <b class="text-on-surface">{{ $lich->nguoiDuyet->name }}</b>@if ($lich->applied_at) · áp dụng {{ $lich->applied_at->format('d/m/Y H:i') }}@endif</div>@endif
@if ($lich->ghi_chu)<div>Ghi chú: {{ $lich->ghi_chu }}</div>@endif
</div>
</div>
<div class="flex items-center gap-2 shrink-0">
@if ($canUpload && in_array($lich->trang_thai, ['nhap', 'tu_choi']))
<form method="POST" action="/{{ $coSo->slug }}/lich-lam-viec/{{ $lich->id }}/gui-duyet">@csrf
<button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-secondary text-on-secondary text-body-md font-semibold hover:opacity-90"><span class="material-symbols-outlined text-[20px]">send</span> Gửi duyệt</button>
</form>
@endif
@if ($canDuyet && $lich->trang_thai === 'cho_duyet')
<form method="POST" action="/{{ $coSo->slug }}/lich-lam-viec/{{ $lich->id }}/duyet" onsubmit="return confirm('Duyệt &amp; áp dụng lịch trực tháng {{ $lich->thang->format('m/Y') }}?')">@csrf @method('PATCH')
<button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-emerald-600 text-white text-body-md font-semibold hover:opacity-90"><span class="material-symbols-outlined text-[20px]">verified</span> Duyệt &amp; áp dụng</button>
</form>
<button onclick="document.getElementById('reject-modal').classList.remove('hidden');document.getElementById('reject-modal').classList.add('flex')" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg border border-red-300 text-red-600 text-body-md font-semibold hover:bg-red-50"><span class="material-symbols-outlined text-[20px]">block</span> Từ chối</button>
@endif
</div>
</div>
@if ($lich->trang_thai === 'tu_choi' && $lich->ly_do_tu_choi)
<div class="mt-4 p-3 rounded-lg bg-red-50 border border-red-200 text-body-sm text-red-700"><b>Lý do từ chối:</b> {{ $lich->ly_do_tu_choi }}</div>
@endif
</div>

@foreach (['bac_si' => ['Bác sĩ', 'stethoscope'], 'ktv' => ['KTV', 'medical_services']] as $loai => $meta)
<div class="bg-surface-container-lowest rounded-2xl border border-outline-variant shadow-sm p-4 mb-5">
<h3 class="font-headline-md text-on-surface mb-3 flex items-center gap-2"><span class="material-symbols-outlined text-secondary">{{ $meta[1] }}</span> {{ $meta[0] }} <span class="text-body-sm text-on-surface-variant font-normal">— Sáng {{ $caMeta['sang']['bd'] }}–{{ $caMeta['sang']['kt'] }}, Chiều {{ $caMeta['chieu']['bd'] }}–{{ $caMeta['chieu']['kt'] }}</span></h3>
@if (!empty($grid['sheets'][$loai]))
<div class="overflow-x-auto border border-outline-variant rounded-lg">
<table class="text-left border-collapse">
<thead>
<tr class="bg-surface-container-low text-label-caps font-label-caps text-on-surface-variant">
<th class="gsticky px-3 py-2 border-b border-r border-outline-variant w-[150px]">Phòng</th>
<th class="gsticky2 px-2 py-2 border-b border-r border-outline-variant w-[70px]">Ca</th>
@foreach ($days as $d)
@php $dow = (int) date('N', strtotime(sprintf('%s-%02d', $ym, $d))); @endphp
<th class="gcell px-1 py-1 text-center border-b border-outline-variant {{ $dow >= 6 ? 'bg-emerald-50' : '' }}"><div>{{ $d }}</div><div class="text-[9px] font-normal">{{ $wd[$dow-1] }}</div></th>
@endforeach
</tr>
</thead>
<tbody>
@foreach ($grid['sheets'][$loai] as $phong)
@foreach (['sang' => 'Sáng', 'chieu' => 'Chiều'] as $ck => $cl)
<tr class="hover:bg-surface-container-low/40">
@if ($ck === 'sang')<td rowspan="2" class="gsticky px-3 py-2 border-b border-r border-outline-variant align-middle font-semibold text-body-sm text-on-surface">{{ $phong['ten'] }}</td>@endif
<td class="gsticky2 px-2 py-1.5 border-b border-r border-outline-variant text-body-sm {{ $ck === 'sang' ? 'text-amber-700' : 'text-secondary' }}">{{ $cl }}</td>
@foreach ($days as $d)
@php $cell = $phong[$ck][$d] ?? null; $dow = (int) date('N', strtotime(sprintf('%s-%02d', $ym, $d))); @endphp
<td class="gcell px-1 py-1 text-center border-b border-outline-variant/60 {{ $dow >= 6 ? 'bg-emerald-50/40' : '' }}">
@if ($cell)<span class="inline-block px-1 py-0.5 rounded bg-emerald-100 text-emerald-800 text-[10px] font-semibold leading-tight" title="{{ $cell['name'] }}">{{ \Illuminate\Support\Str::limit($cell['name'], 10, '…') }}</span>@endif
</td>
@endforeach
</tr>
@endforeach
@endforeach
</tbody>
</table>
</div>
@else
<p class="text-body-sm text-on-surface-variant">Chưa phân công.</p>
@endif
</div>
@endforeach

</div>
</main>

@if ($canDuyet && $lich->trang_thai === 'cho_duyet')
<div id="reject-modal" class="hidden fixed inset-0 z-[70] bg-black/40 items-center justify-center p-4" onclick="if(event.target===this){this.classList.add('hidden');this.classList.remove('flex')}">
<div class="bg-surface-container-lowest rounded-2xl shadow-xl w-full max-w-md p-6">
<h3 class="text-headline-md font-bold text-on-surface mb-1">Từ chối lịch làm việc</h3>
<p class="text-body-sm text-on-surface-variant mb-4">Tháng <b>{{ $lich->thang->format('m/Y') }}</b></p>
<form method="POST" action="/{{ $coSo->slug }}/lich-lam-viec/{{ $lich->id }}/tu-choi">@csrf @method('PATCH')
<textarea name="ly_do_tu_choi" rows="3" required class="w-full border border-outline-variant rounded-lg px-3 py-2 text-body-md focus:border-secondary focus:ring-1 focus:ring-secondary/20 outline-none bg-surface" placeholder="Lý do từ chối...">{{ old('ly_do_tu_choi') }}</textarea>
<div class="flex justify-end gap-2 pt-4">
<button type="button" onclick="document.getElementById('reject-modal').classList.add('hidden');document.getElementById('reject-modal').classList.remove('flex')" class="px-4 py-2 rounded-lg text-body-md font-semibold text-on-surface-variant hover:bg-surface-container-high">Hủy</button>
<button type="submit" class="px-4 py-2 rounded-lg bg-error text-on-error text-body-md font-semibold hover:opacity-90">Từ chối</button>
</div>
</form>
</div>
</div>
@if ($errors->has('ly_do_tu_choi'))<script>document.getElementById('reject-modal').classList.remove('hidden');document.getElementById('reject-modal').classList.add('flex');</script>@endif
@endif
</body></html>
