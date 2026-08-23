@extends('longevity.support._layout')
@section('title', 'Hỗ trợ / Phản hồi')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold mb-1">Hỗ trợ / Phản hồi</h1>
    <p class="text-sm text-slate-600">
        @if ($isAdmin)
            Tất cả ticket của người dùng ({{ $counts['cho_xu_ly'] }} chờ · {{ $counts['da_xu_ly'] }} đã xử lý · {{ $counts['tu_choi'] }} từ chối)
        @else
            Danh sách ticket bạn đã gửi
        @endif
    </p>
</div>

<div class="mb-4 flex gap-2 flex-wrap">
    <a href="/ho-tro" class="px-3 py-1.5 rounded-full text-sm border {{ !request('status') ? 'bg-slate-900 text-white border-slate-900' : 'border-slate-300 hover:bg-slate-50' }}">Tất cả</a>
    @foreach (\App\Models\SupportTicket::STATUSES as $key => $label)
        <a href="/ho-tro?status={{ $key }}" class="px-3 py-1.5 rounded-full text-sm border {{ request('status') === $key ? 'bg-slate-900 text-white border-slate-900' : 'border-slate-300 hover:bg-slate-50' }}">{{ $label }}</a>
    @endforeach
</div>

<div class="bg-white rounded-xl shadow border border-slate-200 divide-y divide-slate-100">
    @forelse ($tickets as $t)
    <a href="/ho-tro/{{ $t->id }}" class="block p-4 hover:bg-slate-50 transition">
        <div class="flex items-center gap-2 mb-1 flex-wrap">
            <span class="font-semibold">#{{ $t->id }} — {{ $t->name }}</span>
            @if ($t->co_so)<span class="text-xs px-2 py-0.5 rounded bg-slate-100 text-slate-700">{{ $t->co_so }}</span>@endif
            <span class="text-xs px-2 py-0.5 rounded border {{ $t->statusColor() }}">{{ $t->statusLabel() }}</span>
        </div>
        <p class="text-sm text-slate-600 line-clamp-2">{{ $t->description }}</p>
        <p class="text-xs text-slate-400 mt-1">
            {{ $t->created_at->format('d/m/Y H:i') }}
            @if ($t->contact) · {{ $t->contact }}@endif
        </p>
    </a>
    @empty
    <div class="p-8 text-center text-slate-500">Chưa có ticket nào.</div>
    @endforelse
</div>

<div class="mt-4">{{ $tickets->links() }}</div>
@endsection
