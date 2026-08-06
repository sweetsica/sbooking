{{--
    Filter bar chuẩn Material 3 dùng chung cho các trang list sbooking.
    Cấu trúc: form GET → grid fields (mặc định 4 cột responsive) → action row cuối form.

    Slots:
      - default: các <x-longevity.filter-field> hoặc div .col-span-* tuỳ ý.
      - actions: button row (căn trái primary, xuất Excel căn phải).
      - toolbar (optional): 1 hàng riêng phía trên grid — dùng cho preset chip Ngày/Tuần/Tháng.

    Props:
      - action (string, optional): URL form submit (mặc định URL hiện tại).
      - cols (int, optional): số cột grid ở breakpoint lg (2..6, mặc định 4).
--}}
@props([
    'action' => null,
    'cols' => 4,
    'toolbar' => null,
    'actions' => null,
])
@php
    $lgCols = in_array((int) $cols, [2, 3, 4, 5, 6], true) ? (int) $cols : 4;
    $gridCls = 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-' . $lgCols . ' gap-3 items-end';
@endphp
<form method="GET" @if ($action) action="{{ $action }}" @endif
      class="mb-6 bg-surface-container-lowest border border-outline-variant rounded-2xl shadow-sm p-5 sm:p-6 space-y-4">
    @if ($toolbar)
        <div class="flex flex-wrap items-center gap-2 pb-3 border-b border-outline-variant/40">
            {{ $toolbar }}
        </div>
    @endif

    <div class="{{ $gridCls }}">
        {{ $slot }}
    </div>

    @if ($actions)
        <div class="flex flex-wrap items-center gap-2 pt-3 border-t border-outline-variant/40">
            {{ $actions }}
        </div>
    @endif
</form>
