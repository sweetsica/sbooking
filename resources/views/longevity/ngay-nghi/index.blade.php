<!DOCTYPE html>
<html class="light" lang="vi"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Ngày nghỉ | Longevity Booking</title>
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
    $loaiBadge = [
        'co_so'  => ['Cơ sở', 'bg-red-100 text-red-700'],
        'phong'  => ['Phòng', 'bg-amber-100 text-amber-700'],
        'bac_si' => ['Bác sĩ', 'bg-secondary-container/40 text-on-secondary-container'],
        'ktv'    => ['KTV', 'bg-tertiary-fixed-dim/40 text-on-tertiary-container'],
    ];
@endphp
@if (session('ok'))
<div class="fixed top-20 left-1/2 -translate-x-1/2 z-[60] px-5 py-3 rounded-xl bg-tertiary-fixed-dim/90 text-on-tertiary-container shadow-lg flex items-center gap-2 text-body-md font-semibold" id="flash-ok">
<span class="material-symbols-outlined">check_circle</span> {{ session('ok') }}
</div>
<script>setTimeout(()=>document.getElementById('flash-ok')?.remove(), 4000);</script>
@endif
@if ($errors->any())
<div class="fixed top-20 left-1/2 -translate-x-1/2 z-[60] px-5 py-3 rounded-xl bg-error/90 text-on-error shadow-lg text-body-md font-semibold max-w-md">
<div class="flex items-center gap-2"><span class="material-symbols-outlined">error</span> Có lỗi:</div>
<ul class="list-disc ml-7 mt-1 text-body-sm font-normal">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif
@include('partials.topnav', ['active' => 'ngay-nghi'])
<main class="pt-24 pb-20 px-container-margin">
<div class="max-w-[1100px] mx-auto">

<div class="mb-6">
<h2 class="text-headline-lg font-extrabold text-on-surface uppercase tracking-tight">Ngày nghỉ</h2>
<p class="text-body-sm text-on-surface-variant mt-1">Khai báo ngày đóng cửa / nghỉ theo khoảng ngày. <b>Cơ sở</b> và <b>Phòng</b> sẽ <b>chặn đặt lịch</b> vào ngày/ca đó; <b>Bác sĩ</b> và <b>KTV</b> chỉ <b>cảnh báo</b> khi chọn trong form đặt lịch.</p>
</div>

<!-- Form thêm ngày nghỉ -->
<div class="bg-surface-container-lowest rounded-2xl border border-outline-variant shadow-sm p-5 mb-6">
<h3 class="text-headline-md font-bold text-on-surface mb-4 flex items-center gap-2"><span class="material-symbols-outlined text-secondary">add_circle</span> Thêm ngày nghỉ</h3>
<form method="POST" action="/{{ $coSo->slug }}/ngay-nghi" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
@csrf
<div>
<label class="text-label-caps font-label-caps text-on-surface-variant block mb-1.5">CẤP ĐỘ <span class="text-error">*</span></label>
<select name="loai" id="nn_loai" required class="w-full border border-outline-variant rounded-lg px-3 py-2 text-body-md bg-surface focus:border-secondary focus:ring-1 focus:ring-secondary/20 outline-none">
@foreach (\App\Models\NgayNghi::LOAI as $k => $v)
<option value="{{ $k }}">{{ $v }}</option>
@endforeach
</select>
</div>
<div id="nn_doituong_wrap">
<label class="text-label-caps font-label-caps text-on-surface-variant block mb-1.5">ĐỐI TƯỢNG <span class="text-error">*</span></label>
<select name="doi_tuong_id" id="nn_doituong" class="w-full border border-outline-variant rounded-lg px-3 py-2 text-body-md bg-surface focus:border-secondary focus:ring-1 focus:ring-secondary/20 outline-none">
<option value="">-- Chọn --</option>
</select>
</div>
<div>
<label class="text-label-caps font-label-caps text-on-surface-variant block mb-1.5">CA <span class="text-error">*</span></label>
<select name="ca" required class="w-full border border-outline-variant rounded-lg px-3 py-2 text-body-md bg-surface focus:border-secondary focus:ring-1 focus:ring-secondary/20 outline-none">
@foreach (\App\Models\NgayNghi::CA as $k => $v)
<option value="{{ $k }}">{{ $v }}</option>
@endforeach
</select>
</div>
<div>
<label class="text-label-caps font-label-caps text-on-surface-variant block mb-1.5">TỪ NGÀY <span class="text-error">*</span></label>
<input type="date" name="tu_ngay" id="nn_tu" required value="{{ now()->format('Y-m-d') }}" class="w-full border border-outline-variant rounded-lg px-3 py-2 text-body-md bg-surface focus:border-secondary focus:ring-1 focus:ring-secondary/20 outline-none"/>
</div>
<div>
<label class="text-label-caps font-label-caps text-on-surface-variant block mb-1.5">ĐẾN NGÀY <span class="text-error">*</span></label>
<input type="date" name="den_ngay" id="nn_den" required value="{{ now()->format('Y-m-d') }}" class="w-full border border-outline-variant rounded-lg px-3 py-2 text-body-md bg-surface focus:border-secondary focus:ring-1 focus:ring-secondary/20 outline-none"/>
</div>
<div>
<label class="text-label-caps font-label-caps text-on-surface-variant block mb-1.5">LÝ DO</label>
<input type="text" name="ly_do" maxlength="255" placeholder="Tuỳ chọn (vd: nghỉ lễ 2/9)" class="w-full border border-outline-variant rounded-lg px-3 py-2 text-body-md bg-surface focus:border-secondary focus:ring-1 focus:ring-secondary/20 outline-none"/>
</div>
<div class="sm:col-span-2 lg:col-span-3">
<label class="text-label-caps font-label-caps text-on-surface-variant block mb-1.5">LẶP THEO THỨ <span class="normal-case font-normal text-on-surface-variant/70">(để trống = mọi ngày trong khoảng)</span></label>
<div class="flex flex-wrap gap-2">
@foreach (\App\Models\NgayNghi::THU as $k => $v)
<label class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-outline-variant bg-surface cursor-pointer hover:bg-surface-container-low text-body-sm select-none has-[:checked]:bg-secondary-container/40 has-[:checked]:border-secondary">
<input type="checkbox" name="thu_trong_tuan[]" value="{{ $k }}" class="rounded border-outline-variant text-secondary focus:ring-secondary/20"/> {{ $v }}
</label>
@endforeach
</div>
</div>
<div class="sm:col-span-2 lg:col-span-3 flex justify-end">
<button type="submit" class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-lg bg-primary text-on-primary text-body-md font-semibold hover:opacity-90 transition-opacity">
<span class="material-symbols-outlined text-[20px]">save</span> Lưu ngày nghỉ
</button>
</div>
</form>
</div>

<!-- Danh sách ngày nghỉ -->
<div class="bg-surface-container-lowest rounded-2xl border border-outline-variant shadow-sm overflow-hidden">
<div class="overflow-x-auto">
<table class="w-full text-left">
<thead class="bg-surface-container-low border-b border-outline-variant">
<tr class="text-label-caps font-label-caps text-on-surface-variant uppercase">
<th class="px-4 py-3">Cấp độ</th>
<th class="px-4 py-3">Đối tượng</th>
<th class="px-4 py-3">Từ ngày</th>
<th class="px-4 py-3">Đến ngày</th>
<th class="px-4 py-3">Ca</th>
<th class="px-4 py-3">Lặp thứ</th>
<th class="px-4 py-3">Lý do</th>
<th class="px-4 py-3 text-right">Hành động</th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant/60">
@forelse ($dsNghi as $nn)
@php
    [$lLabel, $lClass] = $loaiBadge[$nn->loai] ?? ['?', 'bg-surface-container-highest'];
    $tenDt = $nn->loai === 'co_so' ? '— (toàn cơ sở)'
        : ($nn->loai === 'phong' ? ($tenPhong[$nn->doi_tuong_id] ?? '#'.$nn->doi_tuong_id)
        : ($nn->loai === 'bac_si' ? ($tenBacSi[$nn->doi_tuong_id] ?? '#'.$nn->doi_tuong_id)
        : ($tenKtv[$nn->doi_tuong_id] ?? '#'.$nn->doi_tuong_id)));
@endphp
<tr class="hover:bg-surface-container-low/50 transition-colors">
<td class="px-4 py-4"><span class="inline-flex px-2.5 py-1 rounded-full text-body-sm font-semibold {{ $lClass }}">{{ $lLabel }}</span></td>
<td class="px-4 py-4 text-body-md text-on-surface">{{ $tenDt }}</td>
<td class="px-4 py-4 font-time-slot text-on-surface">{{ $nn->tu_ngay->format('d/m/Y') }}</td>
<td class="px-4 py-4 font-time-slot text-on-surface">{{ $nn->den_ngay->format('d/m/Y') }}</td>
<td class="px-4 py-4 text-body-sm text-on-surface-variant">{{ $nn->tenCa() }}</td>
<td class="px-4 py-4 text-body-sm text-on-surface-variant">{{ $nn->tenThu() ?: 'Mọi ngày' }}</td>
<td class="px-4 py-4 text-body-sm text-on-surface-variant max-w-[220px] truncate" title="{{ $nn->ly_do }}">{{ $nn->ly_do ?: '—' }}</td>
<td class="px-4 py-4">
<div class="flex items-center justify-end">
<form method="POST" action="/{{ $coSo->slug }}/ngay-nghi/{{ $nn->id }}" onsubmit="return confirm('Xóa ngày nghỉ này?')">@csrf @method('DELETE')
<button type="submit" title="Xóa" class="p-2 rounded-lg text-on-surface-variant hover:bg-red-50 hover:text-red-600 transition-colors"><span class="material-symbols-outlined text-[20px]">delete</span></button>
</form>
</div>
</td>
</tr>
@empty
<tr><td colspan="8" class="px-4 py-16 text-center text-on-surface-variant">
<span class="material-symbols-outlined text-[48px] text-outline-variant block mb-2">event_busy</span>
Chưa khai báo ngày nghỉ nào.
</td></tr>
@endforelse
</tbody>
</table>
</div>
</div>

</div>
</main>

<script>
(function () {
    const phongs = @json($phongs->map(fn ($p) => ['id' => $p->id, 'ten' => $p->ten])->values());
    const bacSis = @json($bacSis->map(fn ($u) => ['id' => $u->id, 'ten' => $u->name])->values());
    const ktvs   = @json($ktvs->map(fn ($u) => ['id' => $u->id, 'ten' => $u->name])->values());

    const loaiSel = document.getElementById('nn_loai');
    const wrap    = document.getElementById('nn_doituong_wrap');
    const dtSel   = document.getElementById('nn_doituong');
    const tu      = document.getElementById('nn_tu');
    const den     = document.getElementById('nn_den');

    function fill(list) {
        dtSel.innerHTML = '<option value="">-- Chọn --</option>' +
            list.map(o => `<option value="${o.id}">${o.ten}</option>`).join('');
    }

    function sync() {
        const v = loaiSel.value;
        if (v === 'co_so') {
            wrap.style.display = 'none';
            dtSel.required = false;
            dtSel.value = '';
        } else {
            wrap.style.display = '';
            dtSel.required = true;
            fill(v === 'phong' ? phongs : v === 'bac_si' ? bacSis : ktvs);
        }
    }

    loaiSel.addEventListener('change', sync);
    // Đồng bộ "đến ngày" tối thiểu = "từ ngày".
    tu.addEventListener('change', () => {
        den.min = tu.value;
        if (den.value < tu.value) den.value = tu.value;
    });
    sync();
    den.min = tu.value;
})();
</script>
</body></html>
