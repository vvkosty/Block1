<?php

declare(strict_types=1);

namespace App\Commands;

use App\Core\App;

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
require dirname(__DIR__, 2) . '/config/bootstrap.php';

/** @var App $app */
$app->getDeviceService()->recreateEmails();

echo "Emails recreated\n";
