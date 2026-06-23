@php
    $active = $active ?? 'lich-hen';
    $isAdmin = auth()->check() && auth()->user()->is_admin;
    $vaiTroMa = auth()->user()?->vaiTro?->ma;

    // Quyền duyệt lịch (admin hoặc có trường 'duyet_booking' theo phòng ban / vai trò).
    $canDuyet = $isAdmin || \App\Models\PhanQuyen::where(function ($q) {
        if (auth()->user()?->phong_ban_id) $q->orWhere('phong_ban_id', auth()->user()->phong_ban_id);
        if (auth()->user()?->vai_tro_id)   $q->orWhere('vai_tro_id', auth()->user()->vai_tro_id);
    })->where('truong', 'duyet_booking')->exists();

    $items = [
        ['key' => 'lich-hen',  'label' => 'Đặt phòng',       'icon' => 'calendar_month', 'href' => '/'.$coSo->slug.'/lich-hen'],
        ['key' => 'tu-van',    'label' => 'Đặt lịch bác sĩ', 'icon' => 'medical_services', 'href' => '/'.$coSo->slug.'/lich-tu-van'],
        ['key' => 'bac-si',    'label' => 'Bác sĩ',           'icon' => 'stethoscope',    'href' => '/'.$coSo->slug.'/bac-si'],
        ['key' => 'phong',     'label' => 'Phòng Dịch vụ',    'icon' => 'meeting_room',   'href' => '/'.$coSo->slug.'/phong'],
    ];

    // "Duyệt lịch": chỉ hiện cho người có quyền duyệt.
    if ($canDuyet) {
        array_splice($items, 1, 0, [
            ['key' => 'duyet-lich', 'label' => 'Duyệt lịch', 'icon' => 'fact_check', 'href' => '/'.$coSo->slug.'/duyet-lich'],
        ]);
    }

    // Nhân viên: chỉ thấy "Đặt phòng" và "Đặt lịch bác sĩ".
    if ($vaiTroMa === 'nhan_vien') {
        $items = array_values(array_filter($items, fn ($it) => in_array($it['key'], ['lich-hen', 'tu-van'], true)));
    }
    // "Thiết lập" đã có icon bánh răng ở góc phải -> không lặp lại trong menu.
@endphp
<!-- Top Navigation Bar -->
<header class="fixed top-0 left-0 right-0 h-16 bg-surface-container-lowest border-b border-outline-variant flex items-center px-container-margin z-50">
<div class="flex items-center gap-2 lg:gap-4 xl:gap-6 w-full max-w-[1650px] mx-auto min-w-0">
<!-- Brand Identity -->
<a href="/{{ $coSo->slug }}/lich-hen" class="flex items-center gap-2 shrink-0 lg:mr-2">
<div class="w-8 h-8 bg-primary rounded flex items-center justify-center shrink-0">
<span class="material-symbols-outlined text-on-primary text-[20px]">spa</span>
</div>
<h1 class="text-headline-md font-bold text-on-surface leading-tight whitespace-nowrap hidden xl:block">Longevity Booking</h1>
</a>
<!-- Navigation Links (tablet: chỉ icon, lg+: kèm nhãn) -->
<nav class="flex items-center gap-1 shrink-0">
@foreach ($items as $it)
@php $on = $active === $it['key']; @endphp
<a href="{{ $it['href'] }}" title="{{ $it['label'] }}" class="px-2.5 lg:px-3 py-2 text-body-md rounded-lg flex items-center gap-2 whitespace-nowrap transition-colors {{ $on ? 'font-bold bg-secondary-container text-on-secondary-container' : 'text-on-surface-variant hover:bg-surface-container-low hover:text-on-surface' }}">
<span class="material-symbols-outlined text-[20px]" @if($on) style="font-variation-settings: 'FILL' 1;" @endif>{{ $it['icon'] }}</span>
<span class="hidden lg:inline">{{ $it['label'] }}</span>
</a>
@endforeach
</nav>
<!-- Search and Actions -->
<div class="ml-auto flex items-center gap-2 xl:gap-3 min-w-0">
<form method="GET" action="/{{ $coSo->slug }}/tim-kiem" class="relative w-56 2xl:w-72 hidden xl:block">
<span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[20px]">search</span>
<input name="q" value="{{ request('q') }}" class="w-full bg-surface-container-low border-none rounded-xl pl-10 pr-4 py-1.5 text-body-sm focus:ring-2 focus:ring-secondary/20 transition-all" placeholder="Tìm tên / SĐT khách hàng..." type="search"/>
</form>
@php
    $dsCoSo = \App\Models\CoSo::where('active', true)->orderBy('id')->get();
    // Giữ nguyên trang hiện tại khi đổi cơ sở (bỏ slug ở đầu URL)
    $restPath = implode('/', array_slice(request()->segments(), 1));
@endphp
@if ($dsCoSo->count() > 1)
<div class="relative hidden sm:block shrink min-w-0" title="Chuyển cơ sở">
<span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-secondary text-[18px] pointer-events-none">apartment</span>
<select onchange="if(this.value)window.location.href=this.value" class="appearance-none w-full max-w-[180px] pl-9 pr-8 py-1.5 bg-surface-container-low border-none rounded-xl text-body-sm font-semibold text-on-surface focus:ring-2 focus:ring-secondary/20 cursor-pointer transition-all truncate">
@foreach ($dsCoSo as $cs)
<option value="/{{ $cs->slug }}{{ $restPath ? '/'.$restPath : '' }}" @selected($cs->id === $coSo->id)>{{ $cs->ten }}</option>
@endforeach
</select>
<span class="material-symbols-outlined absolute right-1.5 top-1/2 -translate-y-1/2 text-on-surface-variant text-[18px] pointer-events-none">expand_more</span>
</div>
@endif
<div class="flex items-center gap-2 border-l border-outline-variant pl-2 xl:pl-4 shrink-0">
<button class="p-2 text-on-surface-variant hover:bg-surface-container-low transition-all rounded-full relative hidden xl:flex">
<span class="material-symbols-outlined">notifications</span>
<span class="absolute top-2 right-2 w-2 h-2 bg-error rounded-full"></span>
</button>
@if ($isAdmin)
<a href="/{{ $coSo->slug }}/thiet-lap" class="p-2 text-on-surface-variant hover:bg-surface-container-low transition-all rounded-full">
<span class="material-symbols-outlined">settings</span>
</a>
@endif
@auth
<details class="relative ml-2 pl-2 border-l border-outline-variant group">
<summary class="flex items-center gap-2 cursor-pointer list-none select-none rounded-full hover:bg-surface-container-low transition-all py-1 pl-1 pr-2 [&::-webkit-details-marker]:hidden">
<div class="w-8 h-8 rounded-full bg-primary-container flex items-center justify-center text-on-primary">
<span class="material-symbols-outlined text-[18px]">person</span>
</div>
<div class="hidden 2xl:block text-left leading-tight">
<div class="text-body-sm font-semibold text-on-surface">{{ auth()->user()->name }}</div>
<div class="text-[10px] uppercase tracking-wide text-on-surface-variant">{{ auth()->user()->is_admin ? 'Quản trị viên' : (auth()->user()->phongBan?->ten ?? 'Nhân viên') }}</div>
</div>
<span class="material-symbols-outlined text-[18px] text-on-surface-variant group-open:rotate-180 transition-transform">expand_more</span>
</summary>
<div class="absolute right-0 mt-2 w-56 bg-surface-container-lowest border border-outline-variant rounded-xl shadow-lg py-1 z-50">
<a href="/doi-mat-khau" class="flex items-center gap-3 px-4 py-2.5 text-body-md text-on-surface hover:bg-surface-container-low transition-colors">
<span class="material-symbols-outlined text-[20px] text-on-surface-variant">lock_reset</span> Đổi mật khẩu
</a>
<form method="POST" action="/logout">
@csrf
<button type="submit" class="w-full flex items-center gap-3 px-4 py-2.5 text-body-md text-error hover:bg-error/10 transition-colors text-left">
<span class="material-symbols-outlined text-[20px]">logout</span> Đăng xuất
</button>
</form>
</div>
</details>
@else
<a href="/login" class="ml-2 pl-3 border-l border-outline-variant flex items-center gap-2 text-body-md font-semibold text-secondary hover:underline">
<span class="material-symbols-outlined text-[20px]">login</span> Đăng nhập
</a>
@endauth
</div>
</div>
</div>
</header>
