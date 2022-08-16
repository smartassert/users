<?php

declare(strict_types=1);

namespace App\Request;

use Symfony\Component\HttpFoundation\Request;

class RevokeRefreshTokenRequest
{
    public const KEY_ID = 'id';

    public function __construct(
        private string $id,
    ) {
    }

    public static function create(Request $request): RevokeRefreshTokenRequest
    {
        return new RevokeRefreshTokenRequest(
            (string) $request->request->get(self::KEY_ID)
        );
    }

    public function getId(): string
    {
        return $this->id;
    }
}
