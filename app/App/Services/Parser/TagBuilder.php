<?php

declare(strict_types=1);

namespace App\Services\Parser;

class TagBuilder
{
    public static function create(string $string): Tag
    {
        $tagData = explode(',', str_replace('"', '', substr($string, 2, -1)));

        if (TagValueOperation::contains($tagData[1])) {
            $tagOperation = constant(TagValueOperation::class . "::$tagData[1]");
        } else {
            throw new \RuntimeException('Incorrect operation');
        }

        return new Tag($tagData[0], $tagOperation, $tagData[2]);
    }
}
