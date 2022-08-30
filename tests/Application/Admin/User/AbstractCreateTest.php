<?php

declare(strict_types=1);

namespace App\Tests\Application\Admin\User;

use App\Entity\User;
use App\Repository\ApiKeyRepository;
use App\Repository\UserRepository;
use App\Tests\Application\AbstractApplicationTest;

abstract class AbstractCreateTest extends AbstractApplicationTest
{
    private UserRepository $userRepository;
    private ApiKeyRepository $apiKeyRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $userRepository = self::getContainer()->get(UserRepository::class);
        \assert($userRepository instanceof UserRepository);
        $this->userRepository = $userRepository;

        $apiKeyRepository = self::getContainer()->get(ApiKeyRepository::class);
        \assert($apiKeyRepository instanceof ApiKeyRepository);
        $this->apiKeyRepository = $apiKeyRepository;
    }

    /**
     * @dataProvider createBadMethodDataProvider
     */
    public function testCreateBadMethod(string $method): void
    {
        $response = $this->applicationClient->makeAdminCreateUserRequest(
            'email',
            'password',
            $this->getAdminToken(),
            $method
        );

        self::assertSame(405, $response->getStatusCode());
        self::assertSame(0, $this->apiKeyRepository->count([]));
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
        $response = $this->applicationClient->makeAdminCreateUserRequest(
            'email',
            'password',
            'invalid admin token'
        );

        self::assertSame(401, $response->getStatusCode());
        self::assertSame('', $response->getBody()->getContents());
        self::assertSame(0, $this->apiKeyRepository->count([]));
    }

    /**
     * @dataProvider createBadRequestValueMissingDataProvider
     */
    public function testCreateBadRequestValueMissing(
        ?string $email,
        ?string $password,
        string $expectedMissingField
    ): void {
        $response = $this->applicationClient->makeAdminCreateUserRequest(
            $email,
            $password,
            $this->getAdminToken()
        );

        self::assertSame(400, $response->getStatusCode());
        self::assertSame('application/json', $response->getHeaderLine('content-type'));
        self::assertSame(
            [
                'message' => 'Value for field "' . $expectedMissingField . '" missing',
            ],
            json_decode($response->getBody()->getContents(), true)
        );

        self::assertSame(0, $this->userRepository->count([]));
        self::assertSame(0, $this->apiKeyRepository->count([]));
    }

    /**
     * @return array<mixed>
     */
    public function createBadRequestValueMissingDataProvider(): array
    {
        return [
            'email missing' => [
                'email' => null,
                'password' => 'non-empty value',
                'expectedMissingField' => 'email',
            ],
            'password missing' => [
                'email' => 'non-empty value',
                'password' => null,
                'expectedMissingField' => 'password',
            ],
        ];
    }

    public function testCreateBadRequestUserAlreadyExists(): void
    {
        $email = 'user@example.com';
        $password = 'password';

        $successResponse = $this->applicationClient->makeAdminCreateUserRequest(
            $email,
            $password,
            $this->getAdminToken()
        );

        self::assertSame(200, $successResponse->getStatusCode());
        self::assertSame(1, $this->userRepository->count([]));
        self::assertSame(1, $this->apiKeyRepository->count([]));

        $badRequestResponse = $this->applicationClient->makeAdminCreateUserRequest(
            $email,
            $password,
            $this->getAdminToken()
        );

        self::assertSame(1, $this->userRepository->count([]));
        self::assertSame(1, $this->apiKeyRepository->count([]));
        self::assertSame(400, $badRequestResponse->getStatusCode());

        self::assertSame('application/json', $badRequestResponse->getHeaderLine('content-type'));

        $badRequestResponseData = json_decode($badRequestResponse->getBody()->getContents(), true);
        self::assertIsArray($badRequestResponseData);
        self::assertArrayHasKey('message', $badRequestResponseData);
        self::assertSame('User already exists', $badRequestResponseData['message']);

        $user = $this->userRepository->findAll()[0];
        self::assertInstanceOf(User::class, $user);

        self::assertArrayHasKey('user', $badRequestResponseData);
        self::assertSame(
            [
                'id' => $user->getId(),
                'user-identifier' => $email,
            ],
            $badRequestResponseData['user']
        );
    }

    public function testCreateSuccess(): void
    {
        self::assertSame(0, $this->userRepository->count([]));
        self::assertSame(0, $this->apiKeyRepository->count([]));

        $email = 'user@example.com';
        $password = 'password';

        $response = $this->applicationClient->makeAdminCreateUserRequest(
            $email,
            $password,
            $this->getAdminToken()
        );

        self::assertSame(200, $response->getStatusCode());
        self::assertSame(1, $this->userRepository->count([]));
        self::assertSame(1, $this->apiKeyRepository->count([]));

        $responseData = json_decode($response->getBody()->getContents(), true);
        self::assertIsArray($responseData);

        $user = $this->userRepository->findAll()[0];
        self::assertInstanceOf(User::class, $user);

        self::assertArrayHasKey('user', $responseData);
        self::assertSame(
            [
                'id' => $user->getId(),
                'user-identifier' => $email,
            ],
            $responseData['user']
        );
    }

    abstract protected function getAdminToken(): string;
}
