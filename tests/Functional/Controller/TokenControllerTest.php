<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Controller\TokenController;
use App\Security\TokenInterface;
use App\Services\UserFactory;
use App\Tests\Services\Asserter\ResponseAsserter\ArrayBodyAsserter;
use App\Tests\Services\Asserter\ResponseAsserter\JsonResponseAsserter;
use App\Tests\Services\Asserter\ResponseAsserter\JwtTokenBodyAsserterFactory;
use App\Tests\Services\UserRemover;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class TokenControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private UserFactory $userFactory;
    private string $testUserEmail;
    private string $testUserPlainPassword;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();

        $userFactory = self::getContainer()->get(UserFactory::class);
        \assert($userFactory instanceof UserFactory);
        $this->userFactory = $userFactory;

        $testUserEmail = self::getContainer()->getParameter('test_user_email');
        if (!is_string($testUserEmail)) {
            $this->fail('test_user_email parameter not set correctly');
        }
        $this->testUserEmail = $testUserEmail;

        $testUserPlainPassword = self::getContainer()->getParameter('test_user_password');
        if (!is_string($testUserPlainPassword)) {
            $this->fail('test_user_password parameter not set correctly');
        }
        $this->testUserPlainPassword = $testUserPlainPassword;

        $this->removeAllUsers();
    }

    protected function tearDown(): void
    {
        $this->removeAllUsers();

        parent::tearDown();
    }

    public function testCreateSuccess(): void
    {
        $user = $this->userFactory->create($this->testUserEmail, $this->testUserPlainPassword);
        $response = $this->makeTokenCreateRequest();

        $jwtTokenBodyAsserterFactory = self::getContainer()->get(JwtTokenBodyAsserterFactory::class);
        \assert($jwtTokenBodyAsserterFactory instanceof JwtTokenBodyAsserterFactory);

        JsonResponseAsserter::create()
            ->withExpectedStatusCode(200)
            ->withBodyAsserter(
                $jwtTokenBodyAsserterFactory->create(
                    'token',
                    [
                        TokenInterface::CLAIM_EMAIL => $user->getUserIdentifier(),
                        TokenInterface::CLAIM_USER_ID => $user->getId(),
                    ]
                )
            )
            ->assert($response)
        ;
    }

    public function testCreateUserDoesNotExist(): void
    {
        $response = $this->makeTokenCreateRequest();

        self::assertSame(401, $response->getStatusCode());
    }

    /**
     * @dataProvider verifyUnauthorizedDataProvider
     */
    public function testVerifyUnauthorized(?string $jwt): void
    {
        $response = $this->makeTokenVerifyRequest($jwt);

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
        $user = $this->userFactory->create($this->testUserEmail, $this->testUserPlainPassword);
        $createTokenResponse = $this->makeTokenCreateRequest();

        $this->removeAllUsers();

        $createTokenResponseData = json_decode((string) $createTokenResponse->getContent(), true);
        $response = $this->makeTokenVerifyRequest($createTokenResponseData['token']);

        JsonResponseAsserter::create()
            ->withExpectedStatusCode(200)
            ->withBodyAsserter(new ArrayBodyAsserter([
                'id' => $user->getId(),
                'user-identifier' => $user->getUserIdentifier(),
            ]))
            ->assert($response)
        ;
    }

    private function makeTokenCreateRequest(): Response
    {
        $this->client->request(
            'POST',
            TokenController::ROUTE_CREATE,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            (string) json_encode([
                'username' => $this->testUserEmail,
                'password' => $this->testUserPlainPassword,
            ])
        );

        return $this->client->getResponse();
    }

    private function makeTokenVerifyRequest(?string $jwt): Response
    {
        $headers = [];
        if (is_string($jwt)) {
            $headers['HTTP_AUTHORIZATION'] = 'Bearer ' . $jwt;
        }

        $this->client->request(
            'GET',
            TokenController::ROUTE_VERIFY,
            [],
            [],
            $headers,
        );

        return $this->client->getResponse();
    }

    private function removeAllUsers(): void
    {
        $userRemover = self::getContainer()->get(UserRemover::class);
        if ($userRemover instanceof UserRemover) {
            $userRemover->removeAll();
        }
    }
}
