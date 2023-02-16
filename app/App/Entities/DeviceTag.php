<?php

declare(strict_types=1);

namespace App\Entities;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

#[Entity]
#[Table(name: 'devices_tags')]
#[Index(columns: ['value'], name: 'value_idx')]
class DeviceTag
{
    #[Id]
    #[ManyToOne(targetEntity: Device::class)]
    public Device $device;

    #[Id]
    #[ManyToOne(targetEntity: Tag::class)]
    public Tag $tag;

    #[Column(type: Types::STRING)]
    public string $value;
}
