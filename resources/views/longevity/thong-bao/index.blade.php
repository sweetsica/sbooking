<!DOCTYPE html>
<html class="light" lang="vi"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Thông báo | Longevity Booking</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&amp;family=Manrope:wght@600;700;800&amp;family=JetBrains+Mono:wght@500;600&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script>tailwind.config={darkMode:"class",theme:{extend:{"colors":{"on-primary-fixed":"#131b2e","on-secondary-fixed-variant":"#004c6e","primary-fixed-dim":"#bec6e0","inverse-on-surface":"#eff1f3","secondary-fixed-dim":"#89ceff","tertiary-fixed":"#6ffbbe","secondary-container":"#39b8fd","primary-fixed":"#dae2fd","surface-container-low":"#f2f4f6","outline-variant":"#c6c6cd","on-error-container":"#93000a","outline":"#76777d","on-tertiary-container":"#009668","inverse-primary":"#bec6e0","on-background":"#191c1e","surface-container-lowest":"#ffffff","primary":"#000000","on-surface-variant":"#45464d","on-secondary-fixed":"#001e2f","surface":"#f7f9fb","error":"#ba1a1a","tertiary-container":"#002113","on-primary-container":"#7c839b","surface-container-high":"#e6e8ea","surface-bright":"#f7f9fb","on-primary":"#ffffff","on-secondary-container":"#004666","primary-container":"#131b2e","tertiary":"#000000","surface-tint":"#565e74","surface-dim":"#d8dadc","on-error":"#ffffff","on-tertiary":"#ffffff","error-container":"#ffdad6","background":"#f7f9fb","inverse-surface":"#2d3133","on-surface":"#191c1e","on-primary-fixed-variant":"#3f465c","tertiary-fixed-dim":"#4edea3","surface-container-highest":"#e0e3e5","secondary-fixed":"#c9e6ff","on-tertiary-fixed-variant":"#005236","on-tertiary-fixed":"#002113","on-secondary":"#ffffff","surface-variant":"#e0e3e5","secondary":"#006591","surface-container":"#eceef0"},"borderRadius":{"DEFAULT":"0.125rem","lg":"0.25rem","xl":"0.5rem","full":"0.75rem"},"spacing":{"gutter":"12px","container-margin":"24px","row-height-compact":"40px","unit":"4px","row-height-standard":"56px"},"fontFamily":{"headline-md":["Manrope"],"time-slot":["JetBrains Mono"],"headline-lg":["Manrope"],"body-md":["Inter"],"body-sm":["Inter"],"label-caps":["JetBrains Mono"]},"fontSize":{"headline-md":["18px",{"lineHeight":"24px","fontWeight":"600"}],"time-slot":["12px",{"lineHeight":"16px","fontWeight":"500"}],"headline-lg":["24px",{"lineHeight":"32px","fontWeight":"700"}],"body-md":["14px",{"lineHeight":"20px","fontWeight":"400"}],"body-sm":["13px",{"lineHeight":"18px","fontWeight":"400"}],"label-caps":["11px",{"lineHeight":"16px","letterSpacing":"0.05em","fontWeight":"600"}]}}}}</script>
<style>
body { font-family: 'Inter', sans-serif; background-color: #f7f9fb; }
.material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; vertical-align: middle; }
</style>
</head>
<body class="bg-surface text-on-surface">
@include('partials.topnav', ['active' => ''])

<main class="pt-24 pb-32 sm:pb-12 px-container-margin max-w-3xl mx-auto">
<div class="flex items-center justify-between mb-6 gap-3 flex-wrap">
<div>
<h2 class="font-headline-lg text-headline-lg text-primary mb-1">Thông báo</h2>
<p class="text-on-surface-variant text-body-sm">{{ $items->total() }} thông báo · {{ $unreadCount ?? auth()->user()->unreadNotifications()->count() }} chưa đọc</p>
</div>
<div class="flex items-center gap-2 flex-wrap">
@if (($unreadCount ?? auth()->user()->unreadNotifications()->count()) > 0)
<button type="button" data-tb-mark-all class="px-4 py-2 text-body-sm font-semibold bg-surface-container-lowest border border-outline-variant rounded-lg text-on-surface-variant hover:bg-surface-container-low transition-colors flex items-center gap-2 whitespace-nowrap">
<span class="material-symbols-outlined text-[18px]">done_all</span> Đánh dấu tất cả đã đọc
</button>
@endif
@if ($items->total() > 0)
<button type="button" data-tb-hide-all class="px-4 py-2 text-body-sm font-semibold bg-surface-container-lowest border border-outline-variant rounded-lg text-error hover:bg-error/10 transition-colors flex items-center gap-2 whitespace-nowrap">
<span class="material-symbols-outlined text-[18px]">delete_sweep</span> Xóa tất cả
</button>
@endif
</div>
</div>

@if ($items->isEmpty())
<div class="bg-surface-container-lowest border border-outline-variant rounded-2xl p-12 text-center">
<span class="material-symbols-outlined text-[64px] text-on-surface-variant/40">notifications_off</span>
<p class="mt-3 text-on-surface-variant">Chưa có thông báo nào.</p>
</div>
@else
<ul class="space-y-2">
@foreach ($items as $n)
@php
    $isRead = (bool) $n->read_at;
    $event = $n->data['event'] ?? '';
    $iconMap = [
        'tao_moi'  => ['add_circle',     'text-blue-600',    'bg-blue-100'],
        'duyet'    => ['check_circle',   'text-emerald-600', 'bg-emerald-100'],
        'tu_choi'  => ['cancel',         'text-red-600',     'bg-red-100'],
        'cap_nhat' => ['edit',           'text-amber-600',   'bg-amber-100'],
        'huy'      => ['delete',         'text-red-500',     'bg-red-50'],
        'nhac_hen' => ['alarm',          'text-secondary',   'bg-secondary-container/20'],
    ];
    [$icon, $iconColor, $iconBg] = $iconMap[$event] ?? ['notifications', 'text-on-surface-variant', 'bg-surface-container-low'];
@endphp
<li data-tb-item="{{ $n->id }}" class="bg-surface-container-lowest border border-outline-variant rounded-xl p-4 flex items-start gap-3 {{ $isRead ? '' : 'border-l-4 border-l-secondary' }}">
<div class="w-10 h-10 rounded-full {{ $iconBg }} flex items-center justify-center shrink-0">
<span class="material-symbols-outlined text-[22px] {{ $iconColor }}">{{ $icon }}</span>
</div>
<div class="flex-1 min-w-0">
<div class="flex items-start justify-between gap-3">
<a href="{{ $n->data['link'] ?? '#' }}" data-id="{{ $n->id }}" data-mark="1" class="text-body-md font-semibold text-on-surface hover:text-secondary transition-colors">
{{ $n->data['tieu_de'] ?? 'Thông báo' }}
</a>
<span class="text-[11px] text-on-surface-variant whitespace-nowrap shrink-0">{{ $n->created_at->diffForHumans() }}</span>
</div>
<p class="text-body-sm text-on-surface-variant mt-1">{{ $n->data['noi_dung'] ?? '' }}</p>
@if (! empty($n->data['ghi_chu']))
<p class="text-body-sm text-on-surface-variant mt-1 italic">Ghi chú: {{ $n->data['ghi_chu'] }}</p>
@endif
@if (! empty($n->data['actor']))
<p class="text-[11px] text-on-surface-variant/70 mt-1">Bởi: {{ $n->data['actor'] }}</p>
@endif
</div>
<div class="flex flex-col items-center gap-2 shrink-0">
@if (! $isRead)
<button data-mark="1" data-id="{{ $n->id }}" class="w-2 h-2 rounded-full bg-secondary" title="Chưa đọc"></button>
@endif
<button type="button" data-tb-hide="{{ $n->id }}" class="w-7 h-7 rounded-full text-on-surface-variant hover:bg-error/10 hover:text-error transition-colors flex items-center justify-center" title="Xóa thông báo này">
<span class="material-symbols-outlined text-[18px]">close</span>
</button>
</div>
</li>
@endforeach
</ul>

<div class="mt-6">{{ $items->links() }}</div>
@endif
</main>

<script>
(function () {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content
        || '{{ csrf_token() }}';
    const headers = { 'X-CSRF-TOKEN': csrf, Accept: 'application/json' };

    // Click title/link → mark read
    document.querySelectorAll('[data-mark="1"][data-id]').forEach(el => {
        el.addEventListener('click', () => {
            fetch(`/thong-bao/${el.dataset.id}/read`, { method: 'POST', headers }).catch(() => {});
        });
    });

    // X từng item
    document.querySelectorAll('[data-tb-hide]').forEach(btn => {
        btn.addEventListener('click', e => {
            e.preventDefault();
            const id = btn.dataset.tbHide;
            fetch(`/thong-bao/${id}`, { method: 'DELETE', headers })
                .then(r => { if (r.ok) document.querySelector(`[data-tb-item="${id}"]`)?.remove(); })
                .catch(() => {});
        });
    });

    // Xóa tất cả
    document.querySelector('[data-tb-hide-all]')?.addEventListener('click', () => {
        if (! confirm('Xóa tất cả thông báo? (Admin vẫn xem được trong nhật ký)')) return;
        fetch('/thong-bao/hide-all', { method: 'DELETE', headers })
            .then(r => { if (r.ok) location.reload(); })
            .catch(() => {});
    });

    // Đánh dấu tất cả đã đọc
    document.querySelector('[data-tb-mark-all]')?.addEventListener('click', () => {
        fetch('/thong-bao/mark-all-read', { method: 'POST', headers })
            .then(r => { if (r.ok) location.reload(); })
            .catch(() => {});
    });
})();
</script>
</body></html>
