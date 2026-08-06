{{--
    1 ô filter chuẩn cho <x-longevity.filter-bar>: label + slot input.
    Input height 40px, cùng bg + border với các field khác.

    Props:
      - label (required): text label (uppercase, caps).
      - span (optional): số cột chiếm (1..4). Mặc định 1.
      - hint (optional): text nhỏ dưới field.
--}}
@props(['label', 'span' => 1, 'hint' => null])
@php
    $spanCls = match ((int) $span) {
        2 => 'sm:col-span-2',
        3 => 'sm:col-span-2 lg:col-span-3',
        4 => 'sm:col-span-2 lg:col-span-4',
        default => '',
    };
@endphp
<div class="flex flex-col gap-1.5 {{ $spanCls }}">
    <label class="text-label-caps font-label-caps text-on-surface-variant ml-0.5">{{ $label }}</label>
    {{ $slot }}
    @if ($hint)
        <p class="text-[11px] text-on-surface-variant/70 ml-0.5">{{ $hint }}</p>
    @endif
</div>
