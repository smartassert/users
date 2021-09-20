<?php

declare(strict_types=1);

namespace App\Security\Admin;

use App\Security\UserRoleInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class User implements UserInterface
{
    public function __construct(
        private string $token
    ) {
    }

    /**
     * @return string[]
     */
    public function getRoles(): array
    {
        return [
            UserRoleInterface::ROLE_ADMIN,
        ];
    }

    public function getPassword(): ?string
    {
        return null;
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function eraseCredentials(): void
    {
    }

    public function getUsername(): string
    {
        return $this->getUserIdentifier();
    }

    public function getUserIdentifier(): string
    {
        return $this->token;
    }
}
