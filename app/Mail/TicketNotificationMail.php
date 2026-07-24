<?php

namespace App\Mail;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string  $notifTitle,
        public string  $notifMessage,
        public ?string $notifUrl   = null,
        public string  $notifType  = 'info',
        public ?Ticket $ticket     = null,
        public ?string $commentText = null,
        public ?string $commenterName = null,
        // Tambahkan baris ini
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->notifTitle);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.ticket-notification');
    }
}
