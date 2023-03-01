<?php

declare(strict_types=1);

use App\App;
use App\Controllers\DeviceController;
use App\Entities\Device;
use App\Entities\DeviceTag;
use App\Repositories\DeviceRepository;
use App\Repositories\DeviceTagRepository;
use App\Services\DeviceService;
use Doctrine\ORM\Mapping\ClassMetadata;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$app = new App();

$deviceService = new DeviceService(
    $app->entityManager,
    new DeviceTagRepository($app->entityManager, new ClassMetadata(DeviceTag::class)),
    new DeviceRepository($app->entityManager, new ClassMetadata(Device::class)),
);

$deviceController = new DeviceController($deviceService);
