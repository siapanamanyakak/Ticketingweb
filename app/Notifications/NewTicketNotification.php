<?php

namespace App\Notifications;

use App\Mail\TicketNotificationMail;
use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewTicketNotification extends Notification
{
    use Queueable;

    public function __construct(public Ticket $ticket) {}

    public function via(object $notifiable): array
    {
        $channels = ['database'];

        // Jika user punya email, tambahkan channel mail
        if (!empty($notifiable->email)) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    // Gunakan method toMail khusus untuk pengiriman email
    public function toMail(object $notifiable)
    {
    $departmentName = $this->ticket->reporter->department?->name ?? 'N/A';

    return (new TicketNotificationMail(
        notifTitle  : "New Ticket: {$this->ticket->ticket_number}",
        notifMessage: "A new ticket has been submitted by {$this->ticket->reporter->name} ({$departmentName}).",
        notifUrl    : route('support.tickets.show', $this->ticket),
        notifType   : 'info',
        ticket      : $this->ticket  // ← pass ticket untuk detail lengkap
    ))->to($notifiable->email);
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'      => 'new_ticket',
            'ticket_id' => $this->ticket->id,
            'message'   => "🎫 New ticket from {$this->ticket->reporter->name}: {$this->ticket->ticket_number} — {$this->ticket->title}",
            'url'       => route('support.tickets.show', $this->ticket),
        ];
    }
}
