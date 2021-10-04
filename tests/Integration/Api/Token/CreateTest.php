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
    public function testCreateUserDoesNotExist(): void
    {
        $request = $this->createCreateTokenRequest('');

        $response = $this->httpClient->sendRequest($request);

        self::assertSame(401, $response->getStatusCode());
    }

    public function testCreateSuccess(): void
    {
        $this->removeAllUsers();
        $this->createTestUser();

        $userRepository = self::getContainer()->get(UserRepository::class);
        \assert($userRepository instanceof UserRepository);
        $apiKeyFactory = self::getContainer()->get(ApiKeyFactory::class);
        \assert($apiKeyFactory instanceof ApiKeyFactory);

        $user = $userRepository->findByEmail(self::TEST_USER_EMAIL);
        \assert($user instanceof User);
        $apiKey = $apiKeyFactory->create('api key label', $user);

        $request = $this->createCreateTokenRequest((string) $apiKey);
        $response = $this->httpClient->sendRequest($request);

        $applicationResponseAsserter = self::getContainer()->get(ApplicationResponseAsserter::class);
        \assert($applicationResponseAsserter instanceof ApplicationResponseAsserter);

        $applicationResponseAsserter->assertApiTokenCreateSuccessResponse($response, $user);
    }

    private function createCreateTokenRequest(string $apiKey): RequestInterface
    {
        return parent::createRequest(
            'POST',
            Routes::ROUTE_API_TOKEN_CREATE,
            [
                'Authorization' => $apiKey,
            ]
        );
    }
}
