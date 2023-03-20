<?php

declare(strict_types=1);

namespace Unit;

use App\Enums\TagOperation;
use App\Services\Parser\StackBuilder;
use App\Services\Parser\Tag;
use PHPUnit\Framework\TestCase;

class DeviceTest extends TestCase
{
    /**
     * @dataProvider searchProvider
     */
    public function testSearch(string $query, $expected): void
    {
        $stackBuilder = new StackBuilder();
        $stackBuilder->parse($query);

        foreach ($stackBuilder->getStack() as $item) {
            switch (get_class($item)) {
                case Tag::class:
                    $this->assertContains($item->value, $expected);
                    break;
                case TagOperation::class:
                    $this->assertContains($item, $expected);
                    break;
            }
        }
    }

    public function searchProvider(): array
    {
        return [
            ["T(\"Gender\", EQ, \"F\")", ["F"]],
            ["T(\"Gender\", EQ, \"F\") + T(\"Age\", LTE, 20)", ["F", TagOperation::OR, "20"]],
            ["T(\"Gender\", EQ, \"F\") * T(\"Age\", LTE, 20)", ["F", TagOperation::AND, "20"]],
        ];
    }
}
