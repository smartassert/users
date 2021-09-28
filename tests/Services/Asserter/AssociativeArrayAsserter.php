<?php

declare(strict_types=1);

namespace App\Tests\Services\Asserter;

use PHPUnit\Framework\Assert;

class AssociativeArrayAsserter
{
    private bool $interpretNullAsIgnoreValue = false;

    /**
     * @param array<int|string, mixed> $expectedData
     * @param array<int, int|string>   $expectedKeysShouldNotBeSet
     */
    public function __construct(
        private array $expectedData = [],
        private array $expectedKeysShouldNotBeSet = [],
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

        foreach ($this->expectedData as $expectedKey => $expectedValue) {
            Assert::assertArrayHasKey($expectedKey, $actual, $hasKeyFailureMessage);

            if (null !== $expectedValue || false === $this->interpretNullAsIgnoreValue) {
                Assert::assertEquals($expectedValue, $actual[$expectedKey]);
            }
        }

        foreach ($this->expectedKeysShouldNotBeSet as $expectedKey) {
            Assert::assertArrayNotHasKey($expectedKey, $actual);
        }
    }
}
