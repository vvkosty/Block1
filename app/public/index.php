<?php

declare(strict_types=1);

use App\Core\App;

$dirname = dirname(__DIR__);
$loader = require $dirname . '/vendor/autoload.php';
$loader->add('Documents', $dirname);

require_once $dirname . '/config/bootstrap.php';

/** @var App $app */

$request = json_decode(file_get_contents('php://input'), true, 512, JSON_THROW_ON_ERROR);
$url = $_SERVER['REQUEST_URI'];
$matches = [];

switch (true) {
    case preg_match("@^/device/create$@", $url):
        $response = $app->getDeviceController()->create($request);
        print json_encode($response, JSON_THROW_ON_ERROR);
        break;

    case preg_match("@^/device/(\d+)/edit$@", $url, $matches):
        $app->getDeviceController()->edit((int)$matches[1], $request);
        break;

    case preg_match("@^/device/search$@", $url):
        print json_encode($app->getDeviceController()->search($request['query']), JSON_THROW_ON_ERROR);
        break;
    default:
        http_response_code(404);
}
