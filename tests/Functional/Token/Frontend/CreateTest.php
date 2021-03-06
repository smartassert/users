<?php

declare(strict_types=1);

namespace App\Tests\Functional\Token\Frontend;

use App\Tests\Functional\Token\AbstractTokenTest;
use App\Tests\Services\Asserter\ResponseAsserter\JwtTokenBodyAsserterFactory;
use App\Tests\Services\TestUserFactory;
use Doctrine\ORM\EntityManagerInterface;
use Gesdinet\JWTRefreshTokenBundle\Entity\RefreshToken;
use Gesdinet\JWTRefreshTokenBundle\Entity\RefreshTokenRepository;

class CreateTest extends AbstractTokenTest
{
    private EntityManagerInterface $entityManager;
    private RefreshTokenRepository $refreshTokenRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        \assert($entityManager instanceof EntityManagerInterface);
        $this->entityManager = $entityManager;

        $refreshTokenRepository = $entityManager->getRepository(RefreshToken::class);
        \assert($refreshTokenRepository instanceof RefreshTokenRepository);
        $this->refreshTokenRepository = $refreshTokenRepository;

        $this->removeAllRefreshTokens();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->removeAllRefreshTokens();
    }

    public function testCreateSuccess(): void
    {
        $testUserFactory = self::getContainer()->get(TestUserFactory::class);
        \assert($testUserFactory instanceof TestUserFactory);

        self::assertCount(0, $this->refreshTokenRepository->findAll());

        $user = $testUserFactory->create();
        $response = $this->application->makeFrontendCreateTokenRequest(...$testUserFactory->getCredentials());

        $refreshTokens = $this->refreshTokenRepository->findAll();
        self::assertCount(1, $refreshTokens);
        $refreshToken = $refreshTokens[0];
        self::assertInstanceOf(RefreshToken::class, $refreshToken);

        $jwtTokenBodyAsserterFactory = self::getContainer()->get(JwtTokenBodyAsserterFactory::class);
        \assert($jwtTokenBodyAsserterFactory instanceof JwtTokenBodyAsserterFactory);

        $this->applicationResponseAsserter->assertFrontendTokenCreateSuccessResponse(
            $response,
            $user,
            (string) $refreshToken->getRefreshToken()
        );
    }

    public function testCreateUserDoesNotExist(): void
    {
        $response = $this->application->makeFrontendCreateTokenRequest('', '');

        self::assertSame(401, $response->getStatusCode());
    }

    private function removeAllRefreshTokens(): void
    {
        $refreshTokens = $this->refreshTokenRepository->findAll();

        foreach ($refreshTokens as $refreshToken) {
            $this->entityManager->remove($refreshToken);
            $this->entityManager->flush();
        }
    }
}
