<?php

declare(strict_types=1);

namespace App\Tests\Application\Admin\Frontend\RefreshToken;

use App\Entity\User;
use App\Tests\Application\AbstractApplicationTestCase;
use App\Tests\Services\RefreshTokenManager;
use Symfony\Component\Uid\Ulid;

abstract class AbstractRevokeAllForUserTestCase extends AbstractApplicationTestCase
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
        $response = $this->applicationClient->makeRevokeAllRefreshTokensForUserRequest(
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
        $response = $this->applicationClient->makeRevokeAllRefreshTokensForUserRequest(
            'user-id',
            'invalid admin token'
        );

        self::assertSame(401, $response->getStatusCode());
        self::assertSame('', $response->getBody()->getContents());
    }

    public function testRevokeBadRequest(): void
    {
        $response = $this->applicationClient->makeRevokeAllRefreshTokensForUserRequest('', $this->getAdminToken());

        self::assertSame(400, $response->getStatusCode());
        self::assertSame('application/json', $response->getHeaderLine('content-type'));
        self::assertSame(
            [
                'message' => 'Value for field "id" missing',
            ],
            json_decode($response->getBody()->getContents(), true)
        );
    }

    /**
     * @dataProvider revokeSuccessDataProvider
     */
    public function testRevokeSuccess(int $refreshTokenCount): void
    {
        self::assertSame(0, $this->refreshTokenManager->count());

        $userIdentifier = 'user@example.com';
        $userPassword = 'password';

        $createUserResponse = $this->applicationClient->makeAdminCreateUserRequest(
            $userIdentifier,
            $userPassword,
            $this->getAdminToken()
        );
        self::assertSame(200, $createUserResponse->getStatusCode());

        $createUserResponseData = json_decode($createUserResponse->getBody()->getContents(), true);
        \assert(is_array($createUserResponseData));

        $userId = $createUserResponseData['id'] ?? '';

        for ($refreshTokenIndex = 0; $refreshTokenIndex < $refreshTokenCount; ++$refreshTokenIndex) {
            $this->refreshTokenManager->create(new User($userId, $userIdentifier, $userPassword));
        }

        self::assertSame($refreshTokenCount, $this->refreshTokenManager->count());

        $revokeResponse = $this->applicationClient->makeRevokeAllRefreshTokensForUserRequest(
            $userId,
            $this->getAdminToken()
        );
        self::assertSame(200, $revokeResponse->getStatusCode());
        self::assertSame(0, $this->refreshTokenManager->count());
    }

    /**
     * @return array<mixed>
     */
    public function revokeSuccessDataProvider(): array
    {
        return [
            'one' => [
                'refreshTokenCount' => 1,
            ],
            'two' => [
                'refreshTokenCount' => 2,
            ],
            'three' => [
                'refreshTokenCount' => 3,
            ],
        ];
    }

    abstract protected function getAdminToken(): string;
}
