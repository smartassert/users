<?php

declare(strict_types=1);

namespace App\Tests\Functional\Entity;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserTest extends WebTestCase
{
    private EntityManagerInterface $entityManager;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        parent::setUp();

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

    public function testEntityMapping(): void
    {
        self::assertCount(0, $this->userRepository->findAll());

        $entity = new User(
            '01234567890123456789012345678901',
            'user@example.com',
            'hashed-password'
        );

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        $users = $this->userRepository->findAll();

        self::assertCount(1, $users);
        self::assertEquals($entity, $users[0]);
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
