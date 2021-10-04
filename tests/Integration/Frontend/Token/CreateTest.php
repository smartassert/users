<?php

declare(strict_types=1);

namespace App\Tests\Integration\Frontend\Token;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Routes;
use App\Security\AudienceClaimInterface;
use App\Security\TokenInterface;
use App\Tests\Integration\AbstractIntegrationTest;
use App\Tests\Services\Asserter\ResponseAsserter\ArrayBodyAsserter;
use App\Tests\Services\Asserter\ResponseAsserter\JsonResponseAsserter;
use App\Tests\Services\Asserter\ResponseAsserter\JwtTokenBodyAsserterFactory;
use Doctrine\ORM\EntityManagerInterface;
use Gesdinet\JWTRefreshTokenBundle\Entity\RefreshToken;
use Gesdinet\JWTRefreshTokenBundle\Entity\RefreshTokenRepository;
use Psr\Http\Message\RequestInterface;

class CreateTest extends AbstractIntegrationTest
{
    public function testCreateInvalidCredentials(): void
    {
        $request = $this->createCreateTokenRequest([
            'username' => 'invalid@example.com',
            'password' => 'password',
        ]);

        $response = $this->httpClient->sendRequest($request);

        self::assertSame(401, $response->getStatusCode());
    }

    public function testCreateSuccess(): void
    {
        $this->removeAllUsers();
        $this->removeAllRefreshTokens();
        $this->createTestUser();

        $userRepository = self::getContainer()->get(UserRepository::class);
        \assert($userRepository instanceof UserRepository);
        $user = $userRepository->findByEmail(self::TEST_USER_EMAIL);
        \assert($user instanceof User);

        $request = $this->createCreateTokenRequest([
            'username' => self::TEST_USER_EMAIL,
            'password' => self::TEST_USER_PASSWORD,
        ]);

        $response = $this->httpClient->sendRequest($request);

        self::assertSame(200, $response->getStatusCode());

        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        \assert($entityManager instanceof EntityManagerInterface);
        $refreshTokenRepository = $entityManager->getRepository(RefreshToken::class);
        \assert($refreshTokenRepository instanceof RefreshTokenRepository);

        $refreshTokens = $refreshTokenRepository->findAll();
        self::assertCount(1, $refreshTokens);
        $refreshToken = $refreshTokens[0];
        self::assertInstanceOf(RefreshToken::class, $refreshToken);

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
                    'refresh_token' => $refreshToken->getRefreshToken(),
                ])
            )
            ->assert($response)
        ;
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
