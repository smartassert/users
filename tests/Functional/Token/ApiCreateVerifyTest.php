<?php

declare(strict_types=1);

namespace App\Tests\Functional\Token;

use App\Entity\ApiKey;
use App\Entity\User;
use App\Security\AudienceClaimInterface;
use App\Security\TokenInterface;
use App\Services\ApiKeyFactory;
use App\Tests\Functional\AbstractBaseWebTestCase;
use App\Tests\Services\Asserter\ResponseAsserter\JsonResponseAsserter;
use App\Tests\Services\Asserter\ResponseAsserter\JwtTokenBodyAsserterFactory;
use App\Tests\Services\Asserter\ResponseAsserter\TextPlainBodyAsserter;
use App\Tests\Services\Asserter\ResponseAsserter\TextPlainResponseAsserter;
use App\Tests\Services\TestUserFactory;
use Symfony\Component\HttpFoundation\Response;

class ApiCreateVerifyTest extends AbstractBaseWebTestCase
{
    private User $user;
    private ApiKey $apiKey;
    private string $createUrl = '';
    private string $verifyUrl = '';

    protected function setUp(): void
    {
        parent::setUp();

        $this->removeAllUsers();

        $testUserFactory = self::getContainer()->get(TestUserFactory::class);
        \assert($testUserFactory instanceof TestUserFactory);
        $this->user = $testUserFactory->create();

        $apiKeyFactory = self::getContainer()->get(ApiKeyFactory::class);
        \assert($apiKeyFactory instanceof ApiKeyFactory);
        $this->apiKey = $apiKeyFactory->create('api key label', $this->user);

        $createUrl = self::getContainer()->getParameter('route-api-token-create');
        if (is_string($createUrl)) {
            $this->createUrl = $createUrl;
        }

        $verifyUrl = self::getContainer()->getParameter('route-api-token-verify');
        if (is_string($verifyUrl)) {
            $this->verifyUrl = $verifyUrl;
        }
    }

    public function testCreateSuccess(): void
    {
        $response = $this->makeCreateTokenRequest((string) $this->apiKey);

        $jwtTokenBodyAsserterFactory = self::getContainer()->get(JwtTokenBodyAsserterFactory::class);
        \assert($jwtTokenBodyAsserterFactory instanceof JwtTokenBodyAsserterFactory);

        (new JsonResponseAsserter(200))
            ->addBodyAsserter($jwtTokenBodyAsserterFactory->create(
                'token',
                [
                    TokenInterface::CLAIM_EMAIL => $this->user->getUserIdentifier(),
                    TokenInterface::CLAIM_USER_ID => $this->user->getId(),
                    TokenInterface::CLAIM_AUDIENCE => [
                        AudienceClaimInterface::AUD_API,
                    ],
                ]
            ))
            ->assert($response)
        ;
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
        $createTokenResponse = $this->makeCreateTokenRequest((string) $this->apiKey);
        $userId = $this->user->getId();

        $this->removeAllUsers();

        $createTokenResponseData = json_decode((string) $createTokenResponse->getContent(), true);

        $response = $this->makeVerifyTokenRequest($createTokenResponseData['token']);

        (new TextPlainResponseAsserter(200))
            ->addBodyAsserter(new TextPlainBodyAsserter($userId))
            ->assert($response)
        ;
    }

    public function testCreateUserDoesNotExist(): void
    {
        $response = $this->makeCreateTokenRequest('');

        self::assertSame(401, $response->getStatusCode());
    }

    private function makeCreateTokenRequest(string $token): Response
    {
        $headers = [
            'HTTP_AUTHORIZATION' => $token,
        ];

        $this->client->request(
            'POST',
            $this->createUrl,
            [],
            [],
            $headers,
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
