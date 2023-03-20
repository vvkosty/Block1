<?php

declare(strict_types=1);

namespace App\Helpers;

class TagValueHelper
{
    public static function getNormalizedValue(string $value): bool|int|string
    {
        return filter_var($value, FILTER_VALIDATE_INT) ?: filter_var($value, FILTER_VALIDATE_BOOLEAN) ?: $value;
    }
}
