<?php

declare(strict_types=1);

namespace App\Security;

use Lexik\Bundle\JWTAuthenticationBundle\Security\User\JWTUserInterface;

class User implements JWTUserInterface, \JsonSerializable
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
     * @param array<mixed> $payload
     * @param mixed        $username
     */
    public static function createFromPayload($username, array $payload): JWTUserInterface
    {
        $id = $payload['id'] ?? '';
        $roles = $payload['roles'] ?? [];

        return new User($username, $id, $roles);
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
