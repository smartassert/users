<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\User;
use App\Exception\UserAlreadyExistsException;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Uid\Ulid;

class UserFactory
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    /**
     * @throws UserAlreadyExistsException
     */
    public function create(string $email, string $plainPassword): User
    {
        $user = $this->userRepository->findOneBy([
            'email' => $email,
        ]);

        if ($user instanceof User) {
            throw new UserAlreadyExistsException($user);
        }

        $userWithoutPassword = new User(
            (string) new Ulid(),
            $email,
            ''
        );

        $hashedPassword = $this->passwordHasher->hashPassword($userWithoutPassword, $plainPassword);

        $user = new User(
            $userWithoutPassword->getId(),
            $userWithoutPassword->getEmail(),
            $hashedPassword
        );

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }
}
