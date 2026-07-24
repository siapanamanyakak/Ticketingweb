<?php

namespace App\Notifications;

use App\Mail\TicketNotificationMail;
use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SlaWarningNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Ticket $ticket,
        public string $phase
    ) {}

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
        $label   = $this->phase === 'response' ? 'Response' : 'Resolution';
        $message = "⚠️ SLA {$label} for ticket {$this->ticket->ticket_number} is about to be breached!";

        return (new TicketNotificationMail(
            notifTitle  : "SLA Warning: {$this->ticket->ticket_number}",
            notifMessage: $message,
            notifUrl    : route('support.tickets.show', $this->ticket),
            notifType   : 'sla_warning',
            ticket      : $this->ticket,
        ))->to($notifiable->email);
    }

    public function toDatabase(object $notifiable): array
    {
        $label = $this->phase === 'response' ? 'Response' : 'Resolution';

        return [
            'type'      => 'sla_warning_' . $this->phase,
            'ticket_id' => $this->ticket->id,
            'message'   => "⚠️ SLA {$label} for ticket {$this->ticket->ticket_number} is about to be breached!",
            'url'       => route('support.tickets.show', $this->ticket),
        ];
    }
}
