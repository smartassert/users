<?php

declare(strict_types=1);

namespace App\Security;

use Lexik\Bundle\JWTAuthenticationBundle\Security\User\JWTUserInterface;

class JWTUser extends AbstractUser implements JWTUserInterface, \JsonSerializable
{
    /**
     * @param array<mixed> $payload
     * @param mixed        $username
     */
    public static function createFromPayload($username, array $payload): JWTUserInterface
    {
        $id = $payload[TokenInterface::CLAIM_USER_ID] ?? '';
        $roles = $payload['roles'] ?? [];

        return new JWTUser($id, $username, $roles);
    }
}