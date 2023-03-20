<?php

declare(strict_types=1);

namespace App\Services\Notification;

use App\Interfaces\NotificationSenderInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;

class EmailNotificationSender implements NotificationSenderInterface
{
    private Mailer $mailer;

    public function __construct()
    {
        $transport = Transport::fromDsn($_ENV['MAILER_DSN']);
        $this->mailer = new Mailer($transport);
    }

    public function send(string $email): void
    {
        $this->mailer->send(
            (new Email())
                ->from('block3@example.com')
                ->to($email)
                ->subject('Tag updated')
                ->text('Tag updated')
        );
    }
}
