<?php

declare(strict_types=1);

namespace App\Security;

interface TokenInterface
{
    public const CLAIM_USER_ID = 'sub';
    public const CLAIM_EMAIL = 'email';
    public const CLAIM_USER_IDENTIFIER = self::CLAIM_EMAIL;
}
