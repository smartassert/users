<?php

declare(strict_types=1);

namespace App\Tests\Functional\Token;

use App\Security\AudienceClaimInterface;
use App\Security\TokenInterface;
use App\Tests\Functional\AbstractBaseWebTestCase;
use App\Tests\Services\Asserter\ResponseAsserter\ArrayBodyAsserter;
use App\Tests\Services\Asserter\ResponseAsserter\JsonResponseAsserter;
use App\Tests\Services\Asserter\ResponseAsserter\JwtTokenBodyAsserterFactory;
use App\Tests\Services\TestUserFactory;
use Symfony\Component\HttpFoundation\Response;

class FrontendCreateVerifyTest extends AbstractBaseWebTestCase
{
    protected TestUserFactory $testUserFactory;
    protected string $createUrl = '';
    protected string $verifyUrl = '';

    protected function setUp(): void
    {
        parent::setUp();

        $testUserFactory = self::getContainer()->get(TestUserFactory::class);
        \assert($testUserFactory instanceof TestUserFactory);
        $this->testUserFactory = $testUserFactory;

        $createUrl = self::getContainer()->getParameter('route-frontend-token-create');
        if (is_string($createUrl)) {
            $this->createUrl = $createUrl;
        }

        $verifyUrl = self::getContainer()->getParameter('route-frontend-token-verify');
        if (is_string($verifyUrl)) {
            $this->verifyUrl = $verifyUrl;
        }

        $this->removeAllUsers();
    }

    public function testCreateSuccess(): void
    {
        $user = $this->testUserFactory->create();
        $response = $this->makeCreateTokenRequest(...$this->testUserFactory->getCredentials());

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
}
