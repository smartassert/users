<?php

declare(strict_types=1);

namespace App\Tests\Services\Asserter\ResponseAsserter;

use App\Tests\Services\Asserter\AssociativeArrayAsserter;
use PHPUnit\Framework\Assert;

class ArrayBodyAsserter implements BodyAsserterInterface
{
    private AssociativeArrayAsserter $arrayDataAsserter;

    /**
     * @param array<int|string, null|bool|int|string> $expected
     */
    public function __construct(array $expected)
    {
        $this->arrayDataAsserter = (new AssociativeArrayAsserter($expected))
            ->interpretNullAsIgnoreValue()
        ;
    }

    public function assert(string $body): void
    {
        $data = json_decode($body, true);
        Assert::assertIsArray($data);
        $this->arrayDataAsserter->assert($data);
    }
}
