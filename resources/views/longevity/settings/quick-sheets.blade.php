<!DOCTYPE html>
<html class="light" lang="vi">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<meta name="csrf-token" content="{{ csrf_token() }}"/>
<title>Quick Sheets — {{ $coSo->ten }}</title>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<script>
tailwind.config = { darkMode: 'class', theme: { extend: { colors: {
    'surface': '#f7f9fb', 'surface-container-lowest': '#ffffff', 'surface-container-low': '#f2f4f6',
    'surface-container-high': '#e6e8ea', 'on-surface': '#191c1e', 'on-surface-variant': '#45464d',
    'outline-variant': '#c6c6cd', 'secondary': '#006591', 'primary': '#000000',
}}}};
</script>
<style>
    body { font-family: Arial, Roboto, 'Helvetica Neue', sans-serif; background:#f7f9fb; }
    .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; vertical-align: middle; }
</style>
@livewireStyles
</head>
<body class="bg-surface">
@include('partials.topnav', ['active' => 'thiet-lap'])
<main class="pt-16">
    <livewire:settings.quick-sheets :co-so-id="$coSo->id" />
</main>
@livewireScripts
</body>
</html>
