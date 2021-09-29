<?php

declare(strict_types=1);

namespace App\Tests\Functional\Token;

use App\Security\AudienceClaimInterface;
use App\Security\TokenInterface;
use App\Tests\Services\Asserter\ResponseAsserter\ArrayBodyAsserter;
use App\Tests\Services\Asserter\ResponseAsserter\JsonResponseAsserter;
use App\Tests\Services\Asserter\ResponseAsserter\JwtTokenBodyAsserterFactory;
use Doctrine\ORM\EntityManagerInterface;
use Gesdinet\JWTRefreshTokenBundle\Entity\RefreshTokenRepository;
use Gesdinet\JWTRefreshTokenBundle\Entity\RefreshToken;
use Symfony\Component\HttpFoundation\Response;

class FrontendCreateVerifyTest extends AbstractTokenTest
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

    public function testCreateSuccess(): void
    {
        self::assertCount(0, $this->refreshTokenRepository->findAll());

        $user = $this->testUserFactory->create();
        $response = $this->makeCreateTokenRequest(...$this->testUserFactory->getCredentials());

        $refreshTokens = $this->refreshTokenRepository->findAll();
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

    public function testCreateUserDoesNotExist(): void
    {
        $response = $this->makeCreateTokenRequest('', '');

        self::assertSame(401, $response->getStatusCode());
    }

    /**
     * @dataProvider verifyUnauthorizedDataProvider
     */
    public function testVerifyUnauthorized(?string $jwt): void
    {
        $response = $this->makeVerifyTokenRequest($jwt);

        self::assertSame(401, $response->getStatusCode());
    }

    /**
     * @return array<mixed>
     */
    public function verifyUnauthorizedDataProvider(): array
    {
        return [
            'no jwt' => [
                'token' => null,
            ],
            'malformed jwt' => [
                'token' => 'malformed.jwt.token',
            ],
            'invalid jwt' => [
                'token' => 'eyJhbGciOiJIUzI1NiJ9.e30.ZRrHA1JJJW8opsbCGfG_HACGpVUMN_a9IV7pAx_Zmeo',
            ],
        ];
    }

    public function testVerifyValidJwt(): void
    {
        $user = $this->testUserFactory->create();
        $createTokenResponse = $this->makeCreateTokenRequest(...$this->testUserFactory->getCredentials());

        $this->removeAllUsers();

        $createTokenResponseData = json_decode((string) $createTokenResponse->getContent(), true);
        $response = $this->makeVerifyTokenRequest($createTokenResponseData['token']);

        (new JsonResponseAsserter(200))
            ->addBodyAsserter(new ArrayBodyAsserter([
                'id' => $user->getId(),
                'user-identifier' => $user->getUserIdentifier(),
            ]))
            ->assert($response)
        ;
    }

    protected function getCreateUrlParameter(): string
    {
        return 'route-frontend-token-create';
    }

    protected function getVerifyUrlParameter(): string
    {
        return 'route-frontend-token-verify';
    }

    private function makeCreateTokenRequest(string $userIdentifier, string $password): Response
    {
        $this->client->request(
            'POST',
            $this->createUrl,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            (string) json_encode([
                'username' => $userIdentifier,
                'password' => $password,
            ])
        );

        return $this->client->getResponse();
    }

    private function makeVerifyTokenRequest(?string $jwt): Response
    {
        $headers = [];
        if (is_string($jwt)) {
            $headers['HTTP_AUTHORIZATION'] = 'Bearer ' . $jwt;
        }

        $this->client->request(
            'GET',
            $this->verifyUrl,
            [],
            [],
            $headers,
        );

        return $this->client->getResponse();
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
