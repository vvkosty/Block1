<?php

declare(strict_types=1);

namespace App\Services\Parser;

use Ds\Stack;

class StackBuilder
{
    private Stack $stack;

    public function __construct()
    {
        $this->stack = new Stack();
    }

    public function parse(string $query): void
    {
        $j = 0;
        $query = preg_replace('/\s+/', '', $query);
        $strLen = strlen($query);
        $tagStartIndex = 0;

        for ($i = 0; $i < $strLen; $i++) {
            $currentChar = $query[$i];
            if ($currentChar === '(') {
                $j++;
            }
            if ($currentChar === ')') {
                $j--;
            }
            if ($currentChar === 'T' && $query[$i + 1] === '(') {
                $tagStartIndex = $i;
            }

            if ($j === 0) {
                // нашли оператор и\или
                if ($currentChar === TagOperationMapper::OR || $currentChar === TagOperationMapper::AND) {
                    $this->stack->push(TagOperationMapper::getTagFromString($currentChar));
                }

                // нашли тег
                if ($currentChar === ')') {
                    $tag = TagBuilder::create(substr($query, $tagStartIndex, $i - $tagStartIndex + 1));

                    $this->stack->push($tag);
                }
            }
        }
    }

    public function getStack(): Stack
    {
        return $this->stack;
    }
}
