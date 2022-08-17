<?php

declare(strict_types=1);

namespace App\Tests\Functional\Application;

use SmartAssert\SymfonyTestClient\ClientInterface;
use SmartAssert\SymfonyTestClient\SymfonyClient;

trait GetClientAdapterTrait
{
    protected function getClientAdapter(): ClientInterface
    {
        $client = self::getContainer()->get(SymfonyClient::class);
        \assert($client instanceof SymfonyClient);

        $client->setKernelBrowser($this->kernelBrowser);

        return $client;
    }
}
