<?php

declare(strict_types=1);

namespace App\Tests\Functional\Services;

use App\Entity\User;
use App\Services\UserRefreshTokenManager;
use App\Tests\Functional\AbstractBaseFunctionalTestCase;
use App\Tests\Services\RefreshTokenManager;
use App\Tests\Services\TestUserFactory;

class UserRefreshTokenManagerTest extends AbstractBaseFunctionalTestCase
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
        self::assertSame(0, $this->refreshTokenManager->count());

        $this->userRefreshTokenManager->deleteByUserId('invalid');

        self::assertSame(0, $this->refreshTokenManager->count());
    }

    public function testDeleteByUserIdUserHasNoRefreshToken(): void
    {
        self::assertSame(0, $this->refreshTokenManager->count());

        $this->userRefreshTokenManager->deleteByUserId($this->user->getId());

        self::assertSame(0, $this->refreshTokenManager->count());
    }

    /**
     * @dataProvider deleteByUserIdHasRefreshTokensDataProvider
     */
    public function testDeleteByUserIdUserHasRefreshTokens(int $refreshTokenCount): void
    {
        for ($refreshTokenIndex = 0; $refreshTokenIndex < $refreshTokenCount; ++$refreshTokenIndex) {
            $this->refreshTokenManager->create($this->user);
        }

        self::assertSame($refreshTokenCount, $this->refreshTokenManager->count());

        $this->userRefreshTokenManager->deleteByUserId($this->user->getId());

        self::assertSame(0, $this->refreshTokenManager->count());
    }

    /**
     * @return array<mixed>
     */
    public static function deleteByUserIdHasRefreshTokensDataProvider(): array
    {
        return [
            'one' => [
                'refreshTokenCount' => 1,
            ],
            'two' => [
                'refreshTokenCount' => 2,
            ],
            'three' => [
                'refreshTokenCount' => 3,
            ],
        ];
    }

    private function removeAllRefreshTokens(): void
    {
        $this->refreshTokenManager->removeAll();
    }
}
