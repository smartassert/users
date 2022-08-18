<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use SmartAssert\SymfonyTestClient\ClientInterface;
use SmartAssert\SymfonyTestClient\HttpClient;

trait GetClientAdapterTrait
{
    protected function getClientAdapter(): ClientInterface
    {
        $adapter = self::getContainer()->get(HttpClient::class);
        \assert($adapter instanceof ClientInterface);

        return $adapter;
    }
}
