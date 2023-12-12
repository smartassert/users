<?php

declare(strict_types=1);

namespace App\Tests\Application\Frontend\RefreshToken;

use App\Tests\Application\AbstractApplicationTestCase;

abstract class AbstractRevokeTest extends AbstractApplicationTestCase
{
    /**
     * @dataProvider badMethodDataProvider
     */
    public function testRevokeBadMethod(string $method): void
    {
        $response = $this->applicationClient->makeRevokeRefreshTokenRequest('jwt', 'refresh token', $method);

        self::assertSame(405, $response->getStatusCode());
    }

    /**
     * @return array<mixed>
     */
    public function badMethodDataProvider(): array
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
     * @dataProvider revokeUnauthorizedDataProvider
     */
    public function testRevokeUnauthorized(?string $jwt): void
    {
        $response = $this->applicationClient->makeRevokeRefreshTokenRequest($jwt, 'refresh token');

        self::assertSame(401, $response->getStatusCode());
    }

    /**
     * @return array<mixed>
     */
    public function revokeUnauthorizedDataProvider(): array
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

    public function testRevokeSuccess(): void
    {
        $userIdentifier = 'user@example.com';
        $userPassword = 'password';

        $createUserResponse = $this->applicationClient->makeAdminCreateUserRequest(
            $userIdentifier,
            $userPassword,
            $this->getAdminToken()
        );
        self::assertSame(200, $createUserResponse->getStatusCode());

        $createFrontendTokenResponse = $this->applicationClient->makeCreateFrontendTokenRequest(
            $userIdentifier,
            $userPassword
        );
        self::assertSame(200, $createFrontendTokenResponse->getStatusCode());
        self::assertSame('application/json', $createFrontendTokenResponse->getHeaderLine('content-type'));

        $frontendTokenData = json_decode($createFrontendTokenResponse->getBody()->getContents(), true);
        self::assertIsArray($frontendTokenData);
        self::assertArrayHasKey('token', $frontendTokenData);
        self::assertArrayHasKey('refresh_token', $frontendTokenData);

        $refreshResponse = $this->applicationClient->makeRefreshFrontendTokenRequest(
            $frontendTokenData['refresh_token']
        );
        self::assertSame(200, $refreshResponse->getStatusCode());

        $revokeResponse = $this->applicationClient->makeRevokeRefreshTokenRequest(
            $frontendTokenData['token'],
            $frontendTokenData['refresh_token']
        );
        self::assertSame(200, $revokeResponse->getStatusCode());

        $refreshResponse = $this->applicationClient->makeRefreshFrontendTokenRequest(
            $frontendTokenData['refresh_token']
        );
        self::assertSame(401, $refreshResponse->getStatusCode());
    }

    abstract protected function getAdminToken(): string;
}
