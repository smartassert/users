<?php

declare(strict_types=1);

namespace App\Tests\Integration\Admin\Frontend\RefreshToken;

use App\Tests\Integration\AbstractIntegrationTest;
use App\Tests\Services\RefreshTokenManager;

class RevokeTest extends AbstractIntegrationTest
{
    public function testRevokeUnauthorized(): void
    {
        $response = $this->application->makeAdminRevokeRefreshTokenRequest('user-id', 'invalid admin token');

        $this->applicationResponseAsserter->assertAdminUnauthorizedResponse($response);
    }

    public function testRevoke(): void
    {
        $this->removeAllUsers();
        $this->removeAllRefreshTokens();

        $refreshTokenManager = self::getContainer()->get(RefreshTokenManager::class);
        \assert($refreshTokenManager instanceof RefreshTokenManager);

        self::assertSame(0, $refreshTokenManager->count());

        $this->createTestUser();
        $user = $this->getTestUser();

        $this->application->makeFrontendCreateTokenRequest(self::TEST_USER_EMAIL, self::TEST_USER_PASSWORD);
        self::assertSame(1, $refreshTokenManager->count());

        $response = $this->application->makeAdminRevokeRefreshTokenRequest($user->getId(), $this->getAdminToken());

        self::assertSame(200, $response->getStatusCode());
        self::assertSame(0, $refreshTokenManager->count());
    }
}
