<?php

declare(strict_types=1);

namespace App\Services\Parser;

class Tag
{
    public function __construct
    (
        public string $title,
        public TagValueOperation $operation,
        public string $value,
    ) {
    }
}
