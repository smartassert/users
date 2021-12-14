<?php

declare(strict_types=1);

namespace App\Request;

use Symfony\Component\HttpFoundation\Request;
use webignition\EncapsulatingRequestResolverBundle\Model\EncapsulatingRequestInterface;

class RevokeRefreshTokenRequest implements EncapsulatingRequestInterface
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
