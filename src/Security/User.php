<?php

declare(strict_types=1);

namespace App\Security;

use Symfony\Component\Security\Core\User\UserInterface;

class User implements UserInterface, \JsonSerializable
{
    /**
     * @param array<UserRoleInterface::ROLE_*> $roles
     */
    public function __construct(
        private string $userIdentifier,
        private string $id,
        private array $roles
    ) {
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
