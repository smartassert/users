<?php

declare(strict_types=1);

namespace App\Request;

use Symfony\Component\HttpFoundation\Request;

class CreateUserRequest extends AbstractEncapsulatingRequest
{
    public const KEY_EMAIL = 'email';
    public const KEY_PASSWORD = 'password';

    private string $email = '';
    private string $password = '';

    public function processRequest(Request $request): void
    {
        $requestData = $request->request;

        $this->email = (string) $requestData->get(self::KEY_EMAIL);
        $this->password = (string) $requestData->get(self::KEY_PASSWORD);
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
