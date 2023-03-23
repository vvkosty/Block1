<?php

declare(strict_types=1);

namespace App\Repositories\Postgres;

use App\Entities\Device;
use App\Entities\DeviceTag;
use App\Entities\Tag as TagEntity;
use App\Enums\TagOperation;
use App\Interfaces\DeviceRepositoryInterface;
use App\Services\Parser\Tag;
use App\Services\Parser\TagValueOperation;
use DateTime;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Ds\Stack;
use Faker\Factory;
use Generator;

class DeviceRepository extends EntityRepository implements DeviceRepositoryInterface
{
    public function findByCreateDates(DateTime $from, DateTime $to): Generator
    {
        $entity = $this->getEntityName();

        $qb = $this->_em->createQuery("SELECT d FROM $entity d WHERE d.createdAt BETWEEN :from AND :to");
        $qb->setParameter('from', $from);
        $qb->setParameter('to', $to);

        return $qb->toIterable();
    }

    public function recreateEmails(): void
    {
        $entity = $this->getEntityName();
        $q = $this->_em->createQuery("SELECT d FROM $entity d")->toIterable();

        $batchSize = 20;
        $i = 1;
        $faker = Factory::create();

        foreach ($q as $device) {
            ++$i;
            $device->email = $faker->email();
            if (($i % $batchSize) === 0) {
                $this->_em->flush();
                $this->_em->clear();
            }
        }

        $this->_em->flush();
        $this->_em->clear();
    }

    public function create(int $deviceId, array $tags): Device
    {
        $device = new Device();
        $device->id = $deviceId;
        $this->_em->persist($device);

        $this->syncDevicesTags($device, $tags);
        $this->_em->flush();

        return $device;
    }

    public function createBatch(array $devices): void
    {
        foreach ($devices as $deviceId => $tags) {
            $device = new Device();
            $device->id = $deviceId;
            $this->_em->persist($device);
            $this->syncDevicesTags($device, $tags);
        }

        $this->_em->flush();
        $this->_em->clear();
        
    }

    public function edit(int $deviceId, array $tags): void
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->delete(DeviceTag::class, 'dt')
            ->where('dt.device = :deviceId')
            ->setParameter('deviceId', $deviceId)
            ->getQuery()->execute();

        $device = $this->_em->find(Device::class, $deviceId);

        if ($device) {
            $this->syncDevicesTags($device, $tags);
            $this->_em->flush();
        }
    }

    public function search(Stack $infixStack): array
    {
        $dtClass = DeviceTag::class;
        $queryString = "SELECT DISTINCT IDENTITY(dt.device, 'id') FROM {$dtClass} dt LEFT JOIN dt.tag t WHERE ";
        $qb = $this->_em->createQuery($queryString);

        /** @var Tag|TagOperation $item */
        foreach ($infixStack as $key => $item) {
            if ($item instanceof Tag) {
                $this->setTag($qb, $item, $key);
            } elseif ($item instanceof TagOperation) {
                $this->setTagOperation($qb, $item);
            }
        }

        return $qb->getSingleColumnResult();
    }

    public function removeByCreateDates(DateTime $from, DateTime $to): void
    {
        $batchSize = 20;
        $i = 1;

        $devices = $this->findByCreateDates($from, $to);

        /** @var Device $device */
        foreach ($devices as $device) {
            $this->_em->getRepository(DeviceTag::class)->removeByDevice($device);
            $this->_em->remove($device);

            sleep(10); // исскуственная задержка, для теста прерывания

            if (($i % $batchSize) === 0) {
                $this->_em->flush();
                $this->_em->clear();
                pcntl_signal_dispatch();
            }
            ++$i;
        }

        $this->_em->flush();
        $this->_em->clear();
    }

    public function updateTagValue(string $tagName, int $deviceId, string $tagValue): void
    {
        /** @var Device $device */
        $device = $this->find($deviceId);

        /** @var TagEntity $tag */
        $tag = $this->_em->getRepository(TagEntity::class)->findOneBy(['title' => $tagName]);

        /** @var DeviceTag $deviceTag */
        $deviceTag = $this->_em->getRepository(DeviceTag::class)->findOneBy(['device' => $device, 'tag' => $tag]);

        $deviceTag->value = $tagValue;
        $this->_em->persist($deviceTag);
        $this->_em->flush();
    }

    private function syncDevicesTags(Device $device, array $tags): void
    {
        $repository = $this->_em->getRepository(TagEntity::class);
        foreach ($tags as $tagTitle => $tagValue) {
            $tag = $repository->findOneBy(['title' => $tagTitle]);
            if (!$tag) {
                $tag = new TagEntity();
                $tag->title = $tagTitle;
                $this->_em->persist($tag);
            } else {
                $tag = $this->_em->getReference(TagEntity::class, $tag->id);
            }

            $deviceTag = new DeviceTag();
            $deviceTag->device = $device;
            $deviceTag->tag = $tag;
            $deviceTag->value = (string)$tagValue;

            $this->_em->persist($deviceTag);
        }
    }

    private function setTag(Query $q, Tag $tag, ?int $i = null): void
    {
        $map = [
            TagValueOperation::EQ->name => '=',
            TagValueOperation::GTE->name => '>=',
            TagValueOperation::LTE->name => '<=',
        ];

        $operation = $map[$tag->operation->name];
        $queryString = $q->getDQL() . "(t.title = :tagTitle$i AND dt.value $operation :tagValue$i) ";
        $q->setParameter("tagTitle$i", $tag->title);
        $q->setParameter("tagValue$i", $tag->value);
        $q->setDQL($queryString);
    }

    private function setTagOperation(Query $q, TagOperation $tagOperation): void
    {
        switch ($tagOperation) {
            case TagOperation::AND:
                $q->setDQL($q->getDQL() . 'AND ');
                break;
            case TagOperation::OR:
                $q->setDQL($q->getDQL() . 'OR ');
                break;
        }
    }
}
