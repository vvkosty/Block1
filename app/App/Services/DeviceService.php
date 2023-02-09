<?php

declare(strict_types=1);

namespace App\Services;

use App\Entities\Device;
use App\Entities\DeviceTag;
use App\Entities\Tag;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Illuminate\Support\Arr;

class DeviceService
{
    public function __construct(
        public EntityManager $entityManager
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
    public function search(array $tags): array
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('dt', 'd')
            ->from(DeviceTag::class, 'dt')
            ->leftJoin('dt.tag', 't')
            ->leftJoin('dt.device', 'd');

        $i = 0;
        $j = 0;
        foreach ($tags as $tagTitle => $tagValues) {
            $andX = $qb->expr()->andX();

            if (is_string($tagValues)) {
                $andX->add($qb->expr()->andX("t.title = :tagTitle{$i}"));
                $andX->add($qb->expr()->andX('dt.value = :tagValue'));
                $qb->andWhere($andX)
                    ->setParameter("tagTitle{$i}", $tagTitle)
                    ->setParameter('tagValue', $tagValues);
            } else {
                $orX = $qb->expr()->orX();
                foreach ($tagValues as $tagValue) {
                    $qb->setParameter("tagValue{$j}", $tagValue);
                    $orX->add("dt.value = :tagValue{$j}");
                    $j++;
                }

                $andX->add($orX);
                $andX->add($qb->expr()->andX("t.title = :tagTitle{$i}"));
                $qb->andWhere($andX)->setParameter("tagTitle{$i}", $tagTitle);
            }
            $i++;
        }

        $result = $qb->getQuery()->execute(null, AbstractQuery::HYDRATE_SCALAR);

        return Arr::pluck($result, 'd_id');
    }

    protected function syncDevicesTags(Device $device, array $tags): void
    {
        foreach ($tags as $tagTitle => $tagValue) {
            $tag = $this->entityManager->getRepository(Tag::class)->findOneBy(['title' => $tagTitle]);
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
