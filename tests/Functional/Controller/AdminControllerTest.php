<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Request\CreateUserRequest;
use App\Tests\Functional\AbstractBaseWebTestCase;
use App\Tests\Services\TestUserFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class AdminControllerTest extends AbstractBaseWebTestCase
{
    private string $adminToken;
    private UserRepository $userRepository;
    private TestUserFactory $testUserFactory;
    private string $createUserUrl = '';

    protected function setUp(): void
    {
        parent::setUp();

        $adminToken = $this->getContainer()->getParameter('primary-admin-token');
        $this->adminToken = is_string($adminToken) ? $adminToken : '';

        $userRepository = self::getContainer()->get(UserRepository::class);
        \assert($userRepository instanceof UserRepository);
        $this->userRepository = $userRepository;

        $testUserFactory = self::getContainer()->get(TestUserFactory::class);
        \assert($testUserFactory instanceof TestUserFactory);
        $this->testUserFactory = $testUserFactory;

        $createUserUrl = self::getContainer()->getParameter('route-admin-user-create');
        if (is_string($createUserUrl)) {
            $this->createUserUrl = $createUserUrl;
        }
    }

    /**
     * @dataProvider createUserUnauthorizedDataProvider
     */
    public function testCreateUserUnauthorized(?string $token): void
    {
        $response = $this->makeCreateUserRequest('', '', $token);

        self::assertSame(401, $response->getStatusCode());
    }

    /**
     * @return array<mixed>
     */
    public function createUserUnauthorizedDataProvider(): array
    {
        return [
            'no token' => [
                'token' => null,
            ],
            'invalid token' => [
                'token' => 'invalid-admin-token',
            ],
        ];
    }

    public function testCreateUserUserAlreadyExists(): void
    {
        $this->removeAllUsers();

        $user = $this->testUserFactory->create();
        $response = $this->makeCreateUserRequest($user->getUserIdentifier(), $user->getPassword(), $this->adminToken);

        self::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertSame(
            [
                'message' => 'User already exists',
                'user' => $user->jsonSerialize()
            ],
            json_decode((string) $response->getContent(), true)
        );
    }

    public function testCreateUserSuccess(): void
    {
        $this->removeAllUsers();

        $email = 'email';
        $password = 'password';

        $response = $this->makeCreateUserRequest($email, $password, $this->adminToken);

        $expectedUser = $this->userRepository->findByEmail($email);
        self::assertInstanceOf(User::class, $expectedUser);

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertSame(
            [
                'user' => $expectedUser->jsonSerialize(),
            ],
            json_decode((string) $response->getContent(), true)
        );
    }

    private function makeCreateUserRequest(string $email, string $password, ?string $token): Response
    {
        $headers = [];
        if (is_string($token)) {
            $headers['HTTP_AUTHORIZATION'] = $token;
        }

        $this->client->request(
            'POST',
            $this->createUserUrl,
            [
                CreateUserRequest::KEY_EMAIL => $email,
                CreateUserRequest::KEY_PASSWORD => $password,
            ],
            [],
            $headers,
        );

        return $this->client->getResponse();
    }
}
