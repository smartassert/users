<?php

declare(strict_types=1);

namespace App\Request;

class CreateUserRequest
{
    public const KEY_IDENTIFIER = 'identifier';
    public const KEY_PASSWORD = 'password';

    /**
     * @param non-empty-string $identifier
     * @param non-empty-string $password
     */
    public function __construct(
        public readonly string $identifier,
        public readonly string $password,
    ) {
    }
}
