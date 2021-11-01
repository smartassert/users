<?php

declare(strict_types=1);

namespace App\Tests\Services\Asserter\ResponseAsserter;

use App\Tests\Services\Asserter\AssociativeArrayAsserter;
use PHPUnit\Framework\Assert;

class ArrayBodyAsserter implements BodyAsserterInterface
{
    private AssociativeArrayAsserter $arrayDataAsserter;

    /**
     * @param array<int|string, mixed> $expected
     */
    public function __construct(array $expected)
    {
        $this->arrayDataAsserter = (new AssociativeArrayAsserter($expected))
            ->interpretNullAsIgnoreValue()
        ;
    }

    public function errorOnAdditionalActualKeys(): self
    {
        $new = clone $this;
        $new->arrayDataAsserter = $new->arrayDataAsserter->errorOnAdditionalActualKeys();

        return $new;
    }

    /**
     * @return array<mixed>
     */
    public function assert(string $body): array
    {
        $data = json_decode($body, true);
        Assert::assertIsArray($data);
        $this->arrayDataAsserter->assert($data);

        return $data;
    }
}
