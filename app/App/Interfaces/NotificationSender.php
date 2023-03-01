<?php

declare(strict_types=1);

use App\Entities\Device;

interface NotificationSender
{
    public function send(Device $device): void;
}
