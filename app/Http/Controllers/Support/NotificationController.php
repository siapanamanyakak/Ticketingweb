<?php

namespace App\Http\Controllers\Support;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = auth()->user()->notifications()->paginate(15);
        return view('support.notifications.index', compact('notifications'));
    }

    public function markAsRead(string $id)
    {
        auth()->user()->notifications()->where('id', $id)->first()?->markAsRead();
        return back();
    }

    public function markAllRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
        return back()->with('success', 'Semua notifikasi telah ditandai sudah dibaca!');
    }

    public function deleteRead()
    {
        auth()->user()->readNotifications()->delete();
        return back()->with('success', 'Notifikasi yang sudah dibaca berhasil dihapus!');
    }

    public function readAndRedirect(string $id)
    {
        $notif = auth()->user()->notifications()->where('id', $id)->first();

        if ($notif) {
            $notif->markAsRead();
            $url = $notif->data['url'] ?? route('support.tickets.index');
            return redirect($url);
        }

        return redirect()->route('support.tickets.index');
    }
}
