<?php

namespace App\Notifications;

use App\Models\Ticket;
use App\Services\SlaService;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TicketOutsideWorkingHours extends Notification
{
    use Queueable;

    public function __construct(public Ticket $ticket) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $url = match($notifiable->role) {
            'it_support'    => route('support.tickets.show', $this->ticket),
            'it_supervisor' => route('supervisor.tickets.show', $this->ticket),
            default         => route('user.tickets.show', $this->ticket),
        };

        return [
            'type'      => 'outside_working_hours',
            'ticket_id' => $this->ticket->id,
            'message'   => "🌙 Ticket {$this->ticket->ticket_number} was created outside working hours. SLA will start when working hours begin.",
            'url'       => $url,
        ];
    }
    }

