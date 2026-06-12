<?php

namespace App\Http\Controllers\Support;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\TicketLog;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request, Ticket $ticket)
    {
        $request->validate([
            'comment'   => 'required|string',
        ]);

        TicketComment::create([
            'ticket_id' => $ticket->id,
            'user_id'   => auth()->id(),
            'comment'   => $request->comment,
        ]);

        TicketLog::create([
            'ticket_id'     => $ticket->id,
            'updated_by'    => auth()->id(),
            'field_changed' => 'comment',
            'status_before' => null,
            'status_after'  => null,
            'note'          => 'IT Support menambahkan komentar',
        ]);

        // Notifikasi ke reporter
        $ticket->reporter->notify(new \App\Notifications\NewCommentNotification($ticket, auth()->user()));

        return back()->with('success', 'Komentar berhasil ditambahkan!');
    }
}
