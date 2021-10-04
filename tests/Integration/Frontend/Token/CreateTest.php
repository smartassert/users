<?php

declare(strict_types=1);

namespace App\Tests\Integration\Frontend\Token;

use App\Tests\Integration\AbstractIntegrationTest;
use Doctrine\ORM\EntityManagerInterface;
use Gesdinet\JWTRefreshTokenBundle\Entity\RefreshToken;
use Gesdinet\JWTRefreshTokenBundle\Entity\RefreshTokenRepository;

class CreateTest extends AbstractIntegrationTest
{
    private RefreshTokenRepository $refreshTokenRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        \assert($entityManager instanceof EntityManagerInterface);

        $refreshTokenRepository = $entityManager->getRepository(RefreshToken::class);
        \assert($refreshTokenRepository instanceof RefreshTokenRepository);
        $this->refreshTokenRepository = $refreshTokenRepository;
    }

    public function testCreateInvalidCredentials(): void
    {
        $response = $this->application->makeFrontendCreateTokenRequest('invalid@example.com', 'password');

        self::assertSame(401, $response->getStatusCode());
    }

    public function testCreateSuccess(): void
    {
        $this->removeAllUsers();
        $this->removeAllRefreshTokens();
        $this->createTestUser();

        $user = $this->getTestUser();

        $response = $this->application->makeFrontendCreateTokenRequest(self::TEST_USER_EMAIL, self::TEST_USER_PASSWORD);

        $refreshTokens = $this->refreshTokenRepository->findAll();
        self::assertCount(1, $refreshTokens);
        $refreshToken = $refreshTokens[0];
        self::assertInstanceOf(RefreshToken::class, $refreshToken);

        $this->applicationResponseAsserter->assertFrontendTokenCreateSuccessResponse(
            $response,
            $user,
            (string) $refreshToken->getRefreshToken()
        );
    }
}
