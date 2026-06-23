<!DOCTYPE html>
<html class="light" lang="vi">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>@yield('title', 'Thiết lập') — {{ $coSo->ten }}</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&amp;family=Manrope:wght@600;700;800&amp;family=JetBrains+Mono:wght@500;600&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script>
tailwind.config = {
  darkMode: "class",
  theme: { extend: {
    colors: {
      "surface": "#f7f9fb", "surface-container-lowest": "#ffffff", "surface-container-low": "#f2f4f6",
      "surface-container": "#eceef0", "surface-container-high": "#e6e8ea", "surface-container-highest": "#e0e3e5",
      "on-surface": "#191c1e", "on-surface-variant": "#45464d", "outline": "#76777d", "outline-variant": "#c6c6cd",
      "primary": "#000000", "on-primary": "#ffffff", "primary-container": "#131b2e", "on-primary-container": "#7c839b",
      "secondary": "#006591", "on-secondary": "#ffffff", "secondary-container": "#39b8fd", "on-secondary-container": "#004666",
      "tertiary-container": "#002113", "on-tertiary-container": "#009668", "tertiary-fixed-dim": "#4edea3",
      "error": "#ba1a1a", "error-container": "#ffdad6", "on-error-container": "#93000a", "background": "#f7f9fb",
    },
    borderRadius: { "DEFAULT": "0.125rem", "lg": "0.25rem", "xl": "0.5rem", "full": "0.75rem" },
    spacing: { "container-margin": "24px" },
    fontFamily: { "headline-lg": ["Manrope"], "headline-md": ["Manrope"], "body-md": ["Inter"], "body-sm": ["Inter"], "label-caps": ["JetBrains Mono"] },
    fontSize: {
      "headline-lg": ["24px", {"lineHeight":"32px","fontWeight":"700"}],
      "headline-md": ["18px", {"lineHeight":"24px","fontWeight":"600"}],
      "body-md": ["14px", {"lineHeight":"20px","fontWeight":"400"}],
      "body-sm": ["13px", {"lineHeight":"18px","fontWeight":"400"}],
      "label-caps": ["11px", {"lineHeight":"16px","letterSpacing":"0.05em","fontWeight":"600"}],
    },
  }},
}
</script>
<style>
  body { font-family: 'Inter', sans-serif; background-color: #f7f9fb; }
  [x-cloak] { display: none !important; }
  .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; vertical-align: middle; }
</style>
</head>
<body class="bg-surface text-on-surface">
@include('partials.topnav', ['active' => 'thiet-lap'])

<main class="pt-16 min-h-screen">
<div class="px-container-margin py-8 max-w-[1650px] mx-auto">
@if (session('ok'))
<div x-data="{show:true}" x-show="show" x-init="setTimeout(()=>show=false,3500)"
     class="mb-6 px-4 py-3 rounded-xl bg-tertiary-fixed-dim/40 text-on-tertiary-container flex items-center gap-2 text-body-md font-semibold">
<span class="material-symbols-outlined">check_circle</span> {{ session('ok') }}
</div>
@endif
@if (session('err'))
<div class="mb-6 px-4 py-3 rounded-xl bg-error-container text-on-error-container flex items-center gap-2 text-body-md font-semibold">
<span class="material-symbols-outlined">block</span> {{ session('err') }}
</div>
@endif
@if ($errors->any())
<div class="mb-6 px-4 py-3 rounded-xl bg-error-container text-on-error-container">
<p class="font-semibold flex items-center gap-2"><span class="material-symbols-outlined">error</span> Có lỗi nhập liệu:</p>
<ul class="list-disc list-inside text-body-sm mt-1">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif
@yield('content')
</div>
</main>
<script>document.addEventListener('DOMContentLoaded',()=>window.lucide&&lucide.createIcons());</script>
</body>
</html>
