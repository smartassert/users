<?php

declare(strict_types=1);

namespace App\Tests\Functional\Entity;

use App\Entity\User;
use App\Tests\Functional\AbstractBaseFunctionalTest;
use Doctrine\ORM\EntityManagerInterface;

class UserTest extends AbstractBaseFunctionalTest
{
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        parent::setUp();

        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        \assert($entityManager instanceof EntityManagerInterface);
        $this->entityManager = $entityManager;
    }

    public function testEntityMapping(): void
    {
        $repository = $this->entityManager->getRepository(User::class);
        self::assertCount(0, $repository->findAll());

        $entity = new User(
            '01234567890123456789012345678901',
            'user@example.com',
            'hashed-password'
        );

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        $entities = $repository->findAll();

        self::assertCount(1, $entities);
        self::assertEquals($entity, $entities[0]);
    }
}
