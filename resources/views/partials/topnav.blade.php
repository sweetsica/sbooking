@php
    $active = $active ?? 'lich-hen';
    // Phân biệt active giữa "Đặt lịch phòng khám" và "Đặt lịch dịch vụ" qua param ?kieu=dich_vu
    if ($active === 'lich-hen' && request('kieu') === 'dich_vu') {
        $active = 'dich-vu';
    }
    $isAdmin = auth()->check() && auth()->user()->is_admin;
    $vaiTroMa = auth()->user()?->vaiTro?->ma;

    // Quyền duyệt lịch (admin hoặc có trường 'duyet_booking' theo phòng ban / vai trò).
    $canDuyet = $isAdmin || \App\Models\PhanQuyen::where(function ($q) {
        if (auth()->user()?->phong_ban_id) $q->orWhere('phong_ban_id', auth()->user()->phong_ban_id);
        if (auth()->user()?->vai_tro_id)   $q->orWhere('vai_tro_id', auth()->user()->vai_tro_id);
    })->where('truong', 'duyet_booking')->exists();

    // Quyền vào Lịch làm việc (tạo/upload HOẶC duyệt).
    $canLichLamViec = $isAdmin || \App\Models\PhanQuyen::where(function ($q) {
        if (auth()->user()?->phong_ban_id) $q->orWhere('phong_ban_id', auth()->user()->phong_ban_id);
        if (auth()->user()?->vai_tro_id)   $q->orWhere('vai_tro_id', auth()->user()->vai_tro_id);
    })->whereIn('truong', ['quyen_lich_lam_viec', 'duyet_lich_lam_viec'])->exists();

    // Quyền vào Ngày nghỉ.
    $canNgayNghi = $isAdmin || \App\Models\PhanQuyen::where(function ($q) {
        if (auth()->user()?->phong_ban_id) $q->orWhere('phong_ban_id', auth()->user()->phong_ban_id);
        if (auth()->user()?->vai_tro_id)   $q->orWhere('vai_tro_id', auth()->user()->vai_tro_id);
    })->where('truong', 'quyen_ngay_nghi')->exists();

    $items = [
        ['key' => 'lich-hen',  'label' => 'Lịch khám',        'icon' => 'calendar_month', 'href' => '/'.$coSo->slug.'/lich-hen'],
        ['key' => 'dich-vu',   'label' => 'Lịch dịch vụ',     'icon' => 'spa',            'href' => '/'.$coSo->slug.'/lich-hen?kieu=dich_vu'],
    ];

    // 2026-08-10: gom Bác sĩ + Phòng dịch vụ vào dropdown "Khác" cho gọn menu chính.
    $otherItems = [
        ['key' => 'bac-si',  'label' => 'Bác sĩ',        'icon' => 'stethoscope',  'href' => '/'.$coSo->slug.'/bac-si'],
        ['key' => 'phong',   'label' => 'Phòng Dịch vụ', 'icon' => 'meeting_room', 'href' => '/'.$coSo->slug.'/phong'],
    ];
    $otherActive = in_array($active, ['bac-si', 'phong'], true);

    // "Duyệt lịch": chỉ hiện cho người có quyền duyệt.
    if ($canDuyet) {
        array_unshift($items,
            ['key' => 'duyet-lich', 'label' => 'Duyệt lịch', 'icon' => 'fact_check', 'href' => '/'.$coSo->slug.'/duyet-lich']
        );
    }

    // Nhân viên: chỉ thấy 2 loại đặt lịch.
    if ($vaiTroMa === 'nhan_vien') {
        $items = array_values(array_filter($items, fn ($it) => in_array($it['key'], ['lich-hen', 'dich-vu'], true)));
    }

    // Admin: gear icon → /thiet-lap (cards "Lịch làm việc" & "Ngày nghỉ" nằm trong đó).
    // Non-admin có quyền: dropdown nhỏ chứa 2 mục theo cơ sở.
    $canBaoCao = $isAdmin || \App\Models\PhanQuyen::where(function ($q) {
        if (auth()->user()?->phong_ban_id) $q->orWhere('phong_ban_id', auth()->user()->phong_ban_id);
        if (auth()->user()?->vai_tro_id)   $q->orWhere('vai_tro_id', auth()->user()->vai_tro_id);
    })->where('truong', 'xem_bao_cao')->exists();

    $settingsItems = [];
    if (! $isAdmin) {
        if ($canBaoCao) {
            $settingsItems[] = ['key' => 'bao-cao', 'label' => 'Báo cáo', 'icon' => 'analytics', 'href' => '/'.$coSo->slug.'/bao-cao'];
            $settingsItems[] = ['key' => 'so-do', 'label' => 'Sơ đồ tổ chức', 'icon' => 'account_tree', 'href' => '/'.$coSo->slug.'/so-do-to-chuc'];
        }
        if ($canLichLamViec) {
            $settingsItems[] = ['key' => 'lich-lam-viec', 'label' => 'Lịch làm việc', 'icon' => 'event_available', 'href' => '/'.$coSo->slug.'/lich-lam-viec'];
        }
        if ($canNgayNghi) {
            $settingsItems[] = ['key' => 'ngay-nghi', 'label' => 'Ngày nghỉ', 'icon' => 'event_busy', 'href' => '/'.$coSo->slug.'/ngay-nghi'];
        }
    }
    $settingsActive = in_array($active, ['thiet-lap', 'lich-lam-viec', 'ngay-nghi', 'bao-cao', 'so-do'], true);
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
@php $__latestVer = \App\Support\Changelog::latest(); @endphp
@if ($__latestVer)
<a href="/changelog" title="Xem changelog" class="hidden lg:inline-flex items-center px-2 py-0.5 rounded-md bg-surface-container-low border border-outline-variant text-body-sm font-semibold text-on-surface-variant hover:bg-surface-container-high shrink-0">{{ $__latestVer['version'] }}</a>
@endif
<!-- Navigation Links (tablet: chỉ icon, lg+: kèm nhãn) -->
<nav class="flex items-center gap-1 shrink-0">
@foreach ($items as $it)
@php $on = $active === $it['key']; @endphp
<a href="{{ $it['href'] }}" title="{{ $it['label'] }}" class="px-1.5 sm:px-2.5 lg:px-3 py-2 text-body-md rounded-lg flex items-center gap-2 whitespace-nowrap transition-colors {{ $on ? 'font-bold bg-secondary-container text-on-secondary-container' : 'text-on-surface-variant hover:bg-surface-container-low hover:text-on-surface' }}">
<span class="material-symbols-outlined text-[20px]" @if($on) style="font-variation-settings: 'FILL' 1;" @endif>{{ $it['icon'] }}</span>
<span class="hidden lg:inline">{{ $it['label'] }}</span>
</a>
@endforeach
{{-- 2026-08-10: dropdown "Khác" — gom Bác sĩ + Phòng dịch vụ. --}}
@if (! empty($otherItems))
<details class="relative shrink-0">
<summary class="list-none cursor-pointer select-none px-1.5 sm:px-2.5 lg:px-3 py-2 text-body-md rounded-lg flex items-center gap-2 whitespace-nowrap transition-colors {{ $otherActive ? 'font-bold bg-secondary-container text-on-secondary-container' : 'text-on-surface-variant hover:bg-surface-container-low hover:text-on-surface' }} [&::-webkit-details-marker]:hidden">
<span class="material-symbols-outlined text-[20px]" @if($otherActive) style="font-variation-settings: 'FILL' 1;" @endif>more_horiz</span>
<span class="hidden lg:inline">Khác</span>
<span class="material-symbols-outlined text-[16px]">expand_more</span>
</summary>
<div class="absolute left-0 mt-2 w-52 bg-surface-container-lowest border border-outline-variant rounded-xl shadow-lg py-1 z-50">
@foreach ($otherItems as $oi)
@php $ooOn = $active === $oi['key']; @endphp
<a href="{{ $oi['href'] }}" class="flex items-center gap-3 px-4 py-2.5 text-body-md transition-colors {{ $ooOn ? 'bg-secondary-container/40 text-on-secondary-container font-semibold' : 'text-on-surface hover:bg-surface-container-low' }}">
<span class="material-symbols-outlined text-[20px] text-on-surface-variant">{{ $oi['icon'] }}</span> {{ $oi['label'] }}
</a>
@endforeach
</div>
</details>
@endif
</nav>
<!-- Search and Actions -->
<div class="ml-auto flex items-center gap-1 sm:gap-2 xl:gap-3 min-w-0">
<details class="relative shrink-0 hidden sm:block">
<summary title="Tìm kiếm" class="list-none cursor-pointer select-none p-2 text-on-surface-variant hover:bg-surface-container-low transition-all rounded-full flex [&::-webkit-details-marker]:hidden">
<span class="material-symbols-outlined">search</span>
</summary>
<div class="absolute right-0 mt-2 w-72 bg-surface-container-lowest border border-outline-variant rounded-xl shadow-lg p-3 z-50">
<form method="GET" action="/{{ $coSo->slug }}/tim-kiem" class="relative">
<span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[20px]">search</span>
<input name="q" value="{{ request('q') }}" autofocus class="w-full bg-surface-container-low border-none rounded-xl pl-10 pr-4 py-2 text-body-sm focus:ring-2 focus:ring-secondary/20 transition-all" placeholder="Tìm tên / SĐT khách hàng..." type="search"/>
</form>
</div>
</details>
@php
    $dsCoSo = \App\Models\CoSo::where('active', true)->orderBy('id')->get();
    // Giữ nguyên trang hiện tại khi đổi cơ sở (bỏ slug ở đầu URL)
    $restPath = implode('/', array_slice(request()->segments(), 1));
@endphp
@if ($dsCoSo->count() > 1)
<div class="relative hidden sm:block shrink min-w-0" title="Chuyển cơ sở">
<span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-secondary text-[18px] pointer-events-none">apartment</span>
<select onchange="if(this.value)window.location.href=this.value" class="appearance-none w-full max-w-[200px] lg:max-w-[260px] xl:max-w-[320px] pl-9 pr-8 py-1.5 bg-surface-container-low border-none rounded-xl text-body-sm font-semibold text-on-surface focus:ring-2 focus:ring-secondary/20 cursor-pointer transition-all truncate">
@foreach ($dsCoSo as $cs)
<option value="/{{ $cs->slug }}{{ $restPath ? '/'.$restPath : '' }}" @selected($cs->id === $coSo->id)>{{ $cs->ten }}</option>
@endforeach
</select>
<span class="material-symbols-outlined absolute right-1.5 top-1/2 -translate-y-1/2 text-on-surface-variant text-[18px] pointer-events-none">expand_more</span>
</div>
@endif
@php
    $unreadCount = auth()->check() ? auth()->user()->unreadNotifications()->count() : 0;
    // 2026-08-10 — Nút "Dừng nhận lead" chỉ cho sale (chức danh HC/SHC/CM/DM), không cho admin.
    $__saleChucDanh = in_array(auth()->user()?->chuc_danh, ['HC', 'SHC', 'CM', 'DM'], true);
    $__showDungNhanLead = auth()->check() && $__saleChucDanh && ! auth()->user()->is_admin;
    $__isPaused = (bool) (auth()->user()->dung_nhan_lead ?? false);
@endphp
@if ($__showDungNhanLead)
    <form method="POST" action="/dung-nhan-lead" class="shrink-0"
          onsubmit="return confirm('{{ $__isPaused ? 'Tiếp đón lại — bạn sẽ quay về vòng chia UPS?' : 'Tạm dừng tiếp đón — tạm loại bạn khỏi vòng chia UPS?' }}');">
        @csrf
        <button type="submit"
                title="{{ $__isPaused ? 'Bạn đang tạm dừng tiếp đón — bấm để tiếp đón lại' : 'Tạm dừng tiếp đón (loại khỏi UPS)' }}"
                class="px-2.5 py-1.5 rounded-full flex items-center gap-1.5 text-body-sm font-semibold transition-all whitespace-nowrap {{ $__isPaused ? 'bg-slate-200 text-slate-800 hover:bg-slate-300' : 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' }}">
            <span class="material-symbols-outlined text-[18px]">{{ $__isPaused ? 'pause_circle' : 'notifications_active' }}</span>
            <span class="hidden lg:inline">{{ $__isPaused ? 'Đang tạm dừng' : 'Đang tiếp đón' }}</span>
        </button>
    </form>
@endif
<div class="flex items-center gap-0.5 sm:gap-2 border-l border-outline-variant pl-1 sm:pl-2 xl:pl-4 shrink-0">
<details class="relative shrink-0" id="thongbao-details">
<summary title="Thông báo" class="list-none cursor-pointer select-none p-1.5 sm:p-2 text-on-surface-variant hover:bg-surface-container-low transition-all rounded-full flex relative [&::-webkit-details-marker]:hidden">
<span class="material-symbols-outlined text-[22px] sm:text-[24px]">notifications</span>
<span data-thongbao-badge class="{{ $unreadCount > 0 ? '' : 'hidden' }} absolute top-0.5 right-0.5 sm:top-1 sm:right-1 min-w-[18px] h-[18px] px-1 bg-error text-on-error text-[10px] font-bold rounded-full flex items-center justify-center">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
</summary>
<div class="absolute right-0 mt-2 w-[320px] sm:w-[360px] bg-surface-container-lowest border border-outline-variant rounded-xl shadow-lg z-50 max-h-[70vh] overflow-hidden flex flex-col">
<div class="p-3 border-b border-outline-variant flex items-center justify-between gap-2">
<h3 class="font-headline-md text-on-surface">Thông báo</h3>
<button type="button" data-thongbao-mark-all class="text-body-sm text-secondary hover:underline">Đánh dấu tất cả đã đọc</button>
</div>
<div data-thongbao-list class="overflow-y-auto divide-y divide-outline-variant/60 max-h-[50vh]">
<div class="p-6 text-center text-on-surface-variant text-body-sm">Đang tải…</div>
</div>
<a href="/thong-bao" class="p-3 text-center text-body-sm font-semibold text-secondary hover:bg-surface-container-low transition-colors border-t border-outline-variant">Xem tất cả</a>
</div>
</details>
@if ($isAdmin)
<a href="/{{ $coSo->slug }}/thiet-lap" title="Thiết lập" class="p-1.5 sm:p-2 transition-all rounded-full flex {{ $settingsActive ? 'bg-secondary-container text-on-secondary-container' : 'text-on-surface-variant hover:bg-surface-container-low' }}">
<span class="material-symbols-outlined text-[22px] sm:text-[24px]" @if($settingsActive) style="font-variation-settings: 'FILL' 1;" @endif>settings</span>
</a>
@elseif (count($settingsItems))
<details class="relative shrink-0" id="thietlap-details">
<summary title="Thiết lập" class="list-none cursor-pointer select-none p-1.5 sm:p-2 transition-all rounded-full flex [&::-webkit-details-marker]:hidden {{ $settingsActive ? 'bg-secondary-container text-on-secondary-container' : 'text-on-surface-variant hover:bg-surface-container-low' }}">
<span class="material-symbols-outlined text-[22px] sm:text-[24px]" @if($settingsActive) style="font-variation-settings: 'FILL' 1;" @endif>settings</span>
</summary>
<div class="absolute right-0 mt-2 w-56 bg-surface-container-lowest border border-outline-variant rounded-xl shadow-lg py-1 z-50">
@foreach ($settingsItems as $si)
@php $siOn = $active === $si['key']; @endphp
<a href="{{ $si['href'] }}" class="flex items-center gap-3 px-4 py-2.5 text-body-md transition-colors {{ $siOn ? 'bg-secondary-container/40 text-on-secondary-container font-semibold' : 'text-on-surface hover:bg-surface-container-low' }}">
<span class="material-symbols-outlined text-[20px] text-on-surface-variant">{{ $si['icon'] }}</span> {{ $si['label'] }}
</a>
@endforeach
</div>
</details>
@endif
@auth
<details class="relative group">
<summary class="flex items-center gap-1 sm:gap-2 cursor-pointer list-none select-none rounded-full hover:bg-surface-container-low transition-all py-1 pl-0.5 pr-1 sm:pl-1 sm:pr-2 [&::-webkit-details-marker]:hidden">
<div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-primary-container flex items-center justify-center text-on-primary shrink-0">
<span class="material-symbols-outlined text-[16px] sm:text-[18px]">person</span>
</div>
<div class="hidden md:block text-left leading-tight min-w-0">
<div class="text-body-sm font-semibold text-on-surface truncate max-w-[140px] lg:max-w-[180px] xl:max-w-none">{{ auth()->user()->name }}</div>
<div class="text-[10px] uppercase tracking-wide text-on-surface-variant truncate max-w-[140px] lg:max-w-[180px] xl:max-w-none">{{ auth()->user()->is_admin ? 'Quản trị viên' : (auth()->user()->phongBan?->ten ?? 'Nhân viên') }}</div>
</div>
<span class="material-symbols-outlined text-[16px] sm:text-[18px] text-on-surface-variant group-open:rotate-180 transition-transform">expand_more</span>
</summary>
<div class="absolute right-0 mt-2 w-64 bg-surface-container-lowest border border-outline-variant rounded-xl shadow-lg py-1 z-50">
<div class="md:hidden px-4 py-3 border-b border-outline-variant">
<div class="text-body-md font-semibold text-on-surface leading-tight">{{ auth()->user()->name }}</div>
<div class="text-[11px] uppercase tracking-wide text-on-surface-variant mt-0.5">{{ auth()->user()->is_admin ? 'Quản trị viên' : (auth()->user()->phongBan?->ten ?? 'Nhân viên') }}</div>
</div>
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
<a href="/login" class="flex items-center gap-2 text-body-md font-semibold text-secondary hover:underline">
<span class="material-symbols-outlined text-[20px]">login</span> Đăng nhập
</a>
@endauth
</div>
</div>
</div>
</header>
@auth
<script>
(function () {
    const csrf = '{{ csrf_token() }}';
    const coSoSlug = '{{ $coSo->slug }}';
    const details = document.getElementById('thongbao-details');
    if (! details) return;

    const list = details.querySelector('[data-thongbao-list]');
    const badge = details.querySelector('[data-thongbao-badge]');
    const markAllBtn = details.querySelector('[data-thongbao-mark-all]');

    function escapeHtml(s) { return String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }

    function iconFor(event) {
        return ({
            tao_moi: ['add_circle', 'text-blue-600', 'bg-blue-100'],
            duyet: ['check_circle', 'text-emerald-600', 'bg-emerald-100'],
            tu_choi: ['cancel', 'text-red-600', 'bg-red-100'],
            cap_nhat: ['edit', 'text-amber-600', 'bg-amber-100'],
            huy: ['delete', 'text-red-500', 'bg-red-50'],
            nhac_hen: ['alarm', 'text-secondary', 'bg-secondary-container/20'],
        })[event] || ['notifications', 'text-on-surface-variant', 'bg-surface-container-low'];
    }

    function render(items, unreadCount) {
        // badge
        if (unreadCount > 0) {
            badge.textContent = unreadCount > 99 ? '99+' : String(unreadCount);
            badge.classList.remove('hidden');
        } else {
            badge.classList.add('hidden');
        }

        if (! items || items.length === 0) {
            list.innerHTML = '<div class="p-6 text-center text-on-surface-variant text-body-sm">Chưa có thông báo nào.</div>';
            return;
        }
        list.innerHTML = items.map(n => {
            const [icon, color, bg] = iconFor(n.event);
            const unread = ! n.read_at;
            return `
            <a href="${escapeHtml(n.link || '#')}" data-id="${escapeHtml(n.id)}" class="block p-3 hover:bg-surface-container-low transition-colors ${unread ? 'bg-secondary-container/10' : ''}">
                <div class="flex items-start gap-3">
                    <div class="w-9 h-9 rounded-full ${bg} flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-[20px] ${color}">${icon}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-body-sm font-semibold text-on-surface truncate">${escapeHtml(n.tieu_de)}</div>
                        <div class="text-body-sm text-on-surface-variant line-clamp-2">${escapeHtml(n.noi_dung)}</div>
                        <div class="text-[11px] text-on-surface-variant/70 mt-0.5">${escapeHtml(n.created_human)}</div>
                    </div>
                    ${unread ? '<span class="shrink-0 w-2 h-2 rounded-full bg-secondary mt-2"></span>' : ''}
                </div>
            </a>`;
        }).join('');

        // Click → mark read
        list.querySelectorAll('a[data-id]').forEach(el => {
            el.addEventListener('click', () => {
                fetch(`/thong-bao/${el.dataset.id}/read`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf },
                }).catch(() => {});
            });
        });
    }

    function load() {
        fetch(`/thong-bao/summary`, { headers: { Accept: 'application/json' } })
            .then(r => r.ok ? r.json() : null)
            .then(data => { if (data) render(data.items, data.unread_count); })
            .catch(() => { list.innerHTML = '<div class="p-6 text-center text-on-surface-variant text-body-sm">Không tải được thông báo.</div>'; });
    }

    details.addEventListener('toggle', () => { if (details.open) load(); });

    markAllBtn?.addEventListener('click', () => {
        fetch(`/thong-bao/mark-all-read`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf },
        }).then(() => load()).catch(() => {});
    });

    // Poll count mỗi 30s để cập nhật badge
    setInterval(load, 30000);
})();
</script>
@endauth
