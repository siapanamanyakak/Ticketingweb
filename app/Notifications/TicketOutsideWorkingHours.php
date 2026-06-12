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
            'message'   => "🌙 Tiket {$this->ticket->ticket_number} dibuat diluar jam kerja. SLA akan mulai saat jam kerja.",
            'url'       => $url,
        ];
    }
    }

