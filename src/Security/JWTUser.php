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

        $payloadRoles = $payload['roles'] ?? null;
        if (!is_array($payloadRoles)) {
            throw new InvalidJwtUserPayloadException($username, $payload, 'roles');
        }

        if (
            $payloadRoles !== [UserRoleInterface::ROLE_USER]
            && $payloadRoles !== [UserRoleInterface::ROLE_ADMIN]
            && $payloadRoles !== [UserRoleInterface::ROLE_USER, UserRoleInterface::ROLE_ADMIN]
            && $payloadRoles !== [UserRoleInterface::ROLE_ADMIN, UserRoleInterface::ROLE_USER]
        ) {
            throw new InvalidJwtUserPayloadException($username, $payload, 'roles');
        }

        return new JWTUser($id, $username, $payloadRoles);
    }
}
