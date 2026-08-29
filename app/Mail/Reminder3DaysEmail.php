<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class Reminder3DaysEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $user, public string $promoCode) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: '⏰ Вы давно не заходили — у нас для вас сюрприз');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.reminder-3days');
    }
}
