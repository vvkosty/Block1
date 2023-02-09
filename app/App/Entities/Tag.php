<?php

declare(strict_types=1);

namespace App\Entities;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;

#[Entity]
#[Table(name: 'tags')]
class Tag
{
    #[Id]
    #[Column(type: Types::INTEGER)]
    #[GeneratedValue]
    public int $id;

    #[Column(type: Types::STRING)]
    public string $title;
}
