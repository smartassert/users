<?php

declare(strict_types=1);

namespace App\Tests\Application\Frontend\Token;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Services\ApiKeyFactory;
use App\Tests\Application\AbstractApplicationTestCase;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use webignition\ObjectReflector\ObjectReflector;

abstract class AbstractCreateVerifyRefreshTestCase extends AbstractApplicationTestCase
{
    /**
     * @dataProvider createBadMethodDataProvider
     */
    public function testCreateBadMethod(string $method): void
    {
        $response = $this->applicationClient->makeFrontendCreateTokenRequest('user@example.com', 'password', $method);

        self::assertSame(405, $response->getStatusCode());
    }

    /**
     * @return array<mixed>
     */
    public function createBadMethodDataProvider(): array
    {
        return [
            'GET' => [
                'method' => 'GET',
            ],
            'PUT' => [
                'method' => 'PUT',
            ],
            'DELETE' => [
                'method' => 'DELETE',
            ],
        ];
    }

    /**
     * @dataProvider verifyBadMethodDataProvider
     */
    public function testVerifyBadMethod(string $method): void
    {
        $response = $this->applicationClient->makeFrontendVerifyTokenRequest($this->getAdminToken(), $method);

        self::assertSame(405, $response->getStatusCode());
    }

    /**
     * @return array<mixed>
     */
    public function verifyBadMethodDataProvider(): array
    {
        return [
            'POST' => [
                'method' => 'POST',
            ],
            'PUT' => [
                'method' => 'PUT',
            ],
            'DELETE' => [
                'method' => 'DELETE',
            ],
        ];
    }

    /**
     * @dataProvider refreshBadMethodDataProvider
     */
    public function testRefreshBadMethod(string $method): void
    {
        $response = $this->applicationClient->makeFrontendRefreshTokenRequest('refresh token', $method);

        self::assertSame(405, $response->getStatusCode());
    }

    /**
     * @return array<mixed>
     */
    public function refreshBadMethodDataProvider(): array
    {
        return [
            'GET' => [
                'method' => 'GET',
            ],
            'PUT' => [
                'method' => 'PUT',
            ],
            'DELETE' => [
                'method' => 'DELETE',
            ],
        ];
    }

    public function testCreateUnauthorized(): void
    {
        $response = $this->applicationClient->makeFrontendCreateTokenRequest('user@example.com', 'password');

        self::assertSame(401, $response->getStatusCode());
    }

    /**
     * @dataProvider verifyUnauthorizedDataProvider
     */
    public function testVerifyUnauthorized(?string $token): void
    {
        $response = $this->applicationClient->makeFrontendVerifyTokenRequest($token);

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

    public function testVerifyUnauthorizedForApiToken(): void
    {
        $userEmail = 'user@example.com';
        $userPassword = 'password';

        $createUserResponse = $this->applicationClient->makeAdminCreateUserRequest(
            $userEmail,
            $userPassword,
            $this->getAdminToken()
        );
        self::assertSame(200, $createUserResponse->getStatusCode());

        $userRepository = self::getContainer()->get(UserRepository::class);
        \assert($userRepository instanceof UserRepository);
        $user = $userRepository->findAll()[0];
        self::assertInstanceOf(User::class, $user);

        $apiKeyFactory = self::getContainer()->get(ApiKeyFactory::class);
        \assert($apiKeyFactory instanceof ApiKeyFactory);
        $apiKey = $apiKeyFactory->create($user);
        $apiKeyId = ObjectReflector::getProperty($apiKey, 'id');
        self::assertIsString($apiKeyId);

        $createApiTokenResponse = $this->applicationClient->makeApiCreateTokenRequest($apiKeyId);
        self::assertSame(200, $createApiTokenResponse->getStatusCode());
        self::assertSame('application/json', $createApiTokenResponse->getHeaderLine('content-type'));

        $createApiTokenData = json_decode($createApiTokenResponse->getBody()->getContents(), true);
        self::assertIsArray($createApiTokenData);
        self::assertArrayHasKey('token', $createApiTokenData);

        $apiToken = $createApiTokenData['token'];
        self::assertIsString($apiToken);

        $verifyResponse = $this->applicationClient->makeFrontendVerifyTokenRequest($apiToken);

        self::assertSame(401, $verifyResponse->getStatusCode());
    }

    public function testRefreshUnauthorized(): void
    {
        $response = $this->applicationClient->makeFrontendRefreshTokenRequest('invalid token');

        self::assertSame(401, $response->getStatusCode());
    }

    public function testCreateAndVerifyAndRefreshSuccess(): void
    {
        $userEmail = 'user@example.com';
        $userPassword = 'password';

        $createUserResponse = $this->applicationClient->makeAdminCreateUserRequest(
            $userEmail,
            $userPassword,
            $this->getAdminToken()
        );
        self::assertSame(200, $createUserResponse->getStatusCode());

        $userRepository = self::getContainer()->get(UserRepository::class);
        \assert($userRepository instanceof UserRepository);

        $user = $userRepository->findAll()[0];
        self::assertInstanceOf(User::class, $user);

        $createResponse = $this->applicationClient->makeFrontendCreateTokenRequest($userEmail, $userPassword);
        self::assertSame(200, $createResponse->getStatusCode());
        self::assertSame('application/json', $createResponse->getHeaderLine('content-type'));

        $createData = json_decode($createResponse->getBody()->getContents(), true);
        self::assertIsArray($createData);
        self::assertArrayHasKey('token', $createData);
        self::assertArrayHasKey('refresh_token', $createData);

        $token = $createData['token'];
        self::assertIsString($token);

        $tokenManager = self::getContainer()->get(JWTTokenManagerInterface::class);
        \assert($tokenManager instanceof JWTTokenManagerInterface);

        $tokenData = $tokenManager->parse($token);
        self::assertIsArray($tokenData);
        self::assertSame($user->getId(), $tokenData['sub']);
        self::assertSame($user->getUserIdentifier(), $tokenData['email']);
        self::assertSame(['frontend'], $tokenData['aud']);
        self::assertSame(['ROLE_USER'], $tokenData['roles']);

        $verifyResponse = $this->applicationClient->makeFrontendVerifyTokenRequest($token);
        self::assertSame(200, $verifyResponse->getStatusCode());
        self::assertSame('application/json', $createResponse->getHeaderLine('content-type'));

        $verifyData = json_decode($verifyResponse->getBody()->getContents(), true);
        self::assertIsArray($verifyData);
        self::assertEquals(
            [
                'id' => $user->getId(),
                'user-identifier' => $user->getUserIdentifier(),
            ],
            $verifyData
        );

        $refreshResponse = $this->applicationClient->makeFrontendRefreshTokenRequest($createData['refresh_token']);
        self::assertSame(200, $refreshResponse->getStatusCode());
        self::assertSame('application/json', $refreshResponse->getHeaderLine('content-type'));

        $refreshData = json_decode($refreshResponse->getBody()->getContents(), true);
        self::assertIsArray($refreshData);
        self::assertArrayHasKey('token', $refreshData);
        self::assertArrayHasKey('refresh_token', $refreshData);

        $verifyResponse = $this->applicationClient->makeFrontendVerifyTokenRequest($refreshData['token']);
        self::assertSame(200, $verifyResponse->getStatusCode());
        self::assertSame('application/json', $createResponse->getHeaderLine('content-type'));

        $verifyData = json_decode($verifyResponse->getBody()->getContents(), true);
        self::assertIsArray($verifyData);
        self::assertEquals(
            [
                'id' => $user->getId(),
                'user-identifier' => $user->getUserIdentifier(),
            ],
            $verifyData
        );
    }

    abstract protected function getAdminToken(): string;
}
