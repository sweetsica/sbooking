<!DOCTYPE html>
<html class="light" lang="vi"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Đặt Lịch Tư Vấn - Longevity Booking</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&amp;family=Manrope:wght@600;700;800&amp;family=JetBrains+Mono:wght@500;600&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script>tailwind.config={darkMode:"class",theme:{extend:{"colors":{"on-primary-fixed":"#131b2e","on-secondary-fixed-variant":"#004c6e","primary-fixed-dim":"#bec6e0","inverse-on-surface":"#eff1f3","secondary-fixed-dim":"#89ceff","tertiary-fixed":"#6ffbbe","secondary-container":"#39b8fd","primary-fixed":"#dae2fd","surface-container-low":"#f2f4f6","outline-variant":"#c6c6cd","on-error-container":"#93000a","outline":"#76777d","on-tertiary-container":"#009668","inverse-primary":"#bec6e0","on-background":"#191c1e","surface-container-lowest":"#ffffff","primary":"#000000","on-surface-variant":"#45464d","on-secondary-fixed":"#001e2f","surface":"#f7f9fb","error":"#ba1a1a","tertiary-container":"#002113","on-primary-container":"#7c839b","surface-container-high":"#e6e8ea","surface-bright":"#f7f9fb","on-primary":"#ffffff","on-secondary-container":"#004666","primary-container":"#131b2e","tertiary":"#000000","surface-tint":"#565e74","surface-dim":"#d8dadc","on-error":"#ffffff","on-tertiary":"#ffffff","error-container":"#ffdad6","background":"#f7f9fb","inverse-surface":"#2d3133","on-surface":"#191c1e","on-primary-fixed-variant":"#3f465c","tertiary-fixed-dim":"#4edea3","surface-container-highest":"#e0e3e5","secondary-fixed":"#c9e6ff","on-tertiary-fixed-variant":"#005236","on-tertiary-fixed":"#002113","on-secondary":"#ffffff","surface-variant":"#e0e3e5","secondary":"#006591","surface-container":"#eceef0"},"borderRadius":{"DEFAULT":"0.125rem","lg":"0.25rem","xl":"0.5rem","full":"0.75rem"},"spacing":{"gutter":"12px","container-margin":"24px","row-height-compact":"40px","unit":"4px","row-height-standard":"56px"},"fontFamily":{"headline-md":["Manrope"],"time-slot":["JetBrains Mono"],"headline-lg":["Manrope"],"body-md":["Inter"],"body-sm":["Inter"],"label-caps":["JetBrains Mono"]},"fontSize":{"headline-md":["18px",{"lineHeight":"24px","fontWeight":"600"}],"time-slot":["12px",{"lineHeight":"16px","fontWeight":"500"}],"headline-lg":["24px",{"lineHeight":"32px","fontWeight":"700"}],"body-md":["14px",{"lineHeight":"20px","fontWeight":"400"}],"body-sm":["13px",{"lineHeight":"18px","fontWeight":"400"}],"label-caps":["11px",{"lineHeight":"16px","letterSpacing":"0.05em","fontWeight":"600"}]}}}}</script>
<style>
.material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
.form-input-focus:focus { outline: none; border-color: #006591; box-shadow: 0 0 0 1px #006591; }
body { background-color: #f7f9fb; }
</style>
</head>
<body class="font-body-md text-on-surface">
@php $lh = $lh ?? null; $editing = (bool) $lh; @endphp
@include('partials.topnav', ['active' => 'tu-van'])
<main class="pt-16 min-h-screen">
<div class="p-container-margin max-w-5xl mx-auto">
<div class="flex items-center gap-4 py-6">
<a href="{{ $editing ? '/'.$coSo->slug.'/ds-tu-van' : '/'.$coSo->slug.'/lich-tu-van' }}" class="p-2 hover:bg-surface-container-low rounded-full transition-all">
<span class="material-symbols-outlined">arrow_back</span>
</a>
<h2 class="text-headline-md font-headline-md font-extrabold text-on-surface">{{ $editing ? 'Sửa Lịch Tư Vấn' : 'Đặt Lịch Tư Vấn' }}</h2>
<span class="ml-1 px-3 py-1 rounded-full bg-secondary-container/40 text-on-secondary-container text-body-sm font-semibold">{{ $coSo->ten }}</span>
</div>

<div class="mb-8 relative rounded-xl overflow-hidden h-40">
<div class="absolute inset-0 bg-primary-container z-0"></div>
<div class="relative z-10 p-8 flex flex-col justify-end h-full">
<h3 class="text-headline-lg font-headline-lg text-on-primary">{{ $editing ? 'Cập Nhật Lịch Tư Vấn' : 'Đăng Ký Tư Vấn' }}</h3>
<p class="text-body-md text-on-primary-container opacity-90 max-w-lg">Nhập thông tin khách hàng và chọn bác sĩ tư vấn, ca khám phù hợp.</p>
</div>
</div>

@if ($errors->any())
<div class="mb-6 p-4 rounded-xl bg-error-container border border-error/30 text-on-error-container">
<p class="font-semibold mb-1 flex items-center gap-2"><span class="material-symbols-outlined">error</span> Vui lòng kiểm tra lại:</p>
<ul class="list-disc list-inside text-body-sm">
@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
</ul>
</div>
@endif

<div class="bg-surface-container-lowest border border-outline-variant rounded-xl shadow-sm overflow-hidden mb-12">
<form class="p-8" id="lichhen-form" method="POST" action="{{ $editing ? '/'.$coSo->slug.'/sua-tu-van/'.$lh->id : '/'.$coSo->slug.'/dat-kham' }}">
@csrf
@if ($editing) @method('PUT') @endif
<div class="mb-10 grid grid-cols-1 md:grid-cols-2 gap-8">
<div>
<label class="block text-body-sm font-semibold text-on-surface-variant mb-1.5">Dấu thời gian</label>
<input class="w-full px-4 py-2.5 bg-surface-container-low border border-outline-variant rounded-lg text-body-md text-on-surface-variant cursor-not-allowed" readonly type="text" value="{{ now()->format('d/m/Y - h:i:s') }} ({{ now()->hour < 12 ? 'sáng' : 'tối' }})"/>
</div>
<div>
<label class="block text-body-sm font-semibold text-on-surface-variant mb-1.5">Nguồn</label>
<select name="nguon" class="w-full px-4 py-2.5 bg-surface border border-outline rounded-lg form-input-focus transition-all text-body-md">
@foreach (['MKT — Marketing','MKT BR — Marketing BR','BDM','BOD — Ban lãnh đạo giới thiệu','SA — Sale Appointment','BA — Booking Appointment','WI — Walk-in'] as $ng)
<option @selected(old('nguon')===$ng)>{{ $ng }}</option>
@endforeach
</select>
</div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-10">
<!-- Thông tin khách hàng -->
<div class="space-y-6">
<div class="flex items-center gap-2 pb-2 border-b border-outline-variant">
<span class="material-symbols-outlined text-secondary">person</span>
<h3 class="text-headline-md font-headline-md">Thông tin Khách hàng</h3>
</div>
<div class="space-y-4">
<div>
<label class="block text-body-sm font-semibold text-on-surface-variant mb-1.5">Họ tên KH <span class="text-error">*</span></label>
<input name="ho_ten" value="{{ old('ho_ten', $lh?->khachHang?->ho_ten) }}" required class="w-full px-4 py-2.5 bg-surface border border-outline rounded-lg form-input-focus transition-all text-body-md" placeholder="Nhập họ và tên..." type="text"/>
</div>
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
<div>
<label class="block text-body-sm font-semibold text-on-surface-variant mb-1.5">Số điện thoại <span class="text-error">*</span></label>
<input id="sdt" name="so_dien_thoai" value="{{ old('so_dien_thoai', $lh?->khachHang?->so_dien_thoai) }}" required class="w-full px-4 py-2.5 bg-surface border border-outline rounded-lg form-input-focus transition-all text-body-md font-time-slot" placeholder="0xxx xxx xxx" type="tel"/>
<p id="sdt-msg" class="hidden mt-1 text-body-sm text-secondary font-medium"></p>
</div>
<div>
<label class="block text-body-sm font-semibold text-on-surface-variant mb-1.5">Địa chỉ Email</label>
<input name="email" value="{{ old('email', $lh?->khachHang?->email) }}" class="w-full px-4 py-2.5 bg-surface border border-outline rounded-lg form-input-focus transition-all text-body-md" placeholder="email (tuỳ chọn)" type="email"/>
</div>
</div>
</div>
</div>

<!-- Lịch trình & Bác sĩ -->
<div class="space-y-6">
<div class="flex items-center gap-2 pb-2 border-b border-outline-variant">
<span class="material-symbols-outlined text-secondary">calendar_month</span>
<h3 class="text-headline-md font-headline-md">Lịch trình & Bác sĩ <span class="text-error">*</span></h3>
</div>
<div class="space-y-4">
<div>
<label class="block text-body-sm font-semibold text-on-surface-variant mb-1.5">Ngày hẹn <span class="text-error">*</span></label>
<input id="ngay_hen" name="ngay_hen" value="{{ old('ngay_hen', $lh ? $lh->ngay_hen->toDateString() : now()->toDateString()) }}" required class="w-full px-4 py-2.5 bg-surface border border-outline rounded-lg form-input-focus transition-all text-body-md" type="date"/>
</div>
<div>
<label class="block text-body-sm font-semibold text-on-surface-variant mb-1.5">Bác sĩ tư vấn <span class="text-error">*</span></label>
<select id="bac_si" name="bac_si_id" required class="w-full px-4 py-2.5 bg-surface border border-outline rounded-lg form-input-focus transition-all text-body-md">
@foreach ($bacSis as $bs)
<option value="{{ $bs->id }}" @selected(old('bac_si_id', $lh?->bac_si_id)==$bs->id)>{{ $bs->ten_day_du }} ({{ $bs->phut_tu_van }}p/ca)</option>
@endforeach
</select>
</div>
<div>
<label class="block text-body-sm font-semibold text-on-surface-variant mb-1.5">Ca khám <span class="text-error">*</span></label>
<select id="ca_kham" name="ca_kham_id" required data-old="{{ old('ca_kham_id', $lh?->ca_kham_id) }}" class="w-full px-4 py-2.5 bg-surface border border-outline rounded-lg form-input-focus transition-all text-body-md font-time-slot"></select>
</div>
</div>
</div>

<!-- Sale phụ trách -->
<div class="space-y-6">
<div class="flex items-center gap-2 pb-2 border-b border-outline-variant">
<span class="material-symbols-outlined text-secondary">support_agent</span>
<h3 class="text-headline-md font-headline-md">Sale phụ trách <span class="text-error">*</span></h3>
</div>
<div>
<select name="sale_id" required class="w-full px-4 py-2.5 bg-surface border border-outline rounded-lg form-input-focus transition-all text-body-md">
<option value="">-- Chọn sale --</option>
@foreach ($sales as $s)
<option value="{{ $s->id }}" @selected(old('sale_id', $lh?->sale_id)==$s->id)>{{ $s->name }}</option>
@endforeach
</select>
</div>
</div>

<!-- Ghi chú -->
<div class="space-y-6">
<div class="flex items-center gap-2 pb-2 border-b border-outline-variant">
<span class="material-symbols-outlined text-secondary">notes</span>
<h3 class="text-headline-md font-headline-md">Ghi chú</h3>
</div>
<div>
<textarea name="ghi_chu" rows="3" class="w-full px-4 py-2.5 bg-surface border border-outline rounded-lg form-input-focus transition-all text-body-md" placeholder="Ghi chú thêm (tuỳ chọn)...">{{ old('ghi_chu', $lh?->ghi_chu) }}</textarea>
</div>
</div>
</div>

<div class="mt-10 pt-8 border-t border-outline-variant flex justify-end">
<button type="submit" class="px-8 py-3 bg-primary text-on-primary rounded-xl font-semibold text-body-md flex items-center gap-2 hover:opacity-90 active:scale-95 transition-all">
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">{{ $editing ? 'save' : 'add_task' }}</span>
{{ $editing ? 'Lưu thay đổi' : 'Đặt Lịch Tư Vấn' }}
</button>
</div>
</form>
</div>
</div>
</main>

<script>
(function () {
    const slug = '{{ $coSo->slug }}';
    const editId = {{ $editing ? $lh->id : 'null' }};
    const bacSi = document.getElementById('bac_si');
    const caKham = document.getElementById('ca_kham');
    const ngay = document.getElementById('ngay_hen');
    const oldCaKham = caKham.getAttribute('data-old');

    async function loadCaKham() {
        let data = { slots: [] };
        if (bacSi && bacSi.value) {
            try {
                const r = await fetch(`/${slug}/dat-kham/ca-kham?bac_si_id=${bacSi.value}&ngay=${encodeURIComponent(ngay ? ngay.value : '')}${editId ? `&except=${editId}` : ''}`);
                data = await r.json();
            } catch (e) {}
        }
        const slots = data.slots || [];
        caKham.innerHTML = slots.length
            ? slots.map(s => `<option value="${s.id}" ${s.full ? 'disabled' : ''}>${s.nhan}${s.full ? ' — đã đặt 🔒' : ''}</option>`).join('')
            : '<option value="">(Bác sĩ chưa cấu hình ca khám)</option>';
        if (oldCaKham) { const o = caKham.querySelector(`option[value="${oldCaKham}"]`); if (o && !o.disabled) caKham.value = oldCaKham; }
        if (caKham.selectedOptions[0] && caKham.selectedOptions[0].disabled) {
            const avail = caKham.querySelector('option:not([disabled])');
            caKham.value = avail ? avail.value : '';
        }
    }

    if (bacSi) {
        bacSi.addEventListener('change', loadCaKham);
        if (ngay) ngay.addEventListener('change', loadCaKham);
        loadCaKham();
    }

    const sdt = document.getElementById('sdt');
    const sdtMsg = document.getElementById('sdt-msg');
    let timer;
    if (sdt) sdt.addEventListener('input', () => {
        clearTimeout(timer);
        const v = sdt.value.replace(/\s+/g, '');
        if (v.length < 6) { sdtMsg.classList.add('hidden'); return; }
        timer = setTimeout(async () => {
            try {
                const r = await fetch(`/${slug}/dat-kham/check-sdt?sdt=${encodeURIComponent(v)}`);
                const j = await r.json();
                if (j.ton_tai) {
                    sdtMsg.textContent = '*đã tồn tại số điện thoại' + (j.ho_ten ? ' (' + j.ho_ten + ')' : '');
                    sdtMsg.classList.remove('hidden');
                } else { sdtMsg.classList.add('hidden'); }
            } catch (e) {}
        }, 350);
    });
})();
</script>
@include('partials.datepicker')
</body></html>
