<?php

declare(strict_types=1);

use App\Controllers\DeviceController;

require_once dirname(__DIR__) . '/vendor/autoload.php';
require dirname(__DIR__) . '/config/bootstrap.php';

/** @var DeviceController $deviceController */

$request = json_decode(file_get_contents('php://input'), true, 512, JSON_THROW_ON_ERROR);
$url = $_SERVER['REQUEST_URI'];
$matches = [];

switch (true) {
    case preg_match("@^/device/create$@", $url):
        $response = $deviceController->create($request);
        print json_encode($response, JSON_THROW_ON_ERROR);
        break;

    case preg_match("@^/device/(\d+)/edit$@", $url, $matches):
        $deviceController->edit((int)$matches[1], $request);
        break;

    case preg_match("@^/device/search$@", $url):
        print json_encode($deviceController->search($request['query']), JSON_THROW_ON_ERROR);
        break;
    default:
        http_response_code(404);
}
