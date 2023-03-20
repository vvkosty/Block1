<?php

declare(strict_types=1);

namespace App\Core;

use App\Controllers\DeviceController;
use App\Interfaces\DeviceRepositoryInterface;
use App\Interfaces\DeviceServiceInterface;
use App\Services\DeviceService;
use App\Services\EmailQueueService;
use App\Services\EmailService;
use App\Services\Notification\EmailNotificationSender;
use App\Services\RedisService;

abstract class App
{
    public DeviceServiceInterface $deviceService;
    public EmailService $emailService;

    public RedisService $redisService;

    public EmailQueueService $emailQueueService;

    public EmailNotificationSender $emailNotificationSender;

    public DeviceRepositoryInterface $deviceRepository;

    public function getDeviceController(): DeviceController
    {
        return new DeviceController($this->getDeviceService());
    }

    public function getDeviceService(): DeviceServiceInterface
    {
        return $this->deviceService ?? new DeviceService(
            $this->objectManager,
            $this->getRedisService(),
            $this->getEmailQueueService(),
            $this->getEmailNotificationSender(),
            $this->getDeviceRepository(),
        );
    }

    public function getEmailService(): EmailService
    {
        return $this->emailService ?? new EmailService(
            $this->getDeviceRepository(),
            $this->getEmailNotificationSender(),
        );
    }

    /**
     * @return RedisService
     */
    public function getRedisService(): RedisService
    {
        return $this->redisService ?? new RedisService();
    }

    /**
     * @return EmailQueueService
     */
    public function getEmailQueueService(): EmailQueueService
    {
        return $this->emailQueueService ?? new EmailQueueService();
    }

    public function getEmailNotificationSender(): EmailNotificationSender
    {
        return $this->emailNotificationSender ?? new EmailNotificationSender();
    }

    abstract public function createDatabaseConnection(): void;

    abstract public function getDeviceRepository(): DeviceRepositoryInterface;
}
