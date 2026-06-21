<!DOCTYPE html>
<html class="light" lang="vi">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Đổi mật khẩu — Longevity Booking</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&amp;family=Manrope:wght@600;700;800&amp;family=JetBrains+Mono:wght@500;600&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script src="https://cdn.tailwindcss.com?plugins=forms"></script>
<script>
tailwind.config = {
  theme: { extend: {
    colors: {
      "surface": "#f7f9fb", "surface-container-lowest": "#ffffff", "surface-container-low": "#f2f4f6",
      "on-surface": "#191c1e", "on-surface-variant": "#45464d", "outline": "#76777d", "outline-variant": "#c6c6cd",
      "primary": "#000000", "on-primary": "#ffffff", "primary-container": "#131b2e",
      "secondary": "#006591", "on-secondary": "#ffffff", "secondary-container": "#39b8fd", "on-secondary-container": "#004666",
      "tertiary-fixed-dim": "#4edea3", "on-tertiary-container": "#009668",
      "error": "#ba1a1a", "error-container": "#ffdad6", "on-error-container": "#93000a",
    },
    borderRadius: { "lg": "0.25rem", "xl": "0.5rem", "full": "0.75rem" },
    fontFamily: { "headline": ["Manrope"], "body": ["Inter"], "mono": ["JetBrains Mono"] },
  }},
}
</script>
<style>
  body { font-family: 'Inter', sans-serif; }
  .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; vertical-align: middle; }
  .ipt:focus { outline: none; border-color: #006591; box-shadow: 0 0 0 1px #006591; }
</style>
</head>
<body class="min-h-screen bg-surface text-on-surface flex items-center justify-center p-4">
<div class="w-full max-w-md">
<div class="flex items-center justify-center gap-2 mb-6">
<div class="w-10 h-10 bg-primary rounded-lg flex items-center justify-center">
<span class="material-symbols-outlined text-on-primary">spa</span>
</div>
<span class="text-2xl font-headline font-extrabold">Longevity Booking</span>
</div>

<div class="bg-surface-container-lowest border border-outline-variant rounded-2xl shadow-sm p-8">
<h1 class="text-xl font-headline font-bold mb-1">Đổi mật khẩu</h1>
<p class="text-sm text-on-surface-variant mb-6">Tài khoản: <span class="font-semibold">{{ auth()->user()->username ?? auth()->user()->name }}</span></p>

@if (session('ok'))
<div class="mb-5 px-4 py-3 rounded-xl bg-tertiary-fixed-dim/30 text-on-tertiary-container text-sm flex items-center gap-2">
<span class="material-symbols-outlined text-[18px]">check_circle</span> {{ session('ok') }}
</div>
@endif
@if ($errors->any())
<div class="mb-5 px-4 py-3 rounded-xl bg-error-container text-on-error-container text-sm flex items-center gap-2">
<span class="material-symbols-outlined text-[18px]">error</span> {{ $errors->first() }}
</div>
@endif

<form method="POST" action="/doi-mat-khau" class="space-y-4">
@csrf
<div>
<label class="block text-sm font-semibold text-on-surface-variant mb-1.5">Mật khẩu hiện tại</label>
<div class="relative">
<span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[20px]">lock</span>
<input name="current_password" type="password" required autofocus autocomplete="current-password"
       class="ipt w-full pl-10 pr-4 py-2.5 bg-surface border border-outline rounded-lg transition-all"
       placeholder="••••••"/>
</div>
</div>
<div>
<label class="block text-sm font-semibold text-on-surface-variant mb-1.5">Mật khẩu mới</label>
<div class="relative">
<span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[20px]">lock_reset</span>
<input name="password" type="password" required autocomplete="new-password"
       class="ipt w-full pl-10 pr-4 py-2.5 bg-surface border border-outline rounded-lg transition-all"
       placeholder="Tối thiểu 6 ký tự"/>
</div>
</div>
<div>
<label class="block text-sm font-semibold text-on-surface-variant mb-1.5">Xác nhận mật khẩu mới</label>
<div class="relative">
<span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[20px]">lock_reset</span>
<input name="password_confirmation" type="password" required autocomplete="new-password"
       class="ipt w-full pl-10 pr-4 py-2.5 bg-surface border border-outline rounded-lg transition-all"
       placeholder="Nhập lại mật khẩu mới"/>
</div>
</div>
<div class="flex items-center gap-3 pt-2">
<a href="{{ url()->previous() !== url()->current() ? url()->previous() : '/' }}" class="flex-1 py-2.5 text-center bg-surface border border-outline-variant text-on-surface-variant font-semibold rounded-lg hover:bg-surface-container-low transition-all">
Quay lại
</a>
<button type="submit" class="flex-1 py-2.5 bg-primary text-on-primary font-semibold rounded-lg hover:opacity-90 active:scale-[0.99] transition-all flex items-center justify-center gap-2">
<span class="material-symbols-outlined text-[20px]">save</span> Lưu
</button>
</div>
</form>
</div>

<p class="text-center text-xs text-on-surface-variant mt-6">© Longevity Booking · Hệ thống quản lý đặt lịch Longevity</p>
</div>
</body>
</html>
