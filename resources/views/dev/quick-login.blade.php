<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>🚀 Quick Login (dev) — Sbooking</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
@if (session('impersonate_original_id'))
    <div class="bg-red-600 text-white text-sm px-4 py-2 flex items-center justify-between gap-3">
        <span>🎭 Đang giả lập <strong>{{ auth()->user()->name }}</strong> ({{ auth()->user()->email }}) — original: {{ session('impersonate_original_name') }}</span>
        <form method="POST" action="{{ route('impersonate.leave') }}" class="inline">
            @csrf
            <button class="px-3 py-1 rounded bg-white text-red-700 hover:bg-red-50 text-xs font-semibold">← Về Admin</button>
        </form>
    </div>
@endif

<div class="max-w-6xl mx-auto p-6">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold">🚀 Quick Login (dev) — Sbooking</h1>
            <p class="text-sm text-gray-600 mt-1">Click "Giả lập" để login nhanh vào user, không cần password. Chỉ hiện APP_ENV=local.</p>
        </div>
        <div class="flex gap-2">
            <a href="/" class="px-3 py-1.5 border rounded text-sm hover:bg-gray-100">← Về app</a>
            @if (session('impersonate_original_id'))
                <form method="POST" action="{{ route('impersonate.leave') }}">
                    @csrf
                    <button class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 text-sm font-semibold">← Về Admin gốc</button>
                </form>
            @endif
        </div>
    </div>

    @if (session('status'))
        <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-800 rounded text-sm">{{ session('status') }}</div>
    @endif

    @foreach ($groups as $coSo => $users)
        <div class="mb-6">
            <h2 class="text-lg font-semibold mb-3 flex items-center gap-2">
                <span class="px-3 py-1 rounded bg-blue-100 text-blue-700 text-sm">{{ $coSo }}</span>
                <span class="text-xs text-gray-500">({{ count($users) }} user)</span>
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                @foreach ($users as $u)
                    <div class="border border-gray-200 rounded-lg p-3 flex items-center justify-between gap-2 bg-white hover:bg-blue-50/50">
                        <div class="min-w-0 flex-1">
                            <div class="font-semibold text-sm truncate">
                                {{ $u['name'] }}
                                @if ($u['is_admin']) <span class="ml-1 text-xs px-1.5 py-0.5 bg-red-100 text-red-700 rounded">Admin</span>@endif
                            </div>
                            <div class="text-xs text-gray-500 truncate">{{ $u['email'] }}</div>
                            <div class="text-xs text-blue-700 mt-0.5">
                                {{ $u['chuc_danh'] ?: $u['vai_tro'] ?: '(no role)' }}@if ($u['phong_ban']) · {{ $u['phong_ban'] }} @endif
                            </div>
                        </div>
                        @if ($u['id'] !== auth()->id())
                            <form method="POST" action="{{ route('impersonate.start', $u['id']) }}">
                                @csrf
                                <button class="px-3 py-1.5 bg-blue-600 text-white rounded text-xs font-semibold hover:bg-blue-700 whitespace-nowrap">
                                    Giả lập →
                                </button>
                            </form>
                        @else
                            <span class="px-3 py-1.5 text-xs text-gray-400">(đang là bạn)</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</div>
</body>
</html>
