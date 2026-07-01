@php
    $f = $baoCao['filters'];
    $opt = $baoCao['options'];
    $c = $baoCao['counter'];
    $action = '/'.$coSo->slug.'/thiet-lap/bao-cao';
    $exportUrl = '/'.$coSo->slug.'/thiet-lap/bao-cao/xuat?'.http_build_query(array_filter([
        'loai' => $f['loai'] ?? null,
        'tu' => $f['tu'] ?? null,
        'den' => $f['den'] ?? null,
        'bac_si_id' => $f['bacSiId'] ?? null,
        'sale_id' => $f['saleId'] ?? null,
        'ktv_id' => $f['ktvId'] ?? null,
    ]));
@endphp

{{-- Form lọc --}}
<form method="GET" action="{{ $action }}" class="mb-4 bg-surface-container-lowest border border-outline-variant rounded-xl p-4">
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
<div class="flex flex-col gap-1">
<label class="text-label-caps font-label-caps text-on-surface-variant">Loại</label>
<select name="loai" class="px-3 py-2 bg-surface-container-low border border-outline-variant rounded-lg focus:outline-none focus:border-secondary">
<option value="all" @selected(($f['loai'] ?? 'all') === 'all')>— Tất cả —</option>
<option value="booking" @selected(($f['loai'] ?? '') === 'booking')>Chỉ đặt phòng</option>
<option value="tu_van" @selected(($f['loai'] ?? '') === 'tu_van')>Chỉ tư vấn</option>
</select>
</div>
<div class="flex flex-col gap-1">
<label class="text-label-caps font-label-caps text-on-surface-variant">Từ ngày</label>
<input type="date" name="tu" value="{{ $f['tu'] }}" class="px-3 py-2 bg-surface-container-low border border-outline-variant rounded-lg focus:outline-none focus:border-secondary"/>
</div>
<div class="flex flex-col gap-1">
<label class="text-label-caps font-label-caps text-on-surface-variant">Đến ngày</label>
<input type="date" name="den" value="{{ $f['den'] }}" class="px-3 py-2 bg-surface-container-low border border-outline-variant rounded-lg focus:outline-none focus:border-secondary"/>
</div>
<div class="flex flex-col gap-1">
<label class="text-label-caps font-label-caps text-on-surface-variant">Bác sĩ / Bác sĩ tư vấn</label>
<select name="bac_si_id" class="px-3 py-2 bg-surface-container-low border border-outline-variant rounded-lg focus:outline-none focus:border-secondary">
<option value="">— Tất cả —</option>
@foreach ($opt['bacSis'] as $u)
<option value="{{ $u->id }}" @selected(($f['bacSiId'] ?? '') == $u->id)>{{ trim(($u->chuc_danh ? $u->chuc_danh.' ' : '').$u->name) }}</option>
@endforeach
</select>
</div>
<div class="flex flex-col gap-1">
<label class="text-label-caps font-label-caps text-on-surface-variant">Nhân viên tư vấn / Sale</label>
<select name="sale_id" class="px-3 py-2 bg-surface-container-low border border-outline-variant rounded-lg focus:outline-none focus:border-secondary">
<option value="">— Tất cả —</option>
@foreach ($opt['sales'] as $u)
<option value="{{ $u->id }}" @selected(($f['saleId'] ?? '') == $u->id)>{{ $u->name }}</option>
@endforeach
</select>
</div>
<div class="flex flex-col gap-1">
<label class="text-label-caps font-label-caps text-on-surface-variant">Kỹ thuật viên (chỉ đặt phòng)</label>
<select name="ktv_id" class="px-3 py-2 bg-surface-container-low border border-outline-variant rounded-lg focus:outline-none focus:border-secondary">
<option value="">— Tất cả —</option>
@foreach ($opt['ktvs'] as $u)
<option value="{{ $u->id }}" @selected(($f['ktvId'] ?? '') == $u->id)>{{ $u->name }}</option>
@endforeach
</select>
</div>
</div>
<div class="flex items-center gap-2 mt-3">
<button type="submit" class="px-4 py-2 bg-primary text-on-primary font-semibold rounded-lg flex items-center gap-2 hover:opacity-90">
<span class="material-symbols-outlined text-[20px]">search</span> Lọc
</button>
<a href="{{ $action }}" class="px-4 py-2 text-on-surface-variant hover:bg-surface-container-high rounded-lg">Xóa lọc</a>
<a href="{{ $exportUrl }}" class="ml-auto px-4 py-2 bg-tertiary text-on-tertiary font-semibold rounded-lg flex items-center gap-2 hover:opacity-90">
<span class="material-symbols-outlined text-[20px]">download</span> Xuất Excel
</a>
</div>
</form>

{{-- Counter --}}
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 mb-5">
@php
$cards = [
    ['Tổng số đơn', $c['tong']['total'], 'list_alt', 'bg-secondary-container/40 text-on-secondary-container'],
    ['Đã duyệt', $c['tong']['da_duyet'], 'verified', 'bg-tertiary-fixed-dim/40 text-on-tertiary-container'],
    ['Chờ duyệt', $c['tong']['cho_duyet'], 'pending', 'bg-surface-container-high text-on-surface'],
    ['Từ chối', $c['tong']['tu_choi'], 'cancel', 'bg-error-container/50 text-on-error-container'],
    ['Đã xong (booking)', $c['tong']['da_xong'], 'task_alt', 'bg-primary-container/40 text-on-primary-container'],
];
@endphp
@foreach ($cards as [$label, $val, $icon, $cls])
<div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-4">
<div class="flex items-center gap-2 mb-1">
<div class="w-8 h-8 rounded-lg flex items-center justify-center {{ $cls }}">
<span class="material-symbols-outlined text-[18px]">{{ $icon }}</span>
</div>
<span class="text-label-caps font-label-caps text-on-surface-variant uppercase">{{ $label }}</span>
</div>
<div class="text-display-sm font-display-sm">{{ $val }}</div>
</div>
@endforeach
</div>

{{-- Counter trạng thái khách sau khi sử dụng dịch vụ (chỉ áp cho Đặt phòng) --}}
@php
    $khCards = [
        ['Khách đúng giờ', $c['tong']['kh_dung_gio'], 'schedule', 'bg-emerald-100 text-emerald-700'],
        ['Khách đến muộn', $c['tong']['kh_muon'],     'update',   'bg-amber-100 text-amber-700'],
        ['Khách hủy',      $c['tong']['kh_huy'],      'cancel',   'bg-red-100 text-red-700'],
    ];
    $khTotal = $c['tong']['kh_dung_gio'] + $c['tong']['kh_muon'] + $c['tong']['kh_huy'];
@endphp
<div class="mb-2 flex items-center gap-2">
<span class="material-symbols-outlined text-secondary text-[20px]">rate_review</span>
<h3 class="text-body-md font-semibold text-on-surface">Trạng thái khách sau buổi hẹn</h3>
<span class="text-body-sm text-on-surface-variant">({{ $khTotal }} lịch đã cập nhật / {{ $c['booking']['total'] }} tổng booking)</span>
</div>
<div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-5">
@foreach ($khCards as [$label, $val, $icon, $cls])
@php $pct = $c['booking']['total'] > 0 ? round($val / $c['booking']['total'] * 100, 1) : 0; @endphp
<div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-4">
<div class="flex items-center gap-2 mb-1">
<div class="w-8 h-8 rounded-lg flex items-center justify-center {{ $cls }}">
<span class="material-symbols-outlined text-[18px]">{{ $icon }}</span>
</div>
<span class="text-label-caps font-label-caps text-on-surface-variant uppercase">{{ $label }}</span>
</div>
<div class="flex items-baseline gap-2">
<div class="text-display-sm font-display-sm">{{ $val }}</div>
<div class="text-body-sm text-on-surface-variant">{{ $pct }}%</div>
</div>
</div>
@endforeach
</div>

{{-- Tách số liệu booking vs tư vấn --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-3 mb-6">
<div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-4">
<div class="flex items-center gap-2 mb-2"><span class="material-symbols-outlined text-secondary">edit_calendar</span><span class="font-semibold">Đặt phòng</span></div>
<div class="flex flex-wrap gap-x-6 gap-y-1 text-body-sm">
<span>Tổng: <strong>{{ $c['booking']['total'] }}</strong></span>
<span>Duyệt: <strong class="text-tertiary">{{ $c['booking']['da_duyet'] }}</strong></span>
<span>Chờ: <strong>{{ $c['booking']['cho_duyet'] }}</strong></span>
<span>Từ chối: <strong class="text-error">{{ $c['booking']['tu_choi'] }}</strong></span>
<span>Xong: <strong class="text-primary">{{ $c['booking']['da_xong'] }}</strong></span>
<span class="w-full border-t border-outline-variant/50 pt-1 mt-1 text-body-sm text-on-surface-variant">Sau buổi hẹn:</span>
<span>Đúng giờ: <strong class="text-emerald-700">{{ $c['booking']['kh_dung_gio'] }}</strong></span>
<span>Muộn: <strong class="text-amber-700">{{ $c['booking']['kh_muon'] }}</strong></span>
<span>Hủy: <strong class="text-red-700">{{ $c['booking']['kh_huy'] }}</strong></span>
</div>
</div>
<div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-4">
<div class="flex items-center gap-2 mb-2"><span class="material-symbols-outlined text-secondary">medical_services</span><span class="font-semibold">Tư vấn</span></div>
<div class="flex flex-wrap gap-x-6 gap-y-1 text-body-sm">
<span>Tổng: <strong>{{ $c['tu_van']['total'] }}</strong></span>
<span>Duyệt: <strong class="text-tertiary">{{ $c['tu_van']['da_duyet'] }}</strong></span>
<span>Chờ: <strong>{{ $c['tu_van']['cho_duyet'] }}</strong></span>
<span>Từ chối: <strong class="text-error">{{ $c['tu_van']['tu_choi'] }}</strong></span>
</div>
</div>
</div>

{{-- Bảng kết quả --}}
@if (($f['loai'] ?? 'all') !== 'tu_van')
<div class="bg-surface-container-lowest border border-outline-variant rounded-xl overflow-hidden mb-6">
<div class="px-4 py-2.5 bg-secondary-container/30 font-semibold text-on-secondary-container flex items-center gap-2">
<span class="material-symbols-outlined text-[18px]">edit_calendar</span> Lịch đặt phòng ({{ $baoCao['bookings']->count() }})
</div>
<div class="overflow-x-auto">
<table class="w-full text-body-sm">
<thead class="bg-surface-container-low border-b border-outline-variant text-left text-label-caps font-label-caps uppercase text-on-surface-variant">
<tr>
<th class="px-3 py-2">Ngày</th><th class="px-3 py-2">Khách</th><th class="px-3 py-2">SĐT</th>
<th class="px-3 py-2">Phòng</th><th class="px-3 py-2">Khung giờ</th>
<th class="px-3 py-2">Bác sĩ</th><th class="px-3 py-2">KTV</th>
<th class="px-3 py-2">Sale</th><th class="px-3 py-2">Trạng thái</th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant/40">
@forelse ($baoCao['bookings'] as $bk)
<tr class="hover:bg-surface-container-low/40">
<td class="px-3 py-2">{{ $bk->ngay_dat?->format('d/m/Y') }}</td>
<td class="px-3 py-2 font-medium">{{ $bk->khachHang?->ho_ten }}</td>
<td class="px-3 py-2 text-on-surface-variant">{{ $bk->khachHang?->so_dien_thoai }}</td>
<td class="px-3 py-2">{{ $bk->phong?->ten }}</td>
<td class="px-3 py-2">{{ $bk->khungGio?->nhan ?? '' }}</td>
<td class="px-3 py-2">{{ $bk->bacSi?->ten_day_du ?? '—' }}</td>
<td class="px-3 py-2">{{ $bk->ktv?->name ?? '—' }}</td>
<td class="px-3 py-2">{{ $bk->sale?->name ?? '—' }}</td>
<td class="px-3 py-2">
@switch($bk->trang_thai)
@case('da_duyet')<span class="px-2 py-0.5 rounded-full text-label-caps bg-tertiary-fixed-dim/40 text-on-tertiary-container">Đã duyệt</span>@break
@case('cho_duyet')<span class="px-2 py-0.5 rounded-full text-label-caps bg-surface-container-high">Chờ duyệt</span>@break
@case('tu_choi')<span class="px-2 py-0.5 rounded-full text-label-caps bg-error-container/50 text-on-error-container">Từ chối</span>@break
@case('da_xong')<span class="px-2 py-0.5 rounded-full text-label-caps bg-primary-container/40 text-on-primary-container">Đã xong</span>@break
@endswitch
</td>
</tr>
@empty
<tr><td colspan="9" class="px-3 py-6 text-center text-on-surface-variant">Không có dữ liệu.</td></tr>
@endforelse
</tbody>
</table>
</div>
</div>
@endif

@if (($f['loai'] ?? 'all') !== 'booking')
<div class="bg-surface-container-lowest border border-outline-variant rounded-xl overflow-hidden mb-6">
<div class="px-4 py-2.5 bg-secondary-container/30 font-semibold text-on-secondary-container flex items-center gap-2">
<span class="material-symbols-outlined text-[18px]">medical_services</span> Lịch tư vấn ({{ $baoCao['lichHens']->count() }})
</div>
<div class="overflow-x-auto">
<table class="w-full text-body-sm">
<thead class="bg-surface-container-low border-b border-outline-variant text-left text-label-caps font-label-caps uppercase text-on-surface-variant">
<tr>
<th class="px-3 py-2">Ngày</th><th class="px-3 py-2">Khách</th><th class="px-3 py-2">SĐT</th>
<th class="px-3 py-2">Bác sĩ TV</th><th class="px-3 py-2">Ca khám</th>
<th class="px-3 py-2">Sale</th><th class="px-3 py-2">Trạng thái</th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant/40">
@forelse ($baoCao['lichHens'] as $lh)
<tr class="hover:bg-surface-container-low/40">
<td class="px-3 py-2">{{ $lh->ngay_hen?->format('d/m/Y') }}</td>
<td class="px-3 py-2 font-medium">{{ $lh->khachHang?->ho_ten }}</td>
<td class="px-3 py-2 text-on-surface-variant">{{ $lh->khachHang?->so_dien_thoai }}</td>
<td class="px-3 py-2">{{ $lh->bacSiTuVan?->name ?? '—' }}</td>
<td class="px-3 py-2">{{ $lh->caKham?->nhan ?? '' }}</td>
<td class="px-3 py-2">{{ $lh->sale?->name ?? '—' }}</td>
<td class="px-3 py-2">
@switch($lh->trang_thai)
@case('da_duyet')<span class="px-2 py-0.5 rounded-full text-label-caps bg-tertiary-fixed-dim/40 text-on-tertiary-container">Đã duyệt</span>@break
@case('cho_duyet')<span class="px-2 py-0.5 rounded-full text-label-caps bg-surface-container-high">Chờ duyệt</span>@break
@case('tu_choi')<span class="px-2 py-0.5 rounded-full text-label-caps bg-error-container/50 text-on-error-container">Từ chối</span>@break
@endswitch
</td>
</tr>
@empty
<tr><td colspan="7" class="px-3 py-6 text-center text-on-surface-variant">Không có dữ liệu.</td></tr>
@endforelse
</tbody>
</table>
</div>
</div>
@endif
