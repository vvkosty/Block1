<?php

declare(strict_types=1);

namespace App\Services\Parser;

enum TagValueOperation
{
    case EQ;
    case LTE;
    case GTE;

    public static function contains(string $value): bool
    {
        foreach (self::cases() as $tagOperation) {
            if ($tagOperation->name === strtoupper($value)) {
                return true;
            }
        }

        return false;
    }
}
