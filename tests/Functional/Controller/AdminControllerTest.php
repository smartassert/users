<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\Functional\AbstractBaseWebTestCase;
use App\Tests\Services\ApplicationResponseAsserter;
use App\Tests\Services\RefreshTokenManager;
use App\Tests\Services\TestUserFactory;

class AdminControllerTest extends AbstractBaseWebTestCase
{
    private string $adminToken;
    private UserRepository $userRepository;
    private TestUserFactory $testUserFactory;
    private RefreshTokenManager $refreshTokenManager;
    private ApplicationResponseAsserter $applicationResponseAsserter;

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

        $refreshTokenManager = self::getContainer()->get(RefreshTokenManager::class);
        \assert($refreshTokenManager instanceof RefreshTokenManager);
        $this->refreshTokenManager = $refreshTokenManager;

        $applicationResponseAsserter = self::getContainer()->get(ApplicationResponseAsserter::class);
        \assert($applicationResponseAsserter instanceof ApplicationResponseAsserter);
        $this->applicationResponseAsserter = $applicationResponseAsserter;
    }

    public function testCreateUserUnauthorized(): void
    {
        $response = $this->application->makeAdminCreateUserRequest(
            'user-email',
            'user-password',
            'invalid-token'
        );

        self::assertSame(401, $response->getStatusCode());
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

        $this->applicationResponseAsserter->assertCreateUserUserAlreadyExistsResponse($response, $user);
    }

    public function testCreateUserSuccess(): void
    {
        $this->removeAllUsers();

        $email = 'email';
        $password = 'password';

        $response = $this->application->makeAdminCreateUserRequest($email, $password, $this->adminToken);

        $expectedUser = $this->userRepository->findByEmail($email);
        self::assertInstanceOf(User::class, $expectedUser);

        $this->applicationResponseAsserter->assertCreateUserSuccessResponse($response, $expectedUser);
    }

    public function testRevokeRefreshToken(): void
    {
        $this->removeAllUsers();
        $this->refreshTokenManager->removeAll();

        $user = $this->testUserFactory->create();

        self::assertSame(0, $this->refreshTokenManager->count());
        $this->refreshTokenManager->create($user);

        self::assertSame(1, $this->refreshTokenManager->count());

        $response = $this->application->makeAdminRevokeRefreshTokenRequest($user->getId(), $this->adminToken);

        self::assertSame(200, $response->getStatusCode());
        self::assertSame(0, $this->refreshTokenManager->count());
    }
}
