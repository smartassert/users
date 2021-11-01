<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Tests\Functional\AbstractBaseWebTestCase;
use App\Tests\Services\ApplicationResponseAsserter;

class HealthCheckControllerTest extends AbstractBaseWebTestCase
{
    private ApplicationResponseAsserter $applicationResponseAsserter;

    protected function setUp(): void
    {
        parent::setUp();

        $applicationResponseAsserter = self::getContainer()->get(ApplicationResponseAsserter::class);
        \assert($applicationResponseAsserter instanceof ApplicationResponseAsserter);
        $this->applicationResponseAsserter = $applicationResponseAsserter;
    }

    public function testGet(): void
    {
        $response = $this->application->makeHealthCheckRequest();

        $this->applicationResponseAsserter->assertHealthCheckResponse(
            $response,
            [
                'database_connection' => true,
                'database_entities' => true,
            ]
        );
    }
}
