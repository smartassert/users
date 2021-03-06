<?php

declare(strict_types=1);

namespace App\Security;

interface UserRoleInterface
{
    public const ROLE_USER = 'ROLE_USER';
    public const ROLE_ADMIN = 'ROLE_ADMIN';
}
