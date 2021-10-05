<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Tests\Services\Application\ApplicationInterface;
use App\Tests\Services\Application\FunctionalApplication;

abstract class AbstractBaseWebTestCase extends AbstractBaseFunctionalTest
{
    protected ApplicationInterface $application;

    protected function setUp(): void
    {
        parent::setUp();

        $client = static::createClient();

        $application = self::getContainer()->get(FunctionalApplication::class);
        \assert($application instanceof FunctionalApplication);
        $application->setClient($client);

        $this->application = $application;
    }
}
