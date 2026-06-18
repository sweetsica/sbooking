@php
    $active = $active ?? 'lich-hen';
    $isAdmin = auth()->check() && auth()->user()->is_admin;

    $items = [
        ['key' => 'tong-quan', 'label' => 'Tổng quan',  'icon' => 'dashboard',      'href' => '/'.$coSo->slug.'/lich-hen'],
        ['key' => 'lich-hen',  'label' => 'Lịch hẹn',   'icon' => 'calendar_month', 'href' => '/'.$coSo->slug.'/lich-hen'],
        ['key' => 'phong',     'label' => 'Phòng bệnh', 'icon' => 'meeting_room',   'href' => '/'.$coSo->slug.'/phong'],
    ];
    // Chỉ admin thấy "Thiết lập"
    if ($isAdmin) {
        $items[] = ['key' => 'thiet-lap', 'label' => 'Thiết lập', 'icon' => 'settings', 'href' => '/'.$coSo->slug.'/thiet-lap'];
    }
@endphp
<!-- Top Navigation Bar -->
<header class="fixed top-0 left-0 right-0 h-16 bg-surface-container-lowest border-b border-outline-variant flex items-center px-container-margin z-50">
<div class="flex items-center gap-8 w-full max-w-[1600px] mx-auto">
<!-- Brand Identity -->
<a href="/{{ $coSo->slug }}/lich-hen" class="flex items-center gap-2 shrink-0">
<div class="w-8 h-8 bg-primary rounded flex items-center justify-center">
<span class="material-symbols-outlined text-on-primary text-[20px]">spa</span>
</div>
<h1 class="text-headline-md font-bold text-on-surface leading-tight">Precision Wellness</h1>
</a>
<!-- Navigation Links -->
<nav class="flex items-center gap-1 ml-4">
@foreach ($items as $it)
@php $on = $active === $it['key']; @endphp
<a href="{{ $it['href'] }}" class="px-4 py-2 text-body-md rounded-lg flex items-center gap-2 transition-colors {{ $on ? 'font-bold bg-secondary-container text-on-secondary-container' : 'text-on-surface-variant hover:bg-surface-container-low hover:text-on-surface' }}">
<span class="material-symbols-outlined text-[20px]" @if($on) style="font-variation-settings: 'FILL' 1;" @endif>{{ $it['icon'] }}</span>
{{ $it['label'] }}
</a>
@endforeach
</nav>
<!-- Search and Actions -->
<div class="ml-auto flex items-center gap-4">
<div class="relative w-64 hidden md:block">
<span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[20px]">search</span>
<input class="w-full bg-surface-container-low border-none rounded-xl pl-10 pr-4 py-1.5 text-body-sm focus:ring-2 focus:ring-secondary/20 transition-all" placeholder="Tìm kiếm..." type="text"/>
</div>
<div class="flex items-center gap-2 border-l border-outline-variant pl-4">
<button class="p-2 text-on-surface-variant hover:bg-surface-container-low transition-all rounded-full relative">
<span class="material-symbols-outlined">notifications</span>
<span class="absolute top-2 right-2 w-2 h-2 bg-error rounded-full"></span>
</button>
@if ($isAdmin)
<a href="/{{ $coSo->slug }}/thiet-lap" class="p-2 text-on-surface-variant hover:bg-surface-container-low transition-all rounded-full">
<span class="material-symbols-outlined">settings</span>
</a>
@endif
@auth
<div class="flex items-center gap-2 ml-2 pl-2 border-l border-outline-variant">
<div class="hidden lg:block text-right leading-tight">
<div class="text-body-sm font-semibold text-on-surface">{{ auth()->user()->name }}</div>
<div class="text-[10px] uppercase tracking-wide text-on-surface-variant">{{ auth()->user()->is_admin ? 'Quản trị viên' : (auth()->user()->phongBan?->ten ?? 'Nhân viên') }}</div>
</div>
<div class="w-8 h-8 rounded-full bg-primary-container flex items-center justify-center text-on-primary">
<span class="material-symbols-outlined text-[18px]">person</span>
</div>
<form method="POST" action="/logout">
@csrf
<button type="submit" title="Đăng xuất" class="p-2 text-on-surface-variant hover:text-error hover:bg-error/10 transition-all rounded-full">
<span class="material-symbols-outlined text-[20px]">logout</span>
</button>
</form>
</div>
@else
<a href="/login" class="ml-2 pl-3 border-l border-outline-variant flex items-center gap-2 text-body-md font-semibold text-secondary hover:underline">
<span class="material-symbols-outlined text-[20px]">login</span> Đăng nhập
</a>
@endauth
</div>
</div>
</div>
</header>
