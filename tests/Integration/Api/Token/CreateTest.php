<?php

declare(strict_types=1);

namespace App\Tests\Integration\Api\Token;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Routes;
use App\Services\ApiKeyFactory;
use App\Tests\Integration\AbstractIntegrationTest;
use App\Tests\Services\ApplicationResponseAsserter;
use Psr\Http\Message\RequestInterface;

class CreateTest extends AbstractIntegrationTest
{
    private UserRepository $userRepository;
    private ApiKeyFactory $apiKeyFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $userRepository = self::getContainer()->get(UserRepository::class);
        \assert($userRepository instanceof UserRepository);
        $this->userRepository = $userRepository;

        $apiKeyFactory = self::getContainer()->get(ApiKeyFactory::class);
        \assert($apiKeyFactory instanceof ApiKeyFactory);
        $this->apiKeyFactory = $apiKeyFactory;
    }

    public function testCreateUserDoesNotExist(): void
    {
        $response = $this->application->makeApiCreateTokenRequest('');

        self::assertSame(401, $response->getStatusCode());
    }

    public function testCreateSuccess(): void
    {
        $this->removeAllUsers();
        $this->createTestUser();

        $user = $this->userRepository->findByEmail(self::TEST_USER_EMAIL);
        \assert($user instanceof User);
        $apiKey = $this->apiKeyFactory->create('api key label', $user);

        $response = $this->application->makeApiCreateTokenRequest((string) $apiKey);

        $this->applicationResponseAsserter->assertApiTokenCreateSuccessResponse($response, $user);
    }
}
