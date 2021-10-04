<?php

declare(strict_types=1);

namespace App\Tests\Integration\Frontend\Token;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Routes;
use App\Tests\Integration\AbstractIntegrationTest;
use App\Tests\Services\ApplicationResponseAsserter;
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
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        \assert($entityManager instanceof EntityManagerInterface);
        $refreshTokenRepository = $entityManager->getRepository(RefreshToken::class);
        \assert($refreshTokenRepository instanceof RefreshTokenRepository);

        $user = $userRepository->findByEmail(self::TEST_USER_EMAIL);
        \assert($user instanceof User);

        $request = $this->createCreateTokenRequest([
            'username' => self::TEST_USER_EMAIL,
            'password' => self::TEST_USER_PASSWORD,
        ]);

        $response = $this->httpClient->sendRequest($request);

        self::assertSame(200, $response->getStatusCode());

        $refreshTokens = $refreshTokenRepository->findAll();
        self::assertCount(1, $refreshTokens);
        $refreshToken = $refreshTokens[0];
        self::assertInstanceOf(RefreshToken::class, $refreshToken);

        $applicationResponseAsserter = self::getContainer()->get(ApplicationResponseAsserter::class);
        \assert($applicationResponseAsserter instanceof ApplicationResponseAsserter);

        $applicationResponseAsserter->assertFrontendTokenCreateSuccessResponse(
            $response,
            $user,
            (string) $refreshToken->getRefreshToken()
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
