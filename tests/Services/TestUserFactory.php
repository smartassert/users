<?php

declare(strict_types=1);

namespace App\Tests\Services;

use App\Entity\User;
use App\Services\UserFactory;

class TestUserFactory
{
    public function __construct(
        private UserFactory $userFactory,
        private string $testUserEmail,
        private string $testUserPassword,
    ) {
    }

    public function create(): User
    {
        return $this->userFactory->create($this->testUserEmail, $this->testUserPassword);
    }

    /**
     * @return array{"userIdentifier": string, "password": string}
     */
    public function getCredentials(): array
    {
        return [
            'userIdentifier' => $this->testUserEmail,
            'password' => $this->testUserPassword,
        ];
    }
}
