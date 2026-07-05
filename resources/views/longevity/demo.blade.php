<!DOCTYPE html>
<html class="light" lang="vi"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Hướng dẫn nhanh — Longevity Booking</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&amp;family=Manrope:wght@600;700;800&amp;family=JetBrains+Mono:wght@500;600&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script id="tailwind-config">
    tailwind.config = {
        darkMode: "class",
        theme: {
            extend: {
                "colors": {
                    "surface-bright": "#f7f9fb",
                    "on-surface": "#191c1e",
                    "on-primary": "#ffffff",
                    "background": "#f7f9fb",
                    "surface-container-high": "#e6e8ea",
                    "surface-container-highest": "#e0e3e5",
                    "surface-container-lowest": "#ffffff",
                    "error": "#ba1a1a",
                    "on-error": "#ffffff",
                    "error-container": "#ffdad6",
                    "on-error-container": "#93000a",
                    "primary": "#000000",
                    "primary-container": "#131b2e",
                    "surface": "#f7f9fb",
                    "secondary": "#006591",
                    "surface-container": "#eceef0",
                    "outline-variant": "#c6c6cd",
                    "secondary-container": "#39b8fd",
                    "on-surface-variant": "#45464d",
                    "on-secondary": "#ffffff",
                    "on-secondary-container": "#004666",
                    "surface-container-low": "#f2f4f6",
                    "outline": "#76777d"
                },
                "borderRadius": { "DEFAULT": "0.125rem", "lg": "0.25rem", "xl": "0.5rem", "full": "0.75rem" },
                "fontFamily": {
                    "body-md": ["Inter"], "body-sm": ["Inter"],
                    "headline-lg": ["Manrope"], "headline-md": ["Manrope"]
                },
                "fontSize": {
                    "body-md": ["14px", {"lineHeight": "20px", "fontWeight": "400"}],
                    "body-sm": ["13px", {"lineHeight": "18px", "fontWeight": "400"}],
                    "headline-lg": ["24px", {"lineHeight": "32px", "fontWeight": "700"}],
                    "headline-md": ["18px", {"lineHeight": "24px", "fontWeight": "600"}]
                }
            }
        }
    }
</script>
<style>
    .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; vertical-align: middle; }
    body { font-family: Inter, sans-serif; }
    [x-tab-panel] { display: none; }
    [x-tab-panel].on { display: block; }
</style>
</head>
<body class="bg-background text-on-surface">

@php
    // Bảng badge trạng thái booking dùng trong demo
    $badge = [
        'cho_duyet' => ['Chờ duyệt', 'bg-amber-100 text-amber-800'],
        'da_duyet'  => ['Đã duyệt',  'bg-emerald-100 text-emerald-800'],
        'tu_choi'   => ['Từ chối',   'bg-red-100 text-red-800'],
        'da_xong'   => ['Đã xong',   'bg-blue-100 text-blue-800'],
    ];
    $tabs = [
        ['id' => 'sale',  'icon' => 'point_of_sale', 'label' => 'Sale đặt lịch',    'desc' => 'Tạo lịch cho khách'],
        ['id' => 'admin', 'icon' => 'fact_check',    'label' => 'Admin duyệt',      'desc' => 'Kiểm tra & phê duyệt'],
        ['id' => 'ktv',   'icon' => 'spa',           'label' => 'KTV thực hiện',    'desc' => 'Xem ca & làm dịch vụ'],
        ['id' => 'bacsi', 'icon' => 'stethoscope',   'label' => 'Bác sĩ & Phản hồi','desc' => 'Khám, ghi nhận phản hồi'],
    ];
@endphp

<!-- Header -->
<header class="bg-surface-container-lowest border-b border-outline-variant">
  <div class="max-w-[960px] mx-auto px-6 py-5 flex items-center gap-3">
    <div class="w-10 h-10 bg-primary rounded-lg flex items-center justify-center shrink-0">
      <span class="material-symbols-outlined text-on-primary">spa</span>
    </div>
    <div>
      <h1 class="text-headline-lg font-[Manrope] font-bold leading-tight">Hướng dẫn nhanh</h1>
      <p class="text-body-sm text-on-surface-variant">Luồng vận hành Longevity Booking — ai làm gì, theo thứ tự nào</p>
    </div>
  </div>
</header>

<!-- Sơ đồ luồng tổng quan -->
<div class="max-w-[960px] mx-auto px-6 pt-6">
  <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-4">
    <div class="flex flex-wrap items-center justify-center gap-2 text-body-sm">
      @foreach ([
        ['point_of_sale', 'Sale tạo lịch', 'text-secondary'],
        ['fact_check', 'Admin duyệt', 'text-emerald-700'],
        ['spa', 'KTV / Bác sĩ làm', 'text-purple-700'],
        ['reviews', 'Ghi phản hồi khách', 'text-amber-700'],
      ] as $i => $s)
        @if ($i > 0)
          <span class="material-symbols-outlined text-on-surface-variant text-[18px]">chevron_right</span>
        @endif
        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-surface-container-low font-semibold {{ $s[2] }}">
          <span class="material-symbols-outlined text-[18px]">{{ $s[0] }}</span>{{ $s[1] }}
        </span>
      @endforeach
    </div>
  </div>
</div>

<!-- Tabs -->
<div class="max-w-[960px] mx-auto px-6 py-6">
  <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mb-6" id="tab-bar">
    @foreach ($tabs as $i => $t)
      <button data-tab="{{ $t['id'] }}"
        class="tab-btn text-left p-3 rounded-xl border transition-colors {{ $i === 0 ? 'border-secondary bg-secondary-container/20' : 'border-outline-variant bg-surface-container-lowest hover:bg-surface-container-low' }}">
        <span class="material-symbols-outlined text-secondary">{{ $t['icon'] }}</span>
        <div class="font-semibold text-body-md mt-1">{{ $t['label'] }}</div>
        <div class="text-body-sm text-on-surface-variant">{{ $t['desc'] }}</div>
      </button>
    @endforeach
  </div>

  {{-- ============ TAB SALE ============ --}}
  <div x-tab-panel="sale" class="on space-y-4">
    @include('longevity.partials.demo-step', ['n' => 1, 'title' => 'Mở form đặt lịch', 'icon' => 'add_circle', 'body' =>
      'Vào <b>Đặt lịch phòng khám</b> (có bác sĩ) hoặc <b>Đặt lịch dịch vụ</b> (chỉ KTV, không bác sĩ) → bấm <b>Tạo mới</b>. Sale luôn là người đặt hộ khách, nên hệ thống tự gắn <b>tên Sale</b> vào lịch.'])

    @include('longevity.partials.demo-step', ['n' => 2, 'title' => 'Nhập thông tin khách', 'icon' => 'person_search', 'body' =>
      'Gõ <b>SĐT</b> — hệ thống tự kiểm tra khách cũ/mới. Chọn <b>ngày</b>, <b>khung giờ</b>, <b>phòng</b>, <b>dịch vụ</b>, và <b>KTV / bác sĩ</b> phụ trách. Nếu trùng KTV hoặc bác sĩ ở khung giờ đó, hệ thống báo ngay để tránh đặt chồng.'])

    @include('longevity.partials.demo-step', ['n' => 3, 'title' => 'Lưu — lịch vào trạng thái Chờ duyệt', 'icon' => 'schedule_send', 'body' =>
      'Bấm lưu là xong. Lịch mới luôn ở trạng thái ' . '<span class="inline-flex px-2 py-0.5 rounded-full text-body-sm font-semibold ' . $badge['cho_duyet'][1] . '">' . $badge['cho_duyet'][0] . '</span>' . ' và admin nhận được thông báo. Sale không tự duyệt lịch của mình.'])

    <div class="rounded-xl border border-outline-variant bg-surface-container-low p-4 text-body-sm text-on-surface-variant">
      <span class="material-symbols-outlined text-secondary text-[18px]">lightbulb</span>
      <b>Mẹo:</b> Có thể tìm lại lịch đã đặt bằng ô <b>Tìm kiếm</b> (tên/SĐT) ở góc phải để sửa hoặc theo dõi trạng thái.
    </div>
  </div>

  {{-- ============ TAB ADMIN ============ --}}
  <div x-tab-panel="admin" class="space-y-4">
    @include('longevity.partials.demo-step', ['n' => 1, 'title' => 'Vào trang Duyệt lịch', 'icon' => 'fact_check', 'body' =>
      'Menu <b>Duyệt lịch</b> chỉ hiện với người có quyền duyệt (admin hoặc phòng ban được cấp <code>duyet_booking</code>). Ở đây gom tất cả lịch đang ' . '<span class="inline-flex px-2 py-0.5 rounded-full text-body-sm font-semibold ' . $badge['cho_duyet'][1] . '">' . $badge['cho_duyet'][0] . '</span>.'])

    @include('longevity.partials.demo-step', ['n' => 2, 'title' => 'Kiểm tra & quyết định', 'icon' => 'checklist', 'body' =>
      'Soát lại khách, giờ, phòng, KTV/bác sĩ có hợp lý không, có trùng lịch không. Sau đó chọn 1 trong 2 hướng ở bước dưới.'])

    <div class="grid sm:grid-cols-2 gap-3">
      <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4">
        <div class="flex items-center gap-2 font-semibold text-emerald-800">
          <span class="material-symbols-outlined">check_circle</span> Duyệt
        </div>
        <p class="text-body-sm text-emerald-900/80 mt-1">Lịch chuyển sang
          <span class="inline-flex px-2 py-0.5 rounded-full text-body-sm font-semibold {{ $badge['da_duyet'][1] }}">{{ $badge['da_duyet'][0] }}</span>.
          KTV và bác sĩ mới thấy trên lịch của mình để chuẩn bị.</p>
      </div>
      <div class="rounded-xl border border-red-200 bg-red-50 p-4">
        <div class="flex items-center gap-2 font-semibold text-red-800">
          <span class="material-symbols-outlined">cancel</span> Từ chối
        </div>
        <p class="text-body-sm text-red-900/80 mt-1">Phải ghi <b>lý do từ chối</b>. Lịch thành
          <span class="inline-flex px-2 py-0.5 rounded-full text-body-sm font-semibold {{ $badge['tu_choi'][1] }}">{{ $badge['tu_choi'][0] }}</span>,
          Sale nhận thông báo để sửa & gửi lại.</p>
      </div>
    </div>

    @include('longevity.partials.demo-step', ['n' => 3, 'title' => 'Sau khi làm xong — đánh dấu Đã xong', 'icon' => 'task_alt', 'body' =>
      'Khi buổi hẹn hoàn tất, admin bấm <b>Xong</b> để chuyển lịch sang ' . '<span class="inline-flex px-2 py-0.5 rounded-full text-body-sm font-semibold ' . $badge['da_xong'][1] . '">' . $badge['da_xong'][0] . '</span>. ' . 'Chỉ lịch <b>Đã xong</b> mới mở được ô ghi phản hồi của khách.'])
  </div>

  {{-- ============ TAB KTV ============ --}}
  <div x-tab-panel="ktv" class="space-y-4">
    @include('longevity.partials.demo-step', ['n' => 1, 'title' => 'Xem lịch làm việc của mình', 'icon' => 'calendar_month', 'body' =>
      'KTV đăng nhập vào xem các lịch <b>đã được duyệt</b> có phân công cho mình — theo ngày trên trang <b>Lịch hẹn</b> / <b>Phòng dịch vụ</b>. Mỗi thẻ hiện khách, dịch vụ, phòng và khung giờ.'])

    @include('longevity.partials.demo-step', ['n' => 2, 'title' => 'Chuẩn bị & thực hiện dịch vụ', 'icon' => 'spa', 'body' =>
      'Đúng khung giờ, KTV đón khách và thực hiện dịch vụ theo lịch. Ghi chú trong lịch (nếu Sale/Admin để lại) cho biết yêu cầu riêng của khách hoặc số buổi trong liệu trình.'])

    @include('longevity.partials.demo-step', ['n' => 3, 'title' => 'Báo hoàn thành', 'icon' => 'done_all', 'body' =>
      'Làm xong, báo admin (hoặc người có quyền) đánh dấu ' . '<span class="inline-flex px-2 py-0.5 rounded-full text-body-sm font-semibold ' . $badge['da_xong'][1] . '">' . $badge['da_xong'][0] . '</span>' . ' cho lịch. Đây là mốc để tính đã phục vụ và mở phần phản hồi.'])

    <div class="rounded-xl border border-outline-variant bg-surface-container-low p-4 text-body-sm text-on-surface-variant">
      <span class="material-symbols-outlined text-secondary text-[18px]">info</span>
      Lịch nghỉ / ngày đóng cửa do admin đặt trong <b>Lịch làm việc</b> và <b>Ngày nghỉ</b> — KTV không bị xếp khách vào những khung đó.
    </div>
  </div>

  {{-- ============ TAB BÁC SĨ & PHẢN HỒI ============ --}}
  <div x-tab-panel="bacsi" class="space-y-4">
    @include('longevity.partials.demo-step', ['n' => 1, 'title' => 'Bác sĩ xem lịch khám của mình', 'icon' => 'stethoscope', 'body' =>
      'Tài khoản bác sĩ vào menu <b>Bác sĩ</b> chỉ thấy lịch của <b>chính mình</b> (xem theo ngày hoặc theo tháng). Dữ liệu lấy từ các lịch đặt phòng có phân công bác sĩ đó.'])

    @include('longevity.partials.demo-step', ['n' => 2, 'title' => 'Khám / tư vấn khách', 'icon' => 'clinical_notes', 'body' =>
      'Bác sĩ thực hiện khám hoặc tư vấn theo lịch đã duyệt. Sau buổi hẹn, lịch được đánh dấu ' . '<span class="inline-flex px-2 py-0.5 rounded-full text-body-sm font-semibold ' . $badge['da_xong'][1] . '">' . $badge['da_xong'][0] . '</span>.'])

    @include('longevity.partials.demo-step', ['n' => 3, 'title' => 'Ghi nhận phản hồi của khách', 'icon' => 'reviews', 'body' =>
      'Với lịch <b>Đã xong</b>, người có quyền mở ô <b>Phản hồi từ khách</b> để ghi lại cảm nhận, khiếu nại hay nhu cầu tiếp theo của khách. Đây là kênh để Sale chăm sóc lại và chốt liệu trình kế tiếp.'])

    <!-- Mô phỏng ô phản hồi -->
    <div class="rounded-xl border border-outline-variant bg-surface-container-lowest p-4">
      <div class="flex items-center justify-between mb-2">
        <div class="font-semibold text-body-md">Nguyễn Thị A — Chăm sóc da mặt</div>
        <span class="inline-flex px-2 py-0.5 rounded-full text-body-sm font-semibold {{ $badge['da_xong'][1] }}">{{ $badge['da_xong'][0] }}</span>
      </div>
      <label class="text-body-sm text-on-surface-variant">Phản hồi từ khách (demo — không lưu)</label>
      <textarea rows="2" class="mt-1 w-full rounded-lg border-outline-variant bg-surface-container-low text-body-md focus:ring-2 focus:ring-secondary/20" placeholder="VD: Khách hài lòng, muốn đặt tiếp buổi 2 vào tuần sau..."></textarea>
      <div class="flex justify-end mt-2">
        <button type="button" onclick="alert('Đây là bản demo — phản hồi thật được lưu trong trang chi tiết lịch.')" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-secondary text-on-secondary text-body-md font-semibold">
          <span class="material-symbols-outlined text-[18px]">save</span> Lưu phản hồi
        </button>
      </div>
    </div>
  </div>
</div>

<footer class="max-w-[960px] mx-auto px-6 pb-10 text-center text-body-sm text-on-surface-variant">
  Đây là trang hướng dẫn nhanh. <a href="/login" class="text-secondary font-semibold hover:underline">Đăng nhập</a> để bắt đầu thao tác thật.
</footer>

<script>
  const bar = document.getElementById('tab-bar');
  const panels = document.querySelectorAll('[x-tab-panel]');
  bar.addEventListener('click', (e) => {
    const btn = e.target.closest('.tab-btn');
    if (!btn) return;
    document.querySelectorAll('.tab-btn').forEach(b => {
      b.classList.remove('border-secondary', 'bg-secondary-container/20');
      b.classList.add('border-outline-variant', 'bg-surface-container-lowest');
    });
    btn.classList.add('border-secondary', 'bg-secondary-container/20');
    btn.classList.remove('border-outline-variant', 'bg-surface-container-lowest');
    panels.forEach(p => p.classList.toggle('on', p.getAttribute('x-tab-panel') === btn.dataset.tab));
  });
</script>
</body>
</html>
