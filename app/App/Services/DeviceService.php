<?php

declare(strict_types=1);

namespace App\Services;

use App\Entities\Device;
use App\Entities\DeviceTag;
use App\Entities\Tag;
use App\Repositories\DeviceTagRepository;
use App\Services\Parser\StackBuilder;
use Doctrine\ORM\EntityManager;

class DeviceService
{
    public function __construct(
        public EntityManager $entityManager,
        public DeviceTagRepository $deviceRepository
    ) {
    }

    public function create(int $deviceId, array $tags): int
    {
        $device = new Device();
        $device->id = $deviceId;
        $this->entityManager->persist($device);

        $this->syncDevicesTags($device, $tags);

        return $device->id;
    }

    public function edit(int $deviceId, array $tags): void
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->delete(DeviceTag::class, 'dt')
            ->where('dt.device = :deviceId')
            ->setParameter('deviceId', $deviceId)
            ->getQuery()->execute();

        $device = $this->entityManager->find(Device::class, $deviceId);

        if ($device) {
            $this->syncDevicesTags($device, $tags);
        }
    }

    /**
     * @return int[]
     */
    public function search(string $query): array
    {
        $stackBuilder = new StackBuilder();
        $stackBuilder->parse($query);

        return $this->deviceRepository->search($stackBuilder->getStack());
    }

    protected function syncDevicesTags(Device $device, array $tags): void
    {
        $repository = $this->entityManager->getRepository(Tag::class);
        foreach ($tags as $tagTitle => $tagValue) {
            $tag = $repository->findOneBy(['title' => $tagTitle]);
            if (!$tag) {
                $tag = new Tag();
                $tag->title = $tagTitle;
                $this->entityManager->persist($tag);
            } else {
                $tag = $this->entityManager->getReference(Tag::class, $tag->id);
            }

            $deviceTag = new DeviceTag();
            $deviceTag->device = $device;
            $deviceTag->tag = $tag;
            $deviceTag->value = $tagValue;

            $this->entityManager->persist($deviceTag);
        }

        $this->entityManager->flush();
    }
}
