<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = auth()->user()->notifications()->paginate(15);
        return view('user.notifications.index', compact('notifications'));
    }

    public function markAsRead(string $id)
    {
        auth()->user()->notifications()->where('id', $id)->first()?->markAsRead();
        return back();
    }

    public function markAllRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
        return back()->with('success', 'All Notifications marked as read!');
    }

    public function deleteRead()
    {
        auth()->user()->readNotifications()->delete();
        return back()->with('success', 'Read notifications successfully deleted!');
    }
    public function readAndRedirect(string $id)
    {
        $notif = auth()->user()->notifications()->where('id', $id)->first();

        if ($notif) {
            $notif->markAsRead();

            $ticketId = $notif->data['ticket_id'] ?? null;

            if ($ticketId) {
                return redirect()->route('user.tickets.show', $ticketId);
            }

            return redirect()->route('user.tickets.index');
        }

        return redirect()->back();
}
}
