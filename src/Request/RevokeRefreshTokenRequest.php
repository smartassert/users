<?php

declare(strict_types=1);

namespace App\Request;

class RevokeRefreshTokenRequest
{
    public const KEY_ID = 'id';

    /**
     * @param non-empty-string $id
     */
    public function __construct(
        public readonly string $id,
    ) {
    }
}
