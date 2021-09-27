<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Controller\TokenController;
use App\Services\UserFactory;
use App\Tests\Services\UserRemover;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
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

        self::assertSame(200, $response->getStatusCode());
        self::assertInstanceOf(JsonResponse::class, $response);

        $responseData = json_decode((string) $response->getContent(), true);
        self::assertArrayHasKey('token', $responseData);

        $token = $responseData['token'];

        $jwtManager = self::getContainer()->get('lexik_jwt_authentication.jwt_manager');
        \assert($jwtManager instanceof JWTTokenManagerInterface);

        $tokenUserData = $jwtManager->parse($token);

        self::assertIsArray($tokenUserData);
        self::assertArrayHasKey('username', $tokenUserData);
        self::assertSame($user->getUserIdentifier(), $tokenUserData['username']);
        self::assertArrayHasKey('id', $tokenUserData);
        self::assertSame($user->getId(), $tokenUserData['id']);
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
        $this->makeTokenVerifyRequest($createTokenResponseData['token']);

        $response = $this->client->getResponse();

        self::assertInstanceOf(JsonResponse::class, $response);
        $responseData = json_decode((string) $response->getContent(), true);

        self::assertArrayHasKey('id', $responseData);
        self::assertSame($user->getId(), $responseData['id']);
        self::assertArrayHasKey('user-identifier', $responseData);
        self::assertSame($user->getUserIdentifier(), $responseData['user-identifier']);
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
