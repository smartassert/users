<?php

declare(strict_types=1);

namespace App\Request;

use Symfony\Component\HttpFoundation\Request;

class RevokeRefreshTokenRequest
{
    public const KEY_ID = 'id';

    /**
     * @param null|non-empty-string $id
     */
    public function __construct(
        public readonly ?string $id,
    ) {
    }

    public static function create(Request $request): RevokeRefreshTokenRequest
    {
        $id = $request->request->get(self::KEY_ID);
        $id = is_string($id) ? trim($id) : null;

        return new RevokeRefreshTokenRequest('' === $id ? null : $id);
    }
}
