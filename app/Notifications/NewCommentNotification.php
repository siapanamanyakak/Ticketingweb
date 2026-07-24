<?php

namespace App\Notifications;

use App\Mail\TicketNotificationMail; // <-- Pastikan class ini di-import
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewCommentNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Ticket $ticket,
        public User   $commenter,
        public object $comment
    ) {}

    public function via(object $notifiable): array
    {
        $channels = ['database'];
        if (!empty($notifiable->email)) {
            $channels[] = 'mail';
        }
        return $channels;
    }

    public function toMail(object $notifiable)
    {
        $url = match($notifiable->role) {
            'it_support'    => route('support.tickets.show', $this->ticket),
            'it_supervisor' => route('supervisor.tickets.show', $this->ticket),
            default         => route('user.tickets.show', $this->ticket),
        };

        // Gunakan TicketNotificationMail yang memanggil template master, BUKAN MailMessage
        return (new TicketNotificationMail(
            notifTitle  : "New Comment: {$this->ticket->ticket_number}",
            notifMessage: "{$this->commenter->name} added a new comment to this ticket.",
            notifUrl    : $url,
            notifType   : 'new_comment',
            ticket      : $this->ticket,
            commentText : $this->comment->comment,
            commenterName : $this->commenter->name
        ))->to($notifiable->email);
    }

    public function toDatabase(object $notifiable): array
    {
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
            'message'       => "{$this->commenter->name} Added a comment to ticket {$this->ticket->ticket_number}",
            'url'           => $url,
        ];
    }
}
