<?php

declare(strict_types=1);

namespace App\Documents;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Doctrine\ODM\MongoDB\Types\Type;

/**
 * @ODM\EmbeddedDocument
 * @ODM\DiscriminatorField("type")
 * @ODM\DiscriminatorMap({
 * "integer"=TagInteger::class,
 * "string"=TagString::class,
 * "boolean"=TagBoolean::class,
 * })
 */
class TagString extends Tag
{
    /**
     * @ODM\Field(type=Type::STRING)
     * @ODM\Index
     */
    public string $value;
}
