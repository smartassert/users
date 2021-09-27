<?php

declare(strict_types=1);

namespace App\Security;

use Symfony\Component\Security\Core\User\UserInterface;

abstract class AbstractUser implements UserInterface, \JsonSerializable
{
    /**
     * @param array<UserRoleInterface::ROLE_*> $roles
     */
    public function __construct(
        protected string $id,
        protected string $userIdentifier,
        protected array $roles
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return array<UserRoleInterface::ROLE_*>
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getPassword()
    {
        return null;
    }

    public function getSalt()
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // Required by UserInterface.
        // Intentionally empty implementation.
    }

    public function getUsername(): string
    {
        return $this->getUserIdentifier();
    }

    public function getUserIdentifier(): string
    {
        return $this->userIdentifier;
    }

    /**
     * @return array{"id": string, "email": string}
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->userIdentifier,
        ];
    }
}
