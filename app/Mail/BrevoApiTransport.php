<?php

namespace App\Mail;

use Illuminate\Support\Facades\Http;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\MessageConverter;

class BrevoApiTransport extends AbstractTransport
{
    public function __construct(
        protected string $apiKey
    ) {
        parent::__construct();
    }

    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());

        $to = [];
        foreach ($email->getTo() as $address) {
            $to[] = [
                'email' => $address->getAddress(),
                'name' => $address->getName() ?: null,
            ];
        }

        $from = $email->getFrom()[0] ?? null;
        $fromName = ($from && $from->getName()) ? $from->getName() : config('mail.from.name', 'SkillUp');
        $fromEmail = ($from && $from->getAddress()) ? $from->getAddress() : config('mail.from.address');

        $htmlBody = $email->getHtmlBody();
        if (empty($htmlBody)) {
            $htmlBody = nl2br($email->getTextBody() ?? 'SkillUp Notification');
        }

        $response = Http::withHeaders([
            'api-key' => $this->apiKey,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->timeout(15)->post('https://api.brevo.com/v3/smtp/email', [
            'sender' => [
                'name' => $fromName,
                'email' => $fromEmail,
            ],
            'to' => $to,
            'subject' => $email->getSubject() ?: 'Notification from SkillUp',
            'htmlContent' => $htmlBody,
        ]);

        if (! $response->successful()) {
            throw new \Exception('Brevo API Error (' . $response->status() . '): ' . $response->body());
        }
    }

    public function __toString(): string
    {
        return 'brevo-api';
    }
}
