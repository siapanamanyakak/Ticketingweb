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

        // 1. Tampung hasil create() ke dalam variabel $comment
        $comment = TicketComment::create([
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
            'note'          => 'IT Support commented on the ticket',
        ]);

        // 2. Kirimkan variabel $comment sebagai argumen ke-3 di sini
        $ticket->reporter->notify(new \App\Notifications\NewCommentNotification($ticket, auth()->user(), $comment));

        return back()->with('success', 'Comment added successfully!');
    }
}
