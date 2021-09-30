<?php

declare(strict_types=1);

namespace App\Tests\Functional\Services;

use App\Entity\User;
use App\Services\UserRefreshTokenManager;
use App\Tests\Functional\AbstractBaseFunctionalTest;
use App\Tests\Services\RefreshTokenManager;
use App\Tests\Services\TestUserFactory;

class UserRefreshTokenManagerTest extends AbstractBaseFunctionalTest
{
    private UserRefreshTokenManager $userRefreshTokenManager;
    private RefreshTokenManager $refreshTokenManager;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $userRefreshTokenManager = self::getContainer()->get(UserRefreshTokenManager::class);
        \assert($userRefreshTokenManager instanceof UserRefreshTokenManager);
        $this->userRefreshTokenManager = $userRefreshTokenManager;

        $refreshTokenManager = self::getContainer()->get(RefreshTokenManager::class);
        \assert($refreshTokenManager instanceof RefreshTokenManager);
        $this->refreshTokenManager = $refreshTokenManager;

        $this->removeAllUsers();
        $this->removeAllRefreshTokens();

        $testUserFactory = self::getContainer()->get(TestUserFactory::class);
        \assert($testUserFactory instanceof TestUserFactory);
        $this->user = $testUserFactory->create();
    }

    protected function tearDown(): void
    {
        $this->removeAllRefreshTokens();

        parent::tearDown();
    }

    public function testDeleteByUserIdInvalidUserId(): void
    {
        self::assertFalse(
            $this->userRefreshTokenManager->deleteByUserId('invalid')
        );
    }

    public function testDeleteByUserIdUserHasNoRefreshToken(): void
    {
        self::assertFalse(
            $this->userRefreshTokenManager->deleteByUserId($this->user->getId())
        );
    }

    public function testDeleteByUserIdUserHasRefreshToken(): void
    {
        $this->refreshTokenManager->create($this->user);

        self::assertTrue(
            $this->userRefreshTokenManager->deleteByUserId($this->user->getId())
        );
    }

    private function removeAllRefreshTokens(): void
    {
        $this->refreshTokenManager->removeAll();
    }
}
