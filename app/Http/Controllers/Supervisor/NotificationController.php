<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = auth()->user()->notifications()->paginate(15);
        return view('supervisor.notifications.index', compact('notifications'));
    }

    public function markAsRead(string $id)
    {
        auth()->user()->notifications()->where('id', $id)->first()?->markAsRead();
        return back();
    }

    public function markAllRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
        return back()->with('success', 'All notifications have been marked as read!');
    }

    public function deleteRead()
    {
        auth()->user()->readNotifications()->delete();
        return back()->with('success', 'Read notifications have been deleted!');
    }
        public function readAndRedirect(string $id)
    {
        $notif = auth()->user()->notifications()->where('id', $id)->first();

        if ($notif) {
            $notif->markAsRead();

            $ticketId = $notif->data['ticket_id'] ?? null;

            if ($ticketId) {
                return redirect()->route('supervisor.tickets.show', $ticketId);
            }

            return redirect()->route('supervisor.tickets.index');
        }

        return redirect()->back();
    }
}
