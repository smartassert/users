<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Tests\Services\Application;

abstract class AbstractBaseWebTestCase extends AbstractBaseFunctionalTest
{
    protected Application $application;

    protected function setUp(): void
    {
        parent::setUp();

        $client = static::createClient();

        $application = self::getContainer()->get(Application::class);
        \assert($application instanceof Application);
        $application->setClient($client);

        $this->application = $application;
    }
}
