<?php

namespace App\Notifications;

use App\Mail\TicketNotificationMail;
use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TicketOutsideWorkingHours extends Notification
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

    // Pindahkan logika pengiriman email ke toMail()
    public function toMail(object $notifiable)
    {
        $message = "Ticket {$this->ticket->ticket_number} was submitted outside working hours. SLA will start when working hours begin.";

        return (new TicketNotificationMail(
            notifTitle  : "Outside Working Hours: {$this->ticket->ticket_number}",
            notifMessage: $message,
            notifUrl    : $this->getUrl($notifiable),
            notifType   : 'warning',
            ticket      : $this->ticket,
        ))->to($notifiable->email);
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'      => 'outside_working_hours',
            'ticket_id' => $this->ticket->id,
            'message'   => "🌙 Ticket {$this->ticket->ticket_number} submitted outside working hours. SLA starts when working hours begin.",
            'url'       => $this->getUrl($notifiable),
        ];
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
