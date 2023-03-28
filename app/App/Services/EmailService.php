<?php

declare(strict_types=1);

namespace App\Services;

use App\Interfaces\DeviceRepositoryInterface;
use App\Interfaces\NotificationSenderInterface;

class EmailService
{

    public function __construct(
        public DeviceRepositoryInterface $deviceRepository,
        public NotificationSenderInterface $notificationSender,
    ) {
    }

    public function send(int $deviceId): void
    {
        $device = $this->deviceRepository->find($deviceId);

        if (isset($device->email)) {
            $this->notificationSender->send($device->email);
        }
    }
}
