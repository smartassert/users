<?php

declare(strict_types=1);

namespace App\Tests\Application\Api\Token;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Services\ApiKeyFactory;
use App\Tests\Application\AbstractApplicationTest;
use webignition\ObjectReflector\ObjectReflector;

abstract class AbstractCreateTest extends AbstractApplicationTest
{
    /**
     * @dataProvider createBadMethodDataProvider
     */
    public function testCreateBadMethod(string $method): void
    {
        $response = $this->applicationClient->makeApiCreateTokenRequest($this->getAdminToken(), $method);

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

    public function testCreateUnauthorized(): void
    {
        $response = $this->applicationClient->makeApiCreateTokenRequest('invalid api key');

        self::assertSame(401, $response->getStatusCode());
        self::assertSame('', $response->getBody()->getContents());
    }

    public function testCreateSuccess(): void
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
        $apiKey = $apiKeyFactory->create('api key label', $user);
        $apiKeyId = ObjectReflector::getProperty($apiKey, 'id');
        self::assertIsString($apiKeyId);

        $response = $this->applicationClient->makeApiCreateTokenRequest($apiKeyId);

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('application/json', $response->getHeaderLine('content-type'));

        $responseData = json_decode($response->getBody()->getContents(), true);
        self::assertIsArray($responseData);
        self::assertArrayHasKey('token', $responseData);

        $verifyResponse = $this->applicationClient->makeApiVerifyTokenRequest($responseData['token']);
        self::assertSame(200, $verifyResponse->getStatusCode());
    }

    abstract protected function getAdminToken(): string;
}
