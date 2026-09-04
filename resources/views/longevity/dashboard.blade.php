<!DOCTYPE html>
<html class="light" lang="vi">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<meta name="csrf-token" content="{{ csrf_token() }}"/>
<title>Dashboard đặt lịch — {{ $coSo->ten }}</title>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&amp;family=Inter:wght@400;500;600&amp;family=JetBrains+Mono:wght@500;600&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<script>
    tailwind.config = {
        darkMode: 'class',
        theme: { extend: {
            colors: {
                'surface': '#f7f9fb', 'on-surface': '#191c1e', 'on-surface-variant': '#45464d',
                'surface-container-lowest': '#ffffff', 'surface-container-low': '#f2f4f6',
                'outline-variant': '#c6c6cd', 'secondary': '#006591', 'primary': '#000000',
                'on-primary': '#ffffff',
            },
            spacing: { 'container-margin': '24px' },
        }},
    };
</script>
<style>
    body { font-family: 'Inter', sans-serif; background-color: #f7f9fb; }
    .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; vertical-align: middle; }
    [x-cloak] { display: none; }
</style>
</head>
<body class="bg-surface text-on-surface">
@include('partials.topnav', ['active' => 'lich-hen'])

<main class="pt-24 pb-12 px-container-margin">
<div class="max-w-[1650px] mx-auto">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div>
            <h1 class="text-2xl font-extrabold uppercase tracking-tight">Dashboard đặt lịch</h1>
            <p class="text-sm text-on-surface-variant mt-0.5">
                {{ $coSo->ten }} — {{ now()->format('d/m/Y') }}
                · <span data-server-time>{{ now()->format('H:i:s') }}</span>
                · <span class="text-emerald-600" title="Tự cập nhật mỗi 15 giây">● Live</span>
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="/{{ $coSo->slug }}/lich-hen/timeline" class="text-sm font-semibold text-secondary border border-secondary/40 hover:bg-secondary/10 px-4 py-2 rounded-lg inline-flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[18px]">calendar_month</span> Xem lịch trình
            </a>
            <a href="/{{ $coSo->slug }}/danh-sach" class="text-sm font-semibold text-white bg-secondary hover:bg-secondary/90 px-4 py-2 rounded-lg inline-flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[18px]">list_alt</span> Danh sách đầy đủ
            </a>
        </div>
    </div>

    {{-- 4 widget --}}
    @php
        // 2026-08-19: label widget "Lịch hôm nay" đổi thành "Lịch ngày DD/MM" khi user chọn ngày khác.
        $__ngayLabel = ($ngay ?? '') !== '' ? \Carbon\Carbon::parse($ngay)->format('d/m') : 'hôm nay';
        $widgets = [
            ['key'=>'today',      'label'=>'Lịch ' . $__ngayLabel,     'desc'=>'Tất cả booking ngày ' . $__ngayLabel,                  'value'=>$todayCount,      'color'=>'blue',    'icon'=>'today'],
            ['key'=>'approval',   'label'=>'Lịch chờ duyệt',          'desc'=>'Tất cả booking đang trạng thái "Chờ duyệt" (mọi ngày)','value'=>$approvalCount,   'color'=>'rose',    'icon'=>'pending_actions'],
            ['key'=>'processing', 'label'=>'Đang xử lý',              'desc'=>'Khách đã tới — đang tiếp đón / khám',                  'value'=>$processingCount, 'color'=>'amber',   'icon'=>'schedule'],
            ['key'=>'upcoming',   'label'=>'Sắp tới (trong 1 giờ)',   'desc'=>'Đã duyệt, giờ hẹn trong vòng 60 phút tới',             'value'=>$upcomingCount,   'color'=>'violet',  'icon'=>'alarm'],
            ['key'=>'done',       'label'=>'Đã hoàn thành',           'desc'=>'Đã khám xong / kết thúc buổi',                          'value'=>$doneCount,       'color'=>'emerald', 'icon'=>'task_alt'],
            // 2026-09-04: widget "Đã từ chối" — booking bị Admin bấm Từ chối trong 7 ngày qua.
            ['key'=>'rejected',   'label'=>'Đã từ chối (7 ngày)',     'desc'=>'Booking bị từ chối gần đây — gọi lại khách / đặt lại',  'value'=>$rejectedCount,   'color'=>'red',     'icon'=>'cancel'],
        ];
        $colorMap = [
            'blue'    => ['bg'=>'bg-blue-50','border'=>'border-blue-200','ring'=>'ring-blue-400','text'=>'text-blue-700'],
            'rose'    => ['bg'=>'bg-rose-50','border'=>'border-rose-200','ring'=>'ring-rose-400','text'=>'text-rose-700'],
            'amber'   => ['bg'=>'bg-amber-50','border'=>'border-amber-200','ring'=>'ring-amber-400','text'=>'text-amber-700'],
            'violet'  => ['bg'=>'bg-violet-50','border'=>'border-violet-200','ring'=>'ring-violet-400','text'=>'text-violet-700'],
            'emerald' => ['bg'=>'bg-emerald-50','border'=>'border-emerald-200','ring'=>'ring-emerald-400','text'=>'text-emerald-700'],
            'red'     => ['bg'=>'bg-red-50','border'=>'border-red-300','ring'=>'ring-red-500','text'=>'text-red-800'],
        ];
    @endphp
    <div class="grid grid-cols-2 lg:grid-cols-6 gap-3 mb-6">
        @php
            // 2026-09-04: widget link phải carry loai/nhom/ngay/q để đang ở "Lịch tư vấn"
            // click "Lịch chờ duyệt" không bị nhảy về "Lịch khám" (default kieu_phong).
            $__widgetCarry = array_filter([
                'loai' => $loai ?? null,
                'nhom' => $nhom ?? null,
                'ngay' => ($ngay ?? '') ?: null,
                'q'    => ($search ?? '') ?: null,
            ], fn ($v) => $v !== null && $v !== '');
        @endphp
        @foreach ($widgets as $w)
            @php
                $c = $colorMap[$w['color']]; $active = $tab === $w['key'];
                $__widgetQs = http_build_query(['tab' => $w['key']] + $__widgetCarry);
            @endphp
            <a href="?{{ $__widgetQs }}" class="block border-2 rounded-xl p-4 transition-all {{ $c['bg'] }} {{ $active ? $c['ring'].' ring-2 '.$c['border'] : $c['border'].' hover:'.$c['ring'].' hover:ring-2' }}">
                <div class="flex items-start justify-between mb-2">
                    <span class="material-symbols-outlined {{ $c['text'] }} opacity-80">{{ $w['icon'] }}</span>
                    @if ($active) <span class="text-[10px] font-bold uppercase {{ $c['text'] }} opacity-70">Đang xem</span> @endif
                </div>
                <div class="text-3xl font-extrabold tabular-nums {{ $c['text'] }} leading-none mb-1" data-count="{{ $w['key'] }}">{{ number_format($w['value']) }}</div>
                <div class="text-sm font-bold {{ $c['text'] }}">{{ $w['label'] }}</div>
                <div class="text-xs text-on-surface-variant opacity-70 mt-0.5 leading-snug">{{ $w['desc'] }}</div>
            </a>
        @endforeach
    </div>

    {{-- 2026-08-10: filter nhóm dịch vụ (tất cả / tư vấn / thăm khám). Giữ tab hiện tại khi đổi nhóm.
         2026-08-19: thêm search (mã ĐL / tên / SĐT) + date picker ở bên phải. Đổi ngày → widget filter theo ngày đó. --}}
    @php
        $nhom = $nhom ?? null;
        $searchQ = $search ?? '';
        $ngayFilter = $ngay ?? '';
        // Query base: giữ tab + loai + nhom + ngay + search khi build link nhóm.
        $qsCarry = ['tab' => $tab, 'loai' => $loai ?? null, 'ngay' => $ngayFilter ?: null, 'q' => $searchQ ?: null];
        $qsCarry = array_filter($qsCarry, fn ($v) => $v !== null && $v !== '');
        $baseQs = http_build_query($qsCarry);
        $nhomBtns = [
            ''        => ['Tất cả',   'apps'],
            'tu_van'  => ['Tư vấn',   'chat'],
            'kham_ls' => ['Thăm khám','medical_information'],
        ];
    @endphp
    <div class="flex items-center gap-2 mb-3 flex-wrap justify-between">
        <div class="flex items-center gap-2 flex-wrap">
            @foreach ($nhomBtns as $k => [$lb, $ico])
                @php
                    $on = ($nhom ?? '') === $k;
                    $href = '?' . $baseQs . ($k ? '&nhom=' . $k : '');
                @endphp
                <a href="{{ $href }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border text-body-sm transition-colors {{ $on ? 'bg-secondary-container text-on-secondary-container border-secondary font-semibold' : 'bg-surface-container-lowest text-on-surface-variant border-outline-variant hover:bg-surface-container-low' }}">
                    <span class="material-symbols-outlined text-[18px]">{{ $ico }}</span> {{ $lb }}
                </a>
            @endforeach
        </div>

        {{-- 2026-08-19: search + date filter (bên phải). Đổi date → reload trang, widget cũng filter theo ngày đó. --}}
        <form method="GET" class="flex items-center gap-2 flex-wrap">
            @if ($loai ?? null)<input type="hidden" name="loai" value="{{ $loai }}"/>@endif
            @if ($nhom)<input type="hidden" name="nhom" value="{{ $nhom }}"/>@endif
            @if ($tab)<input type="hidden" name="tab" value="{{ $tab }}"/>@endif
            <div class="relative">
                <span class="material-symbols-outlined absolute left-2 top-1/2 -translate-y-1/2 text-[18px] text-on-surface-variant/60">search</span>
                <input type="text" name="q" value="{{ $searchQ }}" placeholder="Mã ĐL / tên / SĐT"
                       class="pl-9 pr-3 py-1.5 rounded-lg border border-outline-variant text-body-sm bg-surface-container-lowest focus:outline-none focus:border-primary w-56"/>
            </div>
            <input type="date" name="ngay" value="{{ $ngayFilter }}"
                   onchange="this.form.submit()"
                   class="px-3 py-1.5 rounded-lg border border-outline-variant text-body-sm bg-surface-container-lowest focus:outline-none focus:border-primary"
                   title="Xem lịch của ngày khác — widget bên trên cũng sẽ lọc theo ngày này"/>
            <button type="submit" class="px-3 py-1.5 rounded-lg bg-primary text-on-primary text-body-sm font-semibold hover:opacity-90">Tìm</button>
            @if ($searchQ || $ngayFilter)
                @php $clearQs = http_build_query(array_filter(['tab' => $tab, 'loai' => $loai ?? null, 'nhom' => $nhom])); @endphp
                <a href="?{{ $clearQs }}" class="px-2 py-1.5 text-body-sm text-on-surface-variant hover:text-error" title="Xoá lọc">✕</a>
            @endif
        </form>
    </div>

    {{-- List booking --}}
    <div class="bg-surface-container-lowest border border-outline-variant rounded-xl overflow-hidden">
        <div class="px-5 py-3 border-b border-outline-variant flex items-center justify-between">
            <h2 class="font-bold text-base">
                {{ ['today'=>'Danh sách lịch hẹn (mới nhất trên đầu)','approval'=>'Lịch chờ duyệt','processing'=>'Đang xử lý','upcoming'=>'Sắp tới trong 1 giờ','done'=>'Đã hoàn thành'][$tab] ?? 'Danh sách' }}
                @if ($nhom)<span class="ml-1 text-xs bg-secondary-container/40 text-on-secondary-container px-2 py-0.5 rounded">{{ $nhom === 'tu_van' ? 'Tư vấn' : 'Thăm khám' }}</span>@endif
                <span class="ml-2 text-xs bg-surface-container-low text-on-surface-variant px-2 py-0.5 rounded">{{ $bookings->count() }}</span>
            </h2>
            @if ($tab !== 'today')
                @php $backHref = '?tab=today' . ($nhom ? '&nhom=' . $nhom : ''); @endphp
                <a href="{{ $backHref }}" class="text-xs text-secondary hover:underline">← Về tất cả lịch hẹn</a>
            @endif
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="text-xs uppercase tracking-wider text-on-surface-variant bg-surface-container-low">
                    <tr class="text-left">
                        <th class="px-4 py-2.5 font-semibold w-12">STT</th>
                        <th class="px-4 py-2.5 font-semibold">Mã ĐL</th>
                        <th class="px-4 py-2.5 font-semibold">Tên khách</th>
                        <th class="px-4 py-2.5 font-semibold">SĐT</th>
                        <th class="px-4 py-2.5 font-semibold">Sale chăm sóc</th>
                        <th class="px-4 py-2.5 font-semibold">Danh mục</th>
                        @php
                            // 2026-08-19: cột "Giờ hẹn" sortable — toggle asc ↔ desc qua query ?sort=gio_asc|gio_desc.
                            //   default = giữ order hiện tại (id desc) — không sort.
                            $__sort = request('sort');
                            $__nextSort = $__sort === 'gio_asc' ? 'gio_desc' : 'gio_asc';
                            $__arrow = $__sort === 'gio_asc' ? '▲' : ($__sort === 'gio_desc' ? '▼' : '⇅');
                            $__sortColor = $__sort ? 'text-primary' : 'text-on-surface-variant/50';
                            $__sortUrl = request()->fullUrlWithQuery(['sort' => $__nextSort]);
                        @endphp
                        <th class="px-4 py-2.5 font-semibold">
                            <a href="{{ $__sortUrl }}" class="inline-flex items-center gap-1 hover:text-primary transition-colors" title="Bấm để sắp xếp {{ $__nextSort === 'gio_asc' ? 'giờ sớm → muộn' : 'giờ muộn → sớm' }}">
                                Giờ hẹn <span class="text-[10px] {{ $__sortColor }}">{{ $__arrow }}</span>
                            </a>
                        </th>
                        <th class="px-4 py-2.5 font-semibold">Trạng thái</th>
                        <th class="px-4 py-2.5 font-semibold">Kết quả</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/60" data-bookings-tbody>
                    @forelse ($bookings as $i => $b)
                        @php
                            // 2026-08-19 Phase B: 3 loại — Khám LS / Tư vấn / Dịch vụ.
                            [$loai, $loaiClass] = match ($b->loai_dat_lich) {
                                'kham_ls' => ['🩺 Khám LS',   'bg-sky-50 text-sky-700'],
                                'tu_van'  => ['💬 Tư vấn',    'bg-indigo-50 text-indigo-700'],
                                'dich_vu' => ['💆 Dịch vụ',   'bg-fuchsia-50 text-fuchsia-700'],
                                default   => ['❔ ' . $b->loai_dat_lich, 'bg-slate-100 text-slate-700'],
                            };
                            $st = $b->trang_thai;
                            $stKh = $b->trang_thai_khach;
                            // 2026-08-19: tách 2 cột.
                            //   Trạng thái (approval): Chờ duyệt / Đã duyệt / Từ chối.
                            //     Nếu đã da_xong → coi như đã duyệt xong.
                            //   Kết quả (outcome): Trễ / Hủy / Đã xong. Chưa có → "—".
                            $stApproval = match ($st) {
                                'cho_duyet' => ['⏳ Chờ duyệt', 'bg-amber-100 text-amber-700'],
                                'da_duyet'  => ['✅ Đã duyệt', 'bg-emerald-50 text-emerald-700'],
                                'tu_choi'   => ['❌ Từ chối',  'bg-rose-100 text-rose-700'],
                                'da_xong'   => ['✅ Đã duyệt', 'bg-emerald-50 text-emerald-700'],
                                default     => [$st ?? '—',    'bg-gray-100 text-gray-600'],
                            };
                            $stResult = match (true) {
                                $st === 'da_xong'   => ['🏁 Đã xong', 'bg-emerald-100 text-emerald-700'],
                                $stKh === 'toi_tre' => ['⏰ Trễ',    'bg-orange-100 text-orange-700'],
                                $stKh === 'huy'     => ['🚫 Hủy',    'bg-red-100 text-red-700'],
                                default             => ['—',         'bg-transparent text-on-surface-variant/50'],
                            };
                            // Bôi màu dịu cả dòng theo trạng thái khách (đã tới / trễ / hủy).
                            $rowTint = match ($stKh) {
                                'da_toi'  => 'bg-green-50/70 hover:bg-green-100/70',
                                'toi_tre' => 'bg-amber-50/70 hover:bg-amber-100/70',
                                'huy'     => 'bg-red-50/70 hover:bg-red-100/70',
                                default   => 'hover:bg-surface-container-low',
                            };
                        @endphp
                        <tr class="{{ $rowTint }} cursor-pointer" onclick="window.location='/{{ $coSo->slug }}/xem-dat-phong/{{ $b->id }}'">
                            <td class="px-4 py-2.5 text-on-surface-variant">{{ $i + 1 }}</td>
                            <td class="px-4 py-2.5 font-mono text-xs text-secondary">{{ $b->ma_booking ?? '#'.$b->id }}</td>
                            <td class="px-4 py-2.5 font-semibold">{{ $b->khachHang?->ho_ten ?? '—' }}</td>
                            <td class="px-4 py-2.5 font-mono text-xs">{{ $b->khachHang?->so_dien_thoai ?? '—' }}</td>
                            <td class="px-4 py-2.5 text-on-surface-variant">{{ $b->sale?->name ?? '—' }}</td>
                            <td class="px-4 py-2.5">
                                <span class="text-xs px-2 py-0.5 rounded {{ $loaiClass }}">{{ $loai }}</span>
                                @if ($b->dichVu?->ten) <span class="text-xs text-on-surface-variant ml-1">· {{ $b->dichVu->ten }}</span> @endif
                            </td>
                            <td class="px-4 py-2.5 font-mono text-xs">{{ $b->gio_thuc_hien ? substr($b->gio_thuc_hien, 0, 5) : '—' }}</td>
                            <td class="px-4 py-2.5">
                                <span class="text-xs font-semibold px-2 py-0.5 rounded {{ $stApproval[1] }}">{{ $stApproval[0] }}</span>
                            </td>
                            <td class="px-4 py-2.5">
                                <span class="text-xs font-semibold px-2 py-0.5 rounded {{ $stResult[1] }}">{{ $stResult[0] }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-12 text-center text-on-surface-variant italic">
                                Không có lịch nào khớp bộ lọc.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
</main>

<script>
(function () {
    // 2026-08-05: bán real-time — fetch stats mỗi 15s, update DOM in-place (không reload).
    const slug = @json($coSo->slug);
    const currentTab = @json($tab);
    const url = `/${slug}/lich-hen?tab=${encodeURIComponent(currentTab)}&json=1`;

    // 2026-08-19: tách 2 badge — approval + result. Mirror server-side logic.
    const approvalBadge = (t) => {
        if (t === 'cho_duyet') return ['⏳ Chờ duyệt', 'bg-amber-100 text-amber-700'];
        if (t === 'da_duyet' || t === 'da_xong') return ['✅ Đã duyệt', 'bg-emerald-50 text-emerald-700'];
        if (t === 'tu_choi')   return ['❌ Từ chối',  'bg-rose-100 text-rose-700'];
        return [t || '—', 'bg-gray-100 text-gray-600'];
    };
    const resultBadge = (t, tk) => {
        if (t === 'da_xong')  return ['🏁 Đã xong', 'bg-emerald-100 text-emerald-700'];
        if (tk === 'toi_tre') return ['⏰ Trễ',    'bg-orange-100 text-orange-700'];
        if (tk === 'huy')     return ['🚫 Hủy',    'bg-red-100 text-red-700'];
        return ['—', 'bg-transparent text-on-surface-variant/50'];
    };
    const rowTintClass = (tk) => {
        if (tk === 'da_toi')  return 'bg-green-50/70 hover:bg-green-100/70';
        if (tk === 'toi_tre') return 'bg-amber-50/70 hover:bg-amber-100/70';
        if (tk === 'huy')     return 'bg-red-50/70 hover:bg-red-100/70';
        return 'hover:bg-surface-container-low';
    };

    // 2026-08-19 Phase B: 3 loại — Khám LS / Tư vấn / Dịch vụ. 'phong_kham' cũ fallback.
    const loaiBadge = (loai) => {
        if (loai === 'kham_ls' || loai === 'phong_kham') return ['🩺 Khám LS', 'bg-sky-50 text-sky-700'];
        if (loai === 'tu_van') return ['💬 Tư vấn', 'bg-indigo-50 text-indigo-700'];
        if (loai === 'dich_vu') return ['💆 Dịch vụ', 'bg-fuchsia-50 text-fuchsia-700'];
        return ['❔ ' + (loai || '—'), 'bg-slate-100 text-slate-700'];
    };

    const esc = (s) => String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));

    async function refresh() {
        try {
            const r = await fetch(url, { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
            if (! r.ok) return;
            const d = await r.json();

            // Update 4 counter
            for (const [k, v] of Object.entries(d.counts || {})) {
                const el = document.querySelector(`[data-count="${k}"]`);
                if (el) el.textContent = new Intl.NumberFormat().format(v);
            }

            // Update clock
            const clock = document.querySelector('[data-server-time]');
            if (clock && d.server_time) clock.textContent = d.server_time;

            // Update bookings table
            const tbody = document.querySelector('[data-bookings-tbody]');
            if (! tbody) return;
            if (! d.bookings || d.bookings.length === 0) {
                tbody.innerHTML = `<tr><td colspan="9" class="px-4 py-12 text-center text-on-surface-variant italic">Không có lịch nào khớp bộ lọc.</td></tr>`;
                return;
            }
            tbody.innerHTML = d.bookings.map((b, i) => {
                const [apLabel, apClass] = approvalBadge(b.trang_thai);
                const [rsLabel, rsClass] = resultBadge(b.trang_thai, b.trang_thai_khach);
                const [lLabel, lClass] = loaiBadge(b.loai);
                const dv = b.dich_vu ? `<span class="text-xs text-on-surface-variant ml-1">· ${esc(b.dich_vu)}</span>` : '';
                return `
                <tr class="${rowTintClass(b.trang_thai_khach)} cursor-pointer" onclick="window.location='${esc(b.url)}'">
                    <td class="px-4 py-2.5 text-on-surface-variant">${i + 1}</td>
                    <td class="px-4 py-2.5 font-mono text-xs text-secondary">${esc(b.ma_booking || ('#' + b.id))}</td>
                    <td class="px-4 py-2.5 font-semibold">${esc(b.ten_khach || '—')}</td>
                    <td class="px-4 py-2.5 font-mono text-xs">${esc(b.sdt || '—')}</td>
                    <td class="px-4 py-2.5 text-on-surface-variant">${esc(b.sale || '—')}</td>
                    <td class="px-4 py-2.5"><span class="text-xs px-2 py-0.5 rounded ${lClass}">${lLabel}</span>${dv}</td>
                    <td class="px-4 py-2.5 font-mono text-xs">${esc(b.gio || '—')}</td>
                    <td class="px-4 py-2.5"><span class="text-xs font-semibold px-2 py-0.5 rounded ${apClass}">${apLabel}</span></td>
                    <td class="px-4 py-2.5"><span class="text-xs font-semibold px-2 py-0.5 rounded ${rsClass}">${rsLabel}</span></td>
                </tr>`;
            }).join('');
        } catch (e) { /* silent */ }
    }

    // Poll mỗi 15s. Không poll khi tab ẩn (tiết kiệm request).
    setInterval(() => {
        if (document.hidden) return;
        refresh();
    }, 15000);
})();
</script>
</body>
</html>
