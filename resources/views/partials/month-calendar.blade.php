@php
    /**
     * Lưới lịch tháng dùng chung (trang Bác sĩ + Lịch hẹn).
     * Biến vào: $cells, $monthStart, $linkBase, (tùy chọn) $extra, $unit, $accent.
     *  - $linkBase: vd "/59ntn/bac-si"
     *  - $extra:    query thêm, vd "&phong_id=5" (đã có dấu &)
     *  - $unit:     nhãn đơn vị mỗi ô, vd "lịch"
     */
    $extra = $extra ?? '';
    $unit = $unit ?? 'lịch';
    $prev = $monthStart->copy()->subMonthNoOverflow();
    $next = $monthStart->copy()->addMonthNoOverflow();
    $weekdays = ['Thứ Hai', 'Thứ Ba', 'Thứ Tư', 'Thứ Năm', 'Thứ Sáu', 'Thứ Bảy', 'Chủ Nhật'];
@endphp
<div class="bg-surface-container-lowest rounded-xl border border-outline-variant overflow-hidden shadow-sm">
    <!-- Thanh điều hướng tháng -->
    <div class="flex items-center justify-between gap-4 p-4 border-b border-outline-variant bg-surface-container-low">
        <a href="{{ $linkBase }}?ngay={{ $prev->format('Y-m-d') }}&view=thang{{ $extra }}"
           class="w-9 h-9 rounded-full flex items-center justify-center text-on-surface-variant hover:bg-surface-container-high transition-colors" title="Tháng trước">
            <span class="material-symbols-outlined">chevron_left</span>
        </a>
        <h3 class="font-headline-md text-headline-md text-on-surface">Tháng {{ $monthStart->format('m / Y') }}</h3>
        <a href="{{ $linkBase }}?ngay={{ $next->format('Y-m-d') }}&view=thang{{ $extra }}"
           class="w-9 h-9 rounded-full flex items-center justify-center text-on-surface-variant hover:bg-surface-container-high transition-colors" title="Tháng sau">
            <span class="material-symbols-outlined">chevron_right</span>
        </a>
    </div>
    <!-- Tên thứ -->
    <div class="grid grid-cols-7 border-b border-outline-variant bg-surface-container-low/40">
        @foreach ($weekdays as $i => $wd)
            <div class="py-2.5 text-center text-label-caps font-label-caps {{ $i >= 5 ? 'text-secondary' : 'text-on-surface-variant' }}">{{ $wd }}</div>
        @endforeach
    </div>
    <!-- Các ô ngày -->
    <div class="grid grid-cols-7">
        @foreach ($cells as $c)
            @php
                $has = $c['count'] > 0;
                if (! $c['inMonth']) {
                    $tone = 'bg-surface-container-low/30 text-on-surface-variant/40';
                } elseif ($has) {
                    $tone = 'bg-amber-50 hover:bg-amber-100';
                } else {
                    $tone = 'bg-surface hover:bg-surface-container-low';
                }
            @endphp
            <a href="{{ $linkBase }}?ngay={{ $c['date']->format('Y-m-d') }}&view=ngay{{ $extra }}"
               class="relative block min-h-[92px] p-2 border-b border-r border-outline-variant/60 transition-colors {{ $tone }} {{ $c['isSelected'] ? 'ring-2 ring-inset ring-secondary' : '' }}">
                <div class="flex items-center justify-between">
                    <span class="flex items-center justify-center text-body-sm leading-none {{ $c['isToday'] ? 'w-6 h-6 rounded-full bg-secondary text-on-secondary font-bold' : 'text-on-surface font-semibold' }}">{{ $c['date']->format('j') }}</span>
                    @if ($c['isToday'])
                        <span class="material-symbols-outlined text-secondary text-[16px]">today</span>
                    @endif
                </div>
                @if ($has)
                    <div class="absolute left-2 right-2 bottom-2 text-right">
                        <span class="text-time-slot font-time-slot text-on-surface">{{ $c['count'] }} {{ $unit }}</span>
                    </div>
                @endif
            </a>
        @endforeach
    </div>
</div>
