<?php

declare(strict_types=1);

namespace App\Commands;

use App\Core\App;

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
require dirname(__DIR__, 2) . '/config/bootstrap.php';

/** @var App $app */

$deviceId = (int)$argv[1];
$tagValue = (string)$argv[2];

if (!$deviceId || !$tagValue) {
    echo "Incorrect params\n";

    return;
}

$app->getDeviceService()->updateTagValue($deviceId, $_ENV['TAG_NOTIFICATION'], $tagValue);

echo "Done\n";
