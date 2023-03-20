<?php

declare(strict_types=1);

namespace App\Services;

use App\Interfaces\DeviceRepositoryInterface;
use App\Interfaces\DeviceServiceInterface;
use App\Interfaces\NotificationSenderInterface;
use App\Services\Parser\StackBuilder;
use DateTime;
use Doctrine\Persistence\ObjectManager;

class DeviceService implements DeviceServiceInterface
{

    public function __construct(
        public ObjectManager $objectManager,
        public RedisService $redisService,
        public EmailQueueService $emailQueueService,
        public NotificationSenderInterface $notificationSender,
        public DeviceRepositoryInterface $deviceRepository,
    ) {
    }

    public function create(int $deviceId, array $tags): int
    {
        return $this->deviceRepository->create($deviceId, $tags)->id;
    }

    public function edit(int $deviceId, array $tags): void
    {
        $this->deviceRepository->edit($deviceId, $tags);
    }

    /**
     * @return int[]
     */
    public function search(string $query): array
    {
        $cachedResult = $this->redisService->get(md5($query));
        if ($cachedResult) {
            return $cachedResult;
        }

        $stackBuilder = new StackBuilder();
        $stackBuilder->parse($query);

        $result = $this->deviceRepository->search($stackBuilder->getStack());
        $this->redisService->set(md5($query), $result);

        return $result;
    }

    public function removeByCreateDate(DateTime $from, DateTime $to): void
    {
        $this->deviceRepository->removeByCreateDates($from, $to);
    }

    public function recreateEmails(): void
    {
        $this->deviceRepository->recreateEmails();
    }

    public function updateTagValue(int $deviceId, string $tagName, string $tagValue): void
    {
        $this->deviceRepository->updateTagValue($tagName, $deviceId, $tagValue);
        $this->emailQueueService->sendTagUpdatedEvent($deviceId);
    }
}
