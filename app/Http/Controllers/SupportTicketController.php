<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use Illuminate\Http\Request;

class SupportTicketController extends Controller
{
    public function index(Request $request)
    {
        $u = $request->user();
        $isAdmin = (bool) $u->is_admin;

        $q = SupportTicket::query()->latest();
        if (!$isAdmin) {
            $q->where('user_id', $u->id);
        }
        if ($status = $request->query('status')) {
            $q->where('status', $status);
        }

        $tickets = $q->paginate(30)->withQueryString();

        $counts = $isAdmin ? [
            'cho_xu_ly' => SupportTicket::where('status', 'cho_xu_ly')->count(),
            'da_xu_ly' => SupportTicket::where('status', 'da_xu_ly')->count(),
            'tu_choi' => SupportTicket::where('status', 'tu_choi')->count(),
        ] : null;

        return view('longevity.support.index', compact('tickets', 'isAdmin', 'counts'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'co_so' => ['nullable', 'string', 'max:80'],
            'contact' => ['nullable', 'string', 'max:120'],
            'description' => ['required', 'string', 'min:5', 'max:5000'],
        ]);

        $ticket = SupportTicket::create($data + [
            'user_id' => $request->user()->id,
            'status' => 'cho_xu_ly',
        ]);

        SupportTicketMessage::create([
            'ticket_id' => $ticket->id,
            'sender_type' => 'user',
            'sender_name' => $data['name'],
            'sender_user_id' => $request->user()->id,
            'body' => $data['description'],
        ]);

        return redirect("/ho-tro/{$ticket->id}")->with('ok', 'Đã gửi ticket #' . $ticket->id);
    }

    public function show(Request $request, int $id)
    {
        $ticket = SupportTicket::with('messages')->findOrFail($id);
        $u = $request->user();
        $isAdmin = (bool) $u->is_admin;

        abort_unless($isAdmin || $ticket->user_id === $u->id, 403);

        return view('longevity.support.show', compact('ticket', 'isAdmin'));
    }

    public function reply(Request $request, int $id)
    {
        $data = $request->validate(['body' => ['required', 'string', 'min:1', 'max:5000']]);

        $ticket = SupportTicket::findOrFail($id);
        $u = $request->user();
        $isAdmin = (bool) $u->is_admin;

        abort_unless($isAdmin || $ticket->user_id === $u->id, 403);

        SupportTicketMessage::create([
            'ticket_id' => $ticket->id,
            'sender_type' => $isAdmin ? 'admin' : 'user',
            'sender_name' => $u->name,
            'sender_user_id' => $u->id,
            'body' => $data['body'],
        ]);

        return back()->with('ok', 'Đã gửi trả lời');
    }

    public function updateStatus(Request $request, int $id)
    {
        $u = $request->user();
        abort_unless($u->is_admin, 403);

        $data = $request->validate(['status' => ['required', 'string']]);
        abort_unless(array_key_exists($data['status'], SupportTicket::STATUSES), 400);

        SupportTicket::where('id', $id)->update(['status' => $data['status']]);

        return back()->with('ok', 'Đã đổi trạng thái');
    }
}
