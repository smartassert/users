<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\ApiKey;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Ulid;

class ApiKeyFactory
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function create(string $label, User $user): ApiKey
    {
        $apiKey = new ApiKey(
            (string) new Ulid(),
            $label,
            $user
        );

        $this->entityManager->persist($apiKey);
        $this->entityManager->flush();

        return $apiKey;
    }
}
