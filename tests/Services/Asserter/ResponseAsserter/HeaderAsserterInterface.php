<?php

declare(strict_types=1);

namespace App\Tests\Services\Asserter\ResponseAsserter;

use Symfony\Component\HttpFoundation\ResponseHeaderBag;

interface HeaderAsserterInterface
{
    public function assert(ResponseHeaderBag $headers): void;
}
