<!DOCTYPE html>
<html class="light" lang="vi">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>@yield('title', 'Hỗ trợ')</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&amp;family=Manrope:wght@600;700;800&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script src="https://cdn.tailwindcss.com?plugins=forms"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<style>body{font-family:'Inter',sans-serif;background:#f7f9fb}[x-cloak]{display:none!important}.material-symbols-outlined{vertical-align:middle}</style>
</head>
<body class="text-slate-900">
@php
    // Topnav cần $coSo (dùng link menu). Fallback: cơ sở của user hoặc cơ sở đầu tiên.
    $coSo = auth()->user()?->coSo ?? \App\Models\CoSo::orderBy('id')->first();
@endphp
@include('partials.topnav', ['active' => 'ho-tro'])
<main class="pt-16 min-h-screen">
<div class="max-w-5xl mx-auto px-6 py-8">
@if (session('ok'))
<div x-data="{show:true}" x-show="show" x-init="setTimeout(()=>show=false,3500)"
     class="mb-6 px-4 py-3 rounded-xl bg-green-100 text-green-800 flex items-center gap-2 font-semibold">
<span class="material-symbols-outlined">check_circle</span>{{ session('ok') }}
</div>
@endif
@if ($errors->any())
<div class="mb-6 px-4 py-3 rounded-xl bg-red-100 text-red-800">
<p class="font-semibold flex items-center gap-2"><span class="material-symbols-outlined">error</span>Có lỗi:</p>
<ul class="list-disc list-inside text-sm mt-1">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif
@yield('content')
</div>
</main>
</body>
</html>
