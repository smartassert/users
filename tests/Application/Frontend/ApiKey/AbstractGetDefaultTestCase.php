<?php

declare(strict_types=1);

namespace App\Tests\Application\Frontend\ApiKey;

use App\Entity\ApiKey;
use App\Entity\User;
use App\Repository\ApiKeyRepository;
use App\Repository\UserRepository;
use App\Tests\Application\AbstractApplicationTestCase;

abstract class AbstractGetDefaultTestCase extends AbstractApplicationTestCase
{
    /**
     * @dataProvider badMethodDataProvider
     */
    public function testGetDefaultBadMethod(string $method): void
    {
        $response = $this->applicationClient->makeGetDefaultApkKeyRequest('token', $method);

        self::assertSame(405, $response->getStatusCode());
    }

    /**
     * @return array<mixed>
     */
    public function badMethodDataProvider(): array
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
     * @dataProvider unauthorizedDataProvider
     */
    public function testGetDefaultUnauthorized(?string $token): void
    {
        $response = $this->applicationClient->makeGetDefaultApkKeyRequest($token);

        self::assertSame(401, $response->getStatusCode());
    }

    /**
     * @return array<mixed>
     */
    public function unauthorizedDataProvider(): array
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

    public function testGetDefaultSuccess(): void
    {
        $userIdentifier = md5((string) rand());
        $userPassword = md5((string) rand());

        $this->applicationClient->makeAdminCreateUserRequest(
            $userIdentifier,
            $userPassword,
            $this->getAdminToken()
        );

        $userRepository = self::getContainer()->get(UserRepository::class);
        \assert($userRepository instanceof UserRepository);
        $user = $userRepository->findByUserIdentifier($userIdentifier);
        \assert($user instanceof User);

        $createTokenResponse = $this->applicationClient->makeCreateFrontendTokenRequest($userIdentifier, $userPassword);
        $createTokenData = json_decode($createTokenResponse->getBody()->getContents(), true);
        self::assertIsArray($createTokenData);
        $token = $createTokenData['token'] ?? null;
        $token = is_string($token) ? $token : null;

        $response = $this->applicationClient->makeGetDefaultApkKeyRequest($token);
        self::assertSame(200, $response->getStatusCode());
        self::assertSame('application/json', $response->getHeaderLine('content-type'));

        $apiKeyRepository = self::getContainer()->get(ApiKeyRepository::class);
        \assert($apiKeyRepository instanceof ApiKeyRepository);

        $apKey = $apiKeyRepository->findOneBy(['owner' => $user]);
        \assert($apKey instanceof ApiKey);

        self::assertEquals(
            [
                'label' => null,
                'key' => $apKey->id,
            ],
            json_decode($response->getBody()->getContents(), true)
        );
    }

    abstract protected function getAdminToken(): string;
}
