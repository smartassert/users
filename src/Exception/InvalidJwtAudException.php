<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

class InvalidJwtAudException extends AuthenticationException
{
    public function __construct(
        public readonly ?string $firewallName,
        public readonly ?string $jwtAud
    ) {
        parent::__construct(sprintf(
            'Invalid JWT aud claim "%s" for firewall "%s"',
            $jwtAud,
            $firewallName
        ));
    }
}
