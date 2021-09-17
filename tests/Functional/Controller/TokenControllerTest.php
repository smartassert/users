<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Services\UserFactory;
use App\Tests\Services\UserRemover;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\JsonResponse;

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
        $this->userFactory->create($this->testUserEmail, $this->testUserPlainPassword);

        $this->client->request(
            'POST',
            '/token/create',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            (string) json_encode([
                'username' => $this->testUserEmail,
                'password' => $this->testUserPlainPassword,
            ])
        );

        $response = $this->client->getResponse();

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
        self::assertSame($this->testUserEmail, $tokenUserData['username']);
    }

    private function removeAllUsers(): void
    {
        $userRemover = self::getContainer()->get(UserRemover::class);
        if ($userRemover instanceof UserRemover) {
            $userRemover->removeAll();
        }
    }
}
