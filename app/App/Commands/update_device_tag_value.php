<?php

declare(strict_types=1);

namespace App\Commands;

use App\Services\DeviceService;

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
require dirname(__DIR__, 2) . '/config/bootstrap.php';

/** @var DeviceService $deviceService */

$deviceId = (int)$argv[1];
$tagValue = $argv[2];

if (!$deviceId || !$tagValue) {
    echo "Incorrect params\n";

    return;
}

$deviceService->updateTagValue($deviceId, $_ENV['TAG_NOTIFICATION'], $tagValue);

echo "Done\n";
