@extends('longevity.support._layout')
@section('title', 'Ticket #' . $ticket->id)

@section('content')
<a href="/ho-tro" class="text-sm text-slate-600 hover:underline">← Quay lại danh sách</a>

<div class="mt-4 bg-white rounded-xl shadow border border-slate-200 p-6">
    <div class="flex items-start justify-between gap-3 mb-4">
        <div>
            <h1 class="text-2xl font-bold mb-1">Ticket #{{ $ticket->id }}</h1>
            <p class="text-sm text-slate-600">
                {{ $ticket->name }}
                @if ($ticket->co_so) · {{ $ticket->co_so }}@endif
                @if ($ticket->contact) · {{ $ticket->contact }}@endif
                · {{ $ticket->created_at->format('d/m/Y H:i') }}
            </p>
        </div>
        <span class="text-xs px-3 py-1 rounded-full border {{ $ticket->statusColor() }} whitespace-nowrap">{{ $ticket->statusLabel() }}</span>
    </div>

    @if ($isAdmin)
    <div class="mb-4 flex gap-2 flex-wrap items-center text-sm">
        <span class="text-slate-600">Chuyển trạng thái:</span>
        @foreach (\App\Models\SupportTicket::STATUSES as $key => $label)
            <form method="POST" action="/ho-tro/{{ $ticket->id }}/trang-thai" class="inline">
                @csrf @method('PATCH')
                <input type="hidden" name="status" value="{{ $key }}">
                <button type="submit"
                        @class([
                            'px-3 py-1 rounded-full text-xs border',
                            'bg-slate-900 text-white border-slate-900' => $ticket->status === $key,
                            'border-slate-300 hover:bg-slate-50' => $ticket->status !== $key,
                        ])>{{ $label }}</button>
            </form>
        @endforeach
    </div>
    @endif

    <div class="space-y-3">
        @foreach ($ticket->messages as $m)
        <div class="flex {{ $m->sender_type === 'admin' ? 'justify-start' : 'justify-end' }}">
            <div class="max-w-[75%] rounded-xl px-4 py-3 {{ $m->sender_type === 'admin' ? 'bg-slate-100 text-slate-900' : 'bg-slate-900 text-white' }}">
                <div class="text-xs opacity-75 mb-1">
                    {{ $m->sender_type === 'admin' ? '🛡 Admin' : '👤' }} {{ $m->sender_name }} · {{ $m->created_at->format('d/m H:i') }}
                </div>
                <div class="whitespace-pre-wrap text-sm">{{ $m->body }}</div>
            </div>
        </div>
        @endforeach
    </div>

    <form method="POST" action="/ho-tro/{{ $ticket->id }}/tra-loi" class="mt-6 pt-4 border-t border-slate-100">
        @csrf
        <textarea name="body" rows="3" placeholder="Trả lời..." required
                  class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:border-slate-500"></textarea>
        <div class="mt-2 flex justify-end">
            <button type="submit" class="px-4 py-2 rounded-lg bg-slate-900 hover:bg-slate-800 text-white font-semibold">Gửi trả lời</button>
        </div>
    </form>
</div>
@endsection
