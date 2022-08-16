<?php

declare(strict_types=1);

namespace App\Request;

use Symfony\Component\HttpFoundation\Request;

class CreateUserRequest
{
    public const KEY_EMAIL = 'email';
    public const KEY_PASSWORD = 'password';

    public function __construct(
        private string $email,
        private string $password,
    ) {
    }

    public static function create(Request $request): CreateUserRequest
    {
        return new CreateUserRequest(
            (string) $request->request->get(self::KEY_EMAIL),
            (string) $request->request->get(self::KEY_PASSWORD)
        );
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }
}
