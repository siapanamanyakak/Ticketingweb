<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Services\SlaService;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function index(Request $request)
{
    $query = Ticket::with(['reporter', 'category', 'priority', 'slaRecord']);

    $activeTab = request('tab', 'all');

    if ($request->filled('search')) {
        $query->where(function ($q) use ($request) {
            $q->where('ticket_number', 'like', '%' . $request->search . '%')
              ->orWhere('title', 'like', '%' . $request->search . '%')
              ->orWhereHas('reporter', fn($q) => $q->where('name', 'like', '%' . $request->search . '%'));
        });
    }

    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    if ($request->filled('priority')) {
        $query->whereHas('priority', fn($q) => $q->where('level', $request->priority));
    }

    if ($request->filled('category')) {
        $query->where('category_id', $request->category);
    }

    if ($activeTab === 'closed') {
        $query->where('status', 'closed');

        if ($request->filled('year')) {
            $query->whereYear('closed_at', $request->year);
        }
        if ($request->filled('month')) {
            $query->whereMonth('closed_at', $request->month);
        }
        if ($request->filled('day')) {
            $query->whereDay('closed_at', $request->day);
        }
    } elseif ($activeTab === 'all') {
        $query->whereNotIn('status', ['closed']);
    } else {
        $query->where('status', $activeTab);
    }

    $tickets = $query->latest()->paginate(10)->appends(request()->query());

    $availableYears = Ticket::where('status', 'closed')
        ->whereNotNull('closed_at')
        ->selectRaw('YEAR(closed_at) as year')
        ->distinct()
        ->orderBy('year', 'desc')
        ->pluck('year');

    $tabCounts   = Ticket::selectRaw('status, COUNT(*) as count')->groupBy('status')->pluck('count', 'status');
    $totalActive = Ticket::whereNotIn('status', ['closed'])->count();

    return view('supervisor.tickets.index', compact('tickets', 'availableYears', 'tabCounts', 'totalActive'));
}

public function history(Request $request)
{
    $query = Ticket::with(['reporter', 'category', 'priority', 'slaRecord'])
        ->where('status', 'closed');

    // Filter search
    if ($request->filled('search')) {
        $query->where(function ($q) use ($request) {
            $q->where('ticket_number', 'like', '%' . $request->search . '%')
              ->orWhere('title', 'like', '%' . $request->search . '%')
              ->orWhereHas('reporter', fn($q) => $q->where('name', 'like', '%' . $request->search . '%'));
        });
    }

    // Filter tanggal
    if ($request->filled('year')) {
        $query->whereYear('closed_at', $request->year);
    }

    if ($request->filled('month')) {
        $query->whereMonth('closed_at', $request->month);
    }

    if ($request->filled('day')) {
        $query->whereDay('closed_at', $request->day);
    }

    // Filter priority & category
    if ($request->filled('priority')) {
        $query->whereHas('priority', fn($q) => $q->where('level', $request->priority));
    }

    if ($request->filled('category')) {
        $query->where('category_id', $request->category);
    }

    $tickets = $query->latest()->paginate(10)->appends(request()->query());

    $availableYears = Ticket::where('status', 'closed')
        ->whereNotNull('closed_at')
        ->selectRaw('YEAR(closed_at) as year')
        ->distinct()
        ->orderBy('year', 'desc')
        ->pluck('year');

    return view('supervisor.tickets.history', compact('tickets', 'availableYears'));
}

    public function show(Ticket $ticket, SlaService $slaService)
    {
        $ticket->load(['reporter', 'category', 'priority', 'slaRecord', 'slaPauses', 'comments.user', 'logs.updatedBy']);

        $slaRemaining = null;

        if ($ticket->slaRecord) {
            // 1. Tentukan saat ini sedang mengejar SLA Response atau Resolution
            $phase = $ticket->first_response_at ? 'resolution' : 'response';

            // 2. Ambil deadline untuk mengecek status breached (terlambat)
            $deadline = $phase === 'response'
                ? $ticket->slaRecord->response_deadline
                : $ticket->slaRecord->resolution_deadline;

            // 3. Rakit array manual memanggil fungsi SlaService yang baru
            $slaRemaining = [
                'total_remaining_minutes' => $slaService->getRemainingWorkingMinutes($ticket, $phase),
                'total_sla_minutes'       => $slaService->getTotalSlaMinutes($ticket, $phase),
                'is_breached'             => $deadline ? now()->gte($deadline) : false,
            ];
        }

        return view('supervisor.tickets.show', compact('ticket', 'slaRemaining'));
    }
}
