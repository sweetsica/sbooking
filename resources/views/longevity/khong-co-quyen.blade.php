<!DOCTYPE html>
<html class="light" lang="vi">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Không có quyền truy cập — Longevity Booking</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&amp;family=Manrope:wght@600;700;800&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config = {
  theme: { extend: {
    colors: {
      "surface": "#f7f9fb", "surface-container-lowest": "#ffffff", "surface-container-low": "#f2f4f6",
      "on-surface": "#191c1e", "on-surface-variant": "#45464d", "outline": "#76777d", "outline-variant": "#c6c6cd",
      "primary": "#000000", "on-primary": "#ffffff",
      "secondary": "#006591", "on-secondary": "#ffffff",
      "error": "#ba1a1a", "error-container": "#ffdad6", "on-error-container": "#93000a",
    },
    borderRadius: { "lg": "0.25rem", "xl": "0.5rem", "full": "0.75rem" },
    fontFamily: { "headline": ["Manrope"], "body": ["Inter"] },
  }},
}
</script>
<style>
  body { font-family: 'Inter', sans-serif; }
  .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; vertical-align: middle; }
</style>
</head>
<body class="min-h-screen bg-surface text-on-surface flex items-center justify-center p-4">
<div class="w-full max-w-md text-center">
<div class="flex items-center justify-center gap-2 mb-8">
<div class="w-10 h-10 bg-primary rounded-lg flex items-center justify-center">
<span class="material-symbols-outlined text-on-primary">spa</span>
</div>
<span class="text-2xl font-headline font-extrabold">Longevity Booking</span>
</div>

<div class="bg-surface-container-lowest border border-outline-variant rounded-2xl shadow-sm p-8">
<div class="w-16 h-16 mx-auto mb-5 rounded-full bg-error-container text-on-error-container flex items-center justify-center">
<span class="material-symbols-outlined text-[34px]">lock</span>
</div>
<h1 class="text-xl font-headline font-bold mb-2">Bạn không có quyền truy cập</h1>
<p class="text-sm text-on-surface-variant mb-6">Vui lòng liên hệ admin để được cấp quyền cho mục này.</p>

<div class="flex flex-col gap-2">
@if (!empty($slug))
<a href="/{{ $slug }}/lich-hen" class="w-full py-2.5 bg-primary text-on-primary font-semibold rounded-lg hover:opacity-90 active:scale-[0.99] transition-all flex items-center justify-center gap-2">
<span class="material-symbols-outlined text-[20px]">calendar_month</span> Về trang Lịch hẹn
</a>
@endif
<form method="POST" action="/logout">
@csrf
<button type="submit" class="w-full py-2.5 bg-surface border border-outline-variant text-on-surface-variant font-semibold rounded-lg hover:bg-surface-container-low transition-all flex items-center justify-center gap-2">
<span class="material-symbols-outlined text-[20px]">logout</span> Đăng xuất
</button>
</form>
</div>
</div>

<p class="text-center text-xs text-on-surface-variant mt-6">© Longevity Booking · Hệ thống quản lý đặt lịch Longevity</p>
</div>
</body>
</html>
