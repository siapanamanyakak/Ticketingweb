<?php

namespace App\Http\Controllers\Support;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketLog;
use App\Services\SlaService;
use Illuminate\Http\Request;
use App\Services\AutoCategoryService;
use Illuminate\Validation\Rule;


class TicketController extends Controller
{
public function index(Request $request)
    {
        $query = Ticket::with(['reporter', 'category', 'priority', 'slaRecord']);

        // Menggunakan $request agar konsisten
        $activeTab = $request->query('tab', 'all');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('ticket_number', 'like', '%' . $request->search . '%')
                  ->orWhere('title', 'like', '%' . $request->search . '%')
                  ->orWhereHas('reporter', fn($q) => $q->where('name', 'like', '%' . $request->search . '%'));
            });
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

        // Data untuk modal buat tiket
        $users      = \App\Models\User::whereIn('role', ['user', 'it_support'])
                        ->where('is_active', true)
                        ->orderBy('name')
                        ->get();
        $categories = \App\Models\Category::where('is_active', true)->get();
        $priorities = \App\Models\Priority::all();

        $tabCounts = Ticket::selectRaw('status, COUNT(*) as count')
        ->groupBy('status')
        ->pluck('count', 'status');

        $totalActive = Ticket::whereNotIn('status', ['closed'])->count();

        // Hanya gunakan SATU return di akhir fungsi dengan semua variabel yang dibutuhkan
        return view('support.tickets.index', compact(
            'tickets',
            'availableYears',
            'users',
            'categories',
            'priorities',
            'tabCounts',
            'totalActive'
        ));

    }


public function store(Request $request, SlaService $slaService, AutoCategoryService $autoCategoryService)
{
    $validated = $request->validate([
        'user_id'     => 'required|exists:users,id',
        'title'       => 'required|string|max:255',
        'description' => 'required|string',
        'attachment'  => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048',
        'category_id' => 'nullable|exists:categories,id',
        'priority_id' => 'nullable|exists:priorities,id',
    ]);

    // Auto detect sebagai fallback
    $detected   = $autoCategoryService->detect($validated['title'] . ' ' . $validated['description']);
    $categoryId = $request->filled('category_id') ? $request->category_id : $detected['category_id'];
    $priorityId = $request->filled('priority_id') ? $request->priority_id : $detected['priority_id'];

    // Handle attachment
    $attachmentPath = null;
    if ($request->hasFile('attachment')) {
        $attachmentPath = $request->file('attachment')->store('attachments', 'public');
    }

    $ticket = Ticket::create([
        'user_id'     => $validated['user_id'],
        'category_id' => $categoryId,
        'priority_id' => $priorityId,
        'title'       => $validated['title'],
        'description' => $validated['description'],
        'attachment'  => $attachmentPath,
        'status'      => 'open',
    ]);

    $slaService->createSlaRecord($ticket);

    TicketLog::create([
    'ticket_id'     => $ticket->id,
    'updated_by'    => auth()->id(),
    'field_changed' => 'status',
    'status_before' => null,
    'status_after'  => 'open',
    'note'          => auth()->id() === (int)$validated['user_id']
                        ? 'Ticket created by IT support for self reporting'
                        : 'Ticket created by IT Support for ' . $ticket->reporter->name,
    'visibility'    => 'all',
    ]);

    if (auth()->id() !== (int)$validated['user_id']) {
        $ticket->reporter->notify(new \App\Notifications\TicketStatusUpdated($ticket, 'new'));
    }

    if (!$slaService->isWorkingHours()) {
        $ticket->reporter->notify(new \App\Notifications\TicketOutsideWorkingHours($ticket));
    }

    // Setelah SLA record dibuat
    $ticket->load('reporter');

    // Kirim notifikasi ke semua IT Support & Supervisor kalau critical
    $priority = $ticket->priority;
    if ($priority && $priority->level === 'critical') {
        $recipients = \App\Models\User::whereIn('role', ['it_support', 'it_supervisor'])
            ->where('is_active', true)
            ->get();

        foreach ($recipients as $recipient) {
            $recipient->notify(new \App\Notifications\CriticalTicketNotification($ticket));
        }
    }

    // Notifikasi ke semua IT Support aktif
    $supports = \App\Models\User::where('role', 'it_support')
        ->where('is_active', true)
        ->get();

    foreach ($supports as $support) {
        $support->notify(new \App\Notifications\NewTicketNotification($ticket));
    }

    // Flash modal diluar jam kerja
    if (!$slaService->isWorkingHours()) {
        session()->flash('outside_working_hours', $ticket->ticket_number);
    }

    return back()->with('success', 'Ticket successfully created! Number: ' . $ticket->ticket_number);
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

    // Filter priority
    if ($request->filled('priority')) {
        $query->whereHas('priority', fn($q) => $q->where('level', $request->priority));
    }

    // Filter category
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

    return view('support.tickets.history', compact('tickets', 'availableYears'));
}

    public function show(Ticket $ticket, SlaService $slaService)
    {
        $ticket->load(['reporter', 'category', 'priority', 'slaRecord', 'slaPauses', 'comments.user', 'logs.updatedBy']);

        $slaRemaining = null;

        if ($ticket->slaRecord) {
            $phase = $ticket->first_response_at ? 'resolution' : 'response';
            $deadline = $phase === 'response'
                ? $ticket->slaRecord->response_deadline
                : $ticket->slaRecord->resolution_deadline;

            $slaRemaining = [
                'total_remaining_minutes' => $slaService->getRemainingWorkingMinutes($ticket, $phase),
                'total_sla_minutes'       => $slaService->getTotalSlaMinutes($ticket, $phase),
                'is_breached'             => $deadline ? now()->gte($deadline) : false,
            ];
        }

        // Tambahkan 'slaRemaining' ke dalam compact
        return view('support.tickets.show', compact('ticket', 'slaRemaining'));
    }

    public function updateStatus(Request $request, Ticket $ticket, SlaService $slaService)
    {


        $request->validate([
            'status'           => 'required|in:in_progress,pending,resolved,closed',
            'note'             => 'nullable|string',
            'resolution_notes' => 'nullable|string',
        ]);

        $oldStatus = $ticket->status;
        $newStatus = $request->status;


        if ($oldStatus === 'pending' && in_array($newStatus, ['in_progress', 'resolved'])) {
            $slaService->resumeSla($ticket);

            if ($ticket->pending_at) {
                $pendingDuration = $ticket->pending_at->diffInMinutes(now());
                $ticket->update([
                    'pending_duration' => $ticket->pending_duration + $pendingDuration,
                    'pending_at'       => null,
                ]);
            }
        }

        $updateData = ['status' => $newStatus];

        // ── Handle first response ─────────────────────
        if (!$ticket->first_response_at && $newStatus === 'in_progress') {
            $updateData['first_response_at'] = now();

            $ticket->slaRecord?->update([
                'response_met_at'   => now(),
                'response_breached' => now()->gt($ticket->slaRecord->response_deadline),
            ]);
        }

        // ── Handle pending ────────────────────────────
        if ($newStatus === 'pending') {
            $updateData['had_pending']   = true;
            $updateData['pending_at']    = now();
            $updateData['pending_count'] = $ticket->pending_count + 1;

            $slaService->pauseSla($ticket, 'pending');
        }

        // ── Handle resolved ───────────────────────────
        if ($newStatus === 'resolved') {
            $updateData['resolved_at']      = now();
            $updateData['resolution_notes'] = $request->resolution_notes;

            // Refresh slaRecord setelah resume (kalau dari pending)
            $ticket->refresh();
            $slaRecord = $ticket->slaRecord;

            $isBreached      = $slaRecord ? now()->gt($slaRecord->resolution_deadline) : false;
            $alreadyBreached = $slaRecord?->resolution_breached ?? false;

            $slaRecord?->update([
                'resolution_met_at'   => now(),
                'resolution_breached' => $alreadyBreached || $isBreached,
            ]);
        }

        // ── Handle closed ─────────────────────────────
        if ($newStatus === 'closed') {
            $updateData['closed_at'] = now();
        }

        $ticket->update($updateData);

        // ── Catat log ─────────────────────────────────
        $visibility = match($newStatus) {
            'pending' => 'all',
            'resolved' => 'support_only',
            'closed' => 'support_only',
            'in_progress' => 'support_only',
        };

        $logNote = $newStatus === 'resolved'
            ? ($request->resolution_notes ?? $request->note)
            : $request->note;

        TicketLog::create([
            'ticket_id'     => $ticket->id,
            'updated_by'    => auth()->id(),
            'field_changed' => 'status',
            'status_before' => $oldStatus,
            'status_after'  => $newStatus,
            'note'          => $logNote,
            'visibility'    => $visibility,
        ]);

        $ticket->reporter->notify(new \App\Notifications\TicketStatusUpdated($ticket, $oldStatus));

        return back()->with('success', 'Ticket status updated successfully!');
    }

    public function updateCategory(Request $request, Ticket $ticket)
    {
        $request->validate([
            'category_id' => [
    'required',
    Rule::exists('categories', 'id')->whereNull('deleted_at'),
],
            'note'        => 'nullable|string',
        ]);

        $oldCategory = $ticket->category?->name;
        $ticket->update(['category_id' => $request->category_id]);

        TicketLog::create([
            'ticket_id'     => $ticket->id,
            'updated_by'    => auth()->id(),
            'field_changed' => 'category',
            'status_before' => $oldCategory,
            'status_after'  => $ticket->fresh()->category?->name,
            'note'          => $request->note ?? null,
            'visibility'    => 'support_only',
        ]);

        return back()->with('success', 'Ticket category updated successfully!');
    }

    public function resolve(Request $request, Ticket $ticket, SlaService $slaService)
    {
        $request->validate([
            'resolution_notes' => 'required|string',
        ]);

        $oldStatus = $ticket->status;

        $ticket->update([
            'status'           => 'resolved',
            'resolved_at'      => now(),
            'resolution_notes' => $request->resolution_notes,
        ]);

        $ticket->slaRecord?->update([
            'resolution_met_at'   => now(),
            'resolution_breached' => now()->gt($ticket->slaRecord->resolution_deadline),
        ]);

        TicketLog::create([
            'ticket_id'     => $ticket->id,
            'updated_by'    => auth()->id(),
            'field_changed' => 'status',
            'status_before' => $oldStatus,
            'status_after'  => 'resolved',
            'note'          => $request->resolution_notes,
            'visibility'    => 'support_only',
        ]);

        $ticket->reporter->notify(new \App\Notifications\TicketStatusUpdated($ticket, $oldStatus));

        return back()->with('success', 'Ticket successfully resolved!');
    }

    public function updatePriority(Request $request, Ticket $ticket, SlaService $slaService)
    {
        $request->validate([
            'priority_id' => 'required|exists:priorities,id',
            'note'        => 'nullable|string',
        ]);

        $oldPriority = $ticket->priority?->level;

        $ticket->update(['priority_id' => $request->priority_id]);

        // Recalculate SLA — Idempotent dari created_at
        $slaService->recalculateSla($ticket->fresh());

        TicketLog::create([
            'ticket_id'     => $ticket->id,
            'updated_by'    => auth()->id(),
            'field_changed' => 'priority',
            'status_before' => $oldPriority,
            'status_after'  => $ticket->fresh()->priority?->level,
            'note'          => $request->note ?? null,
            'visibility'    => 'support_only',
        ]);

        return back()->with('success', 'Ticket priority updated successfully!');
    }
}


