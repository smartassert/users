<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\Functional\AbstractBaseWebTestCase;
use App\Tests\Services\TestUserFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class AdminControllerTest extends AbstractBaseWebTestCase
{
    private string $adminToken;
    private UserRepository $userRepository;
    private TestUserFactory $testUserFactory;

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
    }

    /**
     * @dataProvider createUserUnauthorizedDataProvider
     */
    public function testCreateUserUnauthorized(?string $token): void
    {
        $response = $this->application->makeAdminCreateUserRequest('', '', $token);

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
        $response = $this->application->makeAdminCreateUserRequest(
            $user->getUserIdentifier(),
            'password',
            $this->adminToken
        );

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

        $response = $this->application->makeAdminCreateUserRequest($email, $password, $this->adminToken);

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
}
