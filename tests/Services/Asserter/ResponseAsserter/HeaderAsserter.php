<?php

declare(strict_types=1);

namespace App\Tests\Services\Asserter\ResponseAsserter;

use PHPUnit\Framework\Assert;

class HeaderAsserter implements HeaderAsserterInterface
{
    /**
     * @var array<string, string>
     */
    private array $expectedHeaders = [];

    /**
     * @param array<string, string> $expectedHeaders
     */
    public function __construct(
        array $expectedHeaders = [],
    ) {
        foreach ($expectedHeaders as $key => $value) {
            $this->expectedHeaders[strtolower($key)] = $value;
        }
    }

    public function assert(array $headers): void
    {
        foreach ($headers as $key => $value) {
            unset($headers[$key]);
            $headers[strtolower($key)] = $value;
        }

        foreach ($this->expectedHeaders as $expectedKey => $expectedValue) {
            Assert::assertArrayHasKey($expectedKey, $headers);
            Assert::assertSame($expectedValue, $headers[$expectedKey][0]);
        }
    }
}
