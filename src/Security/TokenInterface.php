<?php

declare(strict_types=1);

namespace App\Security;

interface TokenInterface
{
    public const CLAIM_USER_ID = 'sub';
    public const CLAIM_AUDIENCE = 'aud';
}
