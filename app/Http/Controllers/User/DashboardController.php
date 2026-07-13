<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Ticket;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $stats = [
            'total'       => Ticket::where('user_id', $user->id)->count(),
            'open'        => Ticket::where('user_id', $user->id)->where('status', 'open')->count(),
            'in_progress' => Ticket::where('user_id', $user->id)->where('status', 'in_progress')->count(),
            'pending'     => Ticket::where('user_id', $user->id)->where('status', 'pending')->count(),
            'resolved'    => Ticket::where('user_id', $user->id)->whereIn('status', ['resolved', 'closed'])->count(),
        ];

        $recentTickets = Ticket::with(['category', 'priority', 'slaRecord'])
            ->where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        $activeNews = \App\Models\News::active()->latest()->get();

        return view('user.dashboard', compact('stats', 'recentTickets', 'activeNews'));
    }
}
