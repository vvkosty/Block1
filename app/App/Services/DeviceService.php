<?php

declare(strict_types = 1);

namespace App\Services;

use App\Entities\Device;
use App\Entities\DeviceTag;
use App\Entities\Tag;
use App\Repositories\DeviceRepository;
use App\Repositories\DeviceTagRepository;
use App\Repositories\TagRepository;
use App\Services\Parser\StackBuilder;
use DateTime;
use Doctrine\ORM\EntityManager;
use Faker\Factory;
use NotificationSender;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class DeviceService
{

    public function __construct(
        public EntityManager $entityManager,
        public DeviceTagRepository $deviceTagRepository,
        public DeviceRepository $deviceRepository,
        public TagRepository $tagRepository,
        public NotificationSender $notificationSender,
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

        return $this->deviceTagRepository->search($stackBuilder->getStack());
    }

    public function removeByCreateDate(DateTime $from, DateTime $to): void
    {
        $batchSize = 20;
        $i = 0;

        $devices = $this->deviceRepository->findByCreateDates($from, $to);

        /** @var Device $device */
        foreach ($devices as $device) {
            $this->entityManager->beginTransaction();
            $this->deviceTagRepository->removeByDevice($device);
            $this->entityManager->remove($device);
            sleep(10); // исскуственная задержка, для теста прерывания

            if (($i % $batchSize) === 0 || !$devices->valid()) {
                $this->entityManager->flush();
                $this->entityManager->clear();
                $this->entityManager->commit();
                pcntl_signal_dispatch();
            }
            ++$i;
        }
    }

    public function recreateEmails(): void
    {
        $batchSize = 20;
        $i = 1;
        $faker = Factory::create();

        $devices = $this->deviceRepository->getAllIterable();

        foreach ($devices as $device) {
            ++$i;
            $device->email = $faker->email();
            if (($i % $batchSize) === 0) {
                $this->entityManager->flush();
                $this->entityManager->clear();
            }
        }

        $this->entityManager->flush();
        $this->entityManager->clear();
    }

    public function updateTagValue(int $deviceId, string $tagName, string $tagValue): void
    {
        /** @var Device $device */
        $device = $this->deviceRepository->find($deviceId);

        $this->saveTagValue($tagName, $device, $tagValue);

        $connection = new AMQPStreamConnection(
            $_ENV['RABBITMQ_HOST'],
            $_ENV['RABBITMQ_PORT'],
            $_ENV['RABBITMQ_USER'],
            $_ENV['RABBITMQ_PASSWORD']
        );
        $channel = $connection->channel();

        $channel->queue_declare('tag_updated', false, false, false, false);
                $channel->queue_bind('tag_updated', 'email');

        $msg = new AMQPMessage($deviceId);
        $channel->basic_publish($msg, 'email');

        $channel->close();
        $connection->close();
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

    protected function saveTagValue(string $tagName, Device $device, string $tagValue): void
    {
        /** @var Tag $tag */
        $tag = $this->tagRepository->findOneBy(['title' => $tagName]);

        /** @var DeviceTag $deviceTag */
        $deviceTag = $this->deviceTagRepository->findOneBy(['device' => $device, 'tag' => $tag]);

        $deviceTag->value = $tagValue;
        $this->entityManager->persist($deviceTag);
        $this->entityManager->flush();
    }
}
