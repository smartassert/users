<?php

declare(strict_types=1);

namespace App\Tests\Integration\Frontend\Token;

use App\Tests\Integration\AbstractIntegrationTest;

class RefreshTest extends AbstractIntegrationTest
{
    public function testRefreshSuccess(): void
    {
        $this->removeAllUsers();
        $this->removeAllRefreshTokens();
        $this->createTestUser();

        $user = $this->getTestUser();

        $createResponse = $this->application->makeFrontendCreateTokenRequest(
            self::TEST_USER_EMAIL,
            self::TEST_USER_PASSWORD
        );

        $createResponseData = json_decode($createResponse->getBody()->getContents(), true);
        self::assertIsArray($createResponseData);
        self::assertArrayHasKey('refresh_token', $createResponseData);
        $refreshToken = $createResponseData['refresh_token'] ?? '';

        $response = $this->application->makeFrontendRefreshTokenRequest($refreshToken);

        $this->applicationResponseAsserter->assertFrontendTokenCreateSuccessResponse(
            $response,
            $user,
            $refreshToken
        );
    }
}
