<?php

declare(strict_types = 1);

namespace App\Services;

use App\Entities\Device;
use App\Repositories\DeviceRepository;
use NotificationSender;

class EmailService
{

    public function __construct(
        public DeviceRepository $deviceRepository,
        public NotificationSender $notificationSender,
    ) {
    }

    public function send(int $deviceId): void
    {
        /** @var Device $device */
        $device = $this->deviceRepository->find($deviceId);
        $this->notificationSender->send($device);
    }
}
