<?php

declare(strict_types=1);

namespace App\Tests\Integration\Status;

use App\Tests\Integration\AbstractIntegrationTest;

class GetTest extends AbstractIntegrationTest
{
    public function testGetSuccess(): void
    {
        $response = $this->application->makeStatusRequest();

        $this->applicationResponseAsserter->assertStatusResponse($response, true);
    }
}
