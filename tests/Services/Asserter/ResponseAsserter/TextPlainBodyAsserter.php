<?php

declare(strict_types=1);

namespace App\Tests\Services\Asserter\ResponseAsserter;

use PHPUnit\Framework\Assert;

class TextPlainBodyAsserter implements BodyAsserterInterface
{
    public function __construct(
        private string $expected
    ) {
    }

    public function assert(string $body): string
    {
        Assert::assertSame($this->expected, $body);

        return $body;
    }
}
