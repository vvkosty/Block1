<?php

declare(strict_types=1);

use App\Services\DeviceService;

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
require dirname(__DIR__, 2) . '/config/bootstrap.php';

/** @var DeviceService $deviceService */
$deviceService->recreateEmails();

echo "Emails recreated\n";
