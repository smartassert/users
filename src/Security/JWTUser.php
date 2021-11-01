<?php

declare(strict_types=1);

namespace App\Security;

use App\Exception\InvalidJwtUserPayloadException;
use App\Exception\InvalidJwtUserUsernameException;
use Lexik\Bundle\JWTAuthenticationBundle\Security\User\JWTUserInterface;

class JWTUser extends AbstractUser implements JWTUserInterface, \JsonSerializable
{
    /**
     * @param array<mixed> $payload
     * @param mixed        $username
     *
     * @throws InvalidJwtUserUsernameException
     * @throws InvalidJwtUserPayloadException
     */
    public static function createFromPayload($username, array $payload): JWTUserInterface
    {
        if (!is_string($username)) {
            throw new InvalidJwtUserUsernameException($username, $payload);
        }

        $id = $payload[TokenInterface::CLAIM_USER_ID] ?? null;
        if (!is_string($id)) {
            throw new InvalidJwtUserPayloadException($username, $payload, TokenInterface::CLAIM_USER_ID);
        }

        $roles = $payload['roles'] ?? null;
        if (!is_array($roles)) {
            throw new InvalidJwtUserPayloadException($username, $payload, 'roles');
        }

        return new JWTUser($id, $username, $roles);
    }
}
