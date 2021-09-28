<?php

declare(strict_types=1);

namespace App\Tests\Services\Asserter\ResponseAsserter;

interface BodyAsserterInterface
{
    public function assert(string $body): void;
}
