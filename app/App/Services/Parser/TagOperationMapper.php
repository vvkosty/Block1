<?php

declare(strict_types=1);

namespace App\Services\Parser;

class TagOperationMapper
{
    public const OR = '+';
    public const AND = '*';
    
    private const MAP = [
        self::OR => TagOperation::OR,
        self::AND => TagOperation::AND,
    ];

    public static function getTagFromString(string $operation): TagOperation
    {
        return self::MAP[$operation];
    }
}
