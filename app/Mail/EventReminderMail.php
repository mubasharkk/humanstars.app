<?php

namespace App\Mail;

use App\Models\CalendarEvent;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EventReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly CalendarEvent $event,
        public readonly User $recipient,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Reminder: {$this->event->title} starts in {$this->event->reminder_minutes} minutes",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.event-reminder',
        );
    }
}
