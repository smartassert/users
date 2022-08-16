<?php

declare(strict_types=1);

namespace App\Request;

use Symfony\Component\HttpFoundation\Request;

class CreateUserRequest
{
    public const KEY_EMAIL = 'email';
    public const KEY_PASSWORD = 'password';

    /**
     * @param non-empty-string|null $email
     * @param non-empty-string|null $password
     */
    public function __construct(
        public readonly ?string $email,
        public readonly ?string $password,
    ) {
    }

    public static function create(Request $request): CreateUserRequest
    {
        $email = $request->request->get(self::KEY_EMAIL);
        $email = is_string($email) ? trim($email) : null;
        $email = '' === $email ? null : $email;

        $password = $request->request->get(self::KEY_PASSWORD);
        $password = is_string($password) ? $password : null;
        $password = '' === $password ? null : $password;

        return new CreateUserRequest($email, $password);
    }

    /**
     * @return non-empty-string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @return non-empty-string|null
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }
}
