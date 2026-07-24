<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\TicketLog;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request, Ticket $ticket)
    {
        // Pastikan user hanya bisa komen di tiketnya sendiri
        if ($ticket->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'comment'   => 'required|string',
        ]);

        // 1. Tampung hasil create ke variabel $comment
        $comment = TicketComment::create([
            'ticket_id' => $ticket->id,
            'user_id'   => auth()->id(),
            'comment'   => $request->comment,
        ]);

        // Catat log
        TicketLog::create([
            'ticket_id'     => $ticket->id,
            'updated_by'    => auth()->id(),
            'field_changed' => 'comment',
            'status_before' => null,
            'status_after'  => null,
            'note'          => 'Comment added',
        ]);

        // Notifikasi ke IT Support kalau ada
        $support = \App\Models\User::where('role', 'it_support')->first();
        if ($support) {
            // 2. Tambahkan $comment sebagai argumen ketiga
            $support->notify(new \App\Notifications\NewCommentNotification($ticket, auth()->user(), $comment));
        }

        return back()->with('success', 'Comment added successfully!');
    }
}
