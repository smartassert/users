<?php

declare(strict_types=1);

namespace App\Tests\Functional\Entity;

use App\Entity\User;
use App\Tests\Functional\AbstractBaseFunctionalTestCase;
use App\Tests\Services\TestUserFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Security\User\EntityUserProvider;

class UserTest extends AbstractBaseFunctionalTestCase
{
    private EntityManagerInterface $entityManager;
    private TestUserFactory $testUserFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        \assert($entityManager instanceof EntityManagerInterface);
        $this->entityManager = $entityManager;

        $testUserFactory = self::getContainer()->get(TestUserFactory::class);
        \assert($testUserFactory instanceof TestUserFactory);
        $this->testUserFactory = $testUserFactory;

        $this->removeAllUsers();
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
