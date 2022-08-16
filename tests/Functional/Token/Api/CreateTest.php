<?php

declare(strict_types=1);

namespace App\Tests\Functional\Token\Api;

use App\Services\ApiKeyFactory;
use App\Tests\Functional\Token\AbstractTokenTest;
use App\Tests\Services\TestUserFactory;

class CreateTest extends AbstractTokenTest
{
    public function testCreateSuccess(): void
    {
        $this->removeAllUsers();

        $testUserFactory = self::getContainer()->get(TestUserFactory::class);
        \assert($testUserFactory instanceof TestUserFactory);

        $apiKeyFactory = self::getContainer()->get(ApiKeyFactory::class);
        \assert($apiKeyFactory instanceof ApiKeyFactory);

        $user = $testUserFactory->create();
        $apiKey = $apiKeyFactory->create('api key label', $user);

        $response = $this->application->makeApiCreateTokenRequest($apiKey->getId());

        $this->applicationResponseAsserter->assertApiTokenCreateSuccessResponse($response, $user);
    }

    public function testCreateUserDoesNotExist(): void
    {
        $response = $this->application->makeApiCreateTokenRequest('');

        self::assertSame(401, $response->getStatusCode());
    }
}
