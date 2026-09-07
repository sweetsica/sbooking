@extends('longevity.settings.layout')
@section('title', 'Tổng hợp lịch đặt')

@section('content')
@php
    $phanLoai = $filters['phanLoai'] ?? 'all';
    $tu = $filters['tu'] ?? '';
    $den = $filters['den'] ?? '';
    $q = $filters['q'] ?? '';
    $action = '/'.$coSo->slug.'/thiet-lap/tong-hop-lich-dat';

    // 3 màu tương phản mạnh, phân biệt rõ K / TV / DV (không phụ thuộc M3 token tối).
    $badgeClass = fn ($pl) => match ($pl) {
        'K'  => 'bg-blue-100 text-blue-800 ring-1 ring-blue-200',
        'TV' => 'bg-amber-100 text-amber-800 ring-1 ring-amber-200',
        'DV' => 'bg-emerald-100 text-emerald-800 ring-1 ring-emerald-200',
        default => 'bg-slate-100 text-slate-700 ring-1 ring-slate-200',
    };
    $ttLabel = [
        'cho_duyet' => ['Chờ duyệt', 'bg-yellow-100 text-yellow-800'],
        'da_duyet'  => ['Đã duyệt', 'bg-green-100 text-green-800'],
        'da_xong'   => ['Đã xong', 'bg-green-100 text-green-800'],
        'tu_choi'   => ['Từ chối', 'bg-red-100 text-red-700'],
    ];
@endphp

<div class="flex items-center gap-2 text-body-sm text-on-surface-variant mb-4">
<a href="/{{ $coSo->slug }}/thiet-lap" class="hover:text-secondary">Thiết lập</a>
<span class="material-symbols-outlined text-[16px]">chevron_right</span>
<span class="text-on-surface font-semibold">Tổng hợp lịch đặt</span>
</div>

<div class="flex items-start justify-between gap-4 mb-6 flex-wrap">
<div class="flex items-center gap-3">
<div class="w-12 h-12 rounded-xl bg-secondary-container/40 text-on-secondary-container flex items-center justify-center">
<span class="material-symbols-outlined">table_view</span>
</div>
<div>
<h2 class="text-headline-lg font-headline-lg">Tổng hợp lịch đặt</h2>
<p class="text-body-sm text-on-surface-variant">Gộp Lịch khám (K) + Lịch tư vấn (TV) + Lịch dịch vụ (DV) của <strong>{{ $coSo->ten }}</strong>. Xuất/Nhập Excel cho admin hệ thống.</p>
</div>
</div>
<div class="flex items-center gap-2 flex-wrap">
<button type="button" disabled title="Sắp có ở Phase 2" class="px-4 py-2 bg-surface-container-high text-on-surface-variant/60 font-semibold rounded-lg flex items-center gap-2 cursor-not-allowed">
<span class="material-symbols-outlined text-[20px]">download</span> Xuất Excel
</button>
<a href="{{ route('settings.tong-hop-lich-dat.mau', $coSo->slug) }}" class="px-4 py-2 border border-outline text-on-surface font-semibold rounded-lg hover:bg-surface-container-low flex items-center gap-2">
<span class="material-symbols-outlined text-[20px]">description</span> Tải mẫu nhập
</a>
<button type="button" onclick="document.getElementById('form-nhap-xlsx').classList.toggle('hidden')" class="px-4 py-2 bg-primary text-on-primary font-semibold rounded-lg flex items-center gap-2 hover:opacity-90">
<span class="material-symbols-outlined text-[20px]">upload</span> Nhập Excel
</button>
</div>
</div>

{{-- Alert import result --}}
@if (session('import_ok'))
<div class="mb-4 p-3 rounded-lg bg-green-50 border border-green-200 text-green-800 flex items-center gap-2">
<span class="material-symbols-outlined">check_circle</span>
<span>{{ session('import_ok') }}</span>
</div>
@endif
@if (session('import_error'))
<div class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-red-800">
<div class="flex items-center gap-2">
<span class="material-symbols-outlined">error</span>
<span class="font-semibold">{{ session('import_error') }}</span>
</div>
@if (session('import_error_token'))
<div class="mt-2">
<a href="{{ route('settings.tong-hop-lich-dat.taifileloi', [$coSo->slug, session('import_error_token')]) }}" class="inline-flex items-center gap-1 px-3 py-1.5 bg-red-600 text-white text-sm font-semibold rounded hover:bg-red-700">
<span class="material-symbols-outlined text-[16px]">download</span> Tải file lỗi (đã bôi đỏ ô sai)
</a>
</div>
@endif
</div>
@endif

{{-- Form upload xlsx (ẩn mặc định) --}}
<div id="form-nhap-xlsx" class="mb-4 hidden bg-surface-container-lowest border border-outline-variant rounded-xl p-4">
<form method="POST" action="{{ route('settings.tong-hop-lich-dat.nhap', $coSo->slug) }}" enctype="multipart/form-data" class="flex items-center gap-3 flex-wrap">
@csrf
<div class="flex-1 min-w-[300px]">
<label class="text-label-caps font-label-caps text-on-surface-variant block mb-1">Chọn file .xlsx</label>
<input type="file" name="file" accept=".xlsx,.xls" required class="block w-full text-body-sm file:mr-3 file:px-4 file:py-2 file:border-0 file:bg-secondary-container file:text-on-secondary-container file:rounded-lg file:cursor-pointer"/>
</div>
<button type="submit" class="px-4 py-2 bg-primary text-on-primary font-semibold rounded-lg flex items-center gap-2 hover:opacity-90">
<span class="material-symbols-outlined text-[20px]">upload_file</span> Nhập
</button>
<p class="text-body-sm text-on-surface-variant w-full">Fail-fast: 1 dòng lỗi → toàn file bị chặn, không dòng nào được ghi. Chưa có mẫu? Bấm <strong>Tải mẫu nhập</strong> trước.</p>
</form>
</div>

{{-- Counter --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-4">
@foreach ([['total','Tổng','table_rows'],['K','Lịch khám','stethoscope'],['TV','Lịch tư vấn','forum'],['DV','Lịch dịch vụ','healing']] as [$k,$l,$icon])
<div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-4 flex items-center gap-3">
<span class="material-symbols-outlined text-secondary text-[28px]">{{ $icon }}</span>
<div>
<div class="text-label-caps font-label-caps text-on-surface-variant uppercase">{{ $l }}</div>
<div class="text-headline-md font-headline-md">{{ number_format($counter[$k]) }}</div>
</div>
</div>
@endforeach
</div>

{{-- Filter --}}
<form method="GET" action="{{ $action }}" class="mb-4 bg-surface-container-lowest border border-outline-variant rounded-xl p-4">
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
<div class="flex flex-col gap-1">
<label class="text-label-caps font-label-caps text-on-surface-variant">Phân loại</label>
<select name="phan_loai" class="px-3 py-2 bg-surface-container-low border border-outline-variant rounded-lg focus:outline-none focus:border-secondary">
<option value="all" @selected($phanLoai === 'all')>— Tất cả —</option>
<option value="K"   @selected($phanLoai === 'K')>Lịch khám (K)</option>
<option value="TV"  @selected($phanLoai === 'TV')>Lịch tư vấn (TV)</option>
<option value="DV"  @selected($phanLoai === 'DV')>Lịch dịch vụ (DV)</option>
</select>
</div>
<div class="flex flex-col gap-1">
<label class="text-label-caps font-label-caps text-on-surface-variant">Từ ngày</label>
<input type="date" name="tu" value="{{ $tu }}" class="px-3 py-2 bg-surface-container-low border border-outline-variant rounded-lg focus:outline-none focus:border-secondary"/>
</div>
<div class="flex flex-col gap-1">
<label class="text-label-caps font-label-caps text-on-surface-variant">Đến ngày</label>
<input type="date" name="den" value="{{ $den }}" class="px-3 py-2 bg-surface-container-low border border-outline-variant rounded-lg focus:outline-none focus:border-secondary"/>
</div>
<div class="flex flex-col gap-1">
<label class="text-label-caps font-label-caps text-on-surface-variant">Tìm khách / SĐT</label>
<input type="text" name="q" value="{{ $q }}" placeholder="Tên hoặc SĐT..." class="px-3 py-2 bg-surface-container-low border border-outline-variant rounded-lg focus:outline-none focus:border-secondary"/>
</div>
</div>
<div class="flex items-center gap-2 mt-3">
<button type="submit" class="px-4 py-2 bg-primary text-on-primary font-semibold rounded-lg flex items-center gap-2 hover:opacity-90">
<span class="material-symbols-outlined text-[20px]">search</span> Lọc
</button>
<a href="{{ $action }}" class="px-4 py-2 text-on-surface-variant hover:bg-surface-container-high rounded-lg">Xóa lọc</a>
<span class="text-body-sm text-on-surface-variant ml-auto">{{ number_format($counter['total']) }} lịch</span>
</div>
</form>

{{-- Bảng --}}
<div x-data="{
    selected: [],
    allKeys: @js($rows->pluck('bulk_key')->all()),
    toggleAll(e) { this.selected = e.target.checked ? [...this.allKeys] : []; },
    submitDelete() {
        if (! this.selected.length) return;
        if (! confirm(`Xóa ${this.selected.length} lịch đã chọn? (không hoàn tác)`)) return;
        const f = document.getElementById('form-xoa-hang-loat');
        f.querySelectorAll('input[name=&quot;items[]&quot;]').forEach(i => i.remove());
        this.selected.forEach(k => {
            const i = document.createElement('input');
            i.type = 'hidden'; i.name = 'items[]'; i.value = k;
            f.appendChild(i);
        });
        f.submit();
    }
}">
<form id="form-xoa-hang-loat" method="POST" action="{{ route('settings.tong-hop-lich-dat.xoahangloat', $coSo->slug) }}" class="hidden">@csrf</form>

{{-- Floating toolbar khi có row chọn --}}
<div x-show="selected.length > 0" x-cloak class="mb-3 p-3 rounded-xl bg-amber-50 border border-amber-300 flex items-center gap-3 flex-wrap">
<span class="font-semibold text-amber-900">Đã chọn <span x-text="selected.length"></span> lịch</span>
<button type="button" @click="submitDelete()" class="px-3 py-1.5 bg-red-600 text-white text-sm font-semibold rounded-lg flex items-center gap-1 hover:bg-red-700">
<span class="material-symbols-outlined text-[16px]">delete</span> Xóa đã chọn
</button>
<button type="button" @click="selected = []" class="px-3 py-1.5 text-amber-900 text-sm hover:bg-amber-100 rounded-lg">Bỏ chọn</button>
</div>

<div class="bg-surface-container-lowest border border-outline-variant rounded-xl overflow-hidden">
<div class="overflow-x-auto">
<table class="w-full min-w-[900px] text-body-md">
<thead>
<tr class="text-left text-label-caps font-label-caps uppercase text-on-surface-variant bg-surface-container-low border-b border-outline-variant">
<th class="px-3 py-3 text-center w-10">
<input type="checkbox" @change="toggleAll($event)" :checked="selected.length === allKeys.length && allKeys.length > 0" class="w-4 h-4 rounded border-outline text-secondary" title="Chọn tất cả"/>
</th>
<th class="px-4 py-3 whitespace-nowrap">STT</th>
<th class="px-4 py-3 whitespace-nowrap">Phân loại</th>
<th class="px-4 py-3 whitespace-nowrap">Mã ĐL</th>
<th class="px-4 py-3 whitespace-nowrap">Tên khách</th>
<th class="px-4 py-3 whitespace-nowrap">SĐT</th>
<th class="px-4 py-3 whitespace-nowrap">Sale chăm sóc</th>
<th class="px-4 py-3 whitespace-nowrap">Danh mục</th>
<th class="px-4 py-3 whitespace-nowrap">Giờ hẹn</th>
<th class="px-4 py-3 whitespace-nowrap">Trạng thái</th>
<th class="px-4 py-3 whitespace-nowrap">Kết quả</th>
<th class="px-4 py-3 text-right whitespace-nowrap">Thao tác</th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant/60">
@forelse ($rows as $i => $r)
<tr class="hover:bg-surface-container-low/40" :class="selected.includes('{{ $r->bulk_key }}') ? 'bg-amber-50' : ''">
<td class="px-3 py-3 text-center">
<input type="checkbox" value="{{ $r->bulk_key }}" x-model="selected" class="w-4 h-4 rounded border-outline text-secondary"/>
</td>
<td class="px-4 py-3 text-on-surface-variant">{{ $i + 1 }}</td>
<td class="px-4 py-3"><span class="px-2 py-0.5 rounded text-label-caps font-label-caps {{ $badgeClass($r->phan_loai) }}">{{ $r->phan_loai_label }}</span></td>
<td class="px-4 py-3 font-mono text-body-sm">{{ $r->ma_dl }}</td>
<td class="px-4 py-3 font-semibold">{{ $r->ten_khach ?? '—' }}</td>
<td class="px-4 py-3 text-on-surface-variant">{{ $r->sdt ?? '—' }}</td>
<td class="px-4 py-3">{{ $r->sale ?? '—' }}</td>
<td class="px-4 py-3 text-on-surface-variant">{{ $r->danh_muc ?? '—' }}</td>
<td class="px-4 py-3 whitespace-nowrap">
@if ($r->ngay)
<span class="font-semibold">{{ \Illuminate\Support\Carbon::parse($r->ngay)->format('d/m') }}</span>
<span class="text-on-surface-variant"> · {{ $r->gio ? substr($r->gio, 0, 5) : '—' }}</span>
@else — @endif
</td>
<td class="px-4 py-3">
@php [$ttT, $ttC] = $ttLabel[$r->trang_thai] ?? [$r->trang_thai, 'bg-surface-container-high text-on-surface-variant']; @endphp
<span class="px-2 py-0.5 rounded-full text-label-caps font-label-caps {{ $ttC }}">{{ $ttT }}</span>
</td>
<td class="px-4 py-3 text-on-surface-variant">{{ $r->ket_qua ?? '—' }}</td>
<td class="px-4 py-3">
<div class="flex items-center justify-end gap-1">
<a href="{{ $r->url_show }}" class="p-1.5 text-on-surface-variant hover:text-secondary hover:bg-secondary/10 rounded-lg" title="Xem chi tiết"><span class="material-symbols-outlined text-[18px]">visibility</span></a>
<a href="{{ $r->url_edit }}" class="p-1.5 text-on-surface-variant hover:text-secondary hover:bg-secondary/10 rounded-lg" title="Sửa"><span class="material-symbols-outlined text-[18px]">edit</span></a>
<form method="POST" action="{{ $r->url_destroy }}" onsubmit="return confirm('Xóa {{ $r->phan_loai_label }} của {{ $r->ten_khach }}?');">
@csrf @method('DELETE')
<button type="submit" class="p-1.5 text-on-surface-variant hover:text-error hover:bg-error/10 rounded-lg" title="Xóa"><span class="material-symbols-outlined text-[18px]">delete</span></button>
</form>
</div>
</td>
</tr>
@empty
<tr><td colspan="12" class="px-4 py-10 text-center text-on-surface-variant">Chưa có lịch nào khớp bộ lọc.</td></tr>
@endforelse
</tbody>
</table>
</div>
</div>
</div>{{-- /x-data --}}
@endsection
