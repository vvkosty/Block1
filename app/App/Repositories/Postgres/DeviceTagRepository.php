<?php

declare(strict_types=1);

namespace App\Repositories\Postgres;

use App\Entities\Device;
use Doctrine\ORM\EntityRepository;

class DeviceTagRepository extends EntityRepository
{
    public function removeByDevice(Device $device): bool
    {
        $dtClass = $this->getEntityName();

        $queryString = "DELETE FROM $dtClass dt WHERE dt.device = :device";
        $qb = $this->_em->createQuery($queryString);
        $qb->setParameter('device', $device);

        $res = $qb->execute();

        if ($res === 0) {
            $res = true;
        }

        return (bool)$res;
    }
}
