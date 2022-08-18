<?php

declare(strict_types=1);

namespace App\Tests\Application\Frontend\ApiKey;

use App\Tests\Application\AbstractApplicationTest;

abstract class AbstractListTest extends AbstractApplicationTest
{
    /**
     * @dataProvider listBadMethodDataProvider
     */
    public function testListBadMethod(string $method): void
    {
        $response = $this->applicationClient->makeFrontendListApiKeysRequest('token', $method);

        self::assertSame(405, $response->getStatusCode());
    }

    /**
     * @return array<mixed>
     */
    public function listBadMethodDataProvider(): array
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
     * @dataProvider listUnauthorizedDataProvider
     */
    public function testListUnauthorized(?string $token): void
    {
        $response = $this->applicationClient->makeFrontendListApiKeysRequest($token);

        self::assertSame(401, $response->getStatusCode());
    }

    /**
     * @return array<mixed>
     */
    public function listUnauthorizedDataProvider(): array
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

    public function testListSuccess(): void
    {
        $userEmail = 'user@example.com';
        $userPassword = 'password';

        $createUserResponse = $this->applicationClient->makeAdminCreateUserRequest(
            $userEmail,
            $userPassword,
            $this->getAdminToken()
        );
        self::assertSame(200, $createUserResponse->getStatusCode());

        $createTokenResponse = $this->applicationClient->makeFrontendCreateTokenRequest($userEmail, $userPassword);
        self::assertSame(200, $createTokenResponse->getStatusCode());
        self::assertSame('application/json', $createTokenResponse->getHeaderLine('content-type'));

        $createTokenData = json_decode($createTokenResponse->getBody()->getContents(), true);
        self::assertIsArray($createTokenData);

        $token = $createTokenData['token'] ?? null;
        $token = is_string($token) ? $token : null;

        $listResponse = $this->applicationClient->makeFrontendListApiKeysRequest($token);
        self::assertSame(200, $listResponse->getStatusCode());
        self::assertSame('application/json', $listResponse->getHeaderLine('content-type'));
    }

    abstract protected function getAdminToken(): string;
}
