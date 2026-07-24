<?php

namespace App\Notifications;

use App\Mail\TicketNotificationMail;
use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TicketStatusUpdated extends Notification
{
    use Queueable;

    public string $newStatus;
    public ?string $oldStatus;

    // 1. Tambahkan ? dan = null agar opsional dan tidak memicu error argumen
    public function __construct(public Ticket $ticket, ?string $oldStatus = null)
    {
        // Jika controller mengirim kata 'new', kita paksa jadi null agar logika di bawah berjalan benar
        $this->oldStatus = $oldStatus === 'new' ? null : $oldStatus;
        $this->newStatus = $ticket->status;
    }

    public function via(object $notifiable): array
    {
        $channels = ['database'];

        // Cek jika user punya email, tambahkan channel 'mail'
        if (!empty($notifiable->email)) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    // 2. Gunakan method toMail khusus untuk mengirim email, jangan di dalam via()
    public function toMail(object $notifiable)
    {
        return (new TicketNotificationMail(
            notifTitle  : $this->getTitle(),
            notifMessage: $this->buildMessage(),
            notifUrl    : $this->getUrl($notifiable),
            notifType   : 'status',
        ))->to($notifiable->email);
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'       => !$this->oldStatus ? 'ticket_created' : 'status_updated',
            'ticket_id'  => $this->ticket->id,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'message'    => $this->buildMessage(),
            'url'        => $this->getUrl($notifiable),
        ];
    }

    private function getTitle(): string
    {
        return !$this->oldStatus
            ? "Ticket Created: {$this->ticket->ticket_number}"
            : "Status Updated: {$this->ticket->ticket_number}";
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

        return "Ticket {$this->ticket->ticket_number} status changed from {$old} to {$new}.";
    }

    private function getUrl(object $notifiable): string
    {
        return match($notifiable->role) {
            'it_support'    => route('support.tickets.show', $this->ticket),
            'it_supervisor' => route('supervisor.tickets.show', $this->ticket),
            default         => route('user.tickets.show', $this->ticket),
        };
    }
}
