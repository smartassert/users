<?php

declare(strict_types=1);

namespace App\Tests\Integration\Api\Token;

use App\Tests\Integration\AbstractIntegrationTest;

class CreateTest extends AbstractIntegrationTest
{
    public function testCreateUserDoesNotExist(): void
    {
        $response = $this->application->makeApiCreateTokenRequest('');

        self::assertSame(401, $response->getStatusCode());
    }

    public function testCreateSuccess(): void
    {
        $this->removeAllUsers();
        $this->createTestUser();

        $user = $this->getTestUser();
        $apiKey = $this->apiKeyFactory->create('api key label', $user);

        $response = $this->application->makeApiCreateTokenRequest((string) $apiKey);

        $this->applicationResponseAsserter->assertApiTokenCreateSuccessResponse($response, $user);
    }
}
