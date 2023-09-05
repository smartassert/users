<?php

declare(strict_types=1);

namespace App\Tests\Application\Frontend\ApiKey;

use App\Entity\ApiKey;
use App\Entity\User;
use App\Repository\ApiKeyRepository;
use App\Repository\UserRepository;
use App\Tests\Application\AbstractApplicationTestCase;
use App\Tests\Services\ApplicationClient\Client;

abstract class AbstractListTestCase extends AbstractApplicationTestCase
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

    /**
     * @dataProvider listSuccessDataProvider
     */
    public function testListSuccess(
        callable $setup,
        string $userEmail,
        string $userPassword,
        callable $expectedResponseDataCreator,
    ): void {
        $setup($this->applicationClient);

        $this->applicationClient->makeAdminCreateUserRequest(
            $userEmail,
            $userPassword,
            $this->getAdminToken()
        );

        $userRepository = self::getContainer()->get(UserRepository::class);
        \assert($userRepository instanceof UserRepository);
        $user = $userRepository->findByEmail($userEmail);
        \assert($user instanceof User);

        $createTokenResponse = $this->applicationClient->makeFrontendCreateTokenRequest($userEmail, $userPassword);
        $createTokenData = json_decode($createTokenResponse->getBody()->getContents(), true);
        self::assertIsArray($createTokenData);
        $token = $createTokenData['token'] ?? null;
        $token = is_string($token) ? $token : null;

        $listResponse = $this->applicationClient->makeFrontendListApiKeysRequest($token);
        self::assertSame(200, $listResponse->getStatusCode());
        self::assertSame('application/json', $listResponse->getHeaderLine('content-type'));

        $listData = json_decode($listResponse->getBody()->getContents(), true);
        self::assertIsArray($listData);

        $apiKeyRepository = self::getContainer()->get(ApiKeyRepository::class);
        \assert($apiKeyRepository instanceof ApiKeyRepository);

        $expectedResponseData = $expectedResponseDataCreator($user, $apiKeyRepository);
        self::assertEquals($expectedResponseData, $listData);
    }

    /**
     * @return array<mixed>
     */
    public function listSuccessDataProvider(): array
    {
        return [
            'single user' => [
                'setup' => function (Client $applicationClient) {
                },
                'userEmail' => 'user@example.com',
                'userPassword' => 'password',
                'expectedResponseDataCreator' => function (User $user, ApiKeyRepository $apiKeyRepository) {
                    $apiKeys = $apiKeyRepository->findBy(['owner' => $user, 'label' => null]);
                    self::assertIsArray($apiKeys);
                    self::assertCount(1, $apiKeys);

                    return $this->createExpectedResponseDataFromApiKeyCollection($apiKeys);
                },
            ],
            'multiple users' => [
                'setup' => function (Client $applicationClient) {
                    $applicationClient->makeAdminCreateUserRequest(
                        'user2@example.com',
                        'password',
                        $this->getAdminToken()
                    );

                    $applicationClient->makeAdminCreateUserRequest(
                        'user3@example.com',
                        'password',
                        $this->getAdminToken()
                    );
                },
                'userEmail' => 'user@example.com',
                'userPassword' => 'password',
                'expectedResponseDataCreator' => function (User $user, ApiKeyRepository $apiKeyRepository) {
                    $apiKeys = $apiKeyRepository->findBy(['owner' => $user, 'label' => null]);
                    self::assertIsArray($apiKeys);
                    self::assertCount(1, $apiKeys);

                    return $this->createExpectedResponseDataFromApiKeyCollection($apiKeys);
                },
            ],
        ];
    }

    abstract protected function getAdminToken(): string;

    /**
     * @param ApiKey[] $apiKeys
     *
     * @return array<mixed>
     */
    private function createExpectedResponseDataFromApiKeyCollection(iterable $apiKeys): array
    {
        $expectedResponseData = [];

        foreach ($apiKeys as $apiKey) {
            $expectedResponseData[] = [
                'label' => $apiKey->label,
                'key' => $apiKey->id,
            ];
        }

        return $expectedResponseData;
    }
}
