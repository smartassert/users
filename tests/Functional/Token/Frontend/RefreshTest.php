<?php

declare(strict_types=1);

namespace App\Tests\Functional\Token\Frontend;

use App\Security\AudienceClaimInterface;
use App\Security\TokenInterface;
use App\Tests\Functional\AbstractBaseWebTestCase;
use App\Tests\Services\Asserter\ResponseAsserter\ArrayBodyAsserter;
use App\Tests\Services\Asserter\ResponseAsserter\JsonResponseAsserter;
use App\Tests\Services\Asserter\ResponseAsserter\JwtTokenBodyAsserterFactory;
use App\Tests\Services\TestUserFactory;
use Doctrine\ORM\EntityManagerInterface;
use Gesdinet\JWTRefreshTokenBundle\Entity\RefreshToken;
use Gesdinet\JWTRefreshTokenBundle\Entity\RefreshTokenRepository;

class RefreshTest extends AbstractBaseWebTestCase
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

        $responseData = json_decode((string) $createTokenResponse->getContent(), true);
        $refreshToken = $responseData['refresh_token'];

        $response = $this->application->makeFrontendRefreshTokenRequest($refreshToken);

        $jwtTokenBodyAsserterFactory = self::getContainer()->get(JwtTokenBodyAsserterFactory::class);
        \assert($jwtTokenBodyAsserterFactory instanceof JwtTokenBodyAsserterFactory);

        (new JsonResponseAsserter(200))
            ->addBodyAsserter($jwtTokenBodyAsserterFactory->create(
                'token',
                [
                    TokenInterface::CLAIM_EMAIL => $user->getUserIdentifier(),
                    TokenInterface::CLAIM_USER_ID => $user->getId(),
                    TokenInterface::CLAIM_AUDIENCE => [
                        AudienceClaimInterface::AUD_FRONTEND,
                    ],
                ]
            ))
            ->addBodyAsserter(
                new ArrayBodyAsserter([
                    'refresh_token' => $refreshToken,
                ])
            )
            ->assertFromSymfonyResponse($response)
        ;
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
