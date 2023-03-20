<?php

declare(strict_types=1);

namespace App\Services\Parser;

use App\Enums\TagOperation;
use Ds\Stack;

class PostfixConverter
{
    private const OPERATION_PRIORITY = [
        TagOperation::OR->name => 1,
        TagOperation::AND->name => 2,
    ];

    public static function toPostfix(Stack $infixArray): array
    {
        $postfixArray = [];
        $operatorStack = new Stack();

        foreach ($infixArray as $operand) {
            if ($operand instanceof Tag) {
                $postfixArray[] = $operand;
            } elseif ($operand instanceof TagOperation) {
                while (
                    $operatorStack->count() > 0 &&
                    (self::OPERATION_PRIORITY[$operatorStack->peek()] >= self::OPERATION_PRIORITY[$operand->name])
                ) {
                    $postfixArray[] = $operatorStack->pop();
                }

                $operatorStack->push($operand);
            }
        }

        foreach ($operatorStack as $item) {
            $postfixArray[] = $item;
        }

        return $postfixArray;
    }
}
