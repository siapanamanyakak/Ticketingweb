<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TicketStatusUpdated extends Notification
{
    use Queueable;

    public string $newStatus;
    public ?string $oldStatus;

    public function __construct(
        public Ticket $ticket,
        string $oldStatus
    ) {
        $this->oldStatus = $oldStatus;
        $this->newStatus = $ticket->status;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $typeMap = [
            'open'        => 'ticket_created',
            'in_progress' => 'status_updated',
            'pending'     => 'status_updated',
            'resolved'    => 'status_updated',
            'closed'      => 'status_updated',
        ];

        // Tentukan URL berdasarkan role notifiable (bukan auth user)
        $url = match($notifiable->role) {
            'it_support'    => route('support.tickets.show', $this->ticket),
            'it_supervisor' => route('supervisor.tickets.show', $this->ticket),
            default         => route('user.tickets.show', $this->ticket),
        };

        return [
            'type'       => $typeMap[$this->newStatus] ?? 'status_updated',
            'ticket_id'  => $this->ticket->id,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'message'    => $this->buildMessage(),
            'url'        => $url,
        ];
    }

    private function buildMessage(): string
    {
        $statusLabels = [
            'open'        => 'Open',
            'in_progress' => 'In Progress',
            'pending'     => 'Pending',
            'resolved'    => 'Resolved',
            'closed'      => 'Closed',
        ];

        $old = $statusLabels[$this->oldStatus] ?? $this->oldStatus;
        $new = $statusLabels[$this->newStatus] ?? $this->newStatus;

        if (!$this->oldStatus) {
            return "Ticket {$this->ticket->ticket_number} has been created successfully.";
        }

        return "Ticket status {$this->ticket->ticket_number} has been updated from {$old} to {$new}.";
    }
}
