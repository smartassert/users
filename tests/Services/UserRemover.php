<?php

declare(strict_types=1);

namespace App\Tests\Services;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

class UserRemover
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository,
    ) {
    }

    public function removeAll(): void
    {
        $users = $this->userRepository->findAll();
        array_walk($users, function (User $user) {
            $this->entityManager->remove($user);
        });

        $this->entityManager->flush();
    }
}
