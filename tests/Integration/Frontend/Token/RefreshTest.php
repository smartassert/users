<?php

declare(strict_types=1);

namespace App\Tests\Integration\Frontend\Token;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Routes;
use App\Tests\Integration\AbstractIntegrationTest;
use App\Tests\Services\ApplicationResponseAsserter;
use Psr\Http\Message\RequestInterface;

class RefreshTest extends AbstractIntegrationTest
{
    public function testRefreshSuccess(): void
    {
        $this->removeAllUsers();
        $this->removeAllRefreshTokens();
        $this->createTestUser();

        $userRepository = self::getContainer()->get(UserRepository::class);
        \assert($userRepository instanceof UserRepository);

        $user = $userRepository->findByEmail(self::TEST_USER_EMAIL);
        \assert($user instanceof User);

        $createRequest = $this->createCreateTokenRequest([
            'username' => self::TEST_USER_EMAIL,
            'password' => self::TEST_USER_PASSWORD,
        ]);

        $createResponse = $this->httpClient->sendRequest($createRequest);

        $createResponseData = json_decode($createResponse->getBody()->getContents(), true);

        $request = $this->createRefreshTokenRequest($createResponseData['refresh_token']);
        $response = $this->httpClient->sendRequest($request);

        $applicationResponseAsserter = self::getContainer()->get(ApplicationResponseAsserter::class);
        \assert($applicationResponseAsserter instanceof ApplicationResponseAsserter);

        $applicationResponseAsserter->assertFrontendTokenCreateSuccessResponse(
            $response,
            $user,
            $createResponseData['refresh_token']
        );
    }

    private function createRefreshTokenRequest(string $refreshToken): RequestInterface
    {
        return parent::createRequest(
            'POST',
            Routes::ROUTE_FRONTEND_TOKEN_REFRESH,
            ['Content-Type' => 'application/json'],
            (string) json_encode([
                'refresh_token' => $refreshToken
            ])
        );
    }

    /**
     * @param array<string, string> $payload
     */
    private function createCreateTokenRequest(array $payload): RequestInterface
    {
        return parent::createRequest(
            'POST',
            Routes::ROUTE_FRONTEND_TOKEN_CREATE,
            ['Content-Type' => 'application/json'],
            0 < count($payload) ? (string) json_encode($payload) : null
        );
    }
}
