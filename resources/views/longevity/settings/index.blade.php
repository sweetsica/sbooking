@extends('longevity.settings.layout')
@section('title', 'Thiết lập')

@php
    // 2026-08-04 (SCRM T6.3): Tab hóa 9 sections + 2 admin-only thành 4 nhóm.
    // Nguyên tắc: 1 tính năng chỉ 1 chỗ, mỗi tab 1 nhóm rõ.
    $isAdmin = auth()->user()?->is_admin;

    // Map: section_key => tab_group
    $tabMap = [
        // Tab 1: Tổ chức
        'phong-ban'  => 'org',
        'vai-tro'    => 'org',
        'co-so'      => 'org',
        'nguoi-dung' => 'org',
        'quyen'      => 'org',
        // Tab 2: Danh mục
        'dich-vu' => 'catalog',
        'menu'    => 'catalog',
        'phong'   => 'catalog',
        // Tab 3: Báo cáo
        'bao-cao' => 'report',
    ];

    $tabs = [
        'org'     => ['label' => 'Tổ chức', 'desc' => 'Phòng ban, vai trò, cơ sở, người dùng & phân quyền.', 'items' => []],
        'catalog' => ['label' => 'Danh mục', 'desc' => 'Liệu pháp, menu ăn kèm, phòng chức năng.', 'items' => []],
        'report'  => ['label' => 'Báo cáo', 'desc' => 'Tổng hợp lịch + xuất Excel.', 'items' => []],
        'system'  => ['label' => 'Hệ thống', 'desc' => 'Nhật ký thông báo & kết nối SCRM (chỉ admin).', 'items' => []],
    ];

    foreach ($sections as $key => [$ten, $icon, $mota]) {
        $group = $tabMap[$key] ?? 'catalog';
        $tabs[$group]['items'][] = [
            'href' => "/{$coSo->slug}/thiet-lap/{$key}",
            'label' => $ten, 'icon' => $icon, 'desc' => $mota,
        ];
    }

    // Admin-only: 2 mục Hệ thống
    if ($isAdmin) {
        $tabs['system']['items'][] = [
            'href' => route('settings.notification-log', $coSo),
            'label' => 'Nhật ký thông báo', 'icon' => 'history',
            'desc' => 'Toàn bộ thông báo đã gửi cho người dùng — kể cả những cái họ đã ẩn.',
        ];
        $tabs['system']['items'][] = [
            'href' => route('settings.scrm-connection.edit', $coSo),
            'label' => 'Kết nối SCRM', 'icon' => 'cable',
            'desc' => 'Whitelist host được phép nhận callback sau khi đặt lịch (chống open-redirect).',
        ];
    }

    // Ẩn tab không có item nào (permission lọc sections ở controller)
    $tabs = array_filter($tabs, fn ($t) => ! empty($t['items']));
@endphp

@section('content')
<div x-data="{ tab: '{{ array_key_first($tabs) ?? 'org' }}' }">
    <div class="flex items-center gap-3 mb-2">
        <span class="material-symbols-outlined text-secondary text-[28px]">settings</span>
        <h2 class="text-headline-lg font-headline-lg">Thiết lập hệ thống</h2>
    </div>
    <p class="text-body-md text-on-surface-variant mb-6">Cấu hình cho <strong>{{ $coSo->ten }}</strong>.</p>

    @if (empty($tabs))
        <p class="text-body-md text-on-surface-variant bg-surface-container-lowest border border-outline-variant rounded-xl p-8 text-center">Bạn chưa có quyền truy cập mục thiết lập nào.</p>
    @else
        {{-- Tab bar --}}
        <div class="flex flex-wrap gap-1 border-b border-outline-variant mb-5">
            @foreach ($tabs as $key => $tab)
                <button @click="tab = '{{ $key }}'" type="button"
                        :class="tab === '{{ $key }}' ? 'border-secondary text-secondary' : 'border-transparent text-on-surface-variant hover:text-on-surface'"
                        class="text-body-md font-semibold px-4 py-2.5 border-b-2 -mb-px transition">
                    {{ $tab['label'] }}
                    <span class="ml-1.5 text-body-sm bg-surface-container-low text-on-surface-variant px-1.5 py-0.5 rounded">{{ count($tab['items']) }}</span>
                </button>
            @endforeach
        </div>

        @foreach ($tabs as $key => $tab)
            <div x-show="tab === '{{ $key }}'" x-cloak>
                <p class="text-body-sm text-on-surface-variant mb-4">{{ $tab['desc'] }}</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach ($tab['items'] as $it)
                        <a href="{{ $it['href'] }}"
                           class="group bg-surface-container-lowest border border-outline-variant rounded-xl p-5 hover:shadow-lg hover:border-secondary/40 transition-all">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 rounded-xl bg-secondary-container/40 text-on-secondary-container flex items-center justify-center group-hover:scale-110 transition-transform shrink-0">
                                    <span class="material-symbols-outlined">{{ $it['icon'] }}</span>
                                </div>
                                <div>
                                    <h3 class="text-headline-md font-headline-md mb-1">{{ $it['label'] }}</h3>
                                    <p class="text-body-sm text-on-surface-variant">{{ $it['desc'] }}</p>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endforeach
    @endif
</div>
@endsection
