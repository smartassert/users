<?php

declare(strict_types=1);

namespace App\Tests\Services\Asserter;

use PHPUnit\Framework\Assert;

class AssociativeArrayAsserter
{
    private bool $interpretNullAsIgnoreValue = false;
    private bool $ignoreAdditionalActualKeys = true;

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

    public function errorOnAdditionalActualKeys(): self
    {
        $new = clone $this;
        $new->ignoreAdditionalActualKeys = false;

        return $new;
    }

    /**
     * @param array<mixed> $actual
     */
    public function assert(array $actual): void
    {
        $actualKeys = array_keys($actual);
        $hasKeyFailureMessage = 'Available keys: ' . implode(', ', $actualKeys);

        $actualKeysChecked = [];

        foreach ($this->expected as $expectedKey => $expectedValue) {
            Assert::assertArrayHasKey($expectedKey, $actual, $hasKeyFailureMessage);
            $actualKeysChecked[] = $expectedKey;

            if (null !== $expectedValue || false === $this->interpretNullAsIgnoreValue) {
                Assert::assertEquals($expectedValue, $actual[$expectedKey]);
            }
        }

        if (false === $this->ignoreAdditionalActualKeys) {
            $actualKeysNotChecked = array_diff($actualKeys, $actualKeysChecked);

            if ([] !== $actualKeysNotChecked) {
                Assert::fail('Actual keys present and not checked for: ' . implode(', ', $actualKeysNotChecked));
            }
        }
    }
}
