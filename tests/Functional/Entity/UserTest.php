<?php

declare(strict_types=1);

namespace App\Tests\Functional\Entity;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\Functional\AbstractBaseFunctionalTestCase;
use App\Tests\Services\TestUserFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Security\User\EntityUserProvider;

class UserTest extends AbstractBaseFunctionalTestCase
{
    private EntityManagerInterface $entityManager;
    private UserRepository $userRepository;
    private TestUserFactory $testUserFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        \assert($entityManager instanceof EntityManagerInterface);
        $this->entityManager = $entityManager;

        $userRepository = self::getContainer()->get(UserRepository::class);
        \assert($userRepository instanceof UserRepository);
        $this->userRepository = $userRepository;

        $testUserFactory = self::getContainer()->get(TestUserFactory::class);
        \assert($testUserFactory instanceof TestUserFactory);
        $this->testUserFactory = $testUserFactory;

        $this->removeAllUsers();
    }

    public function testEntityMapping(): void
    {
        self::assertCount(0, $this->userRepository->findAll());

        $user = $this->testUserFactory->create();
        $this->entityManager->detach($user);

        $users = $this->userRepository->findAll();

        self::assertCount(1, $users);
        self::assertEquals($user, $users[0]);
    }

    public function testGetRolesDoesNotThrowUninitializedTypedPropertyError(): void
    {
        $this->testUserFactory->create();
        $this->entityManager->clear();

        $frontendUserProvider = self::getContainer()->get('security.user.provider.concrete.frontend_user_provider');
        \assert($frontendUserProvider instanceof EntityUserProvider);

        $user = $frontendUserProvider->loadUserByIdentifier($this->testUserFactory->getCredentials()['userIdentifier']);

        try {
            self::assertSame(User::ROLES, $user->getRoles());
        } catch (\Error $exception) {
            $this->fail($exception->getMessage());
        }
    }
}
