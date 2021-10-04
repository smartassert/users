<?php

declare(strict_types=1);

namespace App\Tests\Integration\Admin\Frontend\RefreshToken;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Routes;
use App\Tests\Integration\AbstractIntegrationTest;
use App\Tests\Services\ApplicationResponseAsserter;
use App\Tests\Services\RefreshTokenManager;
use Psr\Http\Message\RequestInterface;

class RevokeTest extends AbstractIntegrationTest
{
    public function testRevokeUnauthorized(): void
    {
        $request = $this->createRevokeRefreshTokenRequest('invalid admin token', '');
        $response = $this->httpClient->sendRequest($request);

        $applicationResponseAsserter = self::getContainer()->get(ApplicationResponseAsserter::class);
        \assert($applicationResponseAsserter instanceof ApplicationResponseAsserter);

        $applicationResponseAsserter->assertAdminUnauthorizedResponse($response);
    }

    public function testRevoke(): void
    {
        $this->removeAllUsers();
        $this->removeAllRefreshTokens();

        $userRepository = self::getContainer()->get(UserRepository::class);
        \assert($userRepository instanceof UserRepository);
        $refreshTokenManager = self::getContainer()->get(RefreshTokenManager::class);
        \assert($refreshTokenManager instanceof RefreshTokenManager);

        self::assertSame(0, $refreshTokenManager->count());

        $this->createTestUser();

        $user = $userRepository->findByEmail('user@example.com');
        self::assertInstanceOf(User::class, $user);

        $createTokenRequest = $this->createCreateTokenRequest([
            'username' => self::TEST_USER_EMAIL,
            'password' => self::TEST_USER_PASSWORD,
        ]);

        $this->httpClient->sendRequest($createTokenRequest);
        self::assertSame(1, $refreshTokenManager->count());

        $request = $this->createRevokeRefreshTokenRequest($this->getAdminToken(), $user->getId());
        $response = $this->httpClient->sendRequest($request);

        self::assertSame(200, $response->getStatusCode());
        self::assertSame(0, $refreshTokenManager->count());
    }

    private function createRevokeRefreshTokenRequest(string $adminToken, string $userId): RequestInterface
    {
        return $this->createRequest(
            'POST',
            Routes::ROUTE_ADMIN_FRONTEND_REFRESH_TOKEN_REVOKE,
            [
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Authorization' => $adminToken,
            ],
            http_build_query([
                'id' => $userId,
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
