<?php

declare(strict_types=1);

use App\Controllers\DeviceController;
use App\Services\DeviceService;

require_once dirname(__DIR__) . '/vendor/autoload.php';
require dirname(__DIR__) . '/config/bootstrap.php';

$deviceController = new DeviceController(
    new DeviceService($app->entityManager)
);

$url = $_SERVER['REQUEST_URI'];
$matches = [];

switch (true) {
    case preg_match("@^/device/create$@", $url):
        print json_encode($deviceController->create($_POST), JSON_THROW_ON_ERROR);
        break;

    case preg_match("@^/device/(\d+)/edit$@", $url, $matches):
        $deviceController->edit((int)$matches[1], $_POST);
        break;

    case preg_match("@^/device\?.+$@", $url):
        print json_encode($deviceController->search($_GET), JSON_THROW_ON_ERROR);
        break;
    default:
        http_response_code(404);
}
