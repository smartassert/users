<?php

declare(strict_types=1);

namespace App\Tests\Functional\Services;

use App\Entity\User;
use App\Exception\UserAlreadyExistsException;
use App\Repository\UserRepository;
use App\Services\UserFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Uid\Ulid;

class UserFactoryTest extends WebTestCase
{
    private UserFactory $userFactory;
    private EntityManagerInterface $entityManager;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $userFactory = self::getContainer()->get(UserFactory::class);
        \assert($userFactory instanceof UserFactory);
        $this->userFactory = $userFactory;

        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        \assert($entityManager instanceof EntityManagerInterface);
        $this->entityManager = $entityManager;

        $userRepository = self::getContainer()->get(UserRepository::class);
        \assert($userRepository instanceof UserRepository);
        $this->userRepository = $userRepository;

        $this->removeAllUsers();
    }

    protected function tearDown(): void
    {
        $this->removeAllUsers();

        parent::tearDown();
    }

    public function testCreate(): void
    {
        self::assertCount(0, $this->userRepository->findAll());

        $email = 'user@example.com';
        $plainPassword = 'password';

        $user = $this->userFactory->create($email, $plainPassword);

        self::assertNotEquals($plainPassword, $user->getPassword());
        self::assertTrue(Ulid::isValid($user->getId()));

        $retrievedUser = $this->userRepository->findOneBy([
            'email' => $email,
        ]);

        self::assertEquals($user, $retrievedUser);
    }

    public function testCreateUserAlreadyExists(): void
    {
        $email = 'user@example.com';
        $plainPassword = 'password';

        $user = $this->userFactory->create($email, $plainPassword);

        try {
            $this->userFactory->create($email, $plainPassword);
            $this->fail(UserAlreadyExistsException::class . ' not thrown');
        } catch (UserAlreadyExistsException $userAlreadyExistsException) {
            self::assertEquals($user, $userAlreadyExistsException->getUser());
        }
    }

    private function removeAllUsers(): void
    {
        $users = $this->userRepository->findAll();
        array_walk($users, function (User $user) {
            $this->entityManager->remove($user);
        });

        $this->entityManager->flush();
    }
}
