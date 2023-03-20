<?php

declare(strict_types=1);

namespace App\Documents;

use DateTime;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Doctrine\ODM\MongoDB\Types\Type;

/** @ODM\Document(repositoryClass="App\Repositories\Mongo\DeviceRepository") */
class Device
{
    /** @ODM\Id(strategy="NONE", type=Type::INT) */
    public int $id;

    /** @ODM\Field(type=Type::STRING) */
    public string $email;

    /** @ODM\EmbedMany
     */
    public ArrayCollection $tags;

    /** @ODM\Field(type=Type::DATE_IMMUTABLE) */
    public DateTimeImmutable $createdAt;

    /** @ODM\Field(type=Type::DATE) */
    public Datetime $updatedAt;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTime();
        $this->tags = new ArrayCollection();
    }
}
