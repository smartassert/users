<?php

declare(strict_types=1);

namespace App\Security;

interface AudClaimInterface
{
    public const AUD_FRONTEND = 'frontend';
    public const AUD_API = 'api';
}
