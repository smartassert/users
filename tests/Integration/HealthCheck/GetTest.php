<?php

declare(strict_types=1);

namespace App\Tests\Integration\HealthCheck;

use App\Tests\Integration\AbstractIntegrationTest;

class GetTest extends AbstractIntegrationTest
{
    public function testGetSuccess(): void
    {
        $response = $this->application->makeHealthCheckRequest();

        $this->applicationResponseAsserter->assertHealthCheckResponse($response);
    }
}
