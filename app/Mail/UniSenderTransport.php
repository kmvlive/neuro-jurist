<?php

namespace App\Mail;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\RawMessage;

class UniSenderTransport implements TransportInterface
{
    public function send(RawMessage $message, Envelope $envelope = null): ?SentMessage
    {
        if (!$message instanceof Email) {
            throw new \InvalidArgumentException('UniSender transport поддерживает только Email.');
        }

        $html = $message->getHtmlBody();
        $text = $message->getTextBody() ?? ($html ? strip_tags($html) : '');

        $recipients = [];
        foreach ($message->getTo() as $address) {
            $recipients[] = ['email' => $address->getAddress()];
        }

        $payload = [
            'message' => [
                'recipients' => $recipients,
                'subject' => $message->getSubject() ?? '',
                'from_email' => config('services.unisender.from_email'),
                'from_name' => config('services.unisender.from_name'),
                'body' => [
                    'html' => $html ?: '<p>' . e($text) . '</p>',
                    'plaintext' => $text,
                ],
                'skip_unsubscribe' => 0,
            ],
        ];

        $response = Http::withHeaders([
                'X-API-KEY' => config('services.unisender.api_key'),
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])
            ->timeout(20)
            ->post('https://goapi.unisender.ru/ru/transactional/api/v1/email/send.json', $payload);

        if (!$response->successful()) {
            Log::error('UniSender API error', ['status' => $response->status(), 'body' => $response->body()]);
            throw new \RuntimeException('UniSender API error: ' . $response->status() . ' ' . $response->body());
        }

        return new SentMessage($message, $envelope ?? Envelope::create($message));
    }

    public function __toString(): string
    {
        return 'unisender://';
    }
}
