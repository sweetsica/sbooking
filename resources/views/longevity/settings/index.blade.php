@extends('longevity.settings.layout')
@section('title', 'Thiết lập')

@section('content')
<div class="flex items-center gap-3 mb-2">
<span class="material-symbols-outlined text-secondary text-[28px]">settings</span>
<h2 class="text-headline-lg font-headline-lg">Thiết lập hệ thống</h2>
</div>
<p class="text-body-md text-on-surface-variant mb-8">Cấu hình phòng, nhân sự, dịch vụ, menu và phân quyền cho <strong>{{ $coSo->ten }}</strong>.</p>

@php
    // Section nào thuộc về từng cơ sở (dữ liệu tách theo co_so_id) → gắn tag "Theo cơ sở".
    // Còn lại (vai-tro, co-so, quyen) là toàn hệ thống → gắn tag "Toàn hệ thống".
    $perCoSo = ['phong', 'bac-si', 'ktv', 'phong-ban', 'nguoi-dung', 'dich-vu', 'menu', 'bao-cao'];

    // Card ngoại (link tới trang riêng — không đi qua SettingsController::section):
    // "Lịch làm việc" & "Ngày nghỉ" đặt tại đây vì mỗi cơ sở có dữ liệu khác nhau.
    $isAdmin = auth()->check() && auth()->user()->is_admin;
    $userId = auth()->id();
    $canLichLamViec = $isAdmin || \App\Models\PhanQuyen::where(function ($q) use ($userId) {
        $u = auth()->user();
        if ($u?->phong_ban_id) $q->orWhere('phong_ban_id', $u->phong_ban_id);
        if ($u?->vai_tro_id)   $q->orWhere('vai_tro_id', $u->vai_tro_id);
    })->whereIn('truong', ['quyen_lich_lam_viec', 'duyet_lich_lam_viec'])->exists();
    $canNgayNghi = $isAdmin || \App\Models\PhanQuyen::where(function ($q) {
        $u = auth()->user();
        if ($u?->phong_ban_id) $q->orWhere('phong_ban_id', $u->phong_ban_id);
        if ($u?->vai_tro_id)   $q->orWhere('vai_tro_id', $u->vai_tro_id);
    })->where('truong', 'quyen_ngay_nghi')->exists();

    $external = [];
    if ($canLichLamViec) {
        $external[] = ['ten' => 'Lịch làm việc', 'icon' => 'event_available',
            'mota' => 'Đăng ký / upload lịch làm việc của bác sĩ theo tháng cho cơ sở này.',
            'href' => '/'.$coSo->slug.'/lich-lam-viec'];
    }
    if ($canNgayNghi) {
        $external[] = ['ten' => 'Ngày nghỉ', 'icon' => 'event_busy',
            'mota' => 'Ngày nghỉ chung / theo nhân sự áp dụng riêng cho cơ sở này.',
            'href' => '/'.$coSo->slug.'/ngay-nghi'];
    }
@endphp

@php
    $tagCoSo = '<span class="text-[10px] px-1.5 py-0.5 rounded bg-secondary-container/50 text-on-secondary-container uppercase font-label-caps whitespace-nowrap">Theo cơ sở</span>';
    $tagHT = '<span class="text-[10px] px-1.5 py-0.5 rounded bg-surface-container-high text-on-surface-variant uppercase font-label-caps whitespace-nowrap">Toàn hệ thống</span>';
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
@foreach ($sections as $key => [$ten, $icon, $mota])
@php $tag = in_array($key, $perCoSo, true) ? $tagCoSo : $tagHT; @endphp
<a href="/{{ $coSo->slug }}/thiet-lap/{{ $key }}"
   class="group bg-surface-container-lowest border border-outline-variant rounded-xl p-5 hover:shadow-lg hover:border-secondary/40 transition-all">
<div class="flex items-start gap-4">
<div class="w-12 h-12 rounded-xl bg-secondary-container/40 text-on-secondary-container flex items-center justify-center group-hover:scale-110 transition-transform shrink-0">
<span class="material-symbols-outlined">{{ $icon }}</span>
</div>
<div class="min-w-0">
<h3 class="text-headline-md font-headline-md mb-1 flex items-center gap-2 flex-wrap">{{ $ten }} {!! $tag !!}</h3>
<p class="text-body-sm text-on-surface-variant">{{ $mota }}</p>
</div>
</div>
</a>
@endforeach
@foreach ($external as $ex)
<a href="{{ $ex['href'] }}"
   class="group bg-surface-container-lowest border border-outline-variant rounded-xl p-5 hover:shadow-lg hover:border-secondary/40 transition-all">
<div class="flex items-start gap-4">
<div class="w-12 h-12 rounded-xl bg-secondary-container/40 text-on-secondary-container flex items-center justify-center group-hover:scale-110 transition-transform shrink-0">
<span class="material-symbols-outlined">{{ $ex['icon'] }}</span>
</div>
<div class="min-w-0">
<h3 class="text-headline-md font-headline-md mb-1 flex items-center gap-2 flex-wrap">{{ $ex['ten'] }} {!! $tagCoSo !!}</h3>
<p class="text-body-sm text-on-surface-variant">{{ $ex['mota'] }}</p>
</div>
</div>
</a>
@endforeach
</div>
@endsection
