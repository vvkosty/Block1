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
        $batchSize = 20;
        $i = 1;
        $faker = Factory::create();

        foreach ($this->findAll() as $device) {
            ++$i;
            $device->email = $faker->email();
            if (($i % $batchSize) === 0) {
                $this->dm->flush();
                $this->dm->clear();
            }
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

    /** "T(\"Gender\", EQ, \"F\") + T(\"Age\", LTE, 20)" */
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

        /** @var Device $device */
        foreach ($devices as $device) {
            $this->dm->remove($device);

            // sleep(10); // исскуственная задержка, для теста прерывания

            if (($i % $batchSize) === 0 || !$devices->valid()) {
                $this->dm->flush();
                $this->dm->clear();
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
