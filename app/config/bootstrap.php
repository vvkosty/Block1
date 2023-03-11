<?php

declare(strict_types = 1);

use App\App;
use App\Controllers\DeviceController;
use App\Entities\Device;
use App\Entities\DeviceTag;
use App\Entities\Tag;
use App\Repositories\DeviceRepository;
use App\Repositories\DeviceTagRepository;
use App\Repositories\TagRepository;
use App\Services\DeviceService;
use App\Services\EmailService;
use App\Services\Notification\EmailNotificationSender;
use Doctrine\ORM\Mapping\ClassMetadata;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$app = new App();

$deviceService = new DeviceService(
    $app->entityManager,
    new DeviceTagRepository($app->entityManager, new ClassMetadata(DeviceTag::class)),
    new DeviceRepository($app->entityManager, new ClassMetadata(Device::class)),
    new TagRepository($app->entityManager, new ClassMetadata(Tag::class)),
    new EmailNotificationSender(),
);

$deviceController = new DeviceController($deviceService);

$emailService = new EmailService(
    new DeviceRepository($app->entityManager, new ClassMetadata(Device::class)),
    new EmailNotificationSender(),
);
