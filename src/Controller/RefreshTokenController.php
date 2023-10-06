<?php

declare(strict_types=1);

namespace App\Controller;

use App\Request\RevokeRefreshTokenRequest;
use App\Response\BadRequestValueMissingResponse;
use App\Services\UserRefreshTokenManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

readonly class RefreshTokenController
{
    public function __construct(
        private UserRefreshTokenManager $tokenManager,
    ) {
    }

    public function revokeAllForUser(RevokeRefreshTokenRequest $request): Response
    {
        if (null === $request->id) {
            return new BadRequestValueMissingResponse('id');
        }

        $this->tokenManager->deleteByUserId($request->id);

        return new Response();
    }

    public function revoke(Request $request): Response
    {
        return new Response();
    }
}
