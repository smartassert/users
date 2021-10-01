<?php

declare(strict_types=1);

namespace App\Tests\Services\Asserter\ResponseAsserter;

interface HeaderAsserterInterface
{
    /**
     * @param string[][] $headers
     */
    public function assert(array $headers): void;
}
