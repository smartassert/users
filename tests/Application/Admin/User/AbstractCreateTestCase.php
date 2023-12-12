<?php

declare(strict_types=1);

namespace App\Tests\Application\Admin\User;

use App\Entity\User;
use App\Repository\ApiKeyRepository;
use App\Repository\UserRepository;
use App\Tests\Application\AbstractApplicationTestCase;
use Psr\Http\Message\ResponseInterface;

abstract class AbstractCreateTestCase extends AbstractApplicationTestCase
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
            'identifier',
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
            'identifier',
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
        ?string $identifier,
        ?string $password,
        string $expectedMissingField
    ): void {
        $response = $this->applicationClient->makeAdminCreateUserRequest(
            $identifier,
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
            'identifier missing' => [
                'identifier' => null,
                'password' => 'non-empty value',
                'expectedMissingField' => 'identifier',
            ],
            'password missing' => [
                'identifier' => 'non-empty value',
                'password' => null,
                'expectedMissingField' => 'password',
            ],
        ];
    }

    public function testCreateUserAlreadyExists(): void
    {
        $identifier = 'user@example.com';
        $password = 'password';

        $successResponse = $this->applicationClient->makeAdminCreateUserRequest(
            $identifier,
            $password,
            $this->getAdminToken()
        );

        self::assertSame(200, $successResponse->getStatusCode());
        self::assertSame(1, $this->userRepository->count([]));
        self::assertSame(1, $this->apiKeyRepository->count([]));

        $badRequestResponse = $this->applicationClient->makeAdminCreateUserRequest(
            $identifier,
            $password,
            $this->getAdminToken()
        );

        self::assertSame(409, $badRequestResponse->getStatusCode());
        self::assertSame(1, $this->userRepository->count([]));
        self::assertSame(1, $this->apiKeyRepository->count([]));
        $this->verifyCreateUserResponse($badRequestResponse, $identifier);
    }

    public function testCreateSuccess(): void
    {
        self::assertSame(0, $this->userRepository->count([]));
        self::assertSame(0, $this->apiKeyRepository->count([]));

        $identifier = 'user@example.com';
        $password = 'password';

        $response = $this->applicationClient->makeAdminCreateUserRequest(
            $identifier,
            $password,
            $this->getAdminToken()
        );

        self::assertSame(200, $response->getStatusCode());
        self::assertSame(1, $this->userRepository->count([]));
        self::assertSame(1, $this->apiKeyRepository->count([]));

        $this->verifyCreateUserResponse($response, $identifier);
    }

    abstract protected function getAdminToken(): string;

    private function verifyCreateUserResponse(ResponseInterface $response, string $expectedUserIdentifier): void
    {
        self::assertSame('application/json', $response->getHeaderLine('content-type'));

        $responseData = json_decode($response->getBody()->getContents(), true);
        self::assertIsArray($responseData);

        $user = $this->userRepository->findAll()[0];
        self::assertInstanceOf(User::class, $user);

        self::assertSame(
            [
                'id' => $user->getId(),
                'user-identifier' => $expectedUserIdentifier,
            ],
            $responseData
        );
    }
}
