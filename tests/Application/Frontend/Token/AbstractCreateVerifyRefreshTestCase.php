<?php

declare(strict_types=1);

namespace App\Tests\Application\Frontend\Token;

use App\Repository\UserRepository;
use App\Services\ApiKeyFactory;
use App\Tests\Application\AbstractApplicationTestCase;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

abstract class AbstractCreateVerifyRefreshTestCase extends AbstractApplicationTestCase
{
    /**
     * @dataProvider createBadMethodDataProvider
     */
    public function testCreateBadMethod(string $method): void
    {
        $response = $this->applicationClient->makeCreateFrontendTokenRequest('user@example.com', 'password', $method);

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
        $response = $this->applicationClient->makeVerifyFrontendTokenRequest($this->getAdminToken(), $method);

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
        $response = $this->applicationClient->makeRefreshFrontendTokenRequest('refresh token', $method);

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
        $response = $this->applicationClient->makeCreateFrontendTokenRequest('user@example.com', 'password');

        self::assertSame(401, $response->getStatusCode());
    }

    /**
     * @dataProvider verifyUnauthorizedDataProvider
     */
    public function testVerifyUnauthorized(?string $token): void
    {
        $response = $this->applicationClient->makeVerifyFrontendTokenRequest($token);

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
        $userIdentifier = 'user@example.com';
        $userPassword = 'password';

        $createUserResponse = $this->applicationClient->makeAdminCreateUserRequest(
            $userIdentifier,
            $userPassword,
            $this->getAdminToken()
        );
        self::assertSame(200, $createUserResponse->getStatusCode());

        $userRepository = self::getContainer()->get(UserRepository::class);
        \assert($userRepository instanceof UserRepository);
        $user = $userRepository->findAll()[0];

        $apiKeyFactory = self::getContainer()->get(ApiKeyFactory::class);
        \assert($apiKeyFactory instanceof ApiKeyFactory);
        $apiKey = $apiKeyFactory->create($user);

        $createApiTokenResponse = $this->applicationClient->makeCreteApiTokenRequest($apiKey->id);
        self::assertSame(200, $createApiTokenResponse->getStatusCode());
        self::assertSame('application/json', $createApiTokenResponse->getHeaderLine('content-type'));

        $createApiTokenData = json_decode($createApiTokenResponse->getBody()->getContents(), true);
        self::assertIsArray($createApiTokenData);
        self::assertArrayHasKey('token', $createApiTokenData);

        $apiToken = $createApiTokenData['token'];
        self::assertIsString($apiToken);

        $verifyResponse = $this->applicationClient->makeVerifyFrontendTokenRequest($apiToken);

        self::assertSame(401, $verifyResponse->getStatusCode());
    }

    /**
     * @dataProvider refreshUnauthorizedDataProvider
     */
    public function testRefreshUnauthorized(?string $token, bool $forceEmptyPayload = false): void
    {
        $response = $this->applicationClient->makeRefreshFrontendTokenRequest($token, 'POST', $forceEmptyPayload);

        self::assertSame(401, $response->getStatusCode());
    }

    /**
     * @return array<mixed>
     */
    public function refreshUnauthorizedDataProvider(): array
    {
        return [
            'non-empty invalid token' => [
                'token' => 'invalid token',
            ],
            'empty token' => [
                'token' => '',
            ],
            'null token, no payload' => [
                'token' => null,
            ],
            'null token, forced empty payload' => [
                'token' => null,
                'forceEmptyPayload' => true,
            ],
        ];
    }

    /**
     * @dataProvider createBadRequestDataProvider
     */
    public function testCreateBadRequest(
        string $createUserIdentifier,
        string $createUserPassword,
        ?string $createTokenIdentifier,
        ?string $createTokenPassword
    ): void {
        $createUserResponse = $this->applicationClient->makeAdminCreateUserRequest(
            $createUserIdentifier,
            $createUserPassword,
            $this->getAdminToken()
        );
        self::assertSame(200, $createUserResponse->getStatusCode());

        $userRepository = self::getContainer()->get(UserRepository::class);
        \assert($userRepository instanceof UserRepository);

        $createResponse = $this->applicationClient->makeCreateFrontendTokenRequest(
            $createTokenIdentifier,
            $createTokenPassword
        );
        self::assertSame(401, $createResponse->getStatusCode());
        self::assertSame('', $createResponse->getHeaderLine('content-type'));
    }

    /**
     * @return array<mixed>
     */
    public function createBadRequestDataProvider(): array
    {
        $userIdentifier = 'user@example.com';
        $userPassword = 'password';

        return [
            'no credentials' => [
                'createUserIdentifier' => $userIdentifier,
                'createUserPassword' => $userPassword,
                'createTokenIdentifier' => null,
                'createTokenPassword' => null,
            ],
            'user identifier missing' => [
                'createUserIdentifier' => $userIdentifier,
                'createUserPassword' => $userPassword,
                'createTokenIdentifier' => null,
                'createTokenPassword' => $userPassword,
            ],
            'password missing' => [
                'createUserIdentifier' => $userIdentifier,
                'createUserPassword' => $userPassword,
                'createTokenIdentifier' => $userIdentifier,
                'createTokenPassword' => null,
            ],
        ];
    }

    public function testCreateAndVerifyAndRefreshSuccess(): void
    {
        $userIdentifier = 'user@example.com';
        $userPassword = 'password';

        $createUserResponse = $this->applicationClient->makeAdminCreateUserRequest(
            $userIdentifier,
            $userPassword,
            $this->getAdminToken()
        );
        self::assertSame(200, $createUserResponse->getStatusCode());

        $userRepository = self::getContainer()->get(UserRepository::class);
        \assert($userRepository instanceof UserRepository);

        $user = $userRepository->findAll()[0];

        $createResponse = $this->applicationClient->makeCreateFrontendTokenRequest($userIdentifier, $userPassword);
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
        self::assertSame($user->getId(), $tokenData['sub']);
        self::assertSame($user->getUserIdentifier(), $tokenData['userIdentifier']);
        self::assertSame(['frontend'], $tokenData['aud']);
        self::assertSame(['ROLE_USER'], $tokenData['roles']);

        $verifyResponse = $this->applicationClient->makeVerifyFrontendTokenRequest($token);
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

        $refreshResponse = $this->applicationClient->makeRefreshFrontendTokenRequest($createData['refresh_token']);
        self::assertSame(200, $refreshResponse->getStatusCode());
        self::assertSame('application/json', $refreshResponse->getHeaderLine('content-type'));

        $refreshData = json_decode($refreshResponse->getBody()->getContents(), true);
        self::assertIsArray($refreshData);
        self::assertArrayHasKey('token', $refreshData);
        self::assertArrayHasKey('refresh_token', $refreshData);

        $verifyResponse = $this->applicationClient->makeVerifyFrontendTokenRequest($refreshData['token']);
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
