<?php

declare(strict_types=1);

namespace App\Commands;

use App\Core\App;
use DateInterval;
use DatePeriod;
use DateTime;

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
require dirname(__DIR__, 2) . '/config/bootstrap.php';

pcntl_async_signals(false);

/** @var App $app */

$dtStart = new DateTime($argv[1] ?? 'now');
$dtEnd = new DateTime($argv[2] ?? 'now -1 hour');

if ($dtEnd < $dtStart) {
    echo "Incorrect params\n";

    return;
}

$interval = DateInterval::createFromDateString('1 day');
$dateRange = new DatePeriod($dtStart, $interval, $dtEnd);

$parentPgid = posix_getpgid(posix_getpid());
echo "Root PGID $parentPgid\n";

pcntl_signal(SIGTERM, static function () {
    echo "Wait while job is done...\n";
    exit;
});

$processNumber = 10;
$tasks = array_chunk(iterator_to_array($dateRange->getIterator()), $processNumber);
foreach ($tasks as $dates) {
    foreach ($dates as $from) {
        $to = clone $from;
        $to->modify('+1 day');
        if ($to > $dtEnd) {
            break;
        }
        $pid = pcntl_fork();
        if ($pid === -1) {
            exit("fork error!\n");
        }
        if (!$pid) {
            echo "Removing {$from->format('Y-m-d')} to {$to->format('Y-m-d')}...\n";
            $app->getDeviceService()->removeByCreateDate($from, $to);

            exit;
        }
    }

    while (pcntl_waitpid(0, $status, WUNTRACED) !== -1) {
        $code = pcntl_wexitstatus($status);
        pcntl_signal_dispatch();
    }
}

echo "Done\n";
