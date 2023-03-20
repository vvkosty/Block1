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
class TagInteger extends Tag
{
    /**
     * @ODM\Field(type=Type::INT)
     * @ODM\Index
     */
    public int $value;
}
