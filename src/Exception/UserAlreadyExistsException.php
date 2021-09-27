<?php

declare(strict_types=1);

namespace App\Exception;

use App\Entity\User;

class UserAlreadyExistsException extends \Exception
{
    public function __construct(
        private User $user
    ) {
        parent::__construct(sprintf(
            'User "%s" already exists',
            $user->getUserIdentifier()
        ));
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
