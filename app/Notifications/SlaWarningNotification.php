<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SlaWarningNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Ticket $ticket,
        public string $phase // 'response' atau 'resolution'
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $label = $this->phase === 'response' ? 'Respon' : 'Penyelesaian';

                $url = match($notifiable->role) {
            'it_support'    => route('support.tickets.show', $this->ticket),
            'it_supervisor' => route('supervisor.tickets.show', $this->ticket),
            default         => route('user.tickets.show', $this->ticket),
        };

        return [
            'type'      => 'sla_warning_' . $this->phase,
            'ticket_id' => $this->ticket->id,
            'message'   => "⚠️ SLA {$label} tiket {$this->ticket->ticket_number} hampir terlewat!",
            'url'       => $url,
        ];
    }
}
