<?php

declare(strict_types=1);

namespace App\Commands;

use App\Core\App;
use DateTime;

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
require dirname(__DIR__, 2) . '/config/bootstrap.php';

pcntl_async_signals(false);

$dtStart = new DateTime($argv[1] ?? 'now');
$dtEnd = new DateTime($argv[2] ?? 'now -1 hour');

if ($dtEnd < $dtStart) {
    echo "Incorrect params\n";

    return;
}

$parentPid = posix_getpid();
echo "Root PID $parentPid\n";

pcntl_signal(SIGTERM, static function () {
    echo "Wait while job is done...\n";
    exit;
});

echo "Removing {$dtStart->format('Y-m-d')} - {$dtEnd->format('Y-m-d')}...\n";
/** @var App $app */
$app->getDeviceService()->removeByCreateDate($dtStart, $dtEnd);

echo "Done\n";
