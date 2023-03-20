<?php

declare(strict_types=1);

namespace App\Interfaces;

interface NotificationSenderInterface
{
    public function send(string $email): void;
}
