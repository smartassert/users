<?php

declare(strict_types=1);

namespace App\Tests\Application\Frontend\Token;

use App\Tests\Application\AbstractApplicationTest;

abstract class AbstractCreateVerifyRefreshTest extends AbstractApplicationTest
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

        $createResponse = $this->applicationClient->makeFrontendCreateTokenRequest($userEmail, $userPassword);
        self::assertSame(200, $createResponse->getStatusCode());
        self::assertSame('application/json', $createResponse->getHeaderLine('content-type'));

        $createData = json_decode($createResponse->getBody()->getContents(), true);
        self::assertIsArray($createData);
        self::assertArrayHasKey('token', $createData);
        self::assertArrayHasKey('refresh_token', $createData);

        $verifyResponse = $this->applicationClient->makeFrontendVerifyTokenRequest($createData['token']);
        self::assertSame(200, $verifyResponse->getStatusCode());

        $refreshResponse = $this->applicationClient->makeFrontendRefreshTokenRequest($createData['refresh_token']);
        self::assertSame(200, $refreshResponse->getStatusCode());
        self::assertSame('application/json', $refreshResponse->getHeaderLine('content-type'));

        $refreshData = json_decode($refreshResponse->getBody()->getContents(), true);
        self::assertIsArray($refreshData);
        self::assertArrayHasKey('token', $refreshData);
        self::assertArrayHasKey('refresh_token', $refreshData);

        $verifyResponse = $this->applicationClient->makeFrontendVerifyTokenRequest($refreshData['token']);
        self::assertSame(200, $verifyResponse->getStatusCode());
    }

    abstract protected function getAdminToken(): string;
}
