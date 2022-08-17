<?php

declare(strict_types=1);

namespace App\Tests\Application\Admin\Frontend\RefreshToken;

use App\Entity\User;
use App\Tests\Application\AbstractApplicationTest;
use App\Tests\Services\RefreshTokenManager;
use Symfony\Component\Uid\Ulid;

abstract class AbstractRevokeTest extends AbstractApplicationTest
{
    private RefreshTokenManager $refreshTokenManager;

    protected function setUp(): void
    {
        parent::setUp();

        $refreshTokenManager = self::getContainer()->get(RefreshTokenManager::class);
        \assert($refreshTokenManager instanceof RefreshTokenManager);
        $this->refreshTokenManager = $refreshTokenManager;

        $this->refreshTokenManager->removeAll();
    }

    /**
     * @dataProvider revokeBadMethodDataProvider
     */
    public function testRevokeBadMethod(string $method): void
    {
        $response = $this->applicationClient->makeAdminRevokeRefreshTokenRequest(
            (string) new Ulid(),
            $this->getAdminToken(),
            $method
        );

        self::assertSame(405, $response->getStatusCode());
    }

    /**
     * @return array<mixed>
     */
    public function revokeBadMethodDataProvider(): array
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

    public function testRevokeUnauthorized(): void
    {
        $response = $this->applicationClient->makeAdminRevokeRefreshTokenRequest('user-id', 'invalid admin token');

        self::assertSame(401, $response->getStatusCode());
        self::assertSame('', $response->getBody()->getContents());
    }

    public function testRevokeBadRequest(): void
    {
        $response = $this->applicationClient->makeAdminRevokeRefreshTokenRequest('', $this->getAdminToken());

        self::assertSame(400, $response->getStatusCode());
        self::assertSame('application/json', $response->getHeaderLine('content-type'));
        self::assertSame(
            [
                'message' => 'Value for field "id" missing',
            ],
            json_decode($response->getBody()->getContents(), true)
        );
    }

    public function testRevokeSuccess(): void
    {
        self::assertSame(0, $this->refreshTokenManager->count());

        $userEmail = 'user@example.com';
        $userPassword = 'password';

        $createUserResponse = $this->applicationClient->makeAdminCreateUserRequest(
            $userEmail,
            $userPassword,
            $this->getAdminToken()
        );
        self::assertSame(200, $createUserResponse->getStatusCode());

        $createUserResponseData = json_decode($createUserResponse->getBody()->getContents(), true);
        \assert(is_array($createUserResponseData));

        $userData = $createUserResponseData['user'] ?? [];
        $userId = $userData['id'] ?? '';

        $this->refreshTokenManager->create(new User($userId, $userEmail, $userPassword));
        self::assertSame(1, $this->refreshTokenManager->count());

        $revokeResponse = $this->applicationClient->makeAdminRevokeRefreshTokenRequest($userId, $this->getAdminToken());
        self::assertSame(200, $revokeResponse->getStatusCode());
        self::assertSame(0, $this->refreshTokenManager->count());
    }

    abstract protected function getAdminToken(): string;
}