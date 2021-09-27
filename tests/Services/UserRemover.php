<?php

declare(strict_types=1);

namespace App\Tests\Services;

use App\Entity\ApiKey;
use App\Entity\User;
use App\Repository\ApiKeyRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

class UserRemover
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository,
        private ApiKeyRepository $apiKeyRepository,
    ) {
    }

    public function removeAll(): void
    {
        $apiKeys = $this->apiKeyRepository->findAll();
        array_walk($apiKeys, function (ApiKey $apiKey) {
            $this->entityManager->remove($apiKey);
        });

        $users = $this->userRepository->findAll();
        array_walk($users, function (User $user) {
            $this->entityManager->remove($user);
        });

        $this->entityManager->flush();
    }
}
