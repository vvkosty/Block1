<?php

declare(strict_types=1);

namespace App\Repositories;

use DateTime;
use Doctrine\ORM\EntityRepository;

class DeviceRepository extends EntityRepository
{
    public function findByCreateDates(DateTime $from, DateTime $to): array
    {
        $entity = $this->getEntityName();

        $qb = $this->_em->createQuery("SELECT d FROM $entity d WHERE d.createdAt BETWEEN :from AND :to");
        $qb->setParameter('from', $from);
        $qb->setParameter('to', $to);

        return $qb->execute();
    }
}
