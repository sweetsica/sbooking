<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Changelog — Longevity Booking</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Manrope:wght@600;700;800&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com?plugins=forms"></script>
<script>
tailwind.config = { theme: { extend: {
    colors: { "surface":"#f7f9fb","surface-container-lowest":"#ffffff","on-surface":"#191c1e","on-surface-variant":"#45464d","outline":"#76777d","outline-variant":"#c6c6cd","primary":"#000000","on-primary":"#ffffff","secondary":"#006591","on-secondary-container":"#004666" },
    borderRadius: { lg:"0.25rem", xl:"0.5rem" },
    fontFamily: { headline:["Manrope"], body:["Inter"] }
}}};
</script>
<style>body{font-family:Inter,sans-serif;} .material-symbols-outlined{vertical-align:middle;}</style>
</head>
<body class="min-h-screen bg-surface text-on-surface p-4 sm:p-8">
<div class="max-w-3xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-headline font-extrabold">Changelog</h1>
            <p class="text-sm text-on-surface-variant mt-1">Lịch sử phát hành Longevity Booking.</p>
        </div>
        <a href="/" class="text-sm text-secondary hover:underline">← Về trang chính</a>
    </div>

    @php $versions = \App\Support\Changelog::all(); @endphp

    @if (empty($versions))
        <div class="text-center py-16 text-on-surface-variant">Chưa có bản phát hành nào.</div>
    @else
        <div class="space-y-5">
            @foreach ($versions as $i => $v)
                <div class="bg-surface-container-lowest border border-outline-variant rounded-2xl px-6 py-5">
                    <div class="flex items-baseline gap-3 mb-3 flex-wrap">
                        <span class="px-2.5 py-1 rounded-lg text-sm font-bold {{ $i === 0 ? 'bg-primary text-on-primary' : 'bg-surface border border-outline text-on-surface' }}">
                            {{ $v['version'] }}
                        </span>
                        <span class="text-xs text-on-surface-variant">{{ $v['date'] }}</span>
                        @if ($i === 0)
                            <span class="text-xs font-semibold text-secondary uppercase tracking-widest">Mới nhất</span>
                        @endif
                    </div>
                    <ul class="space-y-1.5 text-sm">
                        @foreach ($v['items'] as $item)
                            <li class="flex gap-2"><span class="text-secondary mt-1">•</span><span>{{ $item }}</span></li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    @endif
</div>
</body>
</html>
