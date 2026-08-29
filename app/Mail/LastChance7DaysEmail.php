<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LastChance7DaysEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $user, public string $promoCode) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: '💸 Последнее предложение — скидка только сегодня');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.last-chance-7days');
    }
}
