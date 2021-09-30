<?php

declare(strict_types=1);

namespace App\Request;

use Symfony\Component\HttpFoundation\Request;

class RevokeRefreshTokenRequest extends AbstractEncapsulatingRequest
{
    public const KEY_ID = 'id';

    private string $id = '';

    public function processRequest(Request $request): void
    {
        $requestData = $request->request;

        $this->id = (string) $requestData->get(self::KEY_ID);
    }

    public function getId(): string
    {
        return $this->id;
    }
}
