<!DOCTYPE html>
<html class="light" lang="vi"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Xem trước lịch làm việc | Longevity Booking</title>
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
    $caMeta = \App\Models\LichLamViec::CA;
    $days = $parsed['days'];
    $wd = ['T2','T3','T4','T5','T6','T7','CN'];
@endphp
@include('partials.topnav', ['active' => 'lich-lam-viec'])
<main class="pt-24 pb-32 px-container-margin">
<div class="max-w-[1600px] mx-auto">

<div class="mb-4">
<a href="/{{ $coSo->slug }}/lich-lam-viec" class="inline-flex items-center gap-1 text-body-sm text-secondary hover:underline mb-2"><span class="material-symbols-outlined text-[18px]">arrow_back</span> Hủy &amp; quay lại</a>
<div class="flex items-center gap-3">
<h2 class="text-headline-lg font-extrabold text-on-surface">Xem trước — Lịch trực {{ date('m/Y', strtotime($thang)) }}</h2>
@if ($daCo)<span class="inline-flex px-2.5 py-1 rounded-full text-body-sm font-semibold bg-amber-100 text-amber-700">Sẽ ghi đè bản tháng này</span>@endif
</div>
<p class="text-body-sm text-on-surface-variant mt-1">Ca: <b>Sáng {{ $caMeta['sang']['bd'] }}–{{ $caMeta['sang']['kt'] }}</b>, <b>Chiều {{ $caMeta['chieu']['bd'] }}–{{ $caMeta['chieu']['kt'] }}</b>. Ô <span class="text-emerald-700 font-semibold">xanh</span> = đã khớp bác sĩ/KTV; ô <span class="text-red-600 font-semibold">đỏ</span> = chưa khớp (khớp lại bên dưới); ô trống = <b>đóng phòng</b> ca đó.</p>
</div>

<form method="POST" action="/{{ $coSo->slug }}/lich-lam-viec">
@csrf
<input type="hidden" name="thang" value="{{ $thangYm }}"/>
<input type="hidden" name="file_goc" value="{{ $filePath }}"/>

@if (!empty($parsed['unmatched']))
<div class="mb-5 p-4 rounded-xl bg-amber-50 border border-amber-200">
<div class="flex items-center gap-2 text-amber-800 font-semibold text-body-md mb-2"><span class="material-symbols-outlined text-[20px]">warning</span> {{ count($parsed['unmatched']) }} giá trị chưa khớp được tài khoản — chọn đúng người (hoặc để trống = bỏ qua)</div>
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
@foreach ($parsed['unmatched'] as $raw => $cnt)
<div class="flex items-center gap-2">
<code class="px-2 py-1 rounded bg-white border border-amber-300 text-body-sm text-red-600 shrink-0 max-w-[120px] truncate" title="{{ $raw }}">{{ $raw }}</code>
<span class="material-symbols-outlined text-amber-500 text-[18px]">arrow_forward</span>
<select name="map[{{ $raw }}]" class="flex-1 min-w-0 border border-outline-variant rounded-lg px-2 py-1.5 text-body-sm bg-surface focus:border-secondary focus:ring-1 focus:ring-secondary/20 outline-none">
<option value="">— Bỏ qua —</option>
<optgroup label="Bác sĩ">@foreach ($dsNguoi['bac_si'] as $id => $ten)<option value="{{ $id }}">{{ $ten }}</option>@endforeach</optgroup>
<optgroup label="KTV">@foreach ($dsNguoi['ktv'] as $id => $ten)<option value="{{ $id }}">{{ $ten }}</option>@endforeach</optgroup>
</select>
</div>
@endforeach
</div>
</div>
@endif

@foreach (['bac_si' => ['Bác sĩ', 'stethoscope'], 'ktv' => ['KTV', 'medical_services']] as $loai => $meta)
<div class="bg-surface-container-lowest rounded-2xl border border-outline-variant shadow-sm p-4 mb-5">
<h3 class="font-headline-md text-on-surface mb-3 flex items-center gap-2"><span class="material-symbols-outlined text-secondary">{{ $meta[1] }}</span> {{ $meta[0] }}</h3>
@if (!empty($parsed['sheets'][$loai]))
<div class="overflow-x-auto border border-outline-variant rounded-lg">
<table class="text-left border-collapse">
<thead>
<tr class="bg-surface-container-low text-label-caps font-label-caps text-on-surface-variant">
<th class="gsticky px-3 py-2 border-b border-r border-outline-variant w-[150px]">Phòng</th>
<th class="gsticky2 px-2 py-2 border-b border-r border-outline-variant w-[70px]">Ca</th>
@foreach ($days as $d)
@php $dow = (int) date('N', strtotime(sprintf('%s-%02d', substr($thang,0,7), $d))); @endphp
<th class="gcell px-1 py-1 text-center border-b border-outline-variant {{ $dow >= 6 ? 'bg-emerald-50' : '' }}"><div>{{ $d }}</div><div class="text-[9px] font-normal">{{ $wd[$dow-1] }}</div></th>
@endforeach
</tr>
</thead>
<tbody>
@foreach ($parsed['sheets'][$loai] as $phong)
@foreach (['sang' => 'Sáng', 'chieu' => 'Chiều'] as $ck => $cl)
<tr class="hover:bg-surface-container-low/40">
@if ($ck === 'sang')<td rowspan="2" class="gsticky px-3 py-2 border-b border-r border-outline-variant align-middle font-semibold text-body-sm text-on-surface">{{ $phong['ten'] }}</td>@endif
<td class="gsticky2 px-2 py-1.5 border-b border-r border-outline-variant text-body-sm {{ $ck === 'sang' ? 'text-amber-700' : 'text-secondary' }}">{{ $cl }}</td>
@foreach ($days as $d)
@php $cell = $phong[$ck][$d] ?? null; $dow = (int) date('N', strtotime(sprintf('%s-%02d', substr($thang,0,7), $d))); @endphp
<td class="gcell px-1 py-1 text-center border-b border-outline-variant/60 {{ $dow >= 6 ? 'bg-emerald-50/40' : '' }}">
@if ($cell && $cell['uid'])
<span class="inline-block px-1 py-0.5 rounded bg-emerald-100 text-emerald-800 text-[10px] font-semibold leading-tight" title="{{ $cell['name'] }}">{{ \Illuminate\Support\Str::limit($cell['name'], 10, '…') }}</span>
@elseif ($cell)
<span class="inline-block px-1 py-0.5 rounded bg-red-100 text-red-700 text-[10px] font-semibold leading-tight" title="Chưa khớp: {{ $cell['raw'] }}">{{ \Illuminate\Support\Str::limit($cell['raw'], 10, '…') }}</span>
@endif
</td>
@endforeach
</tr>
@endforeach
@endforeach
</tbody>
</table>
</div>
@else
<p class="text-body-sm text-on-surface-variant">Không có phòng nào khớp trong sheet này.</p>
@endif
</div>
@endforeach

<div class="bg-surface-container-lowest rounded-2xl border border-outline-variant shadow-sm p-5 mb-5">
<label class="text-label-caps font-label-caps text-on-surface-variant block mb-1.5">GHI CHÚ</label>
<textarea name="ghi_chu" rows="2" class="w-full border border-outline-variant rounded-lg px-3 py-2 text-body-md bg-surface focus:border-secondary focus:ring-1 focus:ring-secondary/20 outline-none" placeholder="Tuỳ chọn">{{ $ghiChu }}</textarea>
</div>

<div class="fixed bottom-0 left-0 right-0 bg-surface-container-lowest border-t border-outline-variant px-container-margin py-3 z-40">
<div class="max-w-[1600px] mx-auto flex items-center justify-between gap-3">
<div class="text-body-sm text-on-surface-variant">Đã khớp <b class="text-emerald-700">{{ count($parsed['assignments']) }}</b> ca trực@if(!empty($parsed['unmatched'])) · <b class="text-red-600">{{ count($parsed['unmatched']) }}</b> giá trị chưa khớp@endif</div>
<div class="flex items-center gap-3">
<a href="/{{ $coSo->slug }}/lich-lam-viec" class="px-5 py-2.5 rounded-lg text-body-md font-semibold text-on-surface-variant hover:bg-surface-container-high transition-colors">Hủy / Tải lại</a>
<button type="submit" class="inline-flex items-center gap-1.5 px-6 py-2.5 rounded-lg bg-primary text-on-primary text-body-md font-semibold hover:opacity-90 transition-opacity"><span class="material-symbols-outlined text-[20px]">check</span> Xác nhận &amp; lưu</button>
</div>
</div>
</div>

</form>
</div>
</main>
</body></html>
