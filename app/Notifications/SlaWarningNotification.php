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
        public string $phase // 'response' or 'resolution'
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $label = $this->phase === 'response' ? 'Response' : 'Resolution';

        return [
            'type'      => 'sla_warning_' . $this->phase,
            'ticket_id' => $this->ticket->id,
            'message'   => "⚠️ SLA {$label} for ticket {$this->ticket->ticket_number} is about to breach!",
        ];
    }
}
