<?php

declare(strict_types=1);

namespace App\Tests\Functional\Token\Frontend;

use App\Tests\Functional\Token\AbstractTokenTest;
use App\Tests\Services\Asserter\ResponseAsserter\JwtTokenBodyAsserterFactory;
use App\Tests\Services\TestUserFactory;
use Doctrine\ORM\EntityManagerInterface;
use Gesdinet\JWTRefreshTokenBundle\Entity\RefreshToken;
use Gesdinet\JWTRefreshTokenBundle\Entity\RefreshTokenRepository;

class RefreshTest extends AbstractTokenTest
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

    public function testRefreshSuccess(): void
    {
        $testUserFactory = self::getContainer()->get(TestUserFactory::class);
        \assert($testUserFactory instanceof TestUserFactory);

        self::assertCount(0, $this->refreshTokenRepository->findAll());

        $user = $testUserFactory->create();
        $createTokenResponse = $this->application->makeFrontendCreateTokenRequest(
            ...$testUserFactory->getCredentials()
        );

        $responseData = json_decode($createTokenResponse->getBody()->getContents(), true);
        self::assertIsArray($responseData);
        self::assertArrayHasKey('refresh_token', $responseData);

        $refreshToken = $responseData['refresh_token'];

        $response = $this->application->makeFrontendRefreshTokenRequest($refreshToken);

        $jwtTokenBodyAsserterFactory = self::getContainer()->get(JwtTokenBodyAsserterFactory::class);
        \assert($jwtTokenBodyAsserterFactory instanceof JwtTokenBodyAsserterFactory);

        $this->applicationResponseAsserter->assertFrontendTokenCreateSuccessResponse(
            $response,
            $user,
            $refreshToken
        );
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
