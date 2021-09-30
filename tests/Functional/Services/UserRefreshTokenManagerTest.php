<?php

declare(strict_types=1);

namespace App\Tests\Functional\Services;

use App\Entity\User;
use App\Services\UserRefreshTokenManager;
use App\Tests\Functional\AbstractBaseFunctionalTest;
use App\Tests\Services\TestUserFactory;
use Doctrine\ORM\EntityManagerInterface;
use Gesdinet\JWTRefreshTokenBundle\Entity\RefreshToken;
use Gesdinet\JWTRefreshTokenBundle\Entity\RefreshTokenRepository;
use Gesdinet\JWTRefreshTokenBundle\Generator\RefreshTokenGeneratorInterface;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;

class UserRefreshTokenManagerTest extends AbstractBaseFunctionalTest
{
    private UserRefreshTokenManager $userRefreshTokenManager;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $userRefreshTokenManager = self::getContainer()->get(UserRefreshTokenManager::class);
        \assert($userRefreshTokenManager instanceof UserRefreshTokenManager);
        $this->userRefreshTokenManager = $userRefreshTokenManager;

        $this->removeAllUsers();
        $this->removeAllRefreshTokens();

        $testUserFactory = self::getContainer()->get(TestUserFactory::class);
        \assert($testUserFactory instanceof TestUserFactory);
        $this->user = $testUserFactory->create();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->removeAllRefreshTokens();
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
        $refreshTokenGenerator = self::getContainer()->get(RefreshTokenGeneratorInterface::class);
        \assert($refreshTokenGenerator instanceof RefreshTokenGeneratorInterface);

        $refreshTokenManager = self::getContainer()->get(RefreshTokenManagerInterface::class);
        \assert($refreshTokenManager instanceof RefreshTokenManagerInterface);

        $refreshTokenManager->save(
            $refreshTokenGenerator->createForUserWithTtl($this->user, 3600)
        );

        self::assertTrue(
            $this->userRefreshTokenManager->deleteByUserId($this->user->getId())
        );
    }

    private function removeAllRefreshTokens(): void
    {
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        \assert($entityManager instanceof EntityManagerInterface);

        $refreshTokenRepository = $entityManager->getRepository(RefreshToken::class);
        \assert($refreshTokenRepository instanceof RefreshTokenRepository);

        $refreshTokens = $refreshTokenRepository->findAll();

        foreach ($refreshTokens as $refreshToken) {
            $entityManager->remove($refreshToken);
            $entityManager->flush();
        }
    }
}
