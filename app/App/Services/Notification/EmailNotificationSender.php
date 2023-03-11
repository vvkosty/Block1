<?php

declare(strict_types=1);

namespace App\Services\Notification;

use App\Entities\Device;
use NotificationSender;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;

class EmailNotificationSender implements NotificationSender
{
    private Mailer $mailer;

    public function __construct()
    {
        $transport = Transport::fromDsn($_ENV['MAILER_DSN']);
        $this->mailer = new Mailer($transport);
    }

    public function send(Device $device): void
    {
        if (!isset($device->email)) {
            return;
        }

        $email = (new Email())
            ->from('block3@example.com')
            ->to($device->email)
            ->subject('Tag updated')
            ->text('Tag updated');

        $this->mailer->send($email);
    }
}
