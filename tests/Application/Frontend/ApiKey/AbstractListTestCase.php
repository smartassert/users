<?php

declare(strict_types=1);

namespace App\Tests\Application\Frontend\ApiKey;

use App\Entity\ApiKey;
use App\Entity\User;
use App\Repository\ApiKeyRepository;
use App\Repository\UserRepository;
use App\Security\IdentifiableUserInterface;
use App\Services\ApiKeyFactory;
use App\Tests\Application\AbstractApplicationTestCase;
use App\Tests\Services\ApplicationClient\Client;

abstract class AbstractListTestCase extends AbstractApplicationTestCase
{
    /**
     * @dataProvider listBadMethodDataProvider
     */
    public function testListBadMethod(string $method): void
    {
        $response = $this->applicationClient->makeListApiKeysRequest('token', $method);

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
        $response = $this->applicationClient->makeListApiKeysRequest($token);

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
        string $userIdentifier,
        string $userPassword,
        callable $expectedResponseDataCreator,
    ): void {
        $apiKeyFactory = self::getContainer()->get(ApiKeyFactory::class);
        \assert($apiKeyFactory instanceof ApiKeyFactory);

        $this->applicationClient->makeAdminCreateUserRequest(
            $userIdentifier,
            $userPassword,
            $this->getAdminToken()
        );

        $userRepository = self::getContainer()->get(UserRepository::class);
        \assert($userRepository instanceof UserRepository);
        $user = $userRepository->findByUserIdentifier($userIdentifier);
        \assert($user instanceof User);

        $setup($this->applicationClient, $apiKeyFactory, $user);

        $createTokenResponse = $this->applicationClient->makeCreateFrontendTokenRequest($userIdentifier, $userPassword);
        $createTokenData = json_decode($createTokenResponse->getBody()->getContents(), true);
        self::assertIsArray($createTokenData);
        $token = $createTokenData['token'] ?? null;
        $token = is_string($token) ? $token : null;

        $listResponse = $this->applicationClient->makeListApiKeysRequest($token);
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
            'single user, default api key only' => [
                'setup' => function (Client $applicationClient) {
                },
                'userIdentifier' => 'user@example.com',
                'userPassword' => 'password',
                'expectedResponseDataCreator' => function () {
                    return [];
                },
            ],
            'single user, single non-default api key' => [
                'setup' => function (Client $applicationClient, ApiKeyFactory $apiKeyFactory, User $user) {
                    $apiKeyFactory->create($user, 'label value');
                },
                'userIdentifier' => 'user@example.com',
                'userPassword' => 'password',
                'expectedResponseDataCreator' => function (
                    IdentifiableUserInterface $user,
                    ApiKeyRepository $apiKeyRepository
                ) {
                    $allApiKeys = $apiKeyRepository->findBy(['ownerId' => $user->getId()]);

                    $apiKeys = [];
                    foreach ($allApiKeys as $apiKey) {
                        if ('label value' === $apiKey->label) {
                            $apiKeys[] = $apiKey;
                        }
                    }

                    return $this->createExpectedResponseDataFromApiKeyCollection($apiKeys);
                },
            ],
            'multiple users, specific has has default api key only' => [
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
                'userIdentifier' => 'user@example.com',
                'userPassword' => 'password',
                'expectedResponseDataCreator' => function () {
                    return [];
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
