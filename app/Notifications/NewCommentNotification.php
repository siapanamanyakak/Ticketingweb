<?php

namespace App\Notifications;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewCommentNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Ticket $ticket,
        public User   $commenter
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        // Tentukan URL berdasarkan role penerima
        $url = match($notifiable->role) {
            'it_support'    => route('support.tickets.show', $this->ticket),
            'it_supervisor' => route('supervisor.tickets.show', $this->ticket),
            default         => route('user.tickets.show', $this->ticket),
        };

        return [
            'ticket_id'     => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'title'         => $this->ticket->title,
            'commenter'     => $this->commenter->name,
            'message'       => "{$this->commenter->name} menambahkan komentar pada tiket {$this->ticket->ticket_number}",
            'url'           => $url,
        ];
    }
}
