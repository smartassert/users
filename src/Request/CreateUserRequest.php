<?php

declare(strict_types=1);

namespace App\Request;

class CreateUserRequest
{
    public const KEY_EMAIL = 'email';
    public const KEY_PASSWORD = 'password';

    /**
     * @param null|non-empty-string $email
     * @param null|non-empty-string $password
     */
    public function __construct(
        public readonly ?string $email,
        public readonly ?string $password,
    ) {
    }
}
