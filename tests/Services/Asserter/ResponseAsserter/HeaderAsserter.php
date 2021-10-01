<?php

declare(strict_types=1);

namespace App\Tests\Services\Asserter\ResponseAsserter;

use PHPUnit\Framework\Assert;

class HeaderAsserter implements HeaderAsserterInterface
{
    /**
     * @param array<string, string> $expectedHeaders
     */
    public function __construct(
        private array $expectedHeaders = [],
    ) {
    }

    public function assert(array $headers): void
    {
        foreach ($this->expectedHeaders as $expectedKey => $expectedValue) {
            Assert::assertArrayHasKey($expectedKey, $headers);
            Assert::assertSame($expectedValue, $headers[$expectedKey][0]);
        }
    }
}
