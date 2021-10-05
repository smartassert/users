<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Tests\Services\Application\ApplicationInterface;
use App\Tests\Services\Application\FunctionalApplication;
use App\Tests\Services\Application\SymfonyClient;

abstract class AbstractBaseWebTestCase extends AbstractBaseFunctionalTest
{
    protected ApplicationInterface $application;

    protected function setUp(): void
    {
        parent::setUp();

        $client = static::createClient();

        $application = self::getContainer()->get(FunctionalApplication::class);
        \assert($application instanceof FunctionalApplication);

        $symfonyClient = self::getContainer()->get(SymfonyClient::class);
        \assert($symfonyClient instanceof SymfonyClient);
        $symfonyClient->setKernelBrowser($client);

        $this->application = $application;
    }
}
