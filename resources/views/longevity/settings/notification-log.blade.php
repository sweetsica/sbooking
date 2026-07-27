@extends('longevity.settings.layout')
@section('title', 'Nhật ký thông báo')

@section('content')
<div class="flex items-center gap-3 mb-2">
    <a href="/{{ $coSo->slug }}/thiet-lap" class="text-on-surface-variant hover:text-on-surface">
        <span class="material-symbols-outlined">arrow_back</span>
    </a>
    <span class="material-symbols-outlined text-secondary text-[28px]">history</span>
    <h2 class="text-headline-lg font-headline-lg">Nhật ký thông báo</h2>
</div>
<p class="text-body-md text-on-surface-variant mb-6">Toàn bộ thông báo hệ thống đã gửi — kể cả những cái người dùng đã ẩn.</p>

<form method="GET" class="bg-surface-container-lowest border border-outline-variant rounded-xl p-4 mb-6">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
        <div>
            <label class="text-label-caps text-on-surface-variant block mb-1">Người nhận</label>
            <select name="user_id" class="w-full bg-surface-container-low border-none rounded-lg text-body-sm py-2">
                <option value="">— Tất cả —</option>
                @foreach ($allUsers as $u)
                    <option value="{{ $u->id }}" @selected($filters['userId'] == $u->id)>{{ $u->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="text-label-caps text-on-surface-variant block mb-1">Loại sự kiện</label>
            <select name="event" class="w-full bg-surface-container-low border-none rounded-lg text-body-sm py-2">
                <option value="">— Tất cả —</option>
                @foreach ($eventOptions as $ev)
                    <option value="{{ $ev }}" @selected($filters['event'] === $ev)>{{ $ev }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="text-label-caps text-on-surface-variant block mb-1">Trạng thái</label>
            <select name="status" class="w-full bg-surface-container-low border-none rounded-lg text-body-sm py-2">
                <option value="">— Tất cả —</option>
                <option value="unread"  @selected($filters['status'] === 'unread')>Chưa đọc</option>
                <option value="read"    @selected($filters['status'] === 'read')>Đã đọc</option>
                <option value="hidden"  @selected($filters['status'] === 'hidden')>User đã ẩn</option>
                <option value="visible" @selected($filters['status'] === 'visible')>Còn hiện</option>
            </select>
        </div>
        <div>
            <label class="text-label-caps text-on-surface-variant block mb-1">Từ ngày</label>
            <input type="date" name="tu" value="{{ $filters['tu'] }}" class="w-full bg-surface-container-low border-none rounded-lg text-body-sm py-2">
        </div>
        <div>
            <label class="text-label-caps text-on-surface-variant block mb-1">Đến ngày</label>
            <input type="date" name="den" value="{{ $filters['den'] }}" class="w-full bg-surface-container-low border-none rounded-lg text-body-sm py-2">
        </div>
        <div>
            <label class="text-label-caps text-on-surface-variant block mb-1">Tìm nội dung</label>
            <input type="text" name="q" value="{{ $filters['q'] }}" placeholder="Từ khóa trong tiêu đề/nội dung..." class="w-full bg-surface-container-low border-none rounded-lg text-body-sm py-2">
        </div>
    </div>
    <div class="flex items-center gap-2 mt-4">
        <button type="submit" class="px-4 py-2 bg-secondary text-on-secondary rounded-lg text-body-sm font-semibold flex items-center gap-2">
            <span class="material-symbols-outlined text-[18px]">filter_alt</span> Lọc
        </button>
        <a href="/{{ $coSo->slug }}/thiet-lap/nhat-ky-thong-bao" class="px-4 py-2 border border-outline-variant rounded-lg text-body-sm text-on-surface-variant hover:bg-surface-container-low">Xóa lọc</a>
    </div>
</form>

<div class="bg-surface-container-lowest border border-outline-variant rounded-xl overflow-hidden">
    <div class="p-3 border-b border-outline-variant text-body-sm text-on-surface-variant">
        Tổng: <strong>{{ $items->total() }}</strong> thông báo
    </div>
    @if ($items->isEmpty())
        <div class="p-12 text-center text-on-surface-variant">
            <span class="material-symbols-outlined text-[48px] opacity-40">inbox</span>
            <p class="mt-2">Không có thông báo nào khớp bộ lọc.</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-body-sm">
                <thead class="bg-surface-container-low text-label-caps text-on-surface-variant">
                    <tr>
                        <th class="text-left px-4 py-2">Thời gian</th>
                        <th class="text-left px-4 py-2">Người nhận</th>
                        <th class="text-left px-4 py-2">Loại</th>
                        <th class="text-left px-4 py-2">Nội dung</th>
                        <th class="text-left px-4 py-2">Trạng thái</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/60">
                @foreach ($items as $n)
                    @php
                        $data   = $n->data;
                        $user   = $users[$n->notifiable_id] ?? null;
                        $badges = [];
                        if ($n->hidden_at) $badges[] = ['User đã ẩn', 'bg-error-container text-on-error-container'];
                        if ($n->read_at)   $badges[] = ['Đã đọc',      'bg-surface-container text-on-surface-variant'];
                        else               $badges[] = ['Chưa đọc',    'bg-secondary-container/40 text-on-secondary-container'];
                    @endphp
                    <tr class="hover:bg-surface-container-low/50">
                        <td class="px-4 py-3 whitespace-nowrap text-on-surface-variant">
                            <div>{{ $n->created_at->format('d/m/Y H:i') }}</div>
                            <div class="text-[11px] opacity-70">{{ $n->created_at->diffForHumans() }}</div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            {{ $user?->name ?? '#'.$n->notifiable_id }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <span class="px-2 py-0.5 bg-surface-container text-on-surface-variant rounded text-[11px] font-mono">
                                {{ $data['event'] ?? '—' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="font-semibold text-on-surface">{{ $data['tieu_de'] ?? 'Thông báo' }}</div>
                            <div class="text-on-surface-variant line-clamp-2">{{ $data['noi_dung'] ?? '' }}</div>
                            @if (! empty($data['actor']))
                                <div class="text-[11px] text-on-surface-variant/70 mt-1">Bởi: {{ $data['actor'] }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="flex flex-col gap-1">
                                @foreach ($badges as [$label, $cls])
                                    <span class="{{ $cls }} text-[11px] px-2 py-0.5 rounded font-semibold w-fit">{{ $label }}</span>
                                @endforeach
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-3 border-t border-outline-variant">{{ $items->links() }}</div>
    @endif
</div>
@endsection
