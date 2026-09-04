<!DOCTYPE html>
<html class="light" lang="vi"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Đặt lịch — Chuyển sang Datasource | Longevity Booking</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&amp;family=Manrope:wght@600;700;800&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 text-slate-900 font-[Inter]">
@include('partials.topnav', ['active' => 'bookings'])
<main class="pt-[calc(56px+24px)] pb-12 px-6 max-w-3xl mx-auto">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-10 text-center">
        <div class="w-16 h-16 mx-auto mb-6 rounded-full bg-amber-100 flex items-center justify-center">
            <span class="material-symbols-outlined text-amber-600 text-[36px]">swap_horiz</span>
        </div>
        <h1 class="text-2xl font-bold text-slate-900 mb-3 font-[Manrope]">Tạo booking đã chuyển sang Datasource</h1>
        <p class="text-slate-600 leading-relaxed mb-2">
            Từ 2026-09-04, mọi lịch hẹn phải được tạo bên hệ thống <strong>Datasource (SCRM)</strong> để đảm bảo lead / KPI / báo cáo đồng bộ.
        </p>
        <p class="text-slate-500 text-sm mb-8">
            Sbooking chỉ dùng để <strong>duyệt / cập nhật trạng thái / xem lịch</strong>. Booking tạo trực tiếp bên sbooking sẽ không hiện trong Datasource.
        </p>

        @php $scrmUrl = \App\Models\AppSetting::get('scrm_url', ''); @endphp

        @if ($scrmUrl)
            <a href="{{ $scrmUrl }}" target="_blank" rel="noopener"
               class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors">
                <span class="material-symbols-outlined text-[20px]">open_in_new</span>
                <span>Mở Datasource để tạo booking</span>
            </a>
        @else
            <div class="inline-flex flex-col gap-2 items-center">
                <span class="text-slate-400 text-sm italic">Chưa cấu hình URL Datasource</span>
                <a href="/thiet-lap/ket-noi-scrm"
                   class="inline-flex items-center gap-2 px-4 py-2 border border-slate-300 rounded-lg text-slate-700 hover:bg-slate-100 text-sm">
                    <span class="material-symbols-outlined text-[18px]">settings</span>
                    <span>Vào Thiết lập › Kết nối SCRM</span>
                </a>
            </div>
        @endif

        <div class="mt-10 pt-6 border-t border-slate-200 text-left">
            <p class="text-xs uppercase tracking-wide text-slate-400 mb-2">Cần trợ giúp?</p>
            <ul class="text-sm text-slate-600 space-y-1">
                <li>• Mở <a href="/{{ $coSo->slug }}/danh-sach" class="text-blue-600 hover:underline">danh sách booking</a> để xem/duyệt lịch hiện có.</li>
                <li>• Mở <a href="/{{ $coSo->slug }}/lich-hen" class="text-blue-600 hover:underline">dashboard</a> để theo dõi khung giờ trong ngày.</li>
                <li>• Nếu bạn cần tạo lịch <strong>tư vấn</strong> (không phải khám lâm sàng/dịch vụ), vào <a href="/{{ $coSo->slug }}/dat-lich-tu-van" class="text-blue-600 hover:underline">Đặt lịch tư vấn</a> — luồng riêng, chưa chặn.</li>
            </ul>
        </div>
    </div>
</main>
</body>
</html>
