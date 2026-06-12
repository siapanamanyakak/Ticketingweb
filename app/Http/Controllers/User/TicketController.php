<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketLog;
use App\Services\SlaService;
use App\Services\AutoCategoryService;
use Illuminate\Http\Request;

class TicketController extends Controller
{
   public function index(Request $request)
{
    $query = Ticket::with(['category', 'priority', 'slaRecord'])
        ->where('user_id', auth()->id())
        ->whereNotIn('status', ['closed']); // exclude closed

    if ($request->filled('search')) {
        $query->where(function ($q) use ($request) {
            $q->where('ticket_number', 'like', '%' . $request->search . '%')
              ->orWhere('title', 'like', '%' . $request->search . '%')
              ->orWhere('description', 'like', '%' . $request->search . '%');
        });
    }

    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    if ($request->filled('priority')) {
        $query->whereHas('priority', fn($q) => $q->where('level', $request->priority));
    }

    if ($request->filled('tab') && $request->tab !== 'all') {
        $query->where('status', $request->tab);
    }

    $tickets = $query->latest()->paginate(10)->appends(request()->query());

    $tabCounts = Ticket::where('user_id', auth()->id())
    ->selectRaw('status, COUNT(*) as count')
    ->groupBy('status')
    ->pluck('count', 'status');

    $totalActive = Ticket::where('user_id', auth()->id())
    ->whereNotIn('status', ['closed'])
    ->count();

    return view('user.tickets.index', compact('tickets', 'tabCounts', 'totalActive'));

}

public function history(Request $request)
{
    $query = Ticket::with(['category', 'priority', 'slaRecord'])
        ->where('user_id', auth()->id())
        ->where('status', 'closed');

    if ($request->filled('search')) {
        $query->where(function ($q) use ($request) {
            $q->where('ticket_number', 'like', '%' . $request->search . '%')
              ->orWhere('title', 'like', '%' . $request->search . '%');
        });
    }

    if ($request->filled('year')) {
        $query->whereYear('closed_at', $request->year);
    }
    if ($request->filled('month')) {
        $query->whereMonth('closed_at', $request->month);
    }
    if ($request->filled('day')) {
        $query->whereDay('closed_at', $request->day);
    }
    if ($request->filled('priority')) {
    $query->whereHas('priority', fn($q) => $q->where('level', $request->priority));
    }
    if ($request->filled('category')) {
    $query->where('category_id', $request->category);
    }

    $tickets = $query->latest()->paginate(10)->appends(request()->query());

    $availableYears = Ticket::where('user_id', auth()->id())
        ->where('status', 'closed')
        ->whereNotNull('closed_at')
        ->selectRaw('YEAR(closed_at) as year')
        ->distinct()
        ->orderBy('year', 'desc')
        ->pluck('year');

    return view('user.tickets.history', compact('tickets', 'availableYears'));
}

    public function create()
    {
        return view('user.tickets.create');
    }

    public function store(Request $request, SlaService $slaService, AutoCategoryService $autoCategoryService)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'attachment'  => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048',
        ]);

        // Auto detect category & priority
        $detected   = $autoCategoryService->detect($validated['title'] . ' ' . $validated['description']);
        $categoryId = $detected['category_id'];
        $priorityId = $detected['priority_id'];

        // Handle attachment
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('attachments', 'public');
        }

        $ticket = Ticket::create([
            'user_id'     => auth()->id(),
            'category_id' => $categoryId,
            'priority_id' => $priorityId,
            'title'       => $validated['title'],
            'description' => $validated['description'],
            'attachment'  => $attachmentPath,
            'status'      => 'open',
        ]);

        // Buat SLA record
        $slaService->createSlaRecord($ticket);

        $priority = $ticket->priority;
        if ($priority && $priority->level === 'critical') {
            $recipients = \App\Models\User::whereIn('role', ['it_support', 'it_supervisor'])
                ->where('is_active', true)
                ->get();

            foreach ($recipients as $recipient) {
                $recipient->notify(new \App\Notifications\CriticalTicketNotification($ticket));
            }
        }
        // Kirim notifikasi kalau diluar jam kerja
        if (!$slaService->isWorkingHours()) {
        $ticket->reporter->notify(new \App\Notifications\TicketOutsideWorkingHours($ticket));
        }

        // Catat log
        TicketLog::create([
        'ticket_id'     => $ticket->id,
        'updated_by'    => auth()->id(),
        'field_changed' => 'status',
        'status_before' => null,
        'status_after'  => 'open',
        'note'          => 'Tiket baru dibuat',
        'visibility'    => 'all',
        ]);

        // Flash modal diluar jam kerja
        if (!$slaService->isWorkingHours()) {
            session()->flash('outside_working_hours', $ticket->ticket_number);
        }

        return redirect()->route('user.tickets.show', $ticket)
            ->with('success', 'Tiket berhasil dibuat! Nomor tiket: ' . $ticket->ticket_number);
    }

    public function show(Ticket $ticket)
    {
        // Pastikan user hanya bisa lihat tiketnya sendiri
        if ($ticket->user_id !== auth()->id()) {
            abort(403);
        }

        $ticket->load(['category', 'priority', 'slaRecord', 'comments.user', 'logs.updatedBy']);

        return view('user.tickets.show', compact('ticket'));
    }
}

