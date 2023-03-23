<?php

declare(strict_types=1);

namespace App\Commands;

use App\Core\App;
use Faker\Factory;
use Illuminate\Support\Arr;

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
require dirname(__DIR__, 2) . '/config/bootstrap.php';
/** @var App $app */

$faker = Factory::create();

$tags = [];
for ($i = 0; $i < 50; $i++) {
    $tags[$faker->word()] = $faker->word();
}

$totalRows = 10000;
$batch = 500;

$deviceRepository = $app->getDeviceRepository();
$processNumber = (int)ceil($totalRows / $batch);
$lastBatch = $totalRows % $batch;
$i = 0;
$processedRows = 0;

$time_start = microtime(true);

while ($processedRows < $totalRows) {
    if ($totalRows - $processedRows === $lastBatch) {
        $batch = $lastBatch;
    }

    if ($i < $processNumber) {
        ++$i;
        $pid = pcntl_fork();
        if (!$pid) {
            $devices = [];
            $from = $processedRows + 1;
            $to = $from + $batch;
            while ($from < $to) {
                $devices[$from] = Arr::random($tags, random_int(1, 20), true);
                $from++;
            }
            $deviceRepository->createBatch($devices);

            exit;
        }
    }

    --$i;
    $processedRows += $batch;
    echo "Inserting rows: $processedRows\r";
    if ($processedRows === $totalRows) {
        echo "\n";
    }
}

while (pcntl_waitpid(0, $status) !== -1) {
    $status = pcntl_wexitstatus($status);
}

echo 'Total execution time in seconds: ' . (microtime(true) - $time_start) . "\n";

echo "Done\n";
