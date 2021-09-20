<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Controller\AdminController;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Request\CreateUserRequest;
use App\Services\UserFactory;
use App\Tests\Services\UserRemover;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class AdminControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private string $adminToken;
    private UserFactory $userFactory;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();

        $adminToken = $this->getContainer()->getParameter('primary-admin-token');
        $this->adminToken = is_string($adminToken) ? $adminToken : '';

        $userFactory = self::getContainer()->get(UserFactory::class);
        \assert($userFactory instanceof UserFactory);
        $this->userFactory = $userFactory;

        $userRepository = self::getContainer()->get(UserRepository::class);
        \assert($userRepository instanceof UserRepository);
        $this->userRepository = $userRepository;
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

        $email = 'email';
        $password = 'password';

        $user = $this->userFactory->create($email, $password);

        $response = $this->makeCreateUserRequest($email, $password, $this->adminToken);

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
            AdminController::ROUTE_ADMIN_USER_CREATE,
            [
                CreateUserRequest::KEY_EMAIL => $email,
                CreateUserRequest::KEY_PASSWORD => $password,
            ],
            [],
            $headers,
        );

        return $this->client->getResponse();
    }

    private function removeAllUsers(): void
    {
        $userRemover = self::getContainer()->get(UserRemover::class);
        if ($userRemover instanceof UserRemover) {
            $userRemover->removeAll();
        }
    }
}
