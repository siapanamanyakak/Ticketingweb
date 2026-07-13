<?php

namespace App\Http\Controllers\Support;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\SlaRecord;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $stats = [
            'open'        => Ticket::where('status', 'open')->count(),
            'in_progress' => Ticket::where('status', 'in_progress')->count(),
            'pending'     => Ticket::where('status', 'pending')->count(),
            'sla_breached' => SlaRecord::where('resolution_breached', true)
                                ->whereHas('ticket', fn($q) => $q->whereNotIn('status', ['resolved', 'closed']))
                                ->count(),
        ];

        $openTickets = Ticket::with(['reporter', 'category', 'priority', 'slaRecord'])
            ->where('status', 'open')
            ->latest()
            ->take(5)
            ->get();

        $activeNews = \App\Models\News::active()->latest()->get();

        return view('support.dashboard', compact('stats', 'openTickets', 'activeNews'));
    }
}
