<?php

declare(strict_types=1);

namespace App\Tests\Integration\ApiKey;

use App\Tests\Integration\AbstractIntegrationTest;
use Doctrine\ORM\EntityManagerInterface;
use Gesdinet\JWTRefreshTokenBundle\Entity\RefreshToken;
use Gesdinet\JWTRefreshTokenBundle\Entity\RefreshTokenRepository;

class ListTest extends AbstractIntegrationTest
{
    public function testListInvalidCredentials(): void
    {
        $response = $this->application->makeFrontendListApiKeysRequest('invalid@example.com', 'password');

        $this->applicationResponseAsserter->assertFrontendUnauthorizedResponse($response);
    }

    public function testListSuccess(): void
    {
        $this->createTestUser();

        $response = $this->application->makeFrontendListApiKeysRequest(self::TEST_USER_EMAIL, self::TEST_USER_PASSWORD);

        $this->applicationResponseAsserter->assertFrontendListApiKeysResponse($response, []);
    }
}
