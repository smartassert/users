<?php

declare(strict_types=1);

namespace App\Tests\Services\Asserter\ResponseAsserter;

use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class HeaderAsserter implements HeaderAsserterInterface
{
    /**
     * @param array<string, string> $expectedHeaders
     */
    public function __construct(
        private array $expectedHeaders = [],
    ) {
    }

    public function assert(ResponseHeaderBag $headers): void
    {
        foreach ($this->expectedHeaders as $expectedKey => $expectedValue) {
            Assert::assertTrue($headers->has($expectedKey));
            Assert::assertSame($expectedValue, $headers->get($expectedKey));
        }
    }
}
