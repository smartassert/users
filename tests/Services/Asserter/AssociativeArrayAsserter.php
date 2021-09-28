<?php

declare(strict_types=1);

namespace App\Tests\Services\Asserter;

use PHPUnit\Framework\Assert;

class AssociativeArrayAsserter
{
    private bool $interpretNullAsIgnoreValue = false;

    /**
     * @param array<int|string, mixed> $expected
     */
    public function __construct(
        private array $expected = [],
    ) {
    }

    public function interpretNullAsIgnoreValue(): self
    {
        $new = clone $this;
        $new->interpretNullAsIgnoreValue = true;

        return $new;
    }

    /**
     * @param array<mixed> $actual
     */
    public function assert(array $actual): void
    {
        $hasKeyFailureMessage = 'Available keys: ' . implode(', ', array_keys($actual));

        foreach ($this->expected as $expectedKey => $expectedValue) {
            Assert::assertArrayHasKey($expectedKey, $actual, $hasKeyFailureMessage);

            if (null !== $expectedValue || false === $this->interpretNullAsIgnoreValue) {
                Assert::assertEquals($expectedValue, $actual[$expectedKey]);
            }
        }
    }
}
