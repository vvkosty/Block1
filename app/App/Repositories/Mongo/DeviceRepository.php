<?php

declare(strict_types=1);

namespace App\Repositories\Mongo;

use App\Documents\Device;
use App\Documents\TagBoolean;
use App\Documents\TagInteger;
use App\Documents\TagString;
use App\Enums\TagOperation;
use App\Helpers\TagValueHelper;
use App\Interfaces\DeviceRepositoryInterface;
use App\Services\Parser\PostfixConverter;
use App\Services\Parser\Tag;
use App\Services\Parser\TagValueOperation;
use DateTime;
use Doctrine\ODM\MongoDB\Iterator\CachingIterator;
use Doctrine\ODM\MongoDB\Query\Builder;
use Doctrine\ODM\MongoDB\Query\Expr;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Ds\Stack;
use Faker\Factory;
use Illuminate\Support\Arr;
use RuntimeException;

class DeviceRepository extends DocumentRepository implements DeviceRepositoryInterface
{
    public function recreateEmails(): void
    {
        $batchSize = 500;
        $i = 1;
        $processed = 0;
        $faker = Factory::create();

        /** @var CachingIterator $iterator */
        $iterator = $this->createQueryBuilder()
            ->setRewindable(false)
            ->getQuery()->execute();
        foreach ($iterator as $device) {
            $device->email = $faker->email();
            $this->dm->persist($device);
            if (($i % $batchSize) === 0) {
                $this->dm->flush();
                $this->dm->detach($device);

                $processed += $batchSize;
                echo sprintf("Managed: %d\n%.2f MB\n", $processed, round(memory_get_usage() / 1024 / 1024, 2)) . "\e[F" . "\e[F";
            }
            $i++;
        }

        $this->dm->flush();
        $this->dm->clear();
    }

    public function create(int $deviceId, array $tags): Device
    {
        $device = new Device();
        $device->id = $deviceId;
        $this->fillTags($device, $tags);
        $this->dm->persist($device);
        $this->dm->flush();

        return $device;
    }

    public function createBatch(array $devices): void
    {
        foreach ($devices as $deviceId => $data) {
            $device = new Device();
            $device->id = $deviceId;
            $this->fillTags($device, $data['tags']);
            if (isset($data['createdAt'])) {
                $device->createdAt = $data['createdAt'];
            }
            $this->dm->persist($device);
        }

        $this->dm->flush();
        $this->dm->clear();
    }

    public function edit(int $deviceId, array $tags): void
    {
        $device = $this->dm->find(Device::class, $deviceId);
        if (!$device) {
            throw new RuntimeException('Device not found');
        }
        $device->tags->clear();
        $this->fillTags($device, $tags);

        $this->dm->persist($device);
        $this->dm->flush();
    }

    public function search(Stack $infixStack): array
    {
        $postfixStack = PostfixConverter::toPostfix($infixStack);

        $qb = $this->dm->createQueryBuilder(Device::class);
        $qb->select('id');

        $stack = new Stack();
        /** @var Tag|TagOperation $operand */
        foreach ($postfixStack as $operand) {
            if ($operand instanceof Tag) {
                $stack->push($operand);
            } elseif ($operand instanceof TagOperation) {
                $second = $stack->pop();
                $first = $stack->pop();

                switch ($operand) {
                    case TagOperation::AND:
                        $qb->addAnd($this->setTag($qb, $second), $this->setTag($qb, $first));
                        break;
                    case TagOperation::OR:
                        $qb->addOr($this->setTag($qb, $second), $this->setTag($qb, $first));
                        break;
                }
            }
        }

        $result = $qb->getQuery()->execute();

        return Arr::pluck($result, 'id');
    }

    private function setTag(Builder $qb, Tag $tag): Expr
    {
        $map = [
            TagValueOperation::EQ->name => 'equals',
            TagValueOperation::GTE->name => 'gte',
            TagValueOperation::LTE->name => 'lte',
        ];

        $operation = $map[$tag->operation->name];

        return $qb->expr()
            ->field('tags.title')
            ->equals($tag->title)
            ->field('tags.value')
            ->$operation(
                TagValueHelper::getNormalizedValue($tag->value)
            );
    }

    private function fillTags(Device $device, array $newTags): void
    {
        foreach ($newTags as $tagKey => $tagValue) {
            $tag = $this->createTagByValue($tagValue);
            $tag->title = $tagKey;
            $tag->value = $tagValue;
            $device->tags->add($tag);
        }
    }

    private function createTagByValue($value): TagString|TagInteger|TagBoolean
    {
        return match (true) {
            is_int($value) => new TagInteger(),
            is_string($value) => new TagString(),
            is_bool($value) => new TagBoolean()
        };
    }

    public function removeByCreateDates(DateTime $from, DateTime $to): void
    {
        $batchSize = 20;
        $i = 1;

        $qb = $this->dm->createQueryBuilder($this->getDocumentName())
            ->field('createdAt')
            ->range($from, $to);

        $devices = $qb->getQuery()->execute();
        $devices = $devices->toArray();
        $keyLast = array_key_last($devices);

        /** @var Device $device */
        foreach ($devices as $key => $device) {
            $this->dm->remove($device);

            // sleep(2); // исскуственная задержка, для теста прерывания

            if (($i % $batchSize) === 0 || $key === $keyLast) {
                $this->dm->flush();
                $this->dm->detach($device);
                pcntl_signal_dispatch();
            }
            ++$i;
        }
    }

    public function updateTagValue(string $tagName, int $deviceId, string $tagValue): void
    {
        $this->dm->createQueryBuilder($this->getDocumentName())
            // find
            ->findAndUpdate()
            ->field('id')->equals($deviceId)
            ->field('tags.title')->equals($tagName)
            // update
            ->field('tags.$.value')->set(TagValueHelper::getNormalizedValue($tagValue))
            ->getQuery()
            ->execute();
    }
}
