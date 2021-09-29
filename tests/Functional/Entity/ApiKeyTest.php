<?php

declare(strict_types=1);

namespace App\Tests\Functional\Entity;

use App\Entity\ApiKey;
use App\Entity\User;
use App\Repository\ApiKeyRepository;
use App\Tests\Functional\AbstractBaseFunctionalTest;
use Doctrine\ORM\EntityManagerInterface;

class ApiKeyTest extends AbstractBaseFunctionalTest
{
    private EntityManagerInterface $entityManager;
    private ApiKeyRepository $apiKeyRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        \assert($entityManager instanceof EntityManagerInterface);
        $this->entityManager = $entityManager;

        $apiKeyRepository = self::getContainer()->get(ApiKeyRepository::class);
        \assert($apiKeyRepository instanceof ApiKeyRepository);
        $this->apiKeyRepository = $apiKeyRepository;

        $this->removeAllUsers();
    }

    public function testEntityMapping(): void
    {
        $user = new User(
            '01234567890123456789012345678901',
            'user@example.com',
            'hashed-password'
        );

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        self::assertCount(0, $this->apiKeyRepository->findAll());

        $apiKey = new ApiKey(
            '98765432109876543210987654321098',
            'label value',
            $user
        );

        $this->entityManager->persist($apiKey);
        $this->entityManager->flush();
    }
}
