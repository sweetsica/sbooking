<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nhật ký hệ thống — Longevity Booking</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Symbols+Outlined">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-surface text-on-surface">
@php
    $parse = function (string $line) {
        $out = ['ts' => '', 'who' => '', 'action' => '', 'detail' => '', 'ip' => ''];
        if (preg_match('/^- `([^`]+)`\s+\*\*([^*]+)\*\*\s+—\s+(.+?)(?:\s+·\s+_IP\s+([^_]+)_)?$/u', $line, $m)) {
            $out['ts']  = $m[1];
            $out['who'] = $m[2];
            $parts = explode(' · ', $m[3]);
            $out['action'] = $parts[0];
            $out['detail'] = $parts[1] ?? '';
            $out['ip'] = $m[4] ?? '';
        }
        return $out;
    };
@endphp

<div class="max-w-7xl mx-auto p-6">
    <div class="flex items-center gap-3 mb-1">
        <a href="/" class="text-on-surface-variant hover:text-on-surface">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <span class="material-symbols-outlined text-primary text-[28px]">list_alt</span>
        <h1 class="text-headline-lg font-headline-lg">Nhật ký hệ thống</h1>
    </div>
    <p class="text-body-md text-on-surface-variant mb-5">
        Log hành động (login/logout, tạo/sửa/xoá booking, duyệt lịch làm việc) từ <code>public/logs.md</code>.
        Append-only, admin only.
    </p>

    <form method="get" class="bg-surface-container-lowest border border-outline-variant rounded-xl p-4 mb-4 flex gap-3 text-body-sm items-end">
        <div class="flex-1">
            <label class="text-label-caps text-on-surface-variant block mb-1">Tìm nhanh (user / action / IP)</label>
            <input type="text" name="q" value="{{ $q }}" placeholder="VD: đăng nhập, tạo booking, sale01…"
                class="w-full bg-surface-container-low border-none rounded-lg text-body-sm px-3 py-2">
        </div>
        <div>
            <label class="text-label-caps text-on-surface-variant block mb-1">N dòng cuối</label>
            <select name="tail" class="bg-surface-container-low border-none rounded-lg text-body-sm px-3 py-2">
                @foreach ([200, 500, 1000, 2000, 5000] as $n)
                    <option value="{{ $n }}" @selected($tail == $n)>{{ $n }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="bg-primary text-on-primary text-label-md font-semibold px-4 py-2 rounded-full">Lọc</button>
    </form>

    <div class="text-label-caps text-on-surface-variant mb-2">
        Hiển thị {{ $lines->count() }} / {{ number_format($total) }} dòng (mới nhất trên đầu).
    </div>

    <div class="bg-surface-container-lowest border border-outline-variant rounded-xl overflow-x-auto">
        <table class="w-full text-body-sm">
            <thead class="bg-surface-container-low text-on-surface-variant text-label-caps">
                <tr>
                    <th class="text-left px-3 py-2 w-40">Thời điểm</th>
                    <th class="text-left px-3 py-2">User</th>
                    <th class="text-left px-3 py-2">Hành động</th>
                    <th class="text-left px-3 py-2">Chi tiết</th>
                    <th class="text-left px-3 py-2 w-32">IP</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant font-mono text-[12px]">
                @forelse ($lines as $line)
                    @php $e = $parse($line); @endphp
                    <tr class="hover:bg-surface-container-low">
                        <td class="px-3 py-1.5 text-on-surface-variant">{{ $e['ts'] }}</td>
                        <td class="px-3 py-1.5">{{ $e['who'] }}</td>
                        <td class="px-3 py-1.5">
                            @php
                                $act = $e['action'];
                                $cls = str_contains($act, 'xóa') || str_contains($act, 'đăng xuất')
                                    ? 'bg-error-container text-on-error-container'
                                    : (str_contains($act, 'tạo') ? 'bg-tertiary-container text-on-tertiary-container' : 'bg-surface-container-high text-on-surface');
                            @endphp
                            <span class="text-label-caps font-semibold px-2 py-0.5 rounded-full {{ $cls }}">{{ $act }}</span>
                        </td>
                        <td class="px-3 py-1.5 text-on-surface-variant">{{ $e['detail'] }}</td>
                        <td class="px-3 py-1.5 text-on-surface-variant">{{ $e['ip'] }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center px-3 py-6 text-on-surface-variant italic">Không có dòng khớp filter.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
