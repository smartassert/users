<?php

declare(strict_types=1);

namespace App\Security;

interface AudienceClaimInterface
{
    public const AUD_DEFAULT = self::AUD_FRONTEND;
    public const AUD_FRONTEND = 'frontend';
    public const AUD_API = 'api';
}
