<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Entities\DeviceTag;
use App\Services\Parser\Tag;
use App\Services\Parser\TagOperation;
use App\Services\Parser\TagValueOperation;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Ds\Stack;

class DeviceTagRepository extends EntityRepository
{
    public function search(Stack $stack): array
    {
        $dtClass = DeviceTag::class;
        $queryString = "SELECT DISTINCT IDENTITY(dt.device, 'id') FROM {$dtClass} dt LEFT JOIN dt.tag t WHERE ";
        $qb = $this->_em->createQuery($queryString);

        /** @var Tag|TagOperation $item */
        foreach ($stack as $key => $item) {
            if ($item instanceof Tag) {
                $this->setTag($qb, $item, $key);
            } elseif ($item instanceof TagOperation) {
                $this->setTagOperation($qb, $item);
            }
        }

        return $qb->getSingleColumnResult();
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
